<?php

namespace VisageFour\Bundle\ToolsBundle\Utilities;

use Lexik\Bundle\MailerBundle\Message\MessageFactory;
use VisageFour\Bundle\ToolsBundle\Entity\EmailRegister;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Swift_Mailer;

// this class is used to represent dataa that will be
// rendered in a datatable
class DataTable
{
    /*
    $tableHeader = array (
        array ('caption'    => 'Name',  'reference'     => 'name'),
        array ('caption'    => 'Email', 'reference'     => 'emailAddress')
    );
    $tableData = array (
        array ('name'       => 'tom',   'emailAddress'  => 'tom@hotmail.com'),
        array ('name'       => 'jess',  'emailAddress'  => 'jess@gmail.com')
    );
    $datatable = new DataTable ($tableHeader, $tableData);
    // */

    protected $header;
    protected $data;

    public function __construct($tableHeader, $tableData) {
        $this->setData($tableData);
        $this->setHeader($tableHeader);
    }

    public function setHeader ($tableHeaders) {
        $this->tableHeaders = $tableHeaders;

        return $this;
    }

    public function setData ($tableData) {
        $this->tableData = $tableData;

        return $this;
    }
}