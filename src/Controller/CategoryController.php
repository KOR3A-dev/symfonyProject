<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Category;

class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category')]
    public function index(): Response
    {
        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
        ]);
    }

    #[Route('/categorys', name: 'app_categorys', methods: ['GET'])]
    public function listCategorys(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $categorys = $entityManager->getRepository(Category::class)->findAll();
        $data = [];
        foreach ($categorys as $category) {
            $data[] = [
                'id' => $category->getId(),
                'name' => $category->getName(),               
            ];
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/create/category', name: 'app_create_category', methods: ['POST'])]
    public function createCategory(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $category = new Category();
        $category->setName($request->request->get('name'));
        $entityManager->persist($category);
        $entityManager->flush();

        return $this->json([
            'message' => 'Category created',
        ], Response::HTTP_CREATED);
    }
}
