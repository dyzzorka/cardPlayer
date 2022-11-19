<?php

namespace App\Controller;

use App\Entity\BlackJack;
use App\Entity\Card;
use App\Entity\GameMod;
use App\Entity\Party;
use App\Entity\Rank;
use App\Entity\User;
use App\Repository\CardRepository;
use App\Repository\PartyRepository;
use App\Repository\RankRepository;
use App\Repository\UserRepository;
use phpDocumentor\Reflection\Types\Boolean;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Validator\Constraints\Blank;
use OpenApi\Attributes as OA;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('/api/party')]
class PartyController extends AbstractController
{
    #[Route('/', name: 'party.getAll', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Party::class, groups: ['getParty']))
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
    #[OA\Tag(name: 'Party')]
    /**
     * Function to get all Party.
     *
     * @param SerializerInterface $serializer
     * @param PartyRepository $partyRepository
     * @return JsonResponse
     */
    public function getAll(SerializerInterface $serializer, PartyRepository $partyRepository): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(["getParty"]);
        $jsonParty = $serializer->serialize($partyRepository->findBy(["run" => false, "end" => false, "full" => false, "private" => false]), 'json', $context);
        return new JsonResponse($jsonParty, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/{partyToken}', name: 'party.getOne', methods: ['GET'])]
    #[ParamConverter("party", options: ['mapping' => ['partyToken' => 'token']])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Party::class, groups: ['getParty']))
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
    #[OA\Tag(name: 'Party')]
    /**
     * Function to get one Party by token.
     *
     * @param Party $party
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function getOneParty(Party $party, SerializerInterface $serializer): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(["getParty"]);
        $jsonParty = $serializer->serialize($party, 'json', $context);
        return new JsonResponse($jsonParty, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/create/{Gamemodname}/{bet}/{isPrivate}', name: 'party.create', methods: ['POST'])]
    #[ParamConverter("gameMod", options: ['mapping' => ['Gamemodname' => 'name']])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Party::class, groups: ['getParty']))
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
    #[OA\Tag(name: 'Party')]
    /**
     * Create party by gamemode bet and if public
     *
     * @param SerializerInterface $serializer
     * @param RankRepository $rankRepository
     * @param PartyRepository $partyRepository
     * @param UserRepository $userRepository
     * @param GameMod $gameMod
     * @param integer $bet
     * @param string $isPrivate
     * @return JsonResponse
     */
    public function createParty(SerializerInterface $serializer, RankRepository $rankRepository, PartyRepository $partyRepository, UserRepository $userRepository, GameMod $gameMod, int $bet, string $isPrivate = "public"): JsonResponse
    {
        $rankUser =  $rankRepository->getMmr($gameMod, $userRepository->convertUserInterfaceToUser($this->getUser()));
        if ($rankUser == -1) {
            $rank = new Rank();
            $rank->setGamemod($gameMod)->setUser($this->getUser())->setMmr(15)->setStatus(true);
            $rankRepository->save($rank, true);
            if ($rank->getMmr() < $bet || $bet < 0) {
                $bet = $rank->getMmr();
            }
        } else {
            if ($rankUser < $bet || $bet < 0) {
                $bet = $rankUser;
            }
        }

        $party = new Party();
        $party->setGamemod($gameMod)
            ->setRun(false)
            ->setEnd(false)
            ->setFull(false)
            ->setBet($bet)
            ->setStatus(true)
            ->addUser($this->getUser())
            ->setPrivate($isPrivate == "private" ? true : false)
            ->setToken(md5(random_bytes(100) . $this->getUser()->getUserIdentifier()));
        $user = $this->getUser();
        $party->addUser($user);
        $partyRepository->save($party, true);
        $context = SerializationContext::create()->setGroups(["getParty"]);
        $jsonParty = $serializer->serialize($party, 'json', $context);
        return new JsonResponse($jsonParty, Response::HTTP_CREATED, ['accept' => 'json'], true);
    }

    #[Route('/join/{partyToken}', name: 'party.join', methods: ['POST'])]
    #[ParamConverter("party", options: ['mapping' => ['partyToken' => 'token']])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Party::class, groups: ['getParty']))
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
    #[OA\Tag(name: 'Party')]
    /**
     * Join party by partyToken
     *
     * @param Party $party
     * @param SerializerInterface $serializer
     * @param PartyRepository $partyRepository
     * @param RankRepository $rankRepository
     * @param UserRepository $userRepository
     * @return JsonResponse
     */
    public function joinParty(Party $party, SerializerInterface $serializer, PartyRepository $partyRepository, RankRepository $rankRepository, UserRepository $userRepository): JsonResponse
    {
        if (!$party->isFull() && !$party->isRun() && !$party->isEnd()) {
            $mmr = $rankRepository->getMmr($party->getGamemod(), $userRepository->convertUserInterfaceToUser($this->getUser()));
            if (
                $mmr >= $party->getBet() ||
                $mmr == -1 && $party->getBet() <= 15
            ) {
                if ($mmr == -1) {
                    $rank = new Rank();
                    $rank->setGamemod($party->getGamemod())->setUser($this->getUser())->setMmr(15)->setStatus(true);
                    $rankRepository->save($rank, true);
                }
                $user = $this->getUser();
                $party->addUser($user);
                count($party->getUsers()) == $party->getGamemod()->getPlayerLimit() ? $party->setFull(true) : $party->setFull(false);
                $partyRepository->save($party, true);
                $context = SerializationContext::create()->setGroups(["getParty"]);
                $jsonParty = $serializer->serialize($party, 'json', $context);
                return new JsonResponse($jsonParty, Response::HTTP_OK, ['accept' => 'json'], true);
            } else {
                return new JsonResponse(["status" => Response::HTTP_LOCKED, "message" => "you don't have enough credit"], Response::HTTP_LOCKED, ['accept' => 'json']);
            }
        } else if ($party->isFull() && !$party->isRun() && !$party->isEnd()) {
            return new JsonResponse(["status" => Response::HTTP_LOCKED, "message" => "The game is full."], Response::HTTP_LOCKED, ['accept' => 'json']);
        } else if (!$party->isFull() && $party->isRun() && !$party->isEnd()) {
            return new JsonResponse(["status" => Response::HTTP_LOCKED, "message" => "The game is already runing."], Response::HTTP_LOCKED, ['accept' => 'json']);
        } else if (!$party->isFull() && !$party->isRun() && $party->isEnd()) {
            return new JsonResponse(["status" => Response::HTTP_LOCKED, "message" => "The game is end."], Response::HTTP_LOCKED, ['accept' => 'json']);
        }
    }

    #[Route('/run/{partyToken}', name: 'party.run', methods: ['POST'])]
    #[ParamConverter("party", options: ['mapping' => ['partyToken' => 'token']])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Party::class, groups: ['getParty']))
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
    #[OA\Tag(name: 'Party')]
    /**
     * Run Party by partyToken
     *
     * @param Party $party
     * @param PartyRepository $partyRepository
     * @param CardRepository $cardRepository
     * @param RankRepository $rankRepository
     * @return JsonResponse
     */
    public function runParty(Party $party, PartyRepository $partyRepository, CardRepository $cardRepository, TagAwareCacheInterface $tagAwareCacheInterface, RankRepository $rankRepository): JsonResponse
    {
        $tagAwareCacheInterface->invalidateTags(["getAdvancement" . $party->getToken()]);
        $rankRepository->payMmr($party);
        $black = new BlackJack($party);
        $black->setDeck($cardRepository->doDeck($party));
        $cardRepository->distribCards($black);
        $jsonParty = serialize($black);

        $party->setRun(true)->setAdvancement($jsonParty);

        $partyRepository->save($party, true);
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/play/{partyToken}/{action}', name: 'party.play', methods: ['POST'])]
    #[ParamConverter("party", options: ['mapping' => ['partyToken' => 'token']])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Party::class, groups: ['getParty']))
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
    #[OA\Tag(name: 'Party')]
    /**
     * Run Party by partyToken
     *
     * @param Party $party
     * @param PartyRepository $partyRepository
     * @param UserRepository $userRepository
     * @param string $action
     * @return JsonResponse
     */
    public function playParty(Party $party, PartyRepository $partyRepository, UserRepository $userRepository, TagAwareCacheInterface $tagAwareCacheInterface, string $action = "stand"): JsonResponse
    {
        $tagAwareCacheInterface->invalidateTags(["getAdvancement" . $party->getToken()]);
        $blackJack = unserialize($party->getAdvancement());
        if (get_class($blackJack->getActualPlayer()) == "App\Entity\Croupier" || $userRepository->convertUserInterfaceToUser($this->getUser())->getId() != $blackJack->getActualPlayer()->getUser()->getId()) {
            return new JsonResponse(["status" => Response::HTTP_LOCKED, "message" => "It is not up to you to play."], Response::HTTP_LOCKED, ['accept' => 'json']);
        }
        $blackJack = $partyRepository->play($blackJack,  $action);
        if (get_class($blackJack->getActualPlayer()) == "App\Entity\Croupier") {
            $partyRepository->playCroupiers($blackJack);
        }
        $party->setAdvancement(serialize($blackJack));
        $partyRepository->save($party, true);

        return new JsonResponse($blackJack, Response::HTTP_OK, ['accept' => 'json']);
    }

    #[Route('/advancement/{partyToken}', name: 'party.advancement', methods: ['GET'])]
    #[ParamConverter("party", options: ['mapping' => ['partyToken' => 'token']])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Party::class, groups: ['getParty']))
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
    #[OA\Tag(name: 'Party')]
    /**
     * Get advancement of a Party by partyToken
     *
     * @param Party $party
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function advancementParty(Party $party, SerializerInterface $serializer, TagAwareCacheInterface $tagAwareCacheInterface): JsonResponse
    {
        if ($party->isRun() == false) {
            return new JsonResponse(["status" => Response::HTTP_LOCKED, "message" => "The game isn't runing."], Response::HTTP_LOCKED, ['accept' => 'json']);
        }
        $jsonAdvancement = $tagAwareCacheInterface->get("getAdvancement", function (ItemInterface $itemInterface) use ($party, $serializer) {
            $itemInterface->tag("getAdvancement" . $party->getToken());

            $black = unserialize($party->getAdvancement());
            $context = SerializationContext::create()->setGroups(["getPlay"]);
            return $serializer->serialize($black, 'json', $context);
        });

        return new JsonResponse($jsonAdvancement, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/leave/{partyToken}', name: 'party.leave', methods: ['POST'])]
    #[ParamConverter("party", options: ['mapping' => ['partyToken' => 'token']])]
    #[OA\Response(
        response: 200,
        description: 'Successful leave'
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request'
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized'
    )]
    #[OA\Tag(name: 'Party')]
    /**
     * Leave Party by partyToken
     *
     * @param Party $party
     * @return JsonResponse
     */
    public function leaveParty(Party $party): JsonResponse
    {
        if ($party->isRun() == false) {
            if (in_array($this->getUser(), $party->getUsers()->toArray())) {
                $party->removeUser($this->getUser());
            }
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } else {
            return new JsonResponse(["status" => Response::HTTP_LOCKED, "message" => "The game is already runing."], Response::HTTP_LOCKED, ['accept' => 'json']);
        }
    }

    #[Route('/{partyToken}/delete', name: 'party.delete', methods: ['DELETE'])]
    #[ParamConverter("party", options: ['mapping' => ['partyToken' => 'token']])]
    #[OA\Response(
        response: 200,
        description: 'Successful deleted'
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request'
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized'
    )]
    #[OA\Tag(name: 'Party')]
    /**
     * Delete Party by partyToken
     *
     * @param Party $party
     * @param PartyRepository $partyRepository
     * @return JsonResponse
     */
    #[IsGranted('ROLE_ADMIN')]
    public function deleteParty(Party $party,  PartyRepository $partyRepository): JsonResponse
    {
        $partyRepository->remove($party, true);
        return new JsonResponse([], Response::HTTP_NO_CONTENT, ['accept' => 'json'], true);
    }


