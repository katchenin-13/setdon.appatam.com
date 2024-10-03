<?php

namespace App\Form;
use App\Entity\Demande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class JutificationDemandeType extends AbstractType
{

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
            'data_class' => Demande::class,
        ]);
        $resolver->setRequired('type');
        $resolver->setRequired('etat');

    }


   