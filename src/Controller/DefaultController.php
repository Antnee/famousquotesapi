<?php
namespace App\Controller;

use App\Entity\ApiResponse;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @param LoggerInterface $logger
     * @return Response
     */
    public function indexAction(LoggerInterface $logger): Response
    {
        $logger->debug('Index action dispatched. Redirecting to pingAction');
        return $this->redirectToRoute("pingAction");
    }

    /**
     * @Route("/ping", name="pingAction", defaults={"_format": "json"})
     * @param LoggerInterface $logger
     * @return Response
     */
    public function pingAction(LoggerInterface $logger): Response
    {
        $logger->debug('Ping action dispatched');
        return new Response(ApiResponse::success(['ack' => time()]), 200);
    }
}