<?php

namespace VisageFour\Bundle\ToolsBundle\Utilities;

use Lexik\Bundle\MailerBundle\Message\MessageFactory;
use VisageFour\Bundle\ToolsBundle\Entity\EmailRegister;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Swift_Mailer;

// this class is used to represent data that will be
// rendered in a datatable or into a .CSV
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

    protected $headers;
    protected $data;
    protected $CssClassName;

    public function __construct($tableHeaders, $tableData, $CssClassName = 'datatable1') {
        $this->setData($tableData);
        $this->setHeaders($tableHeaders);
        $this->CssClassName = $CssClassName;
    }

    public function setHeaders ($tableHeaders) {
        $this->headers = $tableHeaders;

        return $this;
    }

    public function setData ($tableData) {
        $this->data = $tableData;

        return $this;
    }

    public function render ($renderDefaultStyle = true) {
        if ($renderDefaultStyle) { $this->renderDefaultStyle(); }


        if (empty($this->headers)) {
            throw new \Exception ('dataTable headers must not be empty');
        }
        if (empty($this->data)) {
            throw new \Exception ('dataTable data must not be empty');
        }

        $class = (!empty($this->CssClassName)) ? ' class="'. $this->CssClassName .'"' : '';
        print '<table'. $class .'>';
        // render table headers
        print '    <tr>';
        foreach ($this->headers as $curi => $curHeader) {
            print '        <th>'. $curHeader['caption'] .'</th>';
        }
        print '        </tr>';

        // render table data
        foreach ($this->data as $curi1 => $curCell) {
            print '        <tr>';
            foreach ($this->headers as $curi2 => $curHeader) {
                //if (!isset($curCell[$curHeader['reference']])) { die ('here'. $curHeader['reference']); }
                $curValue = (isset($curCell[$curHeader['reference']])) ? $curCell[$curHeader['reference']] : '';
                print '        <td>' . $curValue . '</td>';
            }
            print '        </tr>';
        }
        print '</table>';

        return null;        // if return true, will render as : '1'
    }

    // provide some basic default styling for the datatable - this can be customized later
    public function renderDefaultStyle () {
        print '
<style>
.datatable1, .datatable1 th, .datatable1 td {
    border: 4px solid white;
    border-collapse: collapse;

}
.datatable1 th {
    padding: 12px;
    font-weight: bold;
    background-color: #ddd;
}
.datatable1 td {
    padding: 7px;
    background-color: #eee;
}
</style>
';
    }
}