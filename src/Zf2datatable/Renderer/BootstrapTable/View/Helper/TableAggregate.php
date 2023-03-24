<?php
namespace Zf2datatable\Renderer\BootstrapTable\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Zf2datatable\Column;
use Zf2datatable\Column\Action\AbstractAction;

/**
 * View Helper
 */
class TableAggregate extends AbstractHelper
{
    protected  $renderMapRows = [];

    private function getTr($row, $open = true)
    {
        if ($open !== true) {
            return '</tr>';
        } else {

            if (isset($row['idConcated'])) {
                return '<tr id="' . $row['idConcated'] . '">';
            } else {
                return '<tr>';
            }
        }
    }

    private function getTd($dataValue, $attributes = array())
    {
        $attr = array();
        foreach ($attributes as $name => $value) {
            if ($value != '') {
                $attr[] = $name . '="' . $value . '"';
            }
        }

        $attr = implode(' ', $attr);

        if($dataValue instanceof \DateTime)
            $dataValue = $dataValue->format(\Zf2datatable\Column\Type\DateTime::$dateFormatOutput);

        return '<td ' . $attr . '>' . $dataValue . '</td>';
    }


    public function aggregateData($row, array $cols ){
        foreach ($cols as $col) {
            if($col->isAggregateColumn()){
                $this->renderMapRows[$col->getUniqueId()]+= (float) str_replace(",",'.',$row[$col->getUniqueId()]);
            }
            else{
                $this->renderMapRows[$col->getUniqueId()] = '-';
            }
        }
    }

    public function drawRow( ){
        $colTypeNumber = new Column\Type\Number(\NumberFormatter::DECIMAL);
        $colTypeNumber->addAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 3);
        $htmlRow = '';
        foreach($this->renderMapRows as $column => $values){
            $htmlRow.=  '<td class="text-right alert" style="font-weight:600">' . $colTypeNumber->getUserValue($values) . '</td>';
        }
        echo "<tr>$htmlRow</tr>";
    }

    /**
     *
     * @param  array          $row
     * @param  array          $cols
     * @param  AbstractAction $rowClickAction
     * @param  array          $rowStyles
     * @throws \Exception
     * @return string
     */
    public function __invoke()
    {
        return $this;
    }
}
