<?php
namespace App\Repository;

use App\Entity\Author;
use App\Entity\Quote;
use App\Exception\AuthorIdNotFoundException;
use App\Exception\NoQuotesFoundException;
use App\Exception\QuoteNotAddedException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class QuoteRepository
{
    private $emi, $quoteRepository, $authorRepository, $logger;

    public function __construct(EntityManagerInterface $emi, AuthorRepository $authorRepository, LoggerInterface $logger)
    {
        $this->emi = $emi;
        $this->quoteRepository = $this->emi->getRepository(Quote::class);
        $this->authorRepository = $authorRepository;
        $this->logger = $logger;
    }

    /**
     * Get a Random Quote
     *
     * @return Quote
     * @throws NoQuotesFoundException
     */
    public function getRandomQuote(): Quote
    {
        try {
            $count = $this->emi->createQueryBuilder()
                ->select('count(q.id)')
                ->from('App:Quote', 'q')
                ->getQuery()
                ->getSingleScalarResult();
            $offset = random_int(0, $count-1);

            $results = $this->quoteRepository->findBy([], null, 1, $offset);
            return $this->addAuthor($results[0]);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), [$e]);
            throw new NoQuotesFoundException($e);
        }
    }

    /**
     * Get Quote by ID
     *
     * @param string $id
     * @return Quote
     * @throws NoQuotesFoundException
     */
    public function getQuoteById(string $id): Quote
    {
        $quote = $this->quoteRepository->findOneBy(['id'=>$id]);
        if (!$quote) {
            $this->logger->error('Quote ID "%s" could not be found', $id);
            throw new NoQuotesFoundException;
        }
        return $this->addAuthor($quote);
    }

    /**
     * Get Quotes by Author ID
     *
     * @param string $id
     * @return Quote
     */
    public function getQuotesByAuthorId(string $id): Array
    {
        $author = $this->getAuthorById($id);
        $quotes = $this->quoteRepository->findBy(['authorId'=>$id]);
        foreach ($quotes as $k=>$q) {
            $quotes[$k] = $q->withAuthor($author);
        }
        return $quotes;
    }

    /**
     * Get Quotes by Author Name
     *
     * @param string $name
     * @return Array
     */
    public function getQuotesByAuthorName(string $name): Array
    {
        $this->logger->debug(sprintf('Getting quotes for %s', $name));

        try {
            $author = $this->authorRepository->findByName($name);
            $quotes = $this->quoteRepository->findBy(['authorId'=>$author->getId()]);
            $this->logger->debug('Found quotes', $quotes);
            foreach ($quotes as $k=>$q) {
                $quotes[$k] = $q->withAuthor($author);
            }
            $this->logger->debug('Returning quotes', $quotes);
            return $quotes;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), [$e]);
            return [];
        }
    }

    /**
     * Add Quote by Author Name
     *
     * @param string $name
     * @param string $text
     * @return Quote
     * @throws QuoteNotAddedException
     */
    public function addQuoteByAuthorName(string $name, string $text): Quote
    {
        $this->logger->debug(sprintf('Adding quote for %s', $name));

        try {
            $author = $this->authorRepository->findByName($name);
            $quote = Quote::newQuoteWithAuthor(Uuid::uuid4(), $text, $author);
            $this->emi->persist($quote);
            $this->emi->flush();

            $quotes = $this->getQuotesByAuthorName($author->getName());
            $author = $author->withQuotes(... $quotes);
            $this->authorRepository->updateAuthor($author);
            return $quote;

        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), [$e]);
            throw new QuoteNotAddedException($text, $name, $e);
        }
    }

    /**
     * Remove Quote by ID
     *
     * @param string $id
     * @return bool
     * @throws NoQuotesFoundException
     */
    public function removeById(string $id): bool
    {
        try {
            $quote = $this->quoteRepository->findOneBy(['id'=>$id]);
            if (!$quote) {
                throw new NoQuotesFoundException;
            }

            $author = $this->authorRepository->findById($quote->getAuthorId());
            $this->emi->remove($quote);
            $this->emi->flush();

            $quotes = $this->getQuotesByAuthorName($author->getName());
            $author = $author->withQuotes(... $quotes);
            $this->authorRepository->updateAuthor($author);

            return true;
        } catch (Exception $e) {
            $this->logger->error(sprintf('Error %d removing quote ID "%s": %s', $e->getCode(), $id, $e->getMessage()));
            return false;
        }
    }

    /**
     * Remove Quotes by Author ID
     *
     * @param string $id
     * @return bool
     */
    public function removeByAuthorId(string $id): bool
    {
        $this->logger->info(sprintf('Removing quotes for author ID "%s"', $id));
        try {
            $quotes = $this->quoteRepository->findBy(['authorId'=>$id]);
            foreach ($quotes as $quote) {
                $this->emi->merge($quote);
                $this->emi->remove($quote);
            }
            $this->emi->flush();

            $author = $this->authorRepository->findById($id);
            $quotes = $this->getQuotesByAuthorId($id);
            $author = $author->withQuotes(... $quotes);
            $this->authorRepository->updateAuthor($author);

            return true;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), [$e]);
            return false;
        }
    }

    /**
     * Update Quote by ID
     *
     * @param string $id
     * @param string|null $text
     * @param string|null $authorId
     * @return Quote
     * @throws AuthorIdNotFoundException
     * @throws NoQuotesFoundException
     */
    public function updateById(string $id, string $text=null, string $authorId=null): Quote
    {
        try {
            $quote = $this->getQuoteById($id);
            if ($text) {
                $quote = $quote->withText($text);
                $this->emi->merge($quote);
                $this->emi->flush();
            }

            if ($authorId) {
                $previousAuthor = $quote->getAuthor();
                $newAuthor = $this->authorRepository->findById($authorId);

                $quote = $quote->withAuthor($newAuthor);
                $this->emi->merge($quote);
                $this->emi->flush();

                $quotes = $this->getQuotesByAuthorName($newAuthor->getName());
                $newAuthor = $newAuthor->withQuotes(... $quotes);
                $this->authorRepository->updateAuthor($newAuthor);

                $quotes = $this->getQuotesByAuthorName($previousAuthor->getName());
                $previousAuthor = $previousAuthor->withQuotes(... $quotes);
                $this->authorRepository->updateAuthor($previousAuthor);
            }

            return $quote;

        } catch (AuthorIdNotFoundException $e) {
            $this->logger->error(sprintf('Trying to update quote to invalid author ID "%s"', $authorId));
            throw $e;
        } catch (NoQuotesFoundException $e) {
            $this->logger->error(sprintf('Could not find quote ID "%s" to update', $id));
            throw $e;
        }
    }

    /**
     * Add Author Data to Quote
     *
     * @param Quote $quote
     * @return Quote
     */
    private function addAuthor(Quote $quote): Quote
    {
        $this->logger->debug('Quote to add author to', [$quote]);
        return $quote->withAuthor($this->getAuthorById($quote->getAuthorId()));
    }

    /**
     * Get Author by ID
     *
     * @param string $id
     * @return Author
     */
    private function getAuthorById(string $id): Author{
        try {
            $author = $this->authorRepository->findById($id);
        } catch (AuthorIdNotFoundException $e) {
            $this->logger->error($e->getMessage(), [$e]);
            $author = new Author('', 'Unknown Author');
        }
        return $author;
    }
}