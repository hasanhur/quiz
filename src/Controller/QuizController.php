<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Subject;
use App\Entity\Option;
use App\Entity\Test;
use App\Entity\UserAnswer;
use App\Entity\UserTest;
use App\Form\AnswerType;
use App\Repository\SubjectRepository;
use App\Repository\TestRepository;
use App\Repository\QuestionRepository;
use App\Repository\OptionRepository;
use App\Repository\UserAnswerRepository;
use App\Repository\UserTestRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class QuizController extends AbstractController
{
    /**
     * @Route("/", name="home_page")
     */
    public function showHomePage()
    {
        return $this->render('quiz/index.html.twig');
    }

    /**
     * @Route("/subject/{slug}", name="subject_show")
     */
    public function showSubject(Subject $subject, TestRepository $tests)
    {
        return $this->render('quiz/test_list.html.twig', [
            'subject' => $subject,
            'tests' => $tests->findPublishedTestsBySubject($subject),
        ]);
    }

    /**
     * @Route("/subject", name="subject_list")
     */
    public function listSubject(SubjectRepository $subjects)
    {
        return $this->render('quiz/subject_list.html.twig', ['subjects' => $subjects->findAll()]);
    }

    /**
     * @Route("/test/{slug}", name="test_show")
     * @Method({"GET", "POST"})
     */
    public function showTest(Request $request, Test $test, QuestionRepository $questions, OptionRepository $options, UserTestRepository $userTests, UserAnswerRepository $userAnswers)
    {
        // If test doesn't exist or hasn't been published return 404
        if (!$test || $test->getActiveFrom() > new \DateTime()) {
            throw $this->createNotFoundException('The test does not exist');
        }

        // Check if the visitor is logged in
        $this->denyAccessUnlessGranted('ROLE_USER');

        // Check if the current user has already taken the test
        if (!$userTest = $userTests->findOneBy(['user' => $this->getUser(), 'test' => $test])) {
            $userTest = new UserTest();
            $userTest->setTest($test);
            $userTest->setUser($this->getUser());
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($userTest);
        $em->flush();

        // If user has finished the test or time's up, return result page
        if ($userTest && ($userTest->getIsSubmitted() || (!$userTests->isInProgress($userTest)))) {
            return $this->redirectToRoute('test_result', ['slug' => $test->getSlug()]);
        }

        // If the current user hasn't taken the test yet, create a form
        $form = $this->createForm(AnswerType::class, $test);
        $form->handleRequest($request);

        // If the form is submitted, render result page
        if ($form->isSubmitted() && $form->isValid()) {
            $userTest->setIsSubmitted(true);

            // Get data from the form
            $answers = $request->request->get('answer');

            foreach ($answers as $questionSlug => $answer) {
                // Extract only answers from the post request
                if (strpos($questionSlug, $test->getSlug()) === false) {
                    continue;
                }
                $userAnswer = new UserAnswer();
                $question = $questions->findOneBy(['slug' => $questionSlug, 'test' => $test]);
                $option = $options->findOneBy(['question' => $question, 'name' => $answer]);
                $userAnswer->setQuestion($question);
                $userAnswer->setOption($option);
                $userAnswer->setUser($this->getUser());

                $em->persist($userAnswer);
            }
            $em->flush();

            return $this->redirectToRoute('test_show', ['slug' => $test->getSlug()]);
            // return $this->render('quiz/test_result.html.twig', ['test' => $test]);
        }

        // If the form isn't submitted, render the form
        return $this->render('quiz/test_show.html.twig', [
            'form' => $form->createView(),
            'test' => $test,
        ]);
    }

    /**
     * @Route("/test/{slug}/result", name="test_result")
     */
    public function showTestResult(Test $test, UserAnswerRepository $userAnswers, OptionRepository $options, UserTestRepository $userTests)
    {
        $userTest = $userTests->findOneBy(['user' => $this->getUser(), 'test' => $test]);

        if (!$userTest || $userTests->isInProgress($userTest)) {
            return $this->redirectToRoute('test_show', ['slug' => $test->getSlug()]);
        }

        $foundUserAnswers = $userAnswers->findAllUserAnswers($test->getQuestion(), $this->getUser());
        $correctAnswers = $options->findCorrectAnswers($test->getQuestion(), $this->getUser());
        $incorrectAnswers = array_merge(array_diff($foundUserAnswers, $correctAnswers), array_diff($correctAnswers, $foundUserAnswers));
        $incorrectQuestions = $options->findQuestions($incorrectAnswers);

        return $this->render('quiz/test_result.html.twig', [
            'test' => $test,
            'incorrectQuestions' => $incorrectQuestions,
            'incorrectAnswers' => $incorrectAnswers,
            'isSubmitted' => $userTest->getIsSubmitted(),
        ]);
    }

    /**
     * @Route("/test", name="test_list")
     */
    public function listTest(TestRepository $tests)
    {
        return $this->render('quiz/test_list.html.twig', [
            'tests' => $tests->findAllPublishedTests(),
        ]);
    }
}
