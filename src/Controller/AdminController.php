<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Subject;
use App\Entity\Option;
use App\Entity\Test;
use App\Entity\User;
use App\Repository\SubjectRepository;
use App\Repository\TestRepository;
use App\Repository\QuestionRepository;
use App\Repository\UserRepository;
use App\Form\TestEditType;
use App\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * @Route("/admin", name="admin_")
 * @IsGranted("ROLE_ADMIN")
*/
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="home_page")
     * @Method({"GET"})
     */
    public function showHomePage()
    {
        return $this->render('admin/index.html.twig');
    }

    /**
     * @Route("/subject/add", name="subject_add")
     * @Method({"GET", "POST"})
     */
    public function addSubject(Request $request)
    {
        $subject = new Subject();

        $form = $this->createFormBuilder($subject)
        ->add('name', TextType::class, array('label' => 'Subject Name', 'attr' => array('class' => 'form-control')))
        ->add('save', Submittype::class, array('label' => 'Create', 'attr' => array('class' => 'btn btn-primary mt-3')))
        ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $subject = $form->getData();
            $slugger = new AsciiSlugger();
            $slug = $slugger->slug($subject->getName())->lower();

            // Make sure that the slug is unique
            if ($slugNo = $this->getDoctrine()->getRepository(Subject::class)->countSlug($slug)) {
                $subject->setSlug($slug.'-'.$slugNo);
            } else {
                $subject->setSlug($slug);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($subject);
            $em->flush();

            $this->addFlash('success', 'New subject ' . $subject->getName() . ' added!');
        }
        return $this->render('admin/add.html.twig', ['form' => $form->createView(), 'title' => 'subject']);
    }

    /**
     * @Route("/subject/edit/{slug}", name="subject_edit")
     * @Method({"GET", "POST"})
     */
    public function editSubject(Request $request, Subject $subject)
    {
        $form = $this->createFormBuilder($subject)
        ->add('name', TextType::class, array('attr' => array('class' => 'form-control')))
        ->add('save', Submittype::class, array('label' => 'Save Changes', 'attr' => array('class' => 'btn btn-primary mt-3')))
        ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($subject);
            $em->flush();

            return $this->redirectToRoute('subject_list');
        }
        return $this->render('admin/add.html.twig', ['form' => $form->createView(), 'title' => 'subject']);
    }

    /**
     * @Route("/subject/delete/{slug}")
     * @Method({"DELETE"})
     */
    public function deleteSubject(Request $request, Subject $subject)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($subject);
        $em->flush();

        $response = new Response();
        $response->send();
    }

    /**
     * @Route("/test/add", name="test_add")
     * @Method({"GET", "POST"})
     */
    public function addTest(Request $request, SubjectRepository $subjects, TestRepository $tests)
    {
        $test = new Test();
        $foundSubjects = $subjects->findAll();
        $choices = [];

        // Add all subjects to an array called choices to pass them as an argument to the form.
        foreach ($foundSubjects as $subject) {
            $choices[$subject->getName()] = $subject;
        }

        $form = $this->createFormBuilder($test)
        ->add('subject', ChoiceType::class, ['attr' => ['class' => 'form-control'], 'choices'  => $choices])
        ->add('name', TextType::class, ['attr' => ['class' => 'form-control']])
        ->add('maxTime', TimeType::class, [
            'attr' => ['class' => 'max-time'],
            'label' => 'Time limit (optional)',
            'required' => false,
            'input'  => 'datetime',
            'widget' => 'choice',
            'with_seconds' => true,
            'placeholder' => ['hour' => 'Hour', 'minute' => 'Minute', 'second' => 'Second'],
            'mapped' => false,
        ])
        ->add('activeFrom', DateTimeType::class, [
            'label' => 'Publish At',
            'widget' => 'single_text',
            'required' => false,
            'mapped' => false,
            'placeholder' => [
                'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                'hour' => 'Hour', 'minute' => 'Minute', 'second' => 'Second',
            ],
        ])
        ->add('save', Submittype::class, ['label' => 'Create', 'attr' => ['class' => 'btn btn-primary mt-3']])
        ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $test = $form->getData();
            $slugger = new AsciiSlugger();
            $slug = $slugger->slug($test->getName())->lower();

            // Make sure that the slug is unique
            if ($slugNo = $tests->countSlug($slug)) {
                $test->setSlug($slug.'-'.$slugNo);
            } else {
                $test->setSlug($slug);
            }
            $test->setMaxTime($request->request->get('form')['maxTime']);
            if (!empty($request->request->get('form')['activeFrom'])) {
                $test->setActiveFrom($request->request->get('form')['activeFrom']);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($test);
            $em->flush();

            $this->addFlash('success', 'New test ' . $test->getName() . ' added!');
        }
        return $this->render('admin/add.html.twig', ['form' => $form->createView(), 'title' => 'test']);
    }

    /**
     * @Route("/test/edit/{slug}", name="test_edit")
     * @Method({"GET", "POST"})
     */
    public function editTest(Request $request, Test $test, QuestionRepository $questions)// edit here
    {
        $form = $this->createForm(TestEditType::class, $test);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $questions = $test->getQuestion();

            // Get form data
            $formData = $request->request->get('test_edit');

            foreach ($questions as $question) {
                $question->setBody($formData[$question->getSlug()]);
                $options = $question->getOptions();
                foreach ($options as $option) {
                    $option->setBody($formData[$option->getSlug()]);
                    $option->setIsCorrect(isset($formData[$option->getSlug().'_check']));
                    $em->persist($option);
                }
                $em->persist($question);
            }
            $em->flush();
            $this->addFlash('success', 'Test was updated successfully.');
        }

        return $this->render('admin/test_edit.html.twig', ['test' => $test, 'form' => $form->createView()]);
    }

    /**
     * @Route("/test/delete/{slug}")
     * @Method({"DELETE"})
     */
    public function deleteTest(Request $request, Test $test)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($test);
        $em->flush();

        $response = new Response();
        $response->send();
    }

    /**
     * @Route("/question/add/{slug}", name="question_add")
     * @Method({"GET", "POST"})
     */
    public function addQuestion(Request $request, $slug) {
        $test = $this->getDoctrine()->getRepository(Test::class)->findOneBy(['slug' => $slug]);
        $formBuilder = $this->createFormBuilder([], ['allow_extra_fields' => true]);
        $formBuilder->add('body', TextareaType::class, ['attr' => ['class' => 'form-control'], 'required' => true])
                    ->add('type', ChoiceType::class, ['attr' => ['class' => 'form-control'], 'choices' => ['Multiple choice' => 0]])
                    ->add('option_1', TextType::class, ['attr' => ['class' => 'form-control'], 'required' => false])
                    ->add('option_1_check', CheckboxType::class, ['label' => 'Correct', 'required' => false, 'row_attr' => ['class' => 'option-check-1']])
                    ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                        $data = $event->getData();
                        $formBuilder = $event->getForm();

                        if (!$data) {
                            return;
                        }

                        $i = 2;
                        while (isset($data['option_'.$i])) {
                            $option = 'option_'.$i;
                            $formBuilder->add($option, TextType::class, ['attr' => ['class' => 'form-control'], 'required' => false])
                                        ->add($option.'_check', CheckboxType::class, ['label' => 'Correct', 'required' => false, 'row_attr' => ['class' => "option-check-$i"]]);
                            $i++;
                        }
                    })

        ->add('save', Submittype::class, ['label' => 'Create', 'attr' => ['class' => 'btn btn-primary mt-3']]);

        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $question = new Question();
            $question->setTest($test);
            $data = $form->getData();
            $slugger = new AsciiSlugger();
            // return new Response(print_r($data));

            // Question slugs start from question-name-1 (All of them including the first one is adjoined by a number)
            $slug = $test->getSlug().'-';
            $slugNo = count($test->getQuestion()) + 1;
            $slugNo = (string)$slugNo;
            $slug .= $slugNo;
            $question->setSlug($slug);
            $question->setBody($data["body"]);
            $question->setType($data["type"]);

            $em = $this->getDoctrine()->getManager();
            $em->persist($question);
            $em->flush();

            if (null !== $question->getId()) {
                $i = 1;
                foreach ($data as $key => $optionData) {
                    // Extract only answers from the post request
                    if (empty($optionData) || strpos($key, 'option_') === false || strpos($key, '_check')) {
                        continue;
                    }
                    $option = new Option();
                    $option->setBody($optionData);
                    $option->setName($i);
                    $option->setSlug($question->getSlug().'-'.$i);
                    $option->setIsCorrect($data['option_'.$i.'_check']);
                    $question->addOption($option);
                    $em->persist($question);
                    $i++;

                }
                $em->flush();
            }

            $this->addFlash('success', 'New question added!');
            return $this->render('admin/add.html.twig', ['form' => $formBuilder->getForm()->createView(), 'title' => 'question']);
        }
        return $this->render('admin/add.html.twig', ['form' => $form->createView(), 'title' => 'question']);
    }

    /**
     * @Route("/question/delete/{slug}")
     * @Method({"DELETE"})
     */
    public function deleteQuestion(Request $request, Question $question)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($question);
        $em->flush();

        $response = new Response();
        $response->send();
    }

    /**
     * @Route("/user", name="user_list")
     */
    public function listUser(UserRepository $users)
    {
        return $this->render('admin/user_list.html.twig', ['users' => $users->findAll()]);
    }

    /**
     * @Route("/user/edit/{username}", name="user_edit")
     */
    public function editUser(Request $request, User $user)
    {
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
     * @Route("/test", name="test_list")
     */
    public function listTest(TestRepository $tests)
    {
        return $this->render('admin/test_list.html.twig', ['tests' => $tests->findAll()]);
    }
}
