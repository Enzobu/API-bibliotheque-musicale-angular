<?php

namespace App\Controller;

use OpenApi\Annotations as OA;
use App\Entity\Artist;  
use App\Repository\ArtistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ArtistApiController extends AbstractController
{
    /**
     * @OA\Tag(name="Artist", description="API pour gérer les artistes")
     */

    /**
     * @OA\Get(
     *     path="/api/artist/get-artists",
     *     tags={"Artist"},
     *     summary="Get all artists",
     *     @OA\Response(response="200", description="List of all artists",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="illustration", type="string")
     *             )
     *         )
     *     )
     * )
     */
    #[Route('/api/artist/get-artists', name: 'app_gat_artist', methods: ["GET"])]
    public function getArtist(
        ArtistRepository         $artistRepository,
        SerializerInterface     $serializer
    ): JsonResponse
    {
        $artist = $artistRepository->findAll();
        $response = $serializer->serialize($artist, "json", ["groups" => "Artist"]);
        return $this->json(json_decode($response), Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/artist/add-artist",
     *     tags={"Artist"},
     *     summary="Add a new artist",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="Name of the artist"),
     *             @OA\Property(property="illustration", type="string", description="Illustration URL of the artist")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Artist created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="illustration", type="string")
     *         )
     *     )
     * )
     */
    #[Route('/api/artist/add-artist', name: 'app_add_artist', methods: ["POST"])]
    public function addArtist(
        ArtistRepository         $artistRepository,
        SerializerInterface     $serializer,
        Request                 $request,
        EntityManagerInterface  $entityManager
    ): JsonResponse
    {
        $data = json_decode($request->getContent());
        
        if (!$request->request->has('name') && !$request->request->has('illustration')) {
            return $this->json(["message" => "Invalid request"], Response::HTTP_BAD_REQUEST);
            if (empty($data->name) && empty($data->illustration)) {
                return $this->json(["message" => "Invalid request"], Response::HTTP_BAD_REQUEST);
            }
        }
        $artist = new Artist();
        $artist->setName($data->name);
        $artist->setIllustration($data->illustration);
        $entityManager->persist($artist);
        $entityManager->flush();

        $response = json_decode($serializer->serialize($artist, "json"));
        return $this->json([
            "message" => "Artiste créé avec succès",
            "artist"=> $response,
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/artist/remove-artist",
     *     tags={"Artist"},
     *     summary="Delete an artist",
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Artist deleted successfully")
     * )
     */
    #[Route('/api/artist/remove-artist', name: 'app_remove_artist', methods: ["DELETE"])]
    public function removeArtist(
        ArtistRepository         $artistRepository,
        SerializerInterface     $serializer,
        Request                 $request,
        EntityManagerInterface  $entityManager
    ): JsonResponse
    {
        $data = json_decode($request->getContent());
        $id = $data->id;
        $artist = $artistRepository->find($id);
        if (!$artist) {   
            return $this->json(["message" => "Artist introuvable"], Response::HTTP_NOT_FOUND);
        }
        $entityManager->remove($artist);
        // $entityManager->persist($artist);
        $entityManager->flush();

        return $this->json([
            "message" => "Artist supprimé avec succès",
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *     path="/api/artist/update-artist",
     *     tags={"Artist"},
     *     summary="Update an artist",
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="Updated name of the artist"),
     *             @OA\Property(property="illustration", type="string", description="Updated illustration URL of the artist")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Artist updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="illustration", type="string")
     *         )
     *     )
     * )
     */
    #[Route('/api/artist/update-artist', name: 'app_update_artist', methods: ["PUT"])]
    public function updateArtist(
        ArtistRepository         $artistRepository,
        SerializerInterface     $serializer,
        Request                 $request,
        EntityManagerInterface  $entityManager
    ): JsonResponse
    {
        $data = json_decode($request->getContent());
        
        if (!$request->request->has('id') && (!$request->request->has('name') || !$request->request->has('illustration'))) {
            return $this->json(["message" => "Invalid request"], Response::HTTP_BAD_REQUEST);
            if (empty($data->id) && (empty($data->name) || empty($data->illustration))) {
                return $this->json(["message" => "Invalid request"], Response::HTTP_BAD_REQUEST);
            }
        }
        $data = json_decode($request->getContent());
        $id = $data->id;
        $artist = $artistRepository->find($id);
        if (!empty($data->name)) {
            $artist->setName($data->name);
        }
        if (!empty($data->illustration)) {
            $artist->setIllustration($data->illustration);
        }
        $entityManager->persist($artist);
        $entityManager->flush();

        $response = json_decode($serializer->serialize($artist, "json"));
        return $this->json([
            "message" => "Artist modifié avec succès",
            "artist"=> $response,
        ], Response::HTTP_OK);
    }
}