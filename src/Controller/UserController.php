<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * List of all users
     * 
     * @Route("/users", name="user_list")
     *
     * @param UserRepository $userRepository
     * 
     * @return Response
     */
    public function listAction(UserRepository $userRepository)
    {
        return $this->render('user/list.html.twig', ['users' => $userRepository->findAll()]);
    }

    /**
     * User creation
     *
     * @Route("/users/create", name="user_create")
     * 
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordEncoderInterface $encoder
     * 
     * @return Response
     */
    public function createAction(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $password = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', "L'utilisateur a bien été ajouté.");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * User edition
     *
     * @Route("/users/{id}/edit", name="user_edit")
     * @IsGranted("EDIT", subject="userToEdit")
     * 
     * @param User $userToEdit
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function editAction(User $userToEdit, Request $request, UserPasswordEncoderInterface $encoder, EntityManagerInterface $manager)
    {
        $form = $this->createForm(UserType::class, $userToEdit);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $encoder->encodePassword($userToEdit, $userToEdit->getPassword());
            $userToEdit->setPassword($password);

            $manager->flush();

            $this->addFlash('success', "L'utilisateur a bien été modifié");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $userToEdit]);
    }

    /**
     * User deletion
     * 
     * @Route("/users/{id}/delete", name="user_delete")
     * @IsGranted("DELETE", subject="userToDelete")
     * 
     * @param EntityManagerInterface $manager
     * @param User $userToDelete
     * 
     * @return Response
     */
    public function deleteAction(EntityManagerInterface $manager, User $userToDelete)
    {
        $manager->remove($userToDelete);
        $manager->flush();
        $this->addFlash('success', 'L\'utilisateur a bien été supprimé');
        return $this->redirectToRoute('user_list');
    }
}
