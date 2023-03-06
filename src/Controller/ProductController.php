<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
use App\Entity\Category;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_products', methods: ['GET'])]
    public function listProducts(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $products = $entityManager->getRepository(Product::class)->findAll();
        $data = [];
        foreach ($products as $product) { 
            $data[] = [
                'id' => $product->getId(),
                'title' => $product->getTitle(),
                'description' => $product->getDescription(),
                'code' => $product->getCode(),
                'price' => $product->getPrice(),
                'stock' => $product->getStock(),
                'status' => $product->isStatus(),
                'category' => $product->getCategory()->getName()
            ];
            
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/products/{categoryName}', name: 'app_products_by_category', methods: ['GET'])]
    public function listProductsByCategory($categoryName, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $category = $entityManager->getRepository(Category::class)->findOneBy(['name' => $categoryName]);

        if (!$category) {
            return new JsonResponse([
                'message' => 'This product category does not exist'
            ], Response::HTTP_NOT_FOUND);
        }

        $products = $entityManager->getRepository(Product::class)->findBy(['category' => $category]);

        $data = [];
        foreach ($products as $product) {
            if ($product->isStatus()) {
                $data[] = [
                    'id' => $product->getId(),
                    'title' => $product->getTitle(),
                    'description' => $product->getDescription(),
                    'code' => $product->getCode(),
                    'price' => $product->getPrice(),
                    'stock' => $product->getStock(),
                    'status' => $product->isStatus(),
                    'category' => $product->getCategory()->getName()
                ];
            }
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/product/{code}', name: 'app_product', methods: ['GET'])]
    public function getProduct(Product $product = null): JsonResponse
    {
        if (!$product) {
            return new JsonResponse([
                'message' => 'This product does not exist',
            ], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $product->getId(),
            'title' => $product->getTitle(),
            'description' => $product->getDescription(),
            'code' => $product->getCode(),
            'price' => $product->getPrice(),
            'stock' => $product->getStock(),
            'status' => $product->isStatus(),
            'category' => $product->getCategory()->getName()
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/create/product', name: 'app_create_product', methods: ['POST'])]
    public function createProduct(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $entityManager = $doctrine->getManager();

        $product = new Product();
        $product->setTitle($request->request->get('title'));
        $product->setDescription($request->request->get('description'));
        $product->setCode($request->request->get('code'));
        $product->setPrice($request->request->get('price'));
        $product->setStock($request->request->get('stock'));
        $product->setStatus($request->request->get('status'));

        /* products are related to their category */
        $category = $entityManager->getRepository(Category::class)->find($request->request->get('category_id'));
        $product->setCategory($category);

        $entityManager->persist($product);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'The product was successfully created',
        ], Response::HTTP_CREATED);
    }

    #[Route('/update/product/{id}', name: 'app_update_product', methods: ['PUT'])]
    public function updateProduct(Request $request, Product $product, ManagerRegistry $doctrine): JsonResponse
    {
        $product->setTitle($request->request->get('title'));
        $product->setDescription($request->request->get('description'));
        $product->setCode($request->request->get('code'));
        $product->setPrice($request->request->get('price'));
        $product->setStock($request->request->get('stock'));
        $product->setStatus($request->request->get('status'));

        $entityManager = $doctrine->getManager();
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Product updated successfully',
            'product' => $product
        ]);
    }

    #[Route('/inactivate/product/{code}', name: 'app_inactivate_product', methods: ['PUT'])]
    public function inactivateProduct(Product $product, ManagerRegistry $doctrine): JsonResponse
    {
        $status = $product->IsStatus();
        if ($status) {
            $product->setStatus(false);
            $message = 'Product inactivated successfully';
        } else {
            $product->setStatus(true);
            $message = 'Product activated successfully';
        }
        
        $entityManager = $doctrine->getManager();
        $entityManager->flush();
    
        return new JsonResponse([
            'message' => $message,
        ]);
    }
    
}
