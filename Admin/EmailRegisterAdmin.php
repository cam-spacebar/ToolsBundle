<?php
/**
 * Created by PhpStorm.
 * User: cameronburns
 * Date: 31/01/2016
 * Time: 7:55 PM
 */

namespace VisageFour\Bundle\ToolsBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class EmailRegisterAdmin extends Admin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('toEmail')
            ->add('locale')
            ->add('createdAt')
            ->add('paramsSerialized')
            ->add('sendStatus')
            ->add('adapter')
            ->add('emailTemplate')
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('toEmail')
            ->add('locale')
            ->add('createdAt')
            ->add('sendStatus')
            ->add('adapter')
            ->add('emailTemplate')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('toEmail')
            ->add('locale')
            ->add('createdAt')
            ->add('paramsSerialized')
            ->add('sendStatus')
            ->add('adapter')
            ->add('emailTemplate')
        ;
    }
}

?>