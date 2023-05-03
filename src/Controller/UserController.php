<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/dashboard", name="app_dashboard")
     */
    public function index(UserRepository $userRepository): Response
    {
        $isConnect = $this->getUser();
        if ($isConnect) {
            $isAdmin = json_encode($this->getUser()->getRoles());
            if ($isAdmin == '["ROLE_ADMIN","ROLE_USER"]') {
                $users = $userRepository->findAll();
                return $this->render('user/index.html.twig', [
                    'users' => $users,
                ]);
            }
        } else {
            return $this->redirectToRoute("app_home");
        }
    }

    /**
     * @Route("show/{id}", name="user_show")
     */
    public function show(User $user): Response
    {
        $isAdmin = json_encode($this->getUser()->getRoles());
        if ($isAdmin == '["ROLE_ADMIN","ROLE_USER"]') {
            return $this->render('user/show.html.twig', [
                'user' => $user
            ]);
        }
        throw $this->createAccessDeniedException('acces refusé');
    }

    /**
     * @Route("/create", name="app_create")
     */
    public function create(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hash): Response
    {
        $user = new User();
        $form = $this->createFormBuilder($user);
        $form->add('email', EmailType::class, [
            'attr' => [
                'class' => 'form-control'
            ]
        ])
            ->add('password', PasswordType::class, [
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('button', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => [
                    'class' => 'btn btn-primary btn-lg mt-5'
                ]
            ]);
        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (filter_var($form->get('email')->getData(), FILTER_VALIDATE_EMAIL)) {
                $email = $form->get('email')->getData();
                $password = $hash->hashPassword($user,  $form->get('password')->getData());
                $user->setEmail($email)
                    ->setPassword($password)
                    ->setRoles(["ROLE_USER"]);
            }
            $manager->persist($user);
            $manager->flush();
            return $this->redirectToRoute('app_dashboard');
        }
        return $this->render("user/register.html.twig", [
            'formUser' => $form->createView()
        ]);
    }

    /**
     * @Route("/update/{id}", name="app_update")
     */
    public function updatePass(User $user, Request $request, UserRepository $userRepository, UserPasswordHasherInterface $hash)
    {
        $isCnnect = $this->getUser();
        if ($isCnnect) {
            $idUrl =  intval(substr($request->getPathInfo(), -2));
            if ($idUrl == $this->getUser()->getId()) {

                $form = $this->createForm(UserFormType::class, $user);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $password = $hash->hashPassword($user, $form->get('password')->getData());
                    $user->setPassword($password);
                    $userRepository->add($user, true);
                    return $this->redirectToRoute("app_success");
                }

                return $this->render("user/update.html.twig", [
                    'formUpdate' => $form->createView()
                ]);
            }
        }
        return $this->redirectToRoute('app_home');
    }

    /**
     * @Route("/success", name="app_success")
     */
    public function success(): Response
    {
        return $this->render("user/success.html.twig", []);
    }

    /**
     * @Route("/delete/{id}", name="app_delete")
     */
    public function delete(User $user, UserRepository $userRepository, Request $request): Response
    {
        $idUrl =  intval(substr($request->getPathInfo(), -2));
        $isCnnect = $this->getUser();
        if ($isCnnect) {
            $isAdmin = json_encode($this->getUser()->getRoles());
            if ($isAdmin == '["ROLE_ADMIN","ROLE_USER"]') {
                $userRepository->remove($user, true);
                return $this->redirectToRoute("app_dashboard");
            }
            if ($idUrl == $this->getUser()->getId()) {
                $userRepository->remove($user, true);
                $session = new Session();
                $session->invalidate();
                return $this->redirectToRoute("app_home");
            }
        } else {
            return $this->redirectToRoute("app_home");
        }
    }

    /**
     * @Route("/profil", name="modif_profil")
     */
    public function userMenu(): Response
    {
        $isConnect = $this->getUser();
        if ($isConnect) {
            return $this->render("user/navlink.html.twig");
        } else {
            return $this->redirectToRoute("app_home");
        }
    }

    /**
     * @Route("/update/pseudo/{id}", name="update_pseudo")
     */
    public function updatePseudo(User $user, Request $request, UserRepository $userRepository): Response
    {
        $isConnect = $this->getUser();
        if ($isConnect) {
            $idUrl =  intval(substr($request->getPathInfo(), -2));
            if ($idUrl == $this->getUser()->getId()) {
                $form = $this->createFormBuilder($user);
                $form->add('pseudo', TextType::class, [
                    'attr' => [
                        'class' => 'form-control mb-5'
                    ]
                ])
                    ->add('button', SubmitType::class, [
                        'label' => 'Enregistrer',
                        'attr' => [
                            'class' => 'btn btn-success'
                        ]
                    ]);
                $form = $form->getForm();
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $pseudo = $form->get("pseudo")->getData();
                    $user->setPseudo($pseudo);
                    $userRepository->add($user, true);
                    return $this->redirectToRoute("modif_profil");
                }

                return $this->render("user/updatepseudo.html.twig", [
                    'pseudoForm' => $form->createView()
                ]);
            } else {
                return $this->redirectToRoute("modif_profil");
            }
        } else {
            return $this->redirectToRoute("app_home");
        }
    }

    /**
     * @Route("/update/avatar/{id}", name="update_avatar")
     */
    public function updateAvatar(User $user, Request $request, SluggerInterface $slug, UserRepository $userRepository): Response
    {
        if ($request->files->count() > 0) {
            $files = $request->files->get("avatar");
            if ($files) {
                $extensionValid = array('jpg', 'jpeg', 'png');
                $originalFilename = pathinfo($files->getClientOriginalName(), PATHINFO_FILENAME);
                $filesMb = $files->getSize();
                $sizeValid = 200000;
                if ($filesMb <= $sizeValid) {
                    if (in_array($files->guessExtension(), $extensionValid)) {
                        $safeFilename = $slug->slug($originalFilename);
                        $newFilename = "dist/assets/images/image" . $user->getId() . '.' . $files->guessExtension();
                        $user->setAvatar($newFilename);
                        $files->move(
                            $this->getParameter('image_directory'),
                            $newFilename
                        );
                        $userRepository->add($user, true);
                        return $this->redirectToRoute('app_success');
                    } else {
                        return $this->redirectToRoute('app_home');
                    }
                } else {
                    return $this->redirectToRoute('app_home');
                }
            }
        }
        return $this->render("user/updateavatar.html.twig");
    }
}
