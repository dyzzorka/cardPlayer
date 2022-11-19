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
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

#[Route('/api/user')]
class UserController extends AbstractController
{
    #[Route('/register', name: 'user.register', methods: ['POST'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: User::class, groups: ['getParty'])
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request'
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized'
    )]
    #[OA\Tag(name: 'User')]
    /**
     * Allows a user to register.
     *
     * @param Request $request
     * @param UserRepository $userRepository
     * @param SerializerInterface $serializer
     * @param UserPasswordHasherInterface $passwordHasher
     * @return JsonResponse
     */
    public function register(Request $request, UserRepository $userRepository, SerializerInterface $serializer, UserPasswordHasherInterface $passwordHasher, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        $user->setStatus(true)->setRoles(['ROLE_USER'])->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
        $userRepository->save($user, true);
        $context = SerializationContext::create()->setGroups(["registerResponse"]);
        $jsonuser = $serializer->serialize($user, 'json', $context);

        $location = $urlGenerator->generate('user.view',[], UrlGeneratorInterface::ABSOLUTE_PATH);
        return new JsonResponse($jsonuser, Response::HTTP_CREATED, ['Location'=>$location], true);
    }

    #[Route('/view', name: 'user.view', methods: ['GET'])]

    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: User::class, groups: ['getParty'])
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request'
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized'
    )]
    #[OA\Tag(name: 'User')]
    /**
     * Get information of the user connected
     *
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function getUserConnected(SerializerInterface $serializer): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(["getUser"]);
        $jsonuser = $serializer->serialize($this->getUser(), 'json', $context);
        return new JsonResponse($jsonuser, Response::HTTP_OK, [], true);
    }

    #[Route('/{idUser}', name: 'user.get', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]

    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: User::class, groups: ['getParty'])
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request'
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized'
    )]
    #[ParamConverter("user", options: ['mapping' => ['idUser' => 'id']])]
    #[OA\Tag(name: 'User')]
    /**
     * Get one user by ID
     *
     * @param User $user
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function getOneUser(User $user, SerializerInterface $serializer): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(["getUser"]);
        $jsonuser = $serializer->serialize($user, 'json', $context);
        return new JsonResponse($jsonuser, Response::HTTP_OK, [], true);
    }

    #[Route('/{idUser}', name: 'user.status', methods: ['DELETE'])]
    #[OA\Response(
        response: 200,
        description: 'Successfully deleted'
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request'
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized'
    )]
    #[ParamConverter("user", options: ['mapping' => ['idUser' => 'id']])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Tag(name: 'User')]
    /**
     * Change the status of a User.
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
    #[OA\Response(
        response: 200,
        description: 'Successfully deleted'
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request'
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized'
    )]
    #[ParamConverter("user", options: ['mapping' => ['idUser' => 'id']])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Tag(name: 'User')]
    /**
     * Remove a User.
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
