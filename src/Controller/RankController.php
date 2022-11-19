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
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

#[Route('/api/rank')]
class RankController extends AbstractController
{
    #[Route('/', name: 'rank.getAll', methods: ['GET'])]

    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Rank::class, groups: ['getParty']))
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request'
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized'
    )]
    #[OA\Tag(name: 'Rank')]
    /**
     * Returns the list of ranks sorted by gamemod.
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
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Rank::class, groups: ['getParty']))
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request'
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized'
    )]
    #[ParamConverter("rank", options: ['mapping' => ['RankId' => 'id']])]
    #[OA\Tag(name: 'Rank')]
    /**
     * Get one rank by an RankId
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
    #[ParamConverter("rank", options: ['mapping' => ['RankId' => 'id']])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Tag(name: 'Rank')]
    /**
     * Delete a rank by RankId.
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
    #[ParamConverter("rank", options: ['mapping' => ['RankId' => 'id']])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Tag(name: 'Rank')]
    /**
     * Change status of a rank.
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
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Rank::class, groups: ['getParty']))
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request'
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized'
    )]
    #[ParamConverter("gameMod", options: ['mapping' => ['Gamemodname' => 'name']])]
    #[OA\Tag(name: 'Rank')]
    /**
     * Returns the list of ranks for a gamemod.
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
    #[OA\Response(
        response: 200,
        description: 'Successful updated'
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request'
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized'
    )]
    #[ParamConverter("rank", options: ['mapping' => ['RankId' => 'id']])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Tag(name: 'Rank')]
    /**
     * Changes a player’s rank on a gamemod if rank entity not exist create automatically
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
