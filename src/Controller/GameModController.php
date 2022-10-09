<?php

namespace App\Controller;

use App\Entity\GameMod;
use App\Repository\GameModRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/gamemod')]
class GameModController extends AbstractController
{
    #[Route('/', name: 'gamemod.all', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: 'HAHAHAHA ta mere la pute')]
    /**
     * Function to get all GameMod.
     *
     * @param GameModRepository $gameModRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function getAll(GameModRepository $gameModRepository, SerializerInterface $serializer): JsonResponse
    {
        $jsonGamemodCards = $serializer->serialize($gameModRepository->findAll(), 'json', ["groups" => "getGamemod"]);
        return new JsonResponse($jsonGamemodCards, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/{Gamemodname}', name: 'gamemod.one', methods: ['GET'])]
    #[ParamConverter("gameMod", options: ['mapping' => ['Gamemodname' => 'name']])]
    /**
     * Function to get one GameMod.
     *
     * @param GameMod $gameMod
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function getOneGamemod(GameMod $gameMod, SerializerInterface $serializer): JsonResponse
    {
        $jsonGamemodCards = $serializer->serialize($gameMod, 'json');
        return new JsonResponse($jsonGamemodCards, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/{Gamemodname}/cards', name: 'gamemod.card', methods: ['GET'])]
    #[ParamConverter("gameMod", options: ['mapping' => ['Gamemodname' => 'name']])]
    /**
     * Function to get all cards from a deck in GameMod.
     *
     * @param GameMod $gameMod
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function getAllCards(GameMod $gameMod, SerializerInterface $serializer): JsonResponse
    {
        $jsonGamemodCards = $serializer->serialize($gameMod->getCards(), 'json');
        return new JsonResponse($jsonGamemodCards, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/{Gamemodname}/delete', name: 'gamemod.delete', methods: ['DELETE'])]
    #[ParamConverter("gameMod", options: ['mapping' => ['Gamemodname' => 'name']])]
    /**
     * Function that removes a GameMode.
     *
     * @param GameMod $gameMod
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function deleteGamemod(GameMod $gameMod, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($gameMod);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{Gamemodname}', name: 'gamemod.status', methods: ['DELETE'])]
    #[ParamConverter("gameMod", options: ['mapping' => ['Gamemodname' => 'name']])]
    /**
     * Function that changes the status of a GameMod.
     *
     * @param GameMod $gameMod
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function statusGamemod(GameMod $gameMod, EntityManagerInterface $entityManager): JsonResponse
    {
        $gameMod->setStatus(false);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/', name: 'gamemod.add', methods: ['POST'])]
    public function createGamemod(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $gameMod = $serializer->deserialize($request->getContent(), GameMod::class, 'json');
        $gameMod->setStatus(true);
        $entityManager->persist($gameMod);
        $entityManager->flush();
        $jsonGamemod = $serializer->serialize($gameMod, 'json', ["groups" => "getGamemod"]);

        $location = $urlGenerator->generate('gamemod.one', ['Gamemodname' => $gameMod->getId()], UrlGeneratorInterface::ABSOLUTE_PATH);
        return new JsonResponse($jsonGamemod, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/{Gamemodname}', name: 'gamemod.update', methods: ['PUT'])]
    #[ParamConverter("gameMod", options: ['mapping' => ['Gamemodname' => 'name']])]
    public function updateGamemod(GameMod $gameMod, Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $gameModupdate = $serializer->deserialize($request->getContent(), GameMod::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $gameMod]);

        $gameMod->setStatus(true);
        $entityManager->persist($gameMod);
        $entityManager->flush();
        $jsonGamemod = $serializer->serialize($gameMod, 'json', ["groups" => "getGamemod"]);

        $location = $urlGenerator->generate('gamemod.one', ['Gamemodname' => $gameMod->getId()], UrlGeneratorInterface::ABSOLUTE_PATH);
        return new JsonResponse($jsonGamemod, Response::HTTP_CREATED, ["Location" => $location], true);
    }
}
