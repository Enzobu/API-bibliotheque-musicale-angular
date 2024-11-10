<?php

namespace App\Controller;

use OpenApi\Annotations as OA;
use App\Entity\Song;
use App\Repository\SongRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class SongApiController extends AbstractController
{
    /**
     * @OA\Tag(name="Song", description="API pour gérer les chansons")
     */

    /**
     * @OA\Get(
     *     path="/api/song/get-songs",
     *     tags={"Song"},
     *     summary="Get all songs",
     *     @OA\Response(response="200", description="List of all songs",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="duration", type="string")
     *             )
     *         )
     *     )
     * )
     */
    #[Route('/api/song/get-songs', name: 'app_gat_song', methods: ["GET"])]
    public function getSong(
        SongRepository         $songRepository,
        SerializerInterface     $serializer
    ): JsonResponse
    {
        $song = $songRepository->findAll();
        $response = $serializer->serialize($song, "json", ["groups" => "Song"]);
        return $this->json(json_decode($response), Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/song/add-song",
     *     tags={"Song"},
     *     summary="Add a new song",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", description="Title of the song"),
     *             @OA\Property(property="duration", type="string", description="Duration of the song")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Song created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="duration", type="string")
     *         )
     *     )
     * )
     */
    #[Route('/api/song/add-song', name: 'app_add_song', methods: ["POST"])]
    public function addSong(
        SongRepository         $songRepository,
        SerializerInterface     $serializer,
        Request                 $request,
        EntityManagerInterface  $entityManager
    ): JsonResponse
    {
        $data = json_decode($request->getContent());
        
        if (empty($data->title) && empty($data->duration)) {
            return $this->json(["message" => "Invalid request"], Response::HTTP_BAD_REQUEST);
        }
        $song = new Song();
        $song->setTitle($data->title);
        $song->setDuration($data->duration);
        $entityManager->persist($song);
        $entityManager->flush();

        $response = json_decode($serializer->serialize($song, "json"));
        return $this->json([
            "message" => "Songe créé avec succès",
            "song"=> $response,
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/song/remove-song",
     *     tags={"Song"},
     *     summary="Delete a song",
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Song deleted successfully")
     * )
     */
    #[Route('/api/song/remove-song', name: 'app_remove_song', methods: ["DELETE"])]
    public function removeSong(
        SongRepository         $songRepository,
        SerializerInterface     $serializer,
        Request                 $request,
        EntityManagerInterface  $entityManager
    ): JsonResponse
    {
        $data = json_decode($request->getContent());
        $id = $data->id;
        $song = $songRepository->find($id);
        if (!$song) {   
            return $this->json(["message" => "Song introuvable"], Response::HTTP_NOT_FOUND);
        }
        $entityManager->remove($song);
        $entityManager->flush();

        return $this->json([
            "message" => "Song supprimé avec succès",
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *     path="/api/song/update-song",
     *     tags={"Song"},
     *     summary="Update a song",
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", description="Updated title of the song"),
     *             @OA\Property(property="duration", type="string", description="Updated duration of the song")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Song updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="duration", type="string")
     *         )
     *     )
     * )
     */
    #[Route('/api/song/update-song', name: 'app_update_song', methods: ["PUT"])]
    public function updateSong(
        SongRepository         $songRepository,
        SerializerInterface     $serializer,
        Request                 $request,
        EntityManagerInterface  $entityManager
    ): JsonResponse
    {
        $data = json_decode($request->getContent());
        
        if (empty($data->id) && (empty($data->title) || empty($data->duration))) {
            return $this->json(["message" => "Invalid request"], Response::HTTP_BAD_REQUEST);
        }
        $data = json_decode($request->getContent());
        $id = $data->id;
        $song = $songRepository->find($id);
        if (!empty($data->title)) {
            $song->setTitle($data->title);
        }
        if (!empty($data->duration)) {
            $song->setDuration($data->duration);
        }
        $entityManager->persist($song);
        $entityManager->flush();

        $response = json_decode($serializer->serialize($song, "json"));
        return $this->json([
            "message" => "Song modifié avec succès",
            "song"=> $response,
        ], Response::HTTP_OK);
    }
}