<?php

namespace App\Controller;

use App\Entity\News;
use App\Form\NewsType;
use App\Repository\NewsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class NewsController extends AbstractController
{
    #[Route('/news/{page<\d+>?1}', name: 'news_index', methods: ['GET'])]
    public function index(Request $request, Environment $twig, NewsRepository $newsRepository, int $page): Response
    {
        $offset = $page * NewsRepository::PAGINATOR_PER_PAGE - NewsRepository::PAGINATOR_PER_PAGE;
        $news = $newsRepository->getNewsPaginator($offset);
        if($offset > count($news))
            return $this->redirectToRoute('news_index', [], Response::HTTP_SEE_OTHER);

        $page_count = ceil(count($newsRepository->getPublished()) / NewsRepository::PAGINATOR_PER_PAGE);

        return new Response($twig->render('news/index.html.twig', [
            'news' => $news,
            'page' => $page,
            'page_count' => intval($page_count),
            'previous' => $page - 1,
            'next' => min(count($news), $page + 1),
        ]));
     }


    #[Route('/new', name: 'news_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $news = new News();
        $form = $this->createForm(NewsType::class, $news);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($news);
            $entityManager->flush();

            return $this->redirectToRoute('news_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('news/new.html.twig', [
            'news' => $news,
            'form' => $form,
        ]);
    }

    #[Route('/news/read/{id}', name: 'news_show', methods: ['GET'])]
    public function show(News $news): Response
    {
        if(!$news->getPublished())
            return $this->redirectToRoute('news_index');
        return $this->render('news/show.html.twig', [
            'news' => $news,
        ]);
    }
/*
    #[Route('/{id}/edit', name: 'news_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, News $news, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(NewsType::class, $news);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('news_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('news/edit.html.twig', [
            'news' => $news,
            'form' => $form,
        ]);
    }*/
/*
    #[Route('/{id}', name: 'news_delete', methods: ['POST'])]
    public function delete(Request $request, News $news, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$news->getId(), $request->request->get('_token'))) {
            $entityManager->remove($news);
            $entityManager->flush();
        }

        return $this->redirectToRoute('news_index', [], Response::HTTP_SEE_OTHER);
    }*/
}
