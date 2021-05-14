<?php
/*
* created on: 03/06/2020 at 2:05 PM
* by: cameronrobertburns
*/

namespace VisageFour\Bundle\ToolsBundle\Services;

use VisageFour\Bundle\ToolsBundle\Traits\LoggerTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Response;

class CSVMaker
{
    use LoggerTrait;

    public function __construct()
    {
        
    }

    /**
     * @param array $objArray
     * @param null $filename
     * @param null $filenamePrepend
     * @return Response
     *
     * Converts an array of objects into a response object filled with CSVs (from those objects)
     */
    public function generateCSVresponse (Collection $objArray, $filename = null, $filenamePrepend = null) {
        if ($objArray->isEmpty()) {
            throw new \Exception ('the collection / you passed in is empty.');
        }
        if (empty($filename)) {
            $date = new \DateTime('now');
            $dateStr = '('. $date->format('Y-m-d H_i_s') .')';
            $filename = 'CSV '. $dateStr .'.csv';

            if (!empty($filenamePrepend)) {
                $filename = $filename .'-'. $filenamePrepend;
            }
        }

        $filename = $this->sanitizeFilename ($filename);
        $response = new Response($this->getFinishedCSVContent($objArray));
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename='. $filename );

        $this->logger->info(
            'Sent a downloadable .csv file named: "'. $filename .'"'
        );

        return $response;
    }

    // return header and data rows in CSV
    protected function getFinishedCSVContent ($objArray) {
        // todo: fix headers for use with entities.
//        $headRow    = $this->getHeadersAsCSV ();
//        dd($objArray);
        $dataRows   = $this->getDataAsCSV($objArray);
//        $content    = $headRow ."\n". $dataRows;
        $content    = $dataRows;

        return $content;
    }

    // return header captions as CSV
    protected function getHeadersAsCSV () {
        die('this needs to be reimplemented');
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

    /**
     * @return string
     * @throws \Exception
     *
     * convert an array of doctrine entities into a CSV string
     * you could also implement a more advanced version, see: https://stackoverflow.com/questions/3933668/convert-array-into-csv
     */
    protected function getDataAsCSV ($objArray) {
        $dataCSV = '';
        foreach($objArray as $curJ => $curObj) {
//            dump($curObj);

            if (!method_exists($curObj, "getCSVArr")) {
                throw new \Exception('Object with classname: "'. get_class($curObj) .' must implement: getCSVArr()');
            }

            $dataCSV .= implode (', ', $curObj->getCSVArr());
//            dd($curResult);
//                foreach($this->headers as $curI => $curHeader) {
//                    if (in_array($this->columnFormat, $curHeader['supportedFormats'])) {
//                        if (!$firstLoop) {
//                            $dataCSV .= ',';
//                        }
//                        $firstLoop = false;
//                        $curValue = (isset($curDatum[$curHeader['reference']])) ? $curDatum[$curHeader['reference']] : '';
//                        $dataCSV .= $curValue;
//                    }
//                }
            $dataCSV .= "\n";
        }

//        die( $dataCSV);

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

        // check last 4 characters == .csv
        $FNExt = substr($filename, -4);
        if (strtolower($FNExt) != '.csv') {
            $filename .= '.csv.';
        }

        return $filename;
    }
}