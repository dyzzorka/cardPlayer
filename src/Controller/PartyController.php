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
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\Blank;
use OpenApi\Attributes as OA;

#[Route('/api/party')]
class PartyController extends AbstractController
{
    #[Route('/', name: 'party.all', methods: ['GET'])]
    #[OA\Tag(name: 'Party')]
    /**
     * Function to get all Party.
     *
     */
    public function getAll(SerializerInterface $serializer, PartyRepository $partyRepository): JsonResponse
    {
        $jsonParty = $serializer->serialize($partyRepository->findBy(["run" => false, "end" => false, "full" => false, "private" => false]), 'json', ["groups" => "getParty"]);
        return new JsonResponse($jsonParty, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
     * Function to get all Party.
    */
    #[Route('/test', name: 'party.try', methods: ['GET'])]
    #[OA\Tag(name: 'Party')]
    public function try(SerializerInterface $serializer, CardRepository $cardRepository, UserRepository $userRepository, PartyRepository $partyRepository): JsonResponse
    {

        $black = new BlackJack($partyRepository->find(3));
        // dd($black->getActualPlayer());
        $jsonParty = $serializer->serialize($black, 'json', ["groups"=> "getPlay"]);
        $user = $serializer->deserialize($jsonParty, BlackJack::class, 'json',[AbstractNormalizer::OBJECT_TO_POPULATE => $black]);
        dd($user);   /* WTF */
        // $jsonParty2 = $serializer->serialize($user, 'json', ["groups"=> "getPlay"]);
        // dd($jsonParty);
        return new JsonResponse($jsonParty, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
     * Function to get one Party by token.
     * @param string $token
    */
    #[Route('/{partyToken}', name: 'party.one', methods: ['GET'])]
    #[ParamConverter("party", options: ['mapping' => ['partyToken' => 'token']])]
    #[OA\Tag(name: 'Party')]
    public function getOneParty(Party $party, SerializerInterface $serializer): JsonResponse
    {
        $jsonParty = $serializer->serialize($party, 'json', ["groups" => "getParty"]);
        return new JsonResponse($jsonParty, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
     * Join party by token
    */
    #[Route('/join/{partyToken}', name: 'party.join', methods: ['POST'])]
    #[ParamConverter("party", options: ['mapping' => ['partyToken' => 'token']])]
    #[OA\Tag(name: 'Party')]
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
    #[OA\Tag(name: 'Party')]
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

    /**
     * Run the Party from partyToken
     */
    #[Route('/run/{partyToken}', name: 'party.run', methods: ['POST'])]
    #[ParamConverter("party", options: ['mapping' => ['partyToken' => 'token']])]
    #[OA\Tag(name: 'Party')]
    public function runParty(Party $party, SerializerInterface $serializer, PartyRepository $partyRepository): JsonResponse
    {
        $party->setRun(true);
        $partyRepository->save($party, true);

        /*Play Game here*/

        $jsonParty = $serializer->serialize($party, 'json', ["groups" => "getParty"]);
        return new JsonResponse($jsonParty, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
     * Leave a party from partyToken
     */
    #[Route('/leave/{partyToken}', name: 'party.leave', methods: ['POST'])]
    #[ParamConverter("party", options: ['mapping' => ['partyToken' => 'token']])]
    #[OA\Tag(name: 'Party')]
    public function leaveParty(Party $party, SerializerInterface $serializer, PartyRepository $partyRepository): JsonResponse
    {
        $user = $this->getUser();
        if (in_array($user, $party->getUsers()->toArray())) {
            $party->removeUser($user);
        }

        /* appliquer la baisse de mmr et autre action en cas de ff */

        return new JsonResponse([], Response::HTTP_NO_CONTENT, ['accept' => 'json'], true);
    }

    /**
     *  Delete Party from 
     */
    // duplicata
    #[Route('/{partyToken}/delete', name: 'party.delete', methods: ['DELETE'])]
    #[ParamConverter("party", options: ['mapping' => ['partyToken' => 'token']])]
    #[OA\Tag(name: 'Party')]
    public function deleteParty(Party $party,  PartyRepository $partyRepository): JsonResponse
    {
        $partyRepository->remove($party, true);
        return new JsonResponse([], Response::HTTP_NO_CONTENT, ['accept' => 'json'], true);
    }

    /**
     *  Delete a party from partyToken
     */
    #[Route('/{partyToken}', name: 'party.status', methods: ['DELETE'])]
    #[ParamConverter("party", options: ['mapping' => ['partyToken' => 'token']])]
    #[OA\Tag(name: 'Party')]
    public function statusParty(Party $party,  PartyRepository $partyRepository): JsonResponse
    {
        $party->setStatus(false);
        $partyRepository->save($party, true);
        return new JsonResponse([], Response::HTTP_NO_CONTENT, ['accept' => 'json'], true);
    }

    /**
     * Get History from idUser
     */
    #[Route('/history/{idUser}', name: 'party.historyUser', methods: ['GET'])]
    #[ParamConverter("user", options: ['mapping' => ['idUser' => 'id']])]
    #[OA\Tag(name: 'Party')]
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
    ajouter le cache ou c'est necesaire
    tiré une cards + gestion deck user
    enlever la route test ici
    */
}
