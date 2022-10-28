<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Card;
use App\Entity\GameMod;
use Symfony\Component\Console\Output\ConsoleOutput;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // ANCHOR INSERT ALL CARDS

        // $output = new ConsoleOutput();
        $json = file_get_contents(__DIR__.'/cards.json');
        $json = json_decode($json);
        $count = 1;
        $family = ['club', 'diamond', 'heart', 'spade'];
        $family_count = 0;
        $im = imagecreatefrompng('example.png'); 
        $im = gd_info();
        foreach($json as $key => $value) {
            $card = new Card();
            $picture = new Picture();
            dd( );
            $picture->setFile($files)
                    ->setMimeType($files->getClientMimeType())
                    ->setRealName($files->getClientOriginalName())
                    ->setPublicPath('assets/pictures')
                    ->setStatus(true)
                    ->setUploadDate(new \DateTime());
            $card->setValue($count)
                ->addImage($picture)
                ->setStatus(true)
                ->setFamily($family[$family_count]);
            if ($count==13){
                $count = 1;
                $family_count++;
            }else{
                $count++;
            }
            $manager->persist($picture);
            $manager->persist($card);

        }

        // Set all cards 

        // INSERT ONE GAME TO BDD
        // $game = new GameMod;
        // $game->setName('président')->setDescription('Jeu du président')->setPlayerLimit(4)->setStatus(true);
        // $manager->persist($game);
        $manager->flush();
    }
}
