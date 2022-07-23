<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserTestRepository;
use App\Repository\OptionRepository;
use App\Repository\UserAnswerRepository;
use App\Form\Type\ChangePasswordType;
use App\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/profile")
 * @IsGranted("ROLE_USER")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/edit", methods={"GET", "POST"}, name="user_edit")
     */
    public function edit(Request $request)
    {
        $user = $this->getUser();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'user.updated_successfully');

            return $this->redirectToRoute('user_edit');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/change-password", methods={"GET", "POST"}, name="user_change_password")
     */
    public function changePassword(Request $request, UserPasswordEncoderInterface $encoder) {
        $user = $this->getUser();

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($encoder->encodePassword($user, $form->get('newPassword')->getData()));

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('security_logout');
        }

        return $this->render('user/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{username}", name="user_show")
     */
    public function showUser(Request $request, User $user, UserTestRepository $userTests, OptionRepository $options, UserAnswerRepository $userAnswers) {
        if ($user != $this->getUser()) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }

        $foundUserTests = $userTests->findBy(['user' => $user]);
        $testResults = [];

        if (!empty($foundUserTests)) {
            foreach ($foundUserTests as $userTest) {
                $test = $userTest->getTest();
                $foundUserAnswers = $userAnswers->findAllUserAnswers($test->getQuestion(), $user);
                $correctAnswers = $options->findCorrectAnswers($test->getQuestion(), $user);
                $incorrectAnswers = array_merge(array_diff($foundUserAnswers, $correctAnswers), array_diff($correctAnswers, $foundUserAnswers));
                $incorrectQuestions = $options->findQuestions($incorrectAnswers);

                array_push($testResults, [
                    'test' => $test,
                    'incorrectQuestions' => $incorrectQuestions,
                    'incorrectAnswers' => $incorrectAnswers,
                ]);
            }
        }

        return $this->render('user/show.html.twig', ['user' => $user, 'tests' => $testResults]);
    }
}
