<?php
// src/KK/JobeetBundle/Form/AffiliateType.php

namespace KK\JobeetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use KK\JobeetBundle\Entity\Affiliate;
use KK\JobeetBundle\Entity\Category;

class AffiliateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url')
            ->add('email')
            ->add('categories', null, array(
                'expanded' => true
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
           'data_class' => 'KK\JobeetBundle\Entity\Affiliate'
        ));
    }

    public function getName()
    {
        return 'affiliate';
    }
}