<?php

namespace App\Controller;

use App\Entity\GameMod;
use App\Entity\Rank;
use App\Entity\User;
use App\Repository\GameModRepository;
use App\Repository\RankRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;

#[Route('/api/rank')]
class RankController extends AbstractController
{
    #[Route('/', name: 'rank.getAll', methods: ['GET'])]
    /**
     * Function that returns the list of ranks sorted by gamemod.
     *
     * @param GameModRepository $gameModRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function getAllRank(GameModRepository $gameModRepository, SerializerInterface $serializer): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(["getRank"]);
        $jsonRank = $serializer->serialize($gameModRepository->findAll(), 'json', $context);
        return new JsonResponse($jsonRank, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/{RankId}', name: 'rank.getOne', methods: ['GET'])]
    #[ParamConverter("rank", options: ['mapping' => ['RankId' => 'id']])]
    /**
     * 
     *
     * @param Rank $rank
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function getOneRank(Rank $rank, SerializerInterface $serializer): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(["getOneRank"]);
        $jsonRank = $serializer->serialize($rank, 'json', $context);
        return new JsonResponse($jsonRank, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/{RankId}/delete', name: 'rank.delete', methods: ['DELETE'])]
    #[ParamConverter("rank", options: ['mapping' => ['RankId' => 'id']])]
    #[IsGranted('ROLE_ADMIN')]
    /**
     * Function for delete a rank.
     *
     * @param Rank $rank
     * @param RankRepository $rankRepository
     * @return JsonResponse
     */
    public function deleteRank(Rank $rank, RankRepository $rankRepository): JsonResponse
    {
        $rankRepository->remove($rank, true);
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{RankId}', name: 'rank.status', methods: ['DELETE'])]
    #[ParamConverter("rank", options: ['mapping' => ['RankId' => 'id']])]
    #[IsGranted('ROLE_ADMIN')]
    /**
     * Function for change status of a rank.
     *
     * @param Rank $rank
     * @param RankRepository $rankRepository
     * @return JsonResponse
     */
    public function statusRank(Rank $rank, RankRepository $rankRepository): JsonResponse
    {
        $rankRepository->save($rank->setStatus(false), true);
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/gamemod/{Gamemodname}', name: 'rank.getAllInGamemod', methods: ['GET'])]
    #[ParamConverter("gameMod", options: ['mapping' => ['Gamemodname' => 'name']])]
    /**
     * Function that returns the list of ranks for a gamemod.
     *
     * @param GameMod $gameMod
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function getAllRankByGamemod(GameMod $gameMod, SerializerInterface $serializer): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(["getRank"]);
        $jsonRank = $serializer->serialize($gameMod, 'json', $context);
        return new JsonResponse($jsonRank, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/update/{RankId}/{mmr}', name: 'gamemod.update', methods: ['PUT'])]
    #[ParamConverter("rank", options: ['mapping' => ['RankId' => 'id']])]
    #[IsGranted('ROLE_ADMIN')]
    /**
     * Function that changes a player’s rank on a gamemod if rank entity not exist create automatically
     *
     * @param GameMod $gameMod
     * @param User $user
     * @param integer $mmr
     * @param RankRepository $rankRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function updateRank(Rank $rank, int $mmr, RankRepository $rankRepository, SerializerInterface $serializer): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(["getOneRank"]);
        $rankRepository->save($rank->setMmr($rank->getMmr() - $mmr), true);
        $jsonRank = $serializer->serialize($rank, 'json', $context);
        return new JsonResponse($jsonRank, Response::HTTP_OK, ['accept' => 'json'], true);
    }
}
