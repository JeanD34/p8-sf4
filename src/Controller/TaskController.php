<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class TaskController extends AbstractController
{
    /**
     * List not done task
     *
     * @Route("/tasks", name="task_list")
     * 
     * @param TaskRepository $taskRepository
     * 
     * @return Response
     */
    public function listAction(TaskRepository $taskRepository)
    {
        return $this->render('task/list.html.twig', ['tasks' => $taskRepository->findBy(['isDone' => false])]);
    }

    /**
     * List done task
     *
     * @Route("/tasks/done", name="task_list_done")
     * 
     * @param TaskRepository $taskRepository
     * 
     * @return Response
     */
    public function listDoneAction(TaskRepository $taskRepository)
    {
        return $this->render('task/list.html.twig', ['tasks' => $taskRepository->findBy(['isDone' => true])]);
    }

    /**
     * Show one task
     *
     * @Route("/tasks/{id}/show", name="task_show")
     * 
     * @param Task $task
     * 
     * @return Response
     */
    public function showAction(Task $task)
    {
        return $this->render('task/show.html.twig', ['task' => $task]);
    }

    /**
     * Task creation
     * 
     * @Route("/tasks/create", name="task_create")
     * 
     * @param Request $request
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function createAction(Request $request, EntityManagerInterface $manager)
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $task->setUser($this->getUser());
            $manager->persist($task);
            $manager->flush();

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Task Edition
     *
     * @Route("/tasks/{id}/edit", name="task_edit")
     * 
     * @param Task $task
     * @param Request $request
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function editAction(Task $task, Request $request, EntityManagerInterface $manager)
    {
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    /**
     * Mark task as done or not done
     *
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     * 
     * @param Task $task
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function toggleTaskAction(Task $task, EntityManagerInterface $manager)
    {
        $task->toggle(!$task->isDone());
        $manager->flush();

        $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));

        return $this->redirectToRoute('task_list');
    }

    /**
     * Task deletion
     *
     * @Route("/tasks/{id}/delete", name="task_delete")
     * @IsGranted("DELETE", subject="task")
     * 
     * @param Task $task
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function deleteTaskAction(Task $task, EntityManagerInterface $manager)
    {
        $manager->remove($task);
        $manager->flush();

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task_list');
    }
}
