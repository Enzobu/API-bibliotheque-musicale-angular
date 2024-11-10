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
    #[Route('/api/album/get-albums', name: 'app_gat_album', methods: ["GET"])]
    public function getAlbums(
        AlbumRepository         $albumRepository,
        SerializerInterface     $serializer
    ): JsonResponse
    {
        $albums = $albumRepository->findAll();
        $response = $serializer->serialize($albums, "json", ["groups" => "Album"]);
        return $this->json(json_decode($response), Response::HTTP_OK);
    }
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