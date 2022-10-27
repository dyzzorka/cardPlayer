<?php

namespace App\Controller;

use App\Entity\GameMod;
use App\Entity\Party;
use App\Entity\User;
use App\Repository\PartyRepository;
use phpDocumentor\Reflection\Types\Boolean;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
        $jsonParty = $serializer->serialize($partyRepository->findBy(["run" => false, "end" => false, "full" => false, "private" => false]), 'json', ["groups" => "getParty"]);
        return new JsonResponse($jsonParty, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/{partyToken}', name: 'party.one', methods: ['GET'])]
    #[ParamConverter("party", options: ['mapping' => ['partyToken' => 'token']])]
    public function getOneParty(Party $party, SerializerInterface $serializer): JsonResponse
    {
        $jsonParty = $serializer->serialize($party, 'json', ["groups" => "getParty"]);
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
            $jsonParty = $serializer->serialize($party, 'json', ["groups" => "getParty"]);
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
        $jsonParty = $serializer->serialize($party, 'json', ["groups" => "getParty"]);
        return new JsonResponse($jsonParty, Response::HTTP_CREATED, ['accept' => 'json'], true);
    }

    #[Route('/run/{partyToken}', name: 'party.run', methods: ['POST'])]
    #[ParamConverter("party", options: ['mapping' => ['partyToken' => 'token']])]
    public function runParty(Party $party, SerializerInterface $serializer, PartyRepository $partyRepository): JsonResponse
    {
        $party->setRun(true);
        $partyRepository->save($party, true);

        /*Play Game here*/

        $jsonParty = $serializer->serialize($party, 'json', ["groups" => "getParty"]);
        return new JsonResponse($jsonParty, Response::HTTP_OK, ['accept' => 'json'], true);
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

    #[Route('/history/{idUser}', name: 'party.historyUser', methods: ['GET'])]
    #[ParamConverter("user", options: ['mapping' => ['idUser' => 'id']])]
    public function getHistoryByUser(?User $user, SerializerInterface $serializer): JsonResponse
    {
        $user == null ? $this->getUser() : $user = $user;
        $jsonParty = $serializer->serialize($user, 'json', ["groups" => "getPartyHistory"]);
        return new JsonResponse($jsonParty, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /*Rest a faire 
    
    Historique ...
    clear http response
    clear methode
    clear group 
    doc method
    Object->manager  => Repo->add
/leave party
    et a reflechir
tiré une cards + gestion deck user
    */
}
