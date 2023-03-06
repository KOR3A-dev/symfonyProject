<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Customer;

class CustomerController extends AbstractController
{
    #[Route('/customer/{id}', name: 'app_customer', methods: ['GET'])]
    public function getCustomer(Customer $customer = null): JsonResponse
    {
        if (!$customer) {
            return new JsonResponse([
                'message' => 'This customer does not exist',
            ], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $customer->getId(),
            'fullname' => $customer->getFullname(),
            'email' => $customer->getEmail(),
            'address' => $customer->getAddress(),
            'date_birth' => $customer->getDateBirth()->format('Y-m-d')
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }
   
    #[Route('/create/customer', name: 'app_create_customer', methods: ['POST'])]
    public function createCustomer(Request $request, ManagerRegistry $doctrine): Response
    {
        $customer = new Customer();
        $customer->setFullname($request->request->get('fullname'));
        $customer->setEmail($request->request->get('email'));

        /* encript password */
        $hashedPassword = password_hash($request->request->get('password'), PASSWORD_DEFAULT);
        $customer->setPassword($hashedPassword);

        $customer->setAddress($request->request->get('address'));
        $customer->setDateBirth(new \DateTime($request->request->get('date_birth')));

        $entityManager = $doctrine->getManager();
        $entityManager->persist($customer);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'The customer was successfully created',
        ], Response::HTTP_CREATED);
    }

    #[Route('/login', name: 'app_login', methods: ['POST'])]
    public function login(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        $customer = $doctrine->getRepository(Customer::class)->findOneBy(['email' => $email]);

        if (!$customer || !password_verify($password, $customer->getPassword())) {
            return new JsonResponse([
                'message' => 'Invalid email or password',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $customer_session = $customer->getId();

        return new JsonResponse([
            'message' => 'Logged in successfully',
            'customer_session' => $customer_session
        ], Response::HTTP_OK);
    }
}
