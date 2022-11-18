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
use Symfony\Component\Validator\Constraints\Blank;

#[Route('/api/party')]
class PartyController extends AbstractController
{
    #[Route('/', name: 'party.getAll', methods: ['GET'])]
    public function getAll(SerializerInterface $serializer, PartyRepository $partyRepository): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(["getParty"]);
        $jsonParty = $serializer->serialize($partyRepository->findBy(["run" => false, "end" => false, "full" => false, "private" => false]), 'json', $context);
        return new JsonResponse($jsonParty, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/{partyToken}', name: 'party.getOne', methods: ['GET'])]
    #[ParamConverter("party", options: ['mapping' => ['partyToken' => 'token']])]
    public function getOneParty(Party $party, SerializerInterface $serializer): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(["getParty"]);
        $jsonParty = $serializer->serialize($party, 'json', $context);
        return new JsonResponse($jsonParty, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/create/{Gamemodname}/{bet}/{isPrivate}', name: 'party.create', methods: ['POST'])]
    #[ParamConverter("gameMod", options: ['mapping' => ['Gamemodname' => 'name']])]
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
    public function runParty(Party $party, PartyRepository $partyRepository, CardRepository $cardRepository, RankRepository $rankRepository): JsonResponse
    {
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
    public function playParty(Party $party, PartyRepository $partyRepository, UserRepository $userRepository, string $action = "stand"): JsonResponse
    {

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
    public function advancementParty(Party $party, SerializerInterface $serializer): JsonResponse
    {
        if ($party->isRun() == false) {
            return new JsonResponse(["status" => Response::HTTP_LOCKED, "message" => "The game isn't runing."], Response::HTTP_LOCKED, ['accept' => 'json']);
        }
        $black = unserialize($party->getAdvancement());
        $context = SerializationContext::create()->setGroups(["getPlay"]);
        $jsonParty = $serializer->serialize($black, 'json', $context);
        return new JsonResponse($jsonParty, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/leave/{partyToken}', name: 'party.leave', methods: ['POST'])]
    #[ParamConverter("party", options: ['mapping' => ['partyToken' => 'token']])]
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
    public function deleteParty(Party $party,  PartyRepository $partyRepository): JsonResponse
    {
        $partyRepository->remove($party, true);
        return new JsonResponse([], Response::HTTP_NO_CONTENT, ['accept' => 'json'], true);
    }


    #[Route('/{partyToken}', name: 'party.status', methods: ['DELETE'])]
    #[ParamConverter("party", options: ['mapping' => ['partyToken' => 'token']])]
    public function statusParty(Party $party,  PartyRepository $partyRepository): JsonResponse
    {
        $party->setStatus(false);
        $partyRepository->save($party, true);
        return new JsonResponse([], Response::HTTP_NO_CONTENT, ['accept' => 'json'], true);
    }

    #[Route('/history/user/{idUser}', name: 'party.historyUser', methods: ['GET'])]
    #[ParamConverter("user", options: ['mapping' => ['idUser' => 'id']])]
    public function getHistoryByUser(User $user, SerializerInterface $serializer): JsonResponse
    {
        $user == null ? $this->getUser() : $user = $user;
        $context = SerializationContext::create()->setGroups(["getPartyHistory"]);
        $jsonParty = $serializer->serialize($user, 'json', $context);
        return new JsonResponse($jsonParty, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/history/party/{partyToken}', name: 'party.history', methods: ['GET'])]
    #[ParamConverter("party", options: ['mapping' => ['partyToken' => 'token']])]
    public function getHistoryParty(Party $party, SerializerInterface $serializer): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(["getPartyHistoryByParty"]);
        $jsonParty = $serializer->serialize($party, 'json', $context);
        return new JsonResponse($jsonParty, Response::HTTP_OK, ['accept' => 'json'], true);
    }


    /*Rest a faire 

    les truc de location
    clear group 
    doc method
    gerer les acces
    ajouter le cache ou c'est necesaire

    */
}
