<?php

namespace App\Controller;

use App\Entity\Orderproduct;
use App\Entity\Product;
use App\Form\OrderproductType;
use App\Repository\OrderRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class OrderproductController extends AbstractController
{
    #[Route('/orderproduct', name: 'app_orderproduct_index', methods: ['GET'])]
    public function index(OrderRepository $orderRepository): Response
    {
        return $this->render('orderproduct/index.html.twig', [
            'orderproducts' => $orderRepository->findAll(),
        ]);
    }

    #[Route('/neworder{idProduct}', name: 'app_orderproduct_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, $idProduct): Response
    {
        $orderproduct = new Orderproduct();
        $form = $this->createForm(OrderproductType::class, $orderproduct);
        $form->handleRequest($request);
        $product = $entityManager->getRepository(Product::class)->find($idProduct);
        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $orderproduct->setIdProduct($product);
            $orderproduct->setPrice($product->getPrice());
            $orderproduct->setTitle($product->getTitle());
            $orderproduct->setProdImg($product->getProductimage());
            $today = new DateTime();
            $formattedDate = $today->format('d-m-Y');
            $orderproduct->setOrderdate($formattedDate);
            
            $entityManager->persist($orderproduct);
            $entityManager->flush();

            return $this->redirectToRoute('app_orderproduct_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('orderproduct/new.html.twig', [
            'orderproduct' => $orderproduct,
            'form' => $form,
        ]);
    }

    #[Route('/showorder{idOrder}', name: 'app_orderproduct_show', methods: ['GET'])]
    public function show(Orderproduct $orderproduct): Response
    {
        return $this->render('orderproduct/show.html.twig', [
            'orderproduct' => $orderproduct,
        ]);
    }


    #[Route('/deleteorder{idOrder}', name: 'app_orderproduct_delete', methods: ['POST'])]
    public function delete(Request $request, Orderproduct $orderproduct, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$orderproduct->getIdOrder(), $request->request->get('_token'))) {
            $entityManager->remove($orderproduct);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_orderproduct_index', [], Response::HTTP_SEE_OTHER);
    }
}
