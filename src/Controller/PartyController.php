<?php

namespace App\Controller;

use App\Repository\PartyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/party')]
class PartyController extends AbstractController
{
    #[Route('/', name: 'party.all', methods: ['GET'])]
    public function getAll(SerializerInterface $serializer, PartyRepository $partyRepository): JsonResponse
    {
        $jsonGamemodCards = $serializer->serialize($partyRepository->findBy(["run" => false, "end" => false, "full" => false, "private" => false]), 'json', ["groups" => "getParty"]);
        return new JsonResponse($jsonGamemodCards, Response::HTTP_OK, ['accept' => 'json'], true);
    }
}
