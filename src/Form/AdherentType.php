<?php

namespace App\Form;

use App\Entity\Adherent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AdherentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, ["label" => "Nom : "])
            ->add('firstname', null, ["label" => "Prénom : "])
            ->add('birthday', BirthdayType::class, ["label" => "Date de naissance : "])
            ->add('email',EmailType::class, ["label" => "Email : "])
            ->add('matricule', null, ["label" => "N° de matricule d'Assas : "])
            ->add('gdpr_consent', null, ["label" => "Consentement règlementation RGPD"])
            ->add('picture', FileType::class, ["label" => "Votre photo d'identité : "])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Adherent::class,
        ]);
    }
}
