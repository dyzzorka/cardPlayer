<?php

namespace App\Controller;

use App\Entity\GameMod;
use App\Repository\GameModRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\DeserializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;

#[Route('/api/gamemod')]
class GameModController extends AbstractController
{
    #[Route('/', name: 'gamemod.getAll', methods: ['GET'])]
    /**
     * Function to get all GameMod.
     *
     * @param GameModRepository $gameModRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function getAll(GameModRepository $gameModRepository, SerializerInterface $serializer, TagAwareCacheInterface $tagAwareCacheInterface): JsonResponse
    {
        $jsonGamemod = $tagAwareCacheInterface->get("getAllGamemod", function (ItemInterface $itemInterface) use ($gameModRepository, $serializer) {
            $itemInterface->tag("gamemodCache");
            $context = SerializationContext::create()->setGroups(["getGamemod"]);
            return $serializer->serialize($gameModRepository->findAll(), 'json', $context);
        });

        return new JsonResponse($jsonGamemod, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/{Gamemodname}', name: 'gamemod.getOne', methods: ['GET'])]
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
        $context = SerializationContext::create()->setGroups(["getGamemod"]);
        $jsonGamemodCards = $serializer->serialize($gameMod, 'json', $context);
        return new JsonResponse($jsonGamemodCards, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/{Gamemodname}/cards', name: 'gamemod.getCard', methods: ['GET'])]
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
        $context = SerializationContext::create()->setGroups(["getGamemod"]);
        $jsonGamemodCards = $serializer->serialize($gameMod, 'json', $context);
        return new JsonResponse($jsonGamemodCards, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/{Gamemodname}/delete', name: 'gamemod.delete', methods: ['DELETE'])]
    #[ParamConverter("gameMod", options: ['mapping' => ['Gamemodname' => 'name']])]
    #[IsGranted('ROLE_ADMIN')]
    /**
     * Function that removes a GameMode.
     *
     * @param GameMod $gameMod
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function deleteGamemod(GameMod $gameMod, GameModRepository $gameModRepository, TagAwareCacheInterface $tagAwareCacheInterface): JsonResponse
    {
        $tagAwareCacheInterface->invalidateTags(["gamemodCache"]);
        $gameModRepository->remove($gameMod, true);
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{Gamemodname}', name: 'gamemod.status', methods: ['DELETE'])]
    #[ParamConverter("gameMod", options: ['mapping' => ['Gamemodname' => 'name']])]
    #[IsGranted('ROLE_ADMIN')]
    /**
     * Function that changes the status of a GameMod.
     *
     * @param GameMod $gameMod
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function statusGamemod(GameMod $gameMod, GameModRepository $gameModRepository): JsonResponse
    {
        $gameModRepository->save($gameMod->setStatus(false), true);
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/', name: 'gamemod.add', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    /**
     * Function for add a GameMod
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @param UrlGeneratorInterface $urlGenerator
     * @return JsonResponse
     */
    public function createGamemod(Request $request, GameModRepository $gameModRepository, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $gameMod = $serializer->deserialize($request->getContent(), GameMod::class, 'json');
        $gameModRepository->save($gameMod->setStatus(true), true);
        $context = SerializationContext::create()->setGroups(["getGamemod"]);
        $jsonGamemod = $serializer->serialize($gameMod, 'json', $context);

        $location = $urlGenerator->generate('gamemod.one', ['Gamemodname' => $gameMod->getId()], UrlGeneratorInterface::ABSOLUTE_PATH);
        return new JsonResponse($jsonGamemod, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/{Gamemodname}', name: 'gamemod.update', methods: ['PUT'])]
    #[ParamConverter("gameMod", options: ['mapping' => ['Gamemodname' => 'name']])]
    #[IsGranted('ROLE_ADMIN')]
    /**
     * Function for update a GameMod
     *
     * @param GameMod $gameMod
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @param UrlGeneratorInterface $urlGenerator
     * @return JsonResponse
     */
    public function updateGamemod(GameMod $gameMod, Request $request, GameModRepository $gameModRepository, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $serializer->deserialize($request->getContent(), GameMod::class, 'json');

        /* FAIRE LA DESERIALISATION A LA MAIN */

        $gameModRepository->save($gameMod->setStatus(true), true);
        $context = SerializationContext::create()->setGroups(["getGamemod"]);
        $jsonGamemod = $serializer->serialize($gameMod, 'json', $context);

        $location = $urlGenerator->generate('gamemod.one', ['Gamemodname' => $gameMod->getId()], UrlGeneratorInterface::ABSOLUTE_PATH);
        return new JsonResponse($jsonGamemod, Response::HTTP_OK, ["Location" => $location], true);
    }
}
