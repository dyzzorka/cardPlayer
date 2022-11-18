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
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;

#[Route('/api/rank')]
class RankController extends AbstractController
{
    #[Route('/', name: 'rank.getAll', methods: ['GET'])]
    #[OA\Tag(name: 'Rank')]
    /**
     * Function that returns the list of ranks sorted by gamemod.
     *
     * @param GameModRepository $gameModRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function getAllRank(GameModRepository $gameModRepository, SerializerInterface $serializer): JsonResponse
    {
        $jsonRank = $serializer->serialize($gameModRepository->findAll(), 'json', ["groups" => "getRank"]);
        return new JsonResponse($jsonRank, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/{RankId}', name: 'rank.one', methods: ['GET'])]
    #[ParamConverter("rank", options: ['mapping' => ['RankId' => 'id']])]
    #[OA\Tag(name: 'Rank')]
    /**
     * Get the rank from the RankId
     *
     * @param Rank $rank
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function getOneRank(Rank $rank, SerializerInterface $serializer): JsonResponse
    {
        $jsonRank = $serializer->serialize($rank, 'json', ["groups" => "getOneRank"]);
        return new JsonResponse($jsonRank, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/{RankId}/delete', name: 'rank.delete', methods: ['DELETE'])]
    #[ParamConverter("rank", options: ['mapping' => ['RankId' => 'id']])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Tag(name: 'Rank')]
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
    #[OA\Tag(name: 'Rank')]
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
    #[OA\Tag(name: 'Rank')]
    /**
     * Function that returns the list of ranks for a gamemod.
     *
     * @param GameMod $gameMod
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function getAllRankByGamemod(GameMod $gameMod, SerializerInterface $serializer): JsonResponse
    {
        $jsonRank = $serializer->serialize($gameMod, 'json', ["groups" => "getRank"]);
        return new JsonResponse($jsonRank, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/update/{Gamemodname}/{idUser}/{mmr}', name: 'gamemod.update', methods: ['PUT'])]
    #[ParamConverter("gameMod", options: ['mapping' => ['Gamemodname' => 'name']])]
    #[ParamConverter("user", options: ['mapping' => ['idUser' => 'id']])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Tag(name: 'Rank')]
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
    public function updateRank(GameMod $gameMod, User $user, /*changer le system mmr si on a le temps -->*/ int $mmr, RankRepository $rankRepository, SerializerInterface $serializer): JsonResponse
    {
        $rank = $rankRepository->findOneBy(array("gamemod" => $gameMod, "user" => $user));
        if ($rank === null) {
            $rank = new Rank();
            $rank->setUser($user)->setGamemod($gameMod)->setMmr($mmr)->setStatus(true);
            $rankRepository->save($rank, true);
        } else {
            $actualMmr = $rank->getMmr();
            $rank->setMmr($actualMmr += $mmr)->setStatus(true);
            $rankRepository->save($rank, true);
        }
        $jsonRank = $serializer->serialize($rank, 'json', ["groups" => "getOneRank"]);
        return new JsonResponse($jsonRank, Response::HTTP_OK, ['accept' => 'json'], true);
    }
}
