<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Entity\Card;
use App\Repository\GameModRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/picture')]
class PictureController extends AbstractController
{
    #[Route('/{pictureId}', name: 'picture.get', methods:['GET'])]
    #[ParamConverter("picture", options: ['mapping' => ['pictureId' => 'id']])]
    public function getPicture(Picture $picture, UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer, Request $request): JsonResponse
    {
        $relativeLocation = $picture->getPublicPath() . DIRECTORY_SEPARATOR . $picture->getRealPath();
        $location = $request->getUriForPath('/');
        $location = $location . $relativeLocation;

        return new JsonResponse($serializer->serialize($picture, 'json', ['groups'=>'GetPicture']), Response::HTTP_OK, ['Location' => $location], true);
    }

    #[Route('/create', name: 'picture.create', methods:['POST'])]
    public function createPicture(Request $request, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer, GameModRepository $gameModRepository): JsonResponse
    {

        $files = $request->files->get('file');
        $countColumn = 0;
        $countRows = 0;
        $width = 168;
        $height = 244;
        $family = ['club', 'diamond', 'heart', 'spade'];

        $path = $files->getPathName();
        $imgBase64 = file_get_contents(realpath($files->getPathName()));

        while (1){

            $card = new Card();
            $picture = new Picture();
            $picture
                ->setFile($files)
                ->setMimeType($files->getClientMimeType())
                ->setRealName($files->getClientOriginalName())
                ->setPublicPath('assets/pictures')
                ->setStatus(true)
                ->setUploadDate(new \DateTime())
            ;

            $filename = $files->getClientOriginalName();
            $valueAndFamily = (string)$countColumn+1 . '_' . $family[$countRows];

            $card->setValue((int)$valueAndFamily[0])
                ->setFamily(explode('.', $valueAndFamily[1])[0])
                ->setStatus(true)
                ->addGamemod($gameModRepository->findOneBySomeField('blackjack'))
            ;

            // $fp = fopen('\assets\pictures\test.png', 'wp');
            // fwrite($fp, file_get_contents($files->getPathName()));
            // fclose($fp);

            $imagick = new \Imagick('.\assets\pictures\test.png');
            // $imagick->readimageblob($imgBase64);
            $imagick->cropImage(168, 242, $countColumn*$width, $countRows*$height);
            $imagick->thumbnailImage($width, $height);
            $imagick->writeimage($countColumn+1 .'_'.$family[$countRows].'.png');
            if ($countColumn==12 && $countRows == 3){
                break;
            }else if ($countColumn==12){
                $countColumn = 0;
                $countRows++;
            }else{
                $countColumn++;
            }
            $entityManager->persist($card);
            $entityManager->persist($picture);
        }

        // $picture
        //     ->setFile($files)
        //     ->setMimeType($files->getClientMimeType())
        //     ->setRealName($files->getClientOriginalName())
        //     ->setPublicPath('assets/pictures')
        //     ->setStatus(true)
        //     ->setUploadDate(new \DateTime())
        // ;

        // $filename = $files->getClientOriginalName();
        // $valueAndFamily = explode('_', $filename);

        // $card->setValue((int)$valueAndFamily[0])
        //     ->setFamily(explode('.', $valueAndFamily[1])[0])
        //     ->setStatus(true)
        //     ->addGamemod($gameModRepository->findOneBySomeField('blackjack'))
        // ;

        $entityManager->flush();

        $jsonPicture = $serializer->serialize($picture, 'json', ["groups" => "GetPicture"]);

        $location = $urlGenerator->generate('picture.get', ['pictureId' => $picture->getId()], UrlGeneratorInterface::ABSOLUTE_PATH);
        return new JsonResponse($jsonPicture, Response::HTTP_CREATED, ["Location" => $location], true);
    }
}
