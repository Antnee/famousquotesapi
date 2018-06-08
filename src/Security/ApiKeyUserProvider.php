<?php
namespace App\Security;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ApiKeyUserProvider implements UserProviderInterface
{
    /**
     * @var array
     *
     * Done for speed's sake. Not an ideal solution at all!
     */
    private $hardCodedApiKeys = [
        'fhRBi4atT9xcLlQBJMz7lRDH1HL480shLdYlfmuPulQ' => 'wp_user',
    ];

    public function getUsernameByKey($key): string
    {
        if (!isset($this->hardCodedApiKeys[$key])) {
            throw new UsernameNotFoundException;
        }

        return $this->hardCodedApiKeys[$key];
    }

    public function loadUserByUsername($username)
    {
        return new User($username, null, ['ROLE_API']);
    }

    public function refreshUser(UserInterface $user)
    {
        throw new UnsupportedUserException;
    }

    public function supportsClass($class)
    {
        return User::class === $class;
    }
}