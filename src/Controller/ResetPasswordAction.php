<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ResetPasswordAction
{
    public function __invoke(User $data,
                             ValidatorInterface $validator,
                             SerializerInterface $serializer,
                             UserPasswordEncoderInterface $passwordEncoder,
                             EntityManagerInterface $entityManager,
                             JWTTokenManagerInterface $tokenManager)
    {
        $errors = $validator->validate($data, null, ['put-reset-password']);
        if (count($errors) > 0) {
            return new Response($serializer->serialize($errors, 'json'));
        }

        $data->setPassword($passwordEncoder->encodePassword($data, $data->getNewPassword()));
        $data->setPasswordChangedDate(new \DateTime());

        $entityManager->flush();

        $token = $tokenManager->create($data);

        return new JsonResponse(['token' => $token]);
    }
}