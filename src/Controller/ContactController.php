<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    /**
     * @Route("/contact", name="app_contact")
     */
    public function index(Request $request, EntityManagerInterface $manager): Response
    {
        $msg = new Message();
        $form = $this->createForm(MessageType::class, $msg);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = htmlspecialchars($form->get('email')->getData());
            $objet = htmlspecialchars($form->get('objet')->getData());
            $message = htmlspecialchars($form->get('message')->getData());

            $msg = new Message();
            $msg->setEmail($email)
                ->setObjet($objet)
                ->setMessage($message)
                ->setCreatedAt(new \DateTime());

            $manager->persist($msg);
            $manager->flush();
            return $this->redirectToRoute('success_contact');
        }

        if ($request->query->count() > 0) {
            if (filter_var($request->query->get('email'), FILTER_VALIDATE_EMAIL)) {
                $email = htmlspecialchars($request->query->get('email'));
                $objet = htmlspecialchars($request->query->get('objet'));
                $message = htmlspecialchars($request->query->get('message'));

                $msg = new Message();
                $msg->setEmail($email)
                    ->setObjet($objet)
                    ->setMessage($message)
                    ->setCreatedAt(new \DateTime());

                $manager->persist($msg);
                $manager->flush();
                return $this->redirectToRoute('success_contact');
            }
        }

        if ($request->request->count() > 0) {
            if (filter_var($request->request->get('email'), FILTER_VALIDATE_EMAIL)) {
                $email = htmlspecialchars($request->request->get('email'));
                $objet = htmlspecialchars($request->request->get('objet'));
                $message = htmlspecialchars($request->request->get('message'));

                $msg = new Message();
                $msg->setEmail($email)
                    ->setObjet($objet)
                    ->setMessage($message)
                    ->setCreatedAt(new \DateTime());

                $manager->persist($msg);
                $manager->flush();
                return $this->redirectToRoute('success_contact');
            } else {
                return $this->redirectToRoute('error_contact');
            }
        }
        return $this->render('contact/index.html.twig', [
            'formMessage' => $form->createView()
        ]);
    }

    /**
     * @Route("/success/contact", name="success_contact")
     */
    public function successContact(): Response
    {
        return $this->render("contact/success.html.twig");
    }

    /**
     * @Route("/error/contact", name="error_contact")
     */
    public function errorContact(): Response
    {
        return $this->render("contact/error.html.twig");
    }
}
