<?php

namespace App\Controller;

use App\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class PlayerController extends AbstractController
{
    #[Route('/player', name: 'app_player')]
    public function index(PlayerRepository $playerRepository, SerializerInterface $serializer): JsonResponse
    {

        $value = $playerRepository->findAll();
        $jsonPlayer = $serializer->serialize($value, 'json');
        return new JsonResponse($value, Response::HTTP_OK, [], false);
    }
}
