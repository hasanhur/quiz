<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class TestEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $questions = $builder->getData()->getQuestion();
        $builder->add('isPublished', ChoiceType::class, [
            'label' => 'Status',
            'choices'  => [
                'Published' => true,
                'Draft' => false,
            ],
        ]);
        foreach ($questions as $key => $question) {
            $id = (int)$key + 1;
            $builder
                ->add($question->getSlug(), TextareaType::class, [
                    'attr' => ['class' => 'form-control multiple-choice question-edit-body'],
                    'required' => true,
                    'label' => 'Question '.$id,
                    'label_attr' => ['class' => 'question-name'],
                    'mapped' => false,
                    'data' => $question->getBody(),
                ])
            ;
            // Add unique slug to options
            $options = $question->getOptions();
            foreach ($options as $option) {
                $builder
                    ->add($option->getSlug(), TextType::class, [
                        'attr' => ['class' => 'form-control question-edit'],
                        'required' => false,
                        'label' => false,
                        'data' => $option->getBody(),
                        'mapped' => false,
                    ])
                    ->add($option->getSlug().'_check', CheckboxType::class, [
                        'attr' => ['class' => 'question-edit checkbox'],
                        'label' => 'Correct',
                        'required' => false,
                        'data' => $option->getIsCorrect(),
                        'mapped' => false,
                    ])
                ;
            }
        }
        $builder->add('save', SubmitType::class, ['attr' => ['class' => 'btn btn-primary']]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
