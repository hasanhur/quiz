<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AnswerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $questions = $builder->getData()->getQuestion();
        $i = 1;
        foreach ($questions as $question) {
            $options = $question->getOptions();
            $optionList = [];

            foreach ($options as $option) {
                $optionList[] = [$option->getBody() => $option->getName()];
            }

            $builder
                ->add($question->getSlug(), ChoiceType::class, [
                    'attr' => ['class' => 'form-control multiple-choice'],
                    'choices' => $optionList,
                    'label' => $i.') '.$question->getBody(),
                    'label_attr' => ['class' => 'question-body'],
                    'expanded' => true,
                    'mapped' => false,
                    //'multiple' => true, if there are more than one answers this should be set to true
                ])
            ;
            $i++;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
