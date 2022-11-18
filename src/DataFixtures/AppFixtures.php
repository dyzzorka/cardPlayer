<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Card;
use App\Entity\GameMod;
use App\Entity\Party;
use App\Entity\Rank;
use App\Entity\User;
use App\Repository\CardRepository;
use App\Repository\RankRepository;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class AppFixtures extends Fixture
{
    /**
     * Undocumented variable
     *
     * @var UserPasswordHasherInterface
     */
    private $passwordHasher;
    private $rankRepository;
    private $cardRepository;

    public function __construct(UserPasswordHasherInterface $passwordHasher, RankRepository $rankRepository, CardRepository $cardRepository)
    {
        $this->passwordHasher = $passwordHasher;
        $this->rankRepository = $rankRepository;
        $this->cardRepository = $cardRepository;
    }
    public function load(ObjectManager $manager): void
    {
        $output = new ConsoleOutput();
        $json = file_get_contents(__DIR__ . '/cards.json');
        $json = json_decode($json);
        $count = 1;
        $family = ['club', 'diamond', 'heart', 'spade', 'back'];
        $family_count = 0;

        foreach ($json as $value) {
            $card = new Card();
            if ($family[$family_count] == 'back') {
                $card->setValue(0)
                    ->setImage($value)
                    ->setStatus(true)
                    ->setFamily('back');
            } else {
                $card->setValue($count)
                    ->setImage($value)
                    ->setStatus(true)
                    ->setFamily($family[$family_count]);
            }
            if ($count == 13) {
                $count = 1;
                $family_count++;
            } else {
                $count++;
            }
            $manager->persist($card);
        }
        $manager->flush();


        $userUser = new User();
        $userUser->setUsername("admin")->setRoles(['ROLE_ADMIN'])->setPassword($this->passwordHasher->hashPassword($userUser, "password"))->setStatus(true);
        $manager->persist($userUser);
        $manager->flush();


        $cards = $this->cardRepository->findAll();

        $users = array();
        for ($i = 0; $i < 25; $i++) {
            $user = new User();
            $user->setUsername("user" . $i)
                ->setRoles(['ROLE_USER'])
                ->setPassword($this->passwordHasher->hashPassword($userUser, "user" . $i))
                ->setStatus(true);

            $manager->persist($user);
            $manager->flush();
            array_push($users, $user);
        }
        $games = array();
        for ($i = 0; $i < 10; $i++) {
            $game = new GameMod();

            $game->setName("gamemod" . $i)
                ->setDescription("lalala")
                ->setPlayerLimit($i)
                ->setStatus(true);

            foreach ($cards as $card) {
                $game->addCard($card);
            }

            $manager->persist($game);
            $manager->flush();
            array_push($games, $game);
        }

        for ($i = 0; $i < 300; $i++) {
            $party = new Party();
            $user = $users[random_int(0, 24)];
            $gm = $games[random_int(0, 9)];
            $run = random_int(0, 1);
            $end = random_int(0, 1);
            $full = random_int(0, 1);
            $pv = random_int(0, 1);
            $party->setToken(md5(random_bytes(100) . $user->getUserIdentifier()))
                ->setGamemod($gm)
                ->setBet(10)
                ->setRun($run)
                ->setEnd($end)
                ->setFull($full)
                ->setPrivate($pv)
                ->setStatus(true)
                ->addUser($user);
            $manager->persist($party);
            $manager->flush();
        }

        for ($i = 0; $i < 300; $i++) {

            $mmr = random_int(10, 45);
            $user = $users[random_int(0, 24)];
            $gameMod = $games[random_int(0, 9)];

            $rank = $this->rankRepository->findOneBy(array("gamemod" => $gameMod, "user" => $user));
            if ($rank === null) {
                $rank = new Rank();
                $rank->setUser($user)->setGamemod($gameMod)->setMmr($mmr)->setStatus(true);
                $this->rankRepository->save($rank, true);
            } else {
                $actualMmr = $rank->getMmr();
                $rank->setMmr($actualMmr += $mmr)->setStatus(true);
                $this->rankRepository->save($rank, true);
            }
        }
    }
}
