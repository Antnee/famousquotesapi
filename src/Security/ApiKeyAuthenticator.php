<?php
namespace App\Security;

use App\Entity\ApiResponse;
use App\Exception\InvalidApiKeyException;
use InvalidArgumentException;
use MongoDB\Driver\Exception\AuthenticationException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;

class ApiKeyAuthenticator implements SimplePreAuthenticatorInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function createToken(Request $request, $providerKey)
    {
        $this->logger->debug('Creating token', func_get_args());

        $key = $request->headers->get('x-api-key');
        if (!$key) {
            $this->logger->error('API key header (x-api-key) missing');
            throw new BadCredentialsException;
        }
        return new PreAuthenticatedToken('anon.', $key, $providerKey);
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        if (!$userProvider instanceof ApiKeyUserProvider) {
            throw new InvalidArgumentException(sprintf(
                'The user provider must be an instance of ApiKeyUserProvider (%s was given).',
                get_class($userProvider)
            ));
        }

        $key = $token->getCredentials();
        $userName = $userProvider->getUsernameByKey($key);

        if (!$userName) {
            throw new InvalidApiKeyException;
        }

        $user = $userProvider->loadUserByUsername($userName);

        return new PreAuthenticatedToken(
            $user,
            $key,
            $providerKey,
            $user->getRoles()
        );
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $e)
    {
        $this->logger->debug('Authentication Failure', func_get_args());

        return new Response(
            ApiResponse::error($e),
            401,
            ['Content-Type'=>'application/json']
        );
    }
}