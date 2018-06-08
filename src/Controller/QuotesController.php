<?php
namespace App\Controller;

use App\Entity\ApiResponse;
use App\Exception\AuthorIdNotFoundException;
use App\Exception\NoQuotesFoundException;
use App\Exception\QuoteDataInvalidException;
use App\Repository\QuoteRepository;
use Exception;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class QuotesController extends Controller
{
    /**
     * @Route("/quotes", name="quotes", defaults={"_format": "json"})
     * @Method({"GET"})
     * @param LoggerInterface $logger
     * @return Response
     */
    public function quotesAction(LoggerInterface $logger): Response
    {
        $logger->info('Quotes action dispatched. Redirecting to random quote');
        return $this->redirectToRoute("randomQuote");
    }

    /**
     * @Route("/quotes/random", name="randomQuote", defaults={"_format": "json"})
     * @Method({"GET"})
     * @param Request $request
     * @param QuoteRepository $quoteRepository
     * @param LoggerInterface $logger
     * @return Response
     */
    public function randomQuoteAction(
        Request $request,
        QuoteRepository $quoteRepository,
        LoggerInterface $logger
    ): Response {
        $logger->info('Random quotes action dispatched', $request->query->all());

        try {
            $response = new Response(
                ApiResponse::success($quoteRepository->getRandomQuote()),
                200
            );
        } catch (Exception $e) {
            $response = new Response(ApiResponse::error($e), 400);
        }

        $logger->debug('Sending Response', [$response]);
        return $response;
    }

    /**
     * @Route("/quotes/{id}", name="quoteById", defaults={"_format": "json"})
     * @Method({"GET"})
     * @param Request $request
     * @param QuoteRepository $quoteRepository
     * @param LoggerInterface $logger
     * @param string $id
     * @return Response
     */
    public function getQuoteAction(
        Request $request,
        QuoteRepository $quoteRepository,
        LoggerInterface $logger,
        string $id
    ): Response {
        $logger->info('Get-quote-by-id action dispatched', $request->query->all());

        try {
            $response = new Response(
                ApiResponse::success($quoteRepository->getQuoteById($id)),
                200
            );
        } catch (Exception $e) {
            $response = new Response(ApiResponse::error($e), 404);
        }

        $logger->debug('Sending Response', [$response]);
        return $response;
    }

    /**
     * @Route("/quotes/{id}", name="deleteQuoteById", defaults={"_format": "json"})
     * @Method({"DELETE"})
     * @param Request $request
     * @param QuoteRepository $quoteRepository
     * @param LoggerInterface $logger
     * @param string $id
     * @return Response
     */
    public function removeQuoteAction(
        Request $request,
        QuoteRepository $quoteRepository,
        LoggerInterface $logger,
        string $id
    ): Response {
        $logger->info('Remove-quote action dispatched', $request->query->all());

        try {
            $response = new Response(
                ApiResponse::success($quoteRepository->removeById($id)),
                200
            );
        } catch (Exception $e) {
            $response = new Response(ApiResponse::error($e), 400);
        }

        $logger->debug('Sending Response', [$response]);
        return $response;
    }

    /**
     * @Route("/quotes/{id}", name="updateQuoteById", defaults={"_format": "json"})
     * @Method({"PATCH", "PUT"})
     * @param Request $request
     * @param QuoteRepository $quoteRepository
     * @param LoggerInterface $logger
     * @param string $id
     * @return Response
     */
    public function updateQuoteAction(
        Request $request,
        QuoteRepository $quoteRepository,
        LoggerInterface $logger,
        string $id
    ): Response {
        $logger->info('Update-quote action dispatched');

        try {
            $quote = json_decode($request->getContent());
            if (
                !$quote
                || ($request->getMethod() == 'put' && (!isset($quote->text) || !isset($quote->authorId)))
                || ($request->getMethod() == 'patch' && !isset($quote->text) && !isset($quote->authorId))
            ) {
                // Must have a request body
                // PUT must include text and authorID
                // PATCH requires text OR authorId. If neither are provided, it's wrong
                // None of these matched, so 400 error it is
                $response = new Response(ApiResponse::error(new QuoteDataInvalidException), 400);
            } else {
                $response = new Response(
                    ApiResponse::success($quoteRepository->updateById(
                        $id,
                        $quote->text ?? null,
                        $quote->authorId ?? null)
                    ), 200
                );
            }
        } catch (AuthorIdNotFoundException|NoQuotesFoundException $e) {
            $response = new Response(ApiResponse::error($e), 404);
        } catch (Exception $e) {
            $logger->error($e->getMessage(), [$e]);
            $response = new Response(ApiResponse::error($e), 400);
        }

        $logger->debug('Sending Response', [$response]);
        return $response;
    }
}