<?php

namespace KK\JobeetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use KK\JobeetBundle\Entity\Job;

class JobType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', 'choice', array(
                'choices'  => Job::getTypes(),
                'expanded' => true
            ))
            ->add('category')
            ->add('company')
            ->add('logo', null, array(
                'label' => 'Company logo'
            ))
            ->add('url')
            ->add('position')
            ->add('location')
            ->add('description')
            ->add('how_to_apply', null, array(
                'label' => 'How to apply?'
            ))
            ->add('is_public', null, array(
                'label' => 'Public?'
            ))
            ->add('email')
            ->add('file', 'file', array(
                'label'    => 'Company logo',
                'required' => false
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'KK\JobeetBundle\Entity\Job'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'job';
    }
}
