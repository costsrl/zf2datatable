<?php
/**
 * Output as an excel file
 */
namespace Zf2datatable\Renderer\PHPExcel;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Zf2datatable\Renderer\AbstractExport;
use Zf2datatable\Column;
use Zf2datatable\Column\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Laminas\Http\Response\Stream;
use Laminas\Http\Headers;

class Renderer extends AbstractExport
{

    public function getName()
    {
        return 'PHPExcel';
    }

    public function isExport()
    {
        return true;
    }

    public function isHtml()
    {
        return false;
    }

    public function execute()
    {

        if($this->getFindException()){
            foreach ($this->getReasonsException() as $key => $reason){
                $msg[$key] = $reason;
            }


            if($reason !==null) return $this->raiseError($reason,$key);
        }


        $options = $this->getOptions();
        $optionsExport = $options['settings']['export'];

        $optionsRenderer = $this->getOptionsRenderer();

        $phpExcel = new Spreadsheet();

        // Sheet 1
        $phpExcel->setActiveSheetIndex(0);

        $sheet = $phpExcel->getActiveSheet();
        $sheet->setTitle($this->getTranslator()
            ->translate($optionsRenderer['sheetName']));

        if ($optionsRenderer['displayTitle'] === true) {
            $sheet->setCellValue('A' . $optionsRenderer['rowTitle'], $this->getTitle().' - '.$this->getUserInfo().' ('.$this->getDateTimeRender().')');
            $sheet->getStyle('A' . $optionsRenderer['rowTitle'])
                ->getFont()
                ->setSize(15);
        }
        $this->calculateColumnWidth($this->getColumnsToExport());

        $xColumn = 0;
        $yRow = $optionsRenderer['startRowData'];
        foreach ($this->getColumnsToExport() as $column) {
            /* @var $column \Zf2datatable\Column\AbstractColumn */
            //$label = $this->getTranslator()->translate($column->getLabel());
            $label = $column->getLabel();
            $sheet->setCellValueByColumnAndRow($xColumn, $yRow, $label);

            // $sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($xColumn))->setCollapsed(true);
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($xColumn))->setWidth($column->getWidth());

            $xColumn ++;
        }

        /*
         * Data
         */
        $yRow = $optionsRenderer['startRowData'] + 1;
        foreach ($this->getData() as $row) {

            $xColumn = 0;
            foreach ($this->getColumnsToExport() as $column) {
                /* @var $column \Zf2datatable\Column\AbstractColumn */
                $currentColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($xColumn);

                $valueGrid      = $row[$column->getUniqueId()];
                $columnType     = $column->getType();
                $columnTypeName = $columnType->getTypeName();

                if ($columnTypeName == 'number') {
                    $valueGrid  = $column->getType()->getFilterValue($valueGrid);
                    $sheet->getCell($currentColumn . $yRow)->setValueExplicit($valueGrid, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                } else {
                    $sheet->getCell($currentColumn . $yRow)->setValueExplicit($valueGrid, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }



                $columnStyle = $sheet->getStyle($currentColumn . $yRow);
                $columnStyle->getAlignment()->setWrapText(true);

                /*
                 * Styles
                 */
                $styles = array_merge($this->getRowStyles(), $column->getStyles());
                foreach ($styles as $style) {
                    /* @var $style \Zf2datatable\Column\Style\AbstractStyle */
                    if ($style->isApply($row) === true) {
                        switch (get_class($style)) {

                            case \Zf2datatable\Column\Style\Bold::class:
                                $columnStyle->getFont()->setBold(true);
                                break;

                            case \Zf2datatable\Column\Style\Italic::class:
                                $columnStyle->getFont()->setItalic(true);
                                break;

                            case \Zf2datatable\Column\Style\Color::class:
                                $columnStyle->getFont()
                                    ->getColor()
                                    ->setRGB($style->getRgbHexString());
                                break;

                            case \Zf2datatable\Column\Style\BackgroundColor::class:
                                $columnStyle->getFill()->applyFromArray(array(
                                    'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                    'color' => array(
                                        'rgb' => $style->getRgbHexString()
                                    )
                                ));
                                break;

                            default:
                                throw new \Exception('Not defined yet: "' . get_class($style) . '"');
                        }
                    }
                }

                $xColumn ++;
            }

            $yRow ++;
        }

        /*
         * Autofilter, freezing, ...
         */
        // Letzte Zeile merken
        $endRow = $yRow - 1;
        $endColumn = count($this->getColumnsToExport()) - 1;

        // Autofilter + Freeze
        $sheet->setAutoFilter('A' . $optionsRenderer['startRowData'] . ':' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($endColumn) . $endRow);
        $freezeRow = $optionsRenderer['startRowData'] + 1;
        $sheet->freezePane('A' . $freezeRow);

        /*
         * Print settings
         */
        $this->setPrinting($phpExcel);

        /*
         * Save the file
         */
        $path = $optionsExport['path'];
        $saveFilename = $this->getCacheId() . '.xlsx';

        $excelWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($phpExcel);
        $excelWriter->setPreCalculateFormulas(false);

        $excelWriter->save($path . '/' . $saveFilename);

        /*
         * Send the response stream
         */
        $response = new Stream();
        $response->setStream(fopen($path . '/' . $saveFilename, 'r'));

        $headers = new Headers();
        $headers->addHeaders(array(
            'Content-Type' => array(
                'application/force-download',
                'application/octet-stream',
                'application/download'
            ),
            'Content-Length' => filesize($path . '/' . $saveFilename),
            'Content-Disposition' => 'attachment;filename=' . $this->getFilename() . '.xlsx',
            'Cache-Control' => 'must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => 'Thu, 1 Jan 1970 00:00:00 GMT'
        ));

        $response->setHeaders($headers);

        return $response;
    }

    /**
     * Calculates the column width, based on the papersize and orientation
     *
     * @param array $columns
     */
    protected function calculateColumnWidth(array $columns)
    {
        // First make sure the columns width is 100 "percent"
        $this->calculateColumnWidthPercent($columns);

        $paperWidth = $this->getPaperWidth();
        $paperWidth /= 2.29;

        $factor = $paperWidth / 100;
        foreach ($columns as $column) {
            /* @var $column \ZfcDatagrid\Column\AbstractColumn */
            $column->setWidth($column->getWidth() * $factor);
        }
    }

    /**
     * Set the printing options
     *
     * @param PHPExcel $phpExcel
     */
    protected function setPrinting(Spreadsheet $phpExcel)
    {
        $options = $this->getOptions();
        $optionsRenderer = $this->getOptionsRenderer();

        $phpExcel->getProperties()
            ->setCreator('https://github.com/ThaDafinser/Zf2datatable')
            ->setTitle($this->getTitle());

        /*
         * Printing setup
         */
        $papersize = $optionsRenderer['papersize'];
        $orientation = $optionsRenderer['orientation'];
        foreach ($phpExcel->getAllSheets() as $sheet) {
            /* @var $sheet \PHPExcel_Worksheet */
            if ($orientation == 'landscape') {
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
            } else {
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
            }

            switch ($papersize) {

                case 'A5':
                    $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A5);
                    break;

                case 'A4':
                    $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                    break;

                case 'A3':
                    $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A3);
                    break;

                case 'A2':
                    $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A2);
                    break;
            }
        }
        $phpExcel->setActiveSheetIndex(0);
    }
}
