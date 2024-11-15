<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/api/user/get-users', name: 'app_get_users', methods: ["GET"])]
    public function getUsers(
        UserRepository         $userRepository,
        SerializerInterface     $serializer
    ): JsonResponse
    {
        $user = $userRepository->findAll();
        $response = $serializer->serialize($user, "json", ["groups" => "User"]);
        return $this->json(json_decode($response), Response::HTTP_OK);
    }
}
