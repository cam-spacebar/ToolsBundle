<?php

namespace VisageFour\Bundle\ToolsBundle\Utilities;

use Lexik\Bundle\MailerBundle\Message\MessageFactory;
use Symfony\Component\HttpFoundation\Response;
use VisageFour\Bundle\ToolsBundle\Entity\EmailRegister;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Swift_Mailer;

// this class is used to represent data that will be
// rendered in a datatable or into a .CSV
// Note: can be extended to add useful sorting / reordering algorythms for specific use cases.
// (or is it better to have this logic in the manger class?)
class DataTable
{
    /*
    IMPLETEMENTATION CODE:
    // it may be useful to abstract this logic into the manager class
    $tableHeaders = array (
        array ('caption'    => 'Bookings',                      'reference'     => 'bookings'),
        array ('caption'    => 'Event Name',                    'reference'     => 'eventSeriesName'),
        array ('caption'    => 'Event Start',                   'reference'     => 'startDateTime'),
        array ('caption'    => 'Download Bookings (as .csv)',   'reference'     => 'downloadHref')
    );

    // or use whatever source is needed
    $tableData = array (
        array ('name'       => 'tom',   'emailAddress'  => 'tom@hotmail.com'),
        array ('name'       => 'jess',  'emailAddress'  => 'jess@gmail.com')
    );

    $dataTable = new DataTable($tableHeaders, $tableData);

    // generate a response object with CSV data
    $date           = new \datetime ();
    $dateString     = $date->format('Y-m-d H:i');
    $filename       = 'Bookings as CSV (date: '. $dateString .').csv';

    return $dataTable->generateCSVresponse (
        $filename
    );
    */

    protected $columnFormat;
    protected $headers;
    protected $data;
    protected $CssClassName;
    protected $entityName;

    protected $supportedColumnFormats;

    public function __construct($columnFormat, $entityName, $CssClassName = 'datatable1') {
        $this->columnFormat     = $columnFormat;
        $this->entityName       = $entityName;
        $this->CssClassName     = $CssClassName;

        $this->checkColumnFormatValid ($columnFormat);
    }

    protected function setHeaders ($tableHeaders) {
        $this->headers = $tableHeaders;

        return $this;
    }

    protected function setData ($tableData) {
        $this->data = $tableData;

        return $this;
    }

    // return the number of rows in the datatable
    public function getRowCount () {
        return count($this->data);
    }

    // Render the default styles (if nessacary) and redner the datatable
    public function renderTable ($renderDefaultStyle = true)
    {
        if (empty($this->data)) {
            print $this->entityName;
            print 'No '. $this->entityName .' to display';
        } else {
            if ($renderDefaultStyle) {
                $this->renderDefaultStyle();
            }

            if (empty($this->headers)) {
                throw new \Exception ('dataTable headers must not be empty');
            }

            $class = (!empty($this->CssClassName)) ? ' class="' . $this->CssClassName . '"' : '';
            print '<table' . $class . '>';
            // render table headers
            print '    <tr>';
            foreach ($this->headers as $curi => $curHeader) {
                if (in_array($this->columnFormat, $curHeader['supportedFormats'])) {
                    print '        <th>' . $curHeader['caption'] . '</th>';
                }
            }
            print '        </tr>';

            // render table data
            foreach ($this->data as $curi1 => $curCell) {
                print '        <tr>';
                foreach ($this->headers as $curi2 => $curHeader) {
                    if (in_array($this->columnFormat, $curHeader['supportedFormats'])) {
                        $curValue = (isset($curCell[$curHeader['reference']])) ? $curCell[$curHeader['reference']] : '';
                        print '        <td>' . $curValue . '</td>';
                    }
                }
                print '        </tr>';
            }
            print '</table>';
        }

        return null;        // if return true, will render as : '1'
    }

    // provide some basic default styling for the datatable - this can be customized later
    private function renderDefaultStyle () {
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

    // accepts $data as an array of strings (with commas), $headRow must already be sperated by CSV
    public function generateCSVresponse ($filenamePrepend = null, $filename = null) {
        if (empty($filename)) {
            $date = new \DateTime('now');
            $dateStr = '('. $date->format('Y-m-d H_i_s') .')';
            $filename = $this->entityName. ' '. $dateStr .'.csv';

            if (!empty($filenamePrepend)) {
                $filename = $filename .'-'. $filenamePrepend;
            }
        }

        $filename = $this->sanitizeFilename ($filename);
        $response = new Response($this->getFinishedCSVContent());
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename='. $filename );

        return $response;
    }

    // return header and data rows in CSV
    protected function getFinishedCSVContent () {
        $headRow    = $this->getHeadersAsCSV ();
        $dataRows   = $this->getDataAsCSV();
        $content    = $headRow ."\n". $dataRows;

        return $content;
    }

    // return header captions as CSV
    protected function getHeadersAsCSV () {
        $headersCSV = '';
        $firstLoop = true;
        foreach($this->headers as $curI => $curHeader) {
            if (in_array($this->columnFormat, $curHeader['supportedFormats'])) {
                if (!$firstLoop) {
                    $headersCSV .= ',';
                }
                $firstLoop = false;
                $headersCSV .= $curHeader ['caption'];
            }
        }
        return $headersCSV;
    }

    // return values as CSV
    protected function getDataAsCSV () {
        $dataCSV = '';

        if (!empty($this->data)) {
            foreach($this->data as $curJ => $curDatum) {
                $firstLoop = true;
                foreach($this->headers as $curI => $curHeader) {
                    if (in_array($this->columnFormat, $curHeader['supportedFormats'])) {
                        if (!$firstLoop) {
                            $dataCSV .= ',';
                        }
                        $firstLoop = false;
                        $curValue = (isset($curDatum[$curHeader['reference']])) ? $curDatum[$curHeader['reference']] : '';
                        $dataCSV .= $curValue;
                    }
                }
                $dataCSV .= "\n";
            }
        }

        return $dataCSV;
    }

    // used to remove illegal characters from a filename
    protected function sanitizeFilename ($filename) {
        // Remove anything which isn't a word, whitespace, number
        // or any of the following caracters -_~,;[]().
        // If you don't need to handle multi-byte characters
        // you can use preg_replace rather than mb_ereg_replace
        $filename = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $filename);
        // Remove any runs of periods (thanks falstro!)
        $filename = mb_ereg_replace("([\.]{2,})", '', $filename);

        return $filename;
    }

    // will check that the $columnFormat requested for use in dataTable construction is one supported by the dataTable
    protected function checkColumnFormatValid ($requestedColumnFormat) {
        if (!in_array($requestedColumnFormat, $this->supportedColumnFormats)) {
            throw new \Exception ('The requested $columnFormat: '. $requestedColumnFormat .' is not supported in this dataTable');
        }

        return true;
    }
}