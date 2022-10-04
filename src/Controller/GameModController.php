<?php

namespace App\Controller;

use App\Entity\GameMod;
use App\Repository\GameModRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/gamemod')]
class GameModController extends AbstractController
{
    
    #[Route('/all', name: 'gamemod.all')]
    public function getAll(GameModRepository $gameModRepository, SerializerInterface $serializer): JsonResponse
    {
        $jsonGamemodCards = $serializer->serialize($gameModRepository->findAll(), 'json', ["groups" => "getGamemod"]);
        return new JsonResponse($jsonGamemodCards, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/{Gamemodname}', name: 'gamemod.one', methods: ['GET'])]
    #[ParamConverter("gameMod", options: ['mapping' => ['Gamemodname' => 'name']])]
    public function getOneGamemod(GameMod $gameMod, SerializerInterface $serializer): JsonResponse
    {
        $jsonGamemodCards = $serializer->serialize($gameMod, 'json');
        return new JsonResponse($jsonGamemodCards, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/{Gamemodname}/cards', name: 'gamemod.card', methods: ['GET'])]
    #[ParamConverter("gameMod", options: ['mapping' => ['Gamemodname' => 'name']])]
    public function getAllCards(GameMod $gameMod, SerializerInterface $serializer): JsonResponse
    {
        $jsonGamemodCards = $serializer->serialize($gameMod->getCards(), 'json');
        return new JsonResponse($jsonGamemodCards, Response::HTTP_OK, ['accept' => 'json'], true);
    }
}
