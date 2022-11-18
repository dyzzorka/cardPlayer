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
        // ANCHOR INSERT ALL CARDS

        // $output = new ConsoleOutput();
        $count = 1;
        $family = ['club', 'diamond', 'heart', 'spade'];
        $family_count = 0;

        // $package = new Package(new EmptyVersionStrategy());

        // $imagick = new \Imagick('svg-cards.png');
        // $imagick->cropImage(30, 30, 0, 0);
        // header("Content-Type: image/jpg");
        // var_dump($imagick->getImageBlob());

        // foreach($json as $key => $value) {
        //     $card->setValue(1)
        //         ->addImage($picture)
        //         ->setStatus(true)
        //         ->setFamily($family[$family_count]);
        //     $manager->persist($card);

        // }

        $package = new Package(new EmptyVersionStrategy());
        $var = [
            "63760877afb32_1_club.png",
        ];
        $countColumn = 0;
        $countRows = 0;
        $width = 168;
        $height = 244;
        $family = ['club', 'diamond', 'heart', 'spade'];

        while (1){

            $relativeLocation = ".." . $this->toGenericPath('..','public','assets', 'picture', 'svg-cards.png');

            $picture = new Picture();
            $picture->setFile($imagick->getImageBlob())
                ->setMimeType($files->getClientMimeType())
                ->setRealName($files->getClientOriginalName())
                ->setPublicPath('assets/pictures')
                ->setStatus(true)
                ->setUploadDate(new \DateTime())
            ;
            $card = new Card();
            $card->setValue($countColumn+1)
                ->addImage($picture)
                ->setStatus(true)
                ->setFamily($family[$countRows])
            ;
            $picture->setCard($card);

            //$relativeLocation = '.\assets\picture' . DIRECTORY_SEPARATOR . 'svg-cards.png';
            // $imagick = new \Imagick($relativeLocation);
            // $imagick->cropImage(168, 242, $countColumn*$width, $countRows*$height);
            // $imagick->thumbnailImage($width, $height);
            // $imagick->writeimage($countColumn+1 .'_'.$family[$countRows].'.png');
            // if ($countColumn==12 && $countRows == 3){
            //     break;
            // }else if ($countColumn==12){
            //     $countColumn = 0;
            //     $countRows++;
            // }else{
            //     $countColumn++;
            // }
            // $manager->persist($card);
            // $manager->persist($picture);
        }
        $manager->flush();

        // Set all cards 

        // INSERT ONE GAME TO BDD
        // $game = new GameMod;
        // $game->setName('président')->setDescription('Jeu du président')->setPlayerLimit(4)->setStatus(true);
        // $manager->persist($game);
        // $manager->flush();
    }
}
