<?php
namespace App\Repository;

use App\Entity\Author;
use App\Exception\AuthorIdNotFoundException;
use App\Exception\AuthorNotAddedException;
use App\Exception\AuthorNameNotFoundException;
use App\Exception\AuthorNotUpdatedException;
use App\Exception\AuthorQuoteCountNotZeroException;
use App\Exception\NoAuthorsFoundException;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class AuthorRepository
{
    private $emi, $repository, $logger;

    public function __construct(EntityManagerInterface $emi, LoggerInterface $logger)
    {
        $this->emi = $emi;
        $this->repository = $this->emi->getRepository(Author::class);
        $this->logger = $logger;
    }

    /**
     * Find All Authors
     *
     * @return array
     * @throws NoAuthorsFoundException
     */
    public function findAll()
    {
        $all = $this->repository->findBy([], ['name'=>'asc']);
        if (0 === count($all)) {
            $this->logger->error('No authors found');
            throw new NoAuthorsFoundException;
        }
        return $all;
    }

    /**
     * Find Author By ID
     *
     * @param string $id
     * @return Author
     * @throws AuthorIdNotFoundException
     */
    public function findById(string $id): Author
    {
        $author = $this->repository->findOneBy(['id'=>$id]);
        if (!$author) {
            $this->logger->error(sprintf('Author ID "%s" cannot be found', $id));
            throw new AuthorIdNotFoundException($id);
        }
        return $author;
    }

    /**
     * Find Author By Name
     *
     * @param string $name
     * @return Author
     * @throws AuthorNameNotFoundException
     */
    public function findByName(string $name): Author
    {
        $author = $this->repository->findOneBy(['name'=>$name]);
        if (!$author) {
            $this->logger->error(sprintf('Author "%s" cannot be found', $name));
            throw new AuthorNameNotFoundException($name);
        }
        return $author;
    }

    /**
     * Remove Author by Name
     *
     * @param string $name
     * @return bool
     * @throws AuthorNameNotFoundException
     * @throws AuthorQuoteCountNotZeroException
     */
    public function removeByName(string $name): bool
    {
        $author = $this->findByName($name);
        if ($author->getQuoteCount() > 0) {
            throw new AuthorQuoteCountNotZeroException($author->getQuoteCount());
        }
        $this->emi->remove($author);
        $this->emi->flush();
        return true;
    }

    /**
     * Add Author By Name
     *
     * @param string $name
     * @return Author
     * @throws AuthorNotAddedException
     */
    public function addByName(string $name): Author
    {
        try {
            $author = new Author(Uuid::uuid4(), $name);
            $this->emi->persist($author);
            $this->emi->flush();
        } catch (DBALException $e) {
            $this->logger->error($e->getMessage());
            throw new AuthorNotAddedException($name, $e);
        }
        return $author;
    }

    /**
     * Update Author Name By Name
     *
     * @param string $oldName
     * @param string $newName
     * @return Author
     * @throws AuthorNameNotFoundException
     * @throws AuthorNotUpdatedException
     */
    public function updateByName(string $oldName, string $newName): Author
    {
        try {
            $author = $this->findByName($oldName);
            $author = $author->withName($newName);

            $this->emi->detach($author);
            $this->emi->merge($author);
            $this->emi->flush();
        } catch (DBALException $e) {
            $this->logger->error($e->getMessage());
            throw new AuthorNotUpdatedException($oldName, $newName, $e);
        }
        return $author;
    }

    /**
     * Update Author Name By ID
     *
     * @param string $id
     * @param string $newName
     * @return Author
     * @throws AuthorIdNotFoundException
     * @throws AuthorNotUpdatedException
     */
    public function updateById(string $id, string $newName): Author
    {
        try {
            $author = $this->findById($id);
            $author = $author->withName($newName);
            $this->emi->flush();
        } catch (DBALException $e) {
            $this->logger->error($e->getMessage());
            throw new AuthorNotUpdatedException($oldName, $newName, $e);
        }
        return $author;
    }

    /**
     * Update and Save Author
     *
     * @param Author $author
     * @return Author
     */
    public function updateAuthor(Author $author): Author
    {
        $this->emi->merge($author);
        $this->emi->flush();

        return $author;
    }
}