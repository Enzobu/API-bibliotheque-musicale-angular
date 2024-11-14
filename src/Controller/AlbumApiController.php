<?php

namespace App\Controller;

use OpenApi\Annotations as OA;
use App\Entity\Album;
use App\Repository\AlbumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class AlbumApiController extends AbstractController
{
    /**
     * @OA\Tag(name="Album", description="API pour gérer les albums")
     */

    /**
     * @OA\Get(
     *     path="/api/album/get-albums",
     *     tags={"Album"},
     *     summary="Get all albums",
     *     @OA\Response(response="200", description="List of all albums",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="releaseDate", type="string", format="date"),
     *                 @OA\Property(property="cover", type="string")
     *             )
     *         )
     *     )
     * )
     */
    #[Route('/api/album/get-albums', name: 'app_get_album', methods: ["GET"])]
    public function getAlbums(
        AlbumRepository         $albumRepository,
        SerializerInterface     $serializer
    ): JsonResponse
    {
        $albums = $albumRepository->findAll();
        $response = $serializer->serialize($albums, "json", ["groups" => "Album"]);
        return $this->json(json_decode($response), Response::HTTP_OK);
    }  

    /**
     * @OA\Post(
     *     path="/api/album/add-album",
     *     tags={"Album"},
     *     summary="Add a new album",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", description="Title of the album"),
     *             @OA\Property(property="releaseDate", type="string", format="date", description="Release date of the album"),
     *             @OA\Property(property="cover", type="string", description="Cover image URL of the album")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Album created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="releaseDate", type="string", format="date"),
     *             @OA\Property(property="cover", type="string")
     *         )
     *     )
     * )
     */
    #[Route('/api/album/add-album', name: 'app_add_album', methods: ["POST"])]
    public function addAlbum(
        AlbumRepository         $albumRepository,
        SerializerInterface     $serializer,
        Request                 $request,
        EntityManagerInterface  $entityManager
    ): JsonResponse
    {
        $data = json_decode($request->getContent());
        
        if (empty($data->title) || empty($data->releaseDate) || empty($data->cover)) {
            return $this->json(["message" => "Invalid request"], Response::HTTP_CREATED);
        }
        $album = new Album();
        $album->setTitle($data->title);
        $album->setReleaseDate((new \DateTime())->setTimestamp(strtotime($data->releaseDate)));
        $album->setCover($data->cover);
        $entityManager->persist($album);
        $entityManager->flush();

        $response = json_decode($serializer->serialize($album, "json"));
        return $this->json([
            "message" => "Album créé avec succès",
            "album"=> $response,
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/album/remove-album",
     *     tags={"Album"},
     *     summary="Delete an album",
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Album deleted successfully")
     * )
     */
    #[Route('/api/album/remove-album', name: 'app_remove_album', methods: ["DELETE"])]
    public function removeAlbum(
        AlbumRepository         $albumRepository,
        SerializerInterface     $serializer,
        Request                 $request,
        EntityManagerInterface  $entityManager
    ): JsonResponse
    {
        $data = json_decode($request->getContent());
        $id = $data->id;
        $album = $albumRepository->find($id);
        if (!$album) {   
            return $this->json(["message" => "Album introuvable"], Response::HTTP_NOT_FOUND);
        }
        $entityManager->remove($album);
        // $entityManager->persist($album);
        $entityManager->flush();

        return $this->json([
            "message" => "Album supprimé avec succès",
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *     path="/api/album/update-album",
     *     tags={"Album"},
     *     summary="Update an album",
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", description="Updated title of the album"),
     *             @OA\Property(property="releaseDate", type="string", format="date", description="Updated release date of the album"),
     *             @OA\Property(property="cover", type="string", description="Updated cover URL of the album")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Album updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="releaseDate", type="string", format="date"),
     *             @OA\Property(property="cover", type="string")
     *         )
     *     )
     * )
     */
    #[Route('/api/album/update-album', name: 'app_update_album', methods: ["PUT"])]
    public function updateAlbum(
        AlbumRepository         $albumRepository,
        SerializerInterface     $serializer,
        Request                 $request,
        EntityManagerInterface  $entityManager
    ): JsonResponse
    {
        $data = json_decode($request->getContent());
        
        if (empty($data->id) && (empty($data->title) && empty($data->releaseDate) && empty($data->cover))) {
            return $this->json(["message" => "Invalid request"], Response::HTTP_BAD_REQUEST);
        }
        $data = json_decode($request->getContent());
        $id = $data->id;
        $album = $albumRepository->find($id);
        if (!empty($data->title)) {
            $album->setTitle($data->title);
        }
        if (!empty($data->releaseDate)) {
            $album->setReleaseDate((new \DateTime())->setTimestamp(strtotime($data->releaseDate)));
        }
        if (!empty($data->cover)) {
            $album->setCover($data->cover);
        }
        $entityManager->persist($album);
        $entityManager->flush();

        $response = json_decode($serializer->serialize($album, "json"));
        return $this->json([
            "message" => "Album modifié avec succès",
            "album"=> $response,
        ], Response::HTTP_OK);
    }
}