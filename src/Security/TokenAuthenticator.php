<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\ExpiredTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\PreAuthenticationJWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class TokenAuthenticator extends JWTTokenAuthenticator
{
     /**
     * @param PreAuthenticationJWTUserToken $preAuthToken
     * @param UserProviderInterface $userProvider
     * @return UserInterface|void|null
     */
    public function getUser($preAuthToken, UserProviderInterface $userProvider)
    {
        /** @var User $user */
        $user = parent::getUser($preAuthToken, $userProvider);

        $tokenCreatedDate = \DateTime::createFromFormat( 'U', strval($preAuthToken->getPayload()['iat']) );

        if ($user->passwordChangedAfter($tokenCreatedDate)) {
            throw new ExpiredTokenException();
        }

        return $user;
    }
}