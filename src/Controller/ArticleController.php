<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/article')]
final class ArticleController extends AbstractController
{
    #[Route(name: 'app_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->getAll(),
        ]);
    }

    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, Security $security): Response
    {
        $user = $security->getUser();
        $article = new Article($user);
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('image')->getData(); // on récupère le fichier image depuis le formulaire

            if ($imageFile) { // on vérifie si un fichier a été envoyé
                $uploadFileName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME); // on récupère le nom du fichier
                $safeFileName = $slugger->slug($uploadFileName); // on génère un nom safe pour le fichier
                $newFileName = $safeFileName . '-' . uniqid() . '.' . $imageFile->guessExtension(); // on génère un nom unique pour le fichier

                $imageFile->move(
                    $this->getParameter('article_image_directory'),
                    $newFileName
                ); // on déplace le fichier dans le dossier movie_image_directory

                $article->setImage($newFileName); // on enregistre le nom du fichier dans la nouvelle instance de Movie
            }

            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager, SluggerInterface $slugger, Security $security): Response
    {
        $user = $security->getUser();
        if ($article->getAuthor() != $user) {
            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('image')->getData(); // on récupère le fichier image depuis le formulaire

            if ($imageFile) { // on vérifie si un fichier a été envoyé
                $uploadFileName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME); // on récupère le nom du fichier
                $safeFileName = $slugger->slug($uploadFileName); // on génère un nom safe pour le fichier
                $newFileName = $safeFileName . '-' . uniqid() . '.' . $imageFile->guessExtension(); // on génère un nom unique pour le fichier

                $imageFile->move(
                    $this->getParameter('article_image_directory'),
                    $newFileName
                ); // on déplace le fichier dans le dossier movie_image_directory

                $article->setImage($newFileName); // on enregistre le nom du fichier dans la nouvelle instance de Movie
            }
            $article->setUpdatedAt(new \DateTimeImmutable);

            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = $security->getUser();
        if ($article->getAuthor() != $user) {
            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
    }
}
