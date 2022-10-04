<?php

namespace App\Controller;

use App\Entity\Picture;
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
    public function createPicture(Request $request, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer): JsonResponse
    {
        $picture = new Picture();
        $files = $request->files->get('file');
        $picture->setFile($files)
        ->setMimeType($files->getClientMimeType())
        ->setRealName($files->getClientOriginalName())
        ->setPublicPath('assets/pictures')
        ->setStatus(true)
        ->setUploadDate(new \DateTime())
        ;
        $entityManager->persist($picture);
        $entityManager->flush();

        $jsonPicture = $serializer->serialize($picture, 'json', ["groups" => "GetPicture"]);

        $location = $urlGenerator->generate('picture.get', ['pictureId' => $picture->getId()], UrlGeneratorInterface::ABSOLUTE_PATH);
        return new JsonResponse($jsonPicture, Response::HTTP_CREATED, ["Location" => $location], true);
    }
}
