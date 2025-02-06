<?php

namespace App\Controller;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'api_')]
class TaskController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    #[Route('/tasks', name: 'task_index', methods: ['GET'])]
    public function index(): Response
    {
        $tasks = $this->entityManager->getRepository(Task::class)->findAllTasks();
        return $this->json($tasks);
    }

    #[Route('/tasks', name: 'task_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $task = $this->serializer->deserialize($request->getContent(), Task::class, 'json');

        $errors = $this->validator->validate($task);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $this->json($task, Response::HTTP_CREATED);
    }

    #[Route('/tasks/{id}', name: 'task_show', methods: ['GET'])]
    public function show(int $id): Response
    {
        $task = $this->entityManager->getRepository(Task::class)->find($id);

        if (!$task) {
            return $this->json(['error' => 'Task not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($task);
    }

    #[Route('/tasks/{id}', name: 'task_update', methods: ['PUT'])]
    public function update(Request $request, int $id): Response
    {
        $task = $this->entityManager->getRepository(Task::class)->find($id);

        if (!$task) {
            return $this->json(['error' => 'Task not found'], Response::HTTP_NOT_FOUND);
        }

        $updatedTask = $this->serializer->deserialize($request->getContent(), Task::class, 'json');

        $task->setTitle($updatedTask->getTitle());
        $task->setDescription($updatedTask->getDescription());
        $task->setStatus($updatedTask->getStatus());

        $errors = $this->validator->validate($task);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json($task);
    }

    #[Route('/tasks/{id}', name: 'task_delete', methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        $task = $this->entityManager->getRepository(Task::class)->find($id);

        if (!$task) {
            return $this->json(['error' => 'Task not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($task);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
