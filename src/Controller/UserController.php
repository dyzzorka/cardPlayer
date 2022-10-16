<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/user')]
class UserController extends AbstractController
{
    #[Route('/register', name: 'user.register', methods: ['POST'])]
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
        $jsonuser = $serializer->serialize($user, 'json', ["groups" => "registerResponse"]);

        return new JsonResponse($jsonuser, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{idUser}', name: 'user.get', methods: ['GET'])]
    #[ParamConverter("user", options: ['mapping' => ['idUser' => 'id']])]
    /**
     * Function get information of a user
     *
     * @param User $user
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function getOneUser(?User $user, SerializerInterface $serializer): JsonResponse
    {
        $user == null ? $this->getUser() : $user = $user;
        $jsonuser = $serializer->serialize($user, 'json', ["groups" => "getUser"]);
        return new JsonResponse($jsonuser, Response::HTTP_OK, [], true);
    }

    #[Route('/{idUser}', name: 'user.status', methods: ['DELETE'])]
    #[ParamConverter("user", options: ['mapping' => ['idUser' => 'id']])]
    #[IsGranted('ROLE_ADMIN')]
    /**
     * Function that changes the status of a User.
     *
     * @param User $user
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function statusUser(User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        $user->setStatus(false);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{idUser}/delete', name: 'user.delete', methods: ['DELETE'])]
    #[ParamConverter("user", options: ['mapping' => ['idUser' => 'id']])]
    #[IsGranted('ROLE_ADMIN')]
    /**
     * Function that removes a User.
     *
     * @param User $User
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function deleteUser(User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($user);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
