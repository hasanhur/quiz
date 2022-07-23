<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator, MailerInterface $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        // $form = $this->createFormBuilder(RegistrationFormType::class, $user)
        // ->add('username', TextType::class, ['attr' => ['class' => 'form-control']])
        // ->add('email', TextType::class, ['attr' => ['class' => 'form-control']])
        // ->add('first_name', TextType::class, ['label' => 'First Name', 'attr' => ['class' => 'form-control']])
        // ->add('last_name', TextType::class, ['attr' => ['label' => 'Last Name', 'class' => 'form-control']])
        // ->add('save', Submittype::class, ['label' => 'Create', 'attr' => ['class' => 'btn btn-primary mt-3']])
        // ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setUsername($form->get('username')->getData());
            $user->setCreatedAt(new \DateTime());

            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $user->setRoles(['ROLE_USER']);
            $user->setVerificationCode(md5(uniqid(rand(), true)));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
            $email = (new Email())
                ->from('admin@example.com')
                ->to($user->getEmail())
                ->subject('Confirm your email')
                ->html('<p>Click the link to activate your account: <a href="' . $this->generateUrl('app_confirm_email', ['verification_code' => $user->getVerificationCode()], UrlGeneratorInterface::ABSOLUTE_URL) . '">Confirm your email</a></p>');

            $mailer->send($email);

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
