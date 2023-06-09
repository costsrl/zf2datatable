<?php
namespace Zf2datatable\Renderer\BootstrapTable\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Zf2datatable\Column;
use Zf2datatable\Column\Action\AbstractAction;

/**
 * View Helper
 */
class TableRow extends AbstractHelper
{

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

    /**
     *
     * @param  array          $row
     * @param  array          $cols
     * @param  AbstractAction $rowClickAction
     * @param  array          $rowStyles
     * @throws \Exception
     * @return string
     */
    public function __invoke($row, array $cols, AbstractAction $rowClickAction = null, array $rowStyles = array(), $hasMassActions = false)
    {
        $row =(array) $row;
       
        $return = $this->getTr($row);
        if ($hasMassActions === true) {
            $value='';
            foreach ($cols as $col) {
                if($col->isIdentity()){
                    $value.=sprintf('%s~', $row[$col->getUniqueId()]);
                }
            }
            $value = substr($value, 0,-1);
            $return .= '<td><input type="checkbox" name="massActionSelected[]" value="' . $value . '" /></td>';
        }

        foreach ($cols as $col) {
            /* @var $col \Zf2datatable\Column\AbstractColumn */

            $value = $row[$col->getUniqueId()];

            $cssStyles = array();
            
            $classes = $col->getCssClass();
            
            $cssStyles[] = "width:".$col->getWidth().$col->getWidthMesure();

            if ($col->isHidden() === true) {
                $classes[] = 'hidden';
            }
            
            
            if ($col->isNoLink() === true) {
                $classes[] = 'nolink';
            }


            if (get_class($col->getType()) == \Zf2datatable\Column\Type\Number::class) {
                $cssStyles[] = 'text-align: right';
            } elseif (get_class($col->getType()) == \Zf2datatable\Column\Type\PhpArray::class) {
                $value = '<pre>' . print_r($value, true) . '</pre>';
            }

            $styles = array_merge($rowStyles, $col->getStyles());
            foreach ($styles as $style) {
                /* @var $style \Zf2datatable\Column\Style\AbstractStyle */
                if ($style->isApply($row) === true) {

                    switch (get_class($style)) {

                        case \Zf2datatable\Column\Style\Bold::class:
                            $cssStyles[] = 'font-weight: bold';
                            break;

                        case \Zf2datatable\Column\Style\Italic::class:
                            $cssStyles[] = 'font-style: italic';
                            break;

                        case \Zf2datatable\Column\Style\Color::class:
                            $cssStyles[] = 'color: #' . $style->getRgbHexString();
                            break;

                        case \Zf2datatable\Column\Style\BackgroundColor::class:
                            $cssStyles[] = 'background-color: #' . $style->getRgbHexString();
                            break;
                        case \Zf2datatable\Column\Style\CustomStyle::class:
                            $cssStyles[] = $style->getCustomStyle();
                            break;
                        default:
                            throw new \InvalidArgumentException('Not defined style: "' . get_class($style) . '"');
                    }
                }
            }

            if ($col instanceof Column\Action) {
                /* @var $col \Zf2datatable\Column\Action */
                $actions = array();
                foreach ($col->getActions() as $action) {
                    /* @var $action \Zf2datatable\Column\Action\AbstractAction */

                    if ($action->isDisplayed($row) === true) {
                        $actions[] = $action->toHtml($row);
                    }
                }

                $value = implode(' ', $actions);

                //var_dump($actions);
                $cssStyles[] = 'white-space: nowrap;';
            }

            // "rowClick" action
            if ($col instanceof Column\Select && $rowClickAction instanceof AbstractAction) {
                $value = '<a href="' . $rowClickAction->getLinkReplaced($row) . '">' . $value . '</a>';
            }

            $attributes = array(
                'class' => implode(' ', $classes), 
                'style' => implode(';', $cssStyles),
                'data-columnUniqueId' => $col->getUniqueId()
            );

            $return .= $this->getTd($value, $attributes);
        }

        return $return . $this->getTr($row, false);
    }
}
