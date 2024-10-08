<?php

namespace App\Form;

use App\Entity\Audience;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class JutificationAudienceType extends AbstractType
{

    /**
     * Cette fonction permet de confugurer les champs de type text 
     *
     * @param string $label
     * @param string $placeholder
     * @return array
     */
    private function getConfiguration($label, $placeholder, $default = true)
    {
        if ($default) {
            return [
                'label' => $label,
                'attr' => [
                    'placeholder' => $placeholder
                ]
            ];
        }
    }
     public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $type = $options["type"];
        $etat =$options["etat"];

     //  dd($etat);
     
        
       $builder->add('justification', TextareaType::class, [
                    'label' => 'Justifaction',
                    'attr' => ['readonly' => false]
       ]);


   // ->add('accorder', SubmitType::class, ['label' => 'Valider', 'attr' => ['class' => 'btn btn-main btn-ajax valider ']]);
    


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Audience::class,
        ]);
        $resolver->setRequired('type');
        $resolver->setRequired('etat');

    }


   
}