    #[Route('/{partyToken}', name: 'party.status', methods: ['DELETE'])]
    #[ParamConverter("party", options: ['mapping' => ['partyToken' => 'token']])]
    #[OA\Response(
        response: 200,
        description: 'Successful deleted'
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request'
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized'
    )]
    #[OA\Tag(name: 'Party')]
    /**
     * Set status of a Party on false by partyToken
     *
     * @param Party $party
     * @param PartyRepository $partyRepository
     * @return JsonResponse
     */
    #[IsGranted('ROLE_ADMIN')]
    public function statusParty(Party $party,  PartyRepository $partyRepository): JsonResponse
    {
        $party->setStatus(false);
        $partyRepository->save($party, true);
        return new JsonResponse([], Response::HTTP_NO_CONTENT, ['accept' => 'json'], true);
    }

    #[Route('/history/user/{idUser}', name: 'party.historyUser', methods: ['GET'])]
    #[ParamConverter("user", options: ['mapping' => ['idUser' => 'id']])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Party::class, groups: ['getParty']))
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
    #[OA\Tag(name: 'Party')]
    /**
     * Run Party history by idUser
     *
     * @param User $user
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function getHistoryByUser(User $user, SerializerInterface $serializer): JsonResponse
    {
        $user == null ? $this->getUser() : $user = $user;
        $context = SerializationContext::create()->setGroups(["getPartyHistory"]);
        $jsonParty = $serializer->serialize($user, 'json', $context);
        return new JsonResponse($jsonParty, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/history/party/{partyToken}', name: 'party.history', methods: ['GET'])]
    #[ParamConverter("party", options: ['mapping' => ['partyToken' => 'token']])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Party::class, groups: ['getParty']))
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
    #[OA\Tag(name: 'Party')]
    /**
     * Run Party history of one game
     *
     * @param Party $party
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function getHistoryParty(Party $party, SerializerInterface $serializer, TagAwareCacheInterface $tagAwareCacheInterface): JsonResponse
    {
        $jsonHistoryParty = $tagAwareCacheInterface->get("historyParty", function (ItemInterface $itemInterface) use ($party, $serializer) {
            $itemInterface->tag("historyParty" . $party->getToken());
            $context = SerializationContext::create()->setGroups(["getPartyHistoryByParty"]);
            return $serializer->serialize($party, 'json', $context);
        });
        return new JsonResponse($jsonHistoryParty, Response::HTTP_OK, ['accept' => 'json'], true);
    }
}
