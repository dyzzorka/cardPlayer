<?php

namespace App\Controller;

use App\Entity\BlackJack;
use App\Entity\Card;
use App\Entity\GameMod;
use App\Entity\Party;
use App\Entity\User;
use App\Repository\CardRepository;
use App\Repository\PartyRepository;
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
    #[Route('/', name: 'party.all', methods: ['GET'])]
    public function getAll(SerializerInterface $serializer, PartyRepository $partyRepository): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(["getParty"]);
        $jsonParty = $serializer->serialize($partyRepository->findBy(["run" => false, "end" => false, "full" => false, "private" => false]), 'json', $context);
        return new JsonResponse($jsonParty, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/{partyToken}', name: 'party.one', methods: ['GET'])]
    #[ParamConverter("party", options: ['mapping' => ['partyToken' => 'token']])]
    public function getOneParty(Party $party, SerializerInterface $serializer): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(["getParty"]);
        $jsonParty = $serializer->serialize($party, 'json', $context);
        return new JsonResponse($jsonParty, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/join/{partyToken}', name: 'party.join', methods: ['POST'])]
    #[ParamConverter("party", options: ['mapping' => ['partyToken' => 'token']])]
    public function joinParty(Party $party, SerializerInterface $serializer, PartyRepository $partyRepository): JsonResponse
    {
        if (!$party->isFull() && !$party->isRun() && !$party->isEnd()) {
            $user = $this->getUser();
            $party->addUser($user);
            count($party->getUsers()) == $party->getGamemod()->getPlayerLimit() ? $party->setFull(true) : $party->setFull(false);
            $partyRepository->save($party, true);
            $context = SerializationContext::create()->setGroups(["getParty"]);
            $jsonParty = $serializer->serialize($party, 'json', $context);
            return new JsonResponse($jsonParty, Response::HTTP_OK, ['accept' => 'json'], true);
        } else if ($party->isFull() && !$party->isRun() && !$party->isEnd()) {
            return new JsonResponse(["status" => Response::HTTP_LOCKED, "message" => "The game is full."], Response::HTTP_LOCKED, ['accept' => 'json'], true);
        } else if (!$party->isFull() && $party->isRun() && !$party->isEnd()) {
            return new JsonResponse(["status" => Response::HTTP_LOCKED, "message" => "The game is already runing."], Response::HTTP_LOCKED, ['accept' => 'json'], true);
        } else if (!$party->isFull() && !$party->isRun() && $party->isEnd()) {
            return new JsonResponse(["status" => Response::HTTP_LOCKED, "message" => "The game is end."], Response::HTTP_LOCKED, ['accept' => 'json'], true);
        }
    }

    #[Route('/create/{Gamemodname}/{isPrivate}', name: 'party.create', methods: ['POST'])]
    #[ParamConverter("gameMod", options: ['mapping' => ['Gamemodname' => 'name']])]
    public function createParty(SerializerInterface $serializer, PartyRepository $partyRepository, GameMod $gameMod, string $isPrivate = "public"): JsonResponse
    {
        $party = new Party();
        $party->setGamemod($gameMod)
            ->setRun(false)
            ->setEnd(false)
            ->setFull(false)
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

    #[Route('/leave/{partyToken}', name: 'party.leave', methods: ['POST'])]
    #[ParamConverter("party", options: ['mapping' => ['partyToken' => 'token']])]
    public function leaveParty(Party $party, SerializerInterface $serializer, PartyRepository $partyRepository): JsonResponse
    {
        $user = $this->getUser();
        if (in_array($user, $party->getUsers()->toArray())) {
            $party->removeUser($user);
        }

        /* appliquer la baisse de mmr et autre action en cas de ff -> faire une methode qui reprend le update dans rankrepo */

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/run/{partyToken}', name: 'party.run', methods: ['POST'])]
    #[ParamConverter("party", options: ['mapping' => ['partyToken' => 'token']])]
    public function runParty(Party $party, PartyRepository $partyRepository, CardRepository $cardRepository): JsonResponse
    {
        $black = new BlackJack($party);
        $black->setDeck($cardRepository->doDeck($party));
        $cardRepository->distribCards($black);
        $jsonParty =  serialize($black);

        $party->setRun(true)->setAdvancement($jsonParty);

        $partyRepository->save($party, true);
        // $user = unserialize($jsonParty);
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/advancement/{partyToken}', name: 'party.advancement', methods: ['GET'])]
    #[ParamConverter("party", options: ['mapping' => ['partyToken' => 'token']])]
    public function advancementParty(Party $party, SerializerInterface $serializer, PartyRepository $partyRepository, CardRepository $cardRepository): JsonResponse
    {
        /* gerer si pas run */
        $black =  unserialize($party->getAdvancement());

        $context = SerializationContext::create()->setGroups(["getPlay"]);
        $jsonParty = $serializer->serialize($black, 'json', $context);
        return new JsonResponse($jsonParty, Response::HTTP_OK, ['accept' => 'json'], true);
    }


    #[Route('/history/{idUser}', name: 'party.historyUser', methods: ['GET'])]
    #[ParamConverter("user", options: ['mapping' => ['idUser' => 'id']])]
    public function getHistoryByUser(?User $user, SerializerInterface $serializer): JsonResponse
    {
        $user == null ? $this->getUser() : $user = $user;
        $context = SerializationContext::create()->setGroups(["getPartyHistory"]);
        $jsonParty = $serializer->serialize($user, 'json', $context);
        return new JsonResponse($jsonParty, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /*Rest a faire 

    Historique ...
    clear http response
    clear methode
    les truc de location
    clear group 
    doc method
    ajouter le cache ou c'est necesaire
    tiré une cards + gestion deck user
    enlever la route test ici

    */
}
