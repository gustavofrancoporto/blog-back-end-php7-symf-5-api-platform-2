<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class UserAttributeNormalizer implements ContextAwareNormalizerInterface
{
    /**
     * @var ObjectNormalizer
     */
    private $normalizer;
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage, ObjectNormalizer $normalizer)
    {
        $this->tokenStorage = $tokenStorage;
        $this->normalizer = $normalizer;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        if ($this->isUserHimself($object)) {
            $context['groups'][] = 'get-owner';
        }

        return $this->normalizer->normalize($object, $format, $context);
    }

    public function supportsNormalization($object, string $format = null, array $context = [])
    {
        return $object instanceof User;
    }

    private function isUserHimself(User $user)
    {
        return $user->getUsername() === $this->tokenStorage->getToken()->getUsername();
    }
}