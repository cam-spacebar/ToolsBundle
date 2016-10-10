<?php

namespace VisageFour\Bundle\ToolsBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * should extend this class and add to the mappers? but nothing is returned?
 */
class BasePersonAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper->add('firstName',   'text')
            ->add('lastName',           'text')
            ->add('email',              'text')
            ->add('mobileNumber',       'text')
            ->add('city',               'text')
            ->add('suburb',             'text')
            ->add('country',            'text')
            ->add('createdAt',          'text')
        ;
    }
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('firstName')
            ->add('lastName')
            ->add('email')
            ->add('mobileNumber')
            ->add('city')
            ->add('suburb')
            ->add('country')
            ->add('createdAt')
        ;
    }
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->addIdentifier('id')
            ->add('createdAt')
            ->add('firstName')
            ->add('lastName')
            ->add('email')
            ->add('mobileNumber')
            ->add('city')
            ->add('suburb')
            ->add('country')
        ;
    }
}
?>