<?php
namespace App\Controller;

use App\Entity\ApiResponse;
use App\Exception\AuthorDataInvalidException;
use App\Exception\AuthorNotAddedException;
use App\Exception\AuthorNameNotFoundException;
use App\Exception\AuthorNotDeletedException;
use App\Exception\AuthorQuoteCountNotZeroException;
use App\Exception\QuoteDataInvalidException;
use App\Repository\AuthorRepository;
use App\Repository\QuoteRepository;
use Exception;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorsController extends Controller
{
    /**
     * @Route("/authors", name="authors", defaults={"_format": "json"})
     * @Method({"GET"})
     * @param Request $request
     * @param AuthorRepository $authorRepository
     * @param LoggerInterface $logger
     * @return Response
     */
    public function authorsAction(
        Request $request,
        AuthorRepository $authorRepository,
        LoggerInterface $logger
    ): Response {
        $logger->info('Author action dispatched', $request->query->all());

        try {
            $response = new Response(ApiResponse::success($authorRepository->findAll()), 200);
        } catch (Exception $e) {
            $response = new Response(ApiResponse::error($e), 400);
        }

        $logger->debug('Sending Response', [$response]);
        return $response;
    }

    /**
     * @Route("/authors/{name}", name="authorByName", defaults={"_format": "json"})
     * @Method({"GET"})
     * @param Request $request
     * @param AuthorRepository $authorRepository
     * @param LoggerInterface $logger
     * @param string $name
     * @return Response
     */
    public function authorByNameAction(
        Request $request,
        AuthorRepository $authorRepository,
        LoggerInterface $logger,
        string $name
    ): Response {
        $logger->info('Author-by-name action dispatched', $request->query->all());

        try {
            $response = new Response(
                ApiResponse::success($authorRepository->findByName($name)),
                200
            );
        } catch (AuthorNameNotFoundException $e) {
            $response = new Response(ApiResponse::error($e), 404);
        }

        $logger->debug('Sending Response', [$response]);
        return $response;
    }

    /**
     * @Route("/authors", name="authorCreateByName", defaults={"_format": "json"})
     * @Method({"POST"})
     * @param Request $request
     * @param AuthorRepository $authorRepository
     * @param LoggerInterface $logger
     * @return Response
     */
    public function addAuthorByNameAction(
        Request $request,
        AuthorRepository $authorRepository,
        LoggerInterface $logger
    ): Response {
        $logger->info('Add-author-by-name action dispatched', [$request->getContent()]);

        try {
            $author = json_decode($request->getContent());
            if (!$author || !isset($author->name)) {
                $response =  new Response(
                    ApiResponse::error(new AuthorDataInvalidException),
                    400
                );
            } else {
                $response = new Response(
                    ApiResponse::success($authorRepository->addByName($author->name)),
                    201
                );
            }
        } catch (AuthorNotAddedException $e) {
            $response = new Response(ApiResponse::error($e), 400);
        }

        $logger->debug('Sending Response', [$response]);
        return $response;
    }

    /**
     * @Route("/authors/{name}", name="authorDeleteByName", defaults={"_format": "json"})
     * @Method({"DELETE"})
     * @param Request $request
     * @param AuthorRepository $authorRepository
     * @param QuoteRepository $quoteRepository
     * @param LoggerInterface $logger
     * @param string $name
     * @return Response
     */
    public function deleteAuthorByNameAction(
        Request $request,
        AuthorRepository $authorRepository,
        QuoteRepository $quoteRepository,
        LoggerInterface $logger,
        string $name
    ): Response {
        $logger->info('Delete-author-by-name action dispatched', $request->query->all());

        try {
            $author = $authorRepository->findByName($name);
            if ($quoteRepository->removeByAuthorId($author->getId())) {
                $response = new Response(
                    ApiResponse::success($authorRepository->removeByName($name)),
                    200
                );
            } else {
                $response = new Response(
                    ApiResponse::error(new AuthorNotDeletedException($name)),
                    400
                );
            }
        } catch (AuthorNameNotFoundException $e) {
            $response = new Response(ApiResponse::error($e), 404);
        } catch (AuthorQuoteCountNotZeroException $e) {
            $response = new Response(ApiResponse::error($e), 400);
        }

        $logger->debug('Sending Response', [$response]);
        return $response;
    }

    /**
     * @Route("/authors/{name}", name="authorUpdateByName", defaults={"_format": "json"})
     * @Method({"PATCH", "PUT"})
     * @param Request $request
     * @param AuthorRepository $authorRepository
     * @param LoggerInterface $logger
     * @param string $name
     * @return Response
     * @throws \App\Exception\AuthorNotUpdatedException
     */
    public function updateAuthorByNameAction(
        Request $request,
        AuthorRepository $authorRepository,
        LoggerInterface $logger,
        string $name
    ): Response {
        $logger->info('Update-author-by-name action dispatched', $request->query->all());

        try {
            $author = json_decode($request->getContent());
            if (!$author || !isset($author->name)) {
                $response =  new Response(ApiResponse::error(new AuthorDataInvalidException), 400);
            } else {
                $response = new Response(
                    ApiResponse::success($authorRepository->updateByName($name, $author->name)),
                    200
                );
            }
        } catch (AuthorNameNotFoundException $e) {
            $response = new Response(ApiResponse::error($e), 404);
        } catch (AuthorNotUpdatedException $e) {
            $response = new Response(ApiResponse::error($e), 400);
        }
        $logger->debug('Sending Response', [$response]);
        return $response;
    }

    /**
     * @Route("/authors/{name}/quotes", name="quotesByAuthor", defaults={"_format": "json"})
     * @Method({"GET"})
     * @param Request $request
     * @param QuoteRepository $quoteRepository
     * @param LoggerInterface $logger
     * @param string $name
     * @return Response
     */
    public function getQuotesByAuthor(
        Request $request,
        QuoteRepository $quoteRepository,
        LoggerInterface $logger,
        string $name
    ): Response {
        $logger->info('Get-quotes-for-author action dispatched', $request->query->all());
        try {
            $response = new Response(ApiResponse::success(
                $quoteRepository->getQuotesByAuthorName($name)
            ), 200);
        } catch (Exception $e) {
            $response = new Response(ApiResponse::error($e), 404);
        }

        $logger->debug('Sending Response', [$response]);
        return $response;
    }

    /**
     * @Route("/authors/{name}/quotes", name="addQuoteToAuthor", defaults={"_format": "json"})
     * @Method({"POST"})
     * @param Request $request
     * @param QuoteRepository $quoteRepository
     * @param LoggerInterface $logger
     * @param string $name
     * @return Response
     */
    public function addQuoteToAuthor(
        Request $request,
        QuoteRepository $quoteRepository,
        LoggerInterface $logger,
        string $name
    ): Response {
        $logger->info('Add-quote-to-author action dispatched', $request->query->all());
        try {
            $quote = json_decode($request->getContent());
            if (!$quote || !isset($quote->text)) {
                $response =  new Response(
                    ApiResponse::error(new QuoteDataInvalidException),
                    400
                );
            } else {
                $response = new Response(ApiResponse::success(
                    $quoteRepository->addQuoteByAuthorName($name, $quote->text)
                ), 201);
            }
        } catch (Exception $e) {
            $response = new Response(ApiResponse::error($e), 404);
        }

        $logger->debug('Sending Response', [$response]);
        return $response;
    }
}