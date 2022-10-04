<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Card;
use Symfony\Component\Console\Output\ConsoleOutput;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $output = new ConsoleOutput();
        $json = file_get_contents(__DIR__.'/cards.json');
        $json = json_decode($json);
        $count = 1;
        $family = ['club', 'diamond', 'heart', 'spade','back'];
        $family_count = 0;

        foreach($json as $key => &$value) {
            $card = new Card();
            if ($family[$family_count] == 'back'){
                $card->setValue(0)
                    ->setImage($value)
                    ->setStatus(true)
                    ->setFamily('back');
            }else{
                $card->setValue($count)
                    ->setImage($value)
                    ->setStatus(true)
                    ->setFamily($family[$family_count]);
            }
            if ($count==13){
                $count = 1;
                $family_count++;
            }else{
                $count++;
            }
            $manager->persist($card);
        }
        $manager->flush();
    }
}
