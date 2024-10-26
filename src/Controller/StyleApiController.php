<?php

namespace App\Controller;

use App\Entity\Style;
use App\Repository\StyleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class StyleApiController extends AbstractController
{
    #[Route('/api/style/get-styles', name: 'app_gat_style', methods: ["GET"])]
    public function getStyle(
        StyleRepository         $styleRepository,
        SerializerInterface     $serializer
    ): JsonResponse
    {
        $style = $styleRepository->findAll();
        $response = $serializer->serialize($style, "json", ["groups" => "Style"]);
        return $this->json(json_decode($response), Response::HTTP_OK);
    }
    #[Route('/api/style/add-style', name: 'app_add_style', methods: ["POST"])]
    public function addStyle(
        StyleRepository         $styleRepository,
        SerializerInterface     $serializer,
        Request                 $request,
        EntityManagerInterface  $entityManager
    ): JsonResponse
    {
        $data = json_decode($request->getContent());
        
        if (empty($data->id) && empty($data->name)) {
            return $this->json(["message" => "Invalid request"], Response::HTTP_BAD_REQUEST);
        }
        $style = new Style();
        $style->setName($data->name);
        $entityManager->persist($style);
        $entityManager->flush();

        $response = json_decode($serializer->serialize($style, "json"));
        return $this->json([
            "message" => "Stylee créé avec succès",
            "style"=> $response,
        ], Response::HTTP_OK);
    }
    #[Route('/api/style/remove-style', name: 'app_remove_style', methods: ["DELETE"])]
    public function removeStyle(
        StyleRepository         $styleRepository,
        SerializerInterface     $serializer,
        Request                 $request,
        EntityManagerInterface  $entityManager
    ): JsonResponse
    {
        $data = json_decode($request->getContent());
        $id = $data->id;
        $style = $styleRepository->find($id);
        if (!$style) {   
            return $this->json(["message" => "Style introuvable"], Response::HTTP_NOT_FOUND);
        }
        $entityManager->remove($style);
        $entityManager->flush();

        return $this->json([
            "message" => "Style supprimé avec succès",
        ], Response::HTTP_OK);
    }
    #[Route('/api/style/update-style', name: 'app_update_style', methods: ["PUT"])]
    public function updateStyle(
        StyleRepository         $styleRepository,
        SerializerInterface     $serializer,
        Request                 $request,
        EntityManagerInterface  $entityManager
    ): JsonResponse
    {
        $data = json_decode($request->getContent());
        
        if (empty($data->id) && empty($data->name)) {
            return $this->json(["message" => "Invalid request"], Response::HTTP_BAD_REQUEST);
        }
        $data = json_decode($request->getContent());
        $id = $data->id;
        $style = $styleRepository->find($id);
        $style->setName($data->name);
        $entityManager->persist($style);
        $entityManager->flush();

        $response = json_decode($serializer->serialize($style, "json"));
        return $this->json([
            "message" => "Style modifié avec succès",
            "style"=> $response,
        ], Response::HTTP_OK);
    }
}