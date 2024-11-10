<?php

namespace App\Controller;

use OpenApi\Annotations as OA;
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

    /**
     * @OA\Tag(name="Style", description="API pour gérer les styles")
     */

    /**
     * @OA\Get(
     *     path="/api/style/get-styles",
     *     tags={"Style"},
     *     summary="Get all styles",
     *     @OA\Response(response="200", description="List of all styles",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string")
     *             )
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/style/add-style",
     *     tags={"Style"},
     *     summary="Add a new style",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="Name of the style")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Style created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/style/remove-style",
     *     tags={"Style"},
     *     summary="Delete a style",
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Style deleted successfully")
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/api/style/update-style",
     *     tags={"Style"},
     *     summary="Update a style",
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="Updated name of the style")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Style updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string")
     *         )
     *     )
     * )
     */
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