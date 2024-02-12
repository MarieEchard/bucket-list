<?php

namespace App\Controller;

use App\Entity\Wish;
use App\Form\WishType;
use App\Repository\WishRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WishController extends AbstractController
{
    #[Route('/wish', name: 'app_wish')]
    public function index(): Response
    {
        return $this->render('wish/index.html.twig', [
            'controller_name' => 'WishController',
        ]);
    }

    #[Route('/wishes', name: 'wish_list', methods: ['GET'])]
    public function list(WishRepository $wishRepository): Response
    {
        // récupère les Wish publiés, du plus récent au plus ancien
        $wishes = $wishRepository->findBy(['isPublished' => true], ['dateCreated' =>
            'DESC']);
        return $this->render('wish/list.html.twig', [
            // passe les données à Twig
            "wishes" => $wishes
        ]);
    }


    #[Route('/wishes/{id}', name: 'wish_detail', requirements: ['id'=>'\d+'], methods:
        ['GET'])]
    public function detail(int $id, WishRepository $wishRepository): Response
    {
        $wish = $wishRepository->find($id);

        // s'il n'existe pas en bdd, on déclenche une erreur 404
        if (!$wish){
            throw $this->createNotFoundException('This wish do not exists! Sorry!');
        }
        return $this->render('wish/detail.html.twig', [
            "wish" => $wish
        ]);
    }

    #[Route('/wishes/create', name: '_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {

        // dd($request);

        $wish = new Wish();

        $form = $this->createForm(WishType::class, $wish);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($wish);
            $em->flush();

            $this->addFlash('success', 'La liste a été enregistrée');
            return $this->redirectToRoute('wish_detail', ['id' => $wish->getId()]);
        }

        return $this->render('wish/edit.html.twig', [
            'form' => $form
        ]);
    }


}
