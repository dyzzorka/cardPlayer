<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;

#[Route('/card', name: 'app_card')]
class CardController extends AbstractController
{
    #[Route('', name: 'app_card')]
    public function index(/*Serializer $serializer, ValidatorInterface $validator*/): JsonResponse
    {
        // $errors = $validator->validate($event);
        // if($errors->count()>0){
        //     return new JsonResponse($serializer ->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        // }
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/CardController.php',
        ]);
    }
}
