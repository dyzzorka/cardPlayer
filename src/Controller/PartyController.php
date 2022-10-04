<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\GameMod;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/party', name: 'app_party')]
class PartyController extends AbstractController
{
    #[Route('', name: 'app_party')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PartyController.php',
        ]);
    }

    // #[Route('/{roomId}/drawCards', name: 'app_party.draw')]
    // #[ParamConverter("gameMod", options: ['mapping' => ['roomId' => 'name']])]
    // public function drawCards(GameMod $gamemod, Card $card, SerializerInterface $serializer): JsonResponse
    // {
    //     $jsonGamemodCards = $serializer->serialize($gamemod->getCards(), 'json');
    //     return new JsonResponse($jsonGamemodCards, Response::HTTP_OK, ['accept' => 'json'], true);
    // }
}
