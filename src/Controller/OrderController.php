<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\Customer;
use App\Entity\OrderDetail;

class OrderController extends AbstractController
{
    #[Route('/create/order', name: 'app_create_order', methods: ['POST'])]
    public function createOrder(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $customer = $doctrine->getRepository(Customer::class)->find($request->request->get('customer_id'));
        
        if (!$customer) {
            return new JsonResponse([
                'message' => 'The customer does not exist',
            ], Response::HTTP_BAD_REQUEST);
        }

        $order = new Order();
        $order->setCustomer($customer);
        $order->setOrderDate(\DateTime::createFromFormat('Y-m-d', $request->request->get('order_date')));

        $entityManager = $doctrine->getManager();
        $entityManager->persist($order);
        $entityManager->flush();
        
        $orderDetails = json_decode($request->request->get('order_details'), true);

        foreach ($orderDetails as $orderDetail) {
            $product = $doctrine->getRepository(Product::class)->find($orderDetail['product_id']);

            if (!$product) {
                return new JsonResponse([
                    'message' => 'The product with ID ' . $orderDetail['product_id'] . ' does not exist',
                ], Response::HTTP_BAD_REQUEST);
            }

            /*  // Decrement the stock
                $quantity = $orderDetail['quantity'];
                $product->setStock($product->getStock() - $quantity);
                $entityManager->persist($product);
            */

            $orderDetailEntity = new OrderDetail();
            $orderDetailEntity->setOrder($order);
            $orderDetailEntity->setCustomer($customer);
            $orderDetailEntity->setQuantity($orderDetail['quantity']);
            $orderDetailEntity->setProduct($product);
            $orderDetailEntity->setUnitPrice($product->getPrice() * $orderDetail['quantity']);

            $entityManager->persist($orderDetailEntity);
        }

        $entityManager->flush();

        return new JsonResponse([
            'message' => 'The order was successfully created',
        ], Response::HTTP_CREATED);
    }
}
