<?php

namespace App\Controller;

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