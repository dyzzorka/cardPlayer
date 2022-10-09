<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/user')]
class UserController extends AbstractController
{
    #[Route('/register', name: 'app_user', methods: ['POST'])]
    /**
     * Function that allows a user to register.
     *
     * @param Request $request
     * @param UserRepository $userRepository
     * @param SerializerInterface $serializer
     * @param UserPasswordHasherInterface $passwordHasher
     * @return JsonResponse
     */
    public function register(Request $request, UserRepository $userRepository, SerializerInterface $serializer, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {

        $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        $user->setStatus(true)->setRoles(['ROLE_USER'])->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
        $userRepository->save($user, true);
        $jsonuser = $serializer->serialize($user, 'json', ["groups" => "getUser"]);

        return new JsonResponse($jsonuser, Response::HTTP_CREATED, [], true);
    }
}
