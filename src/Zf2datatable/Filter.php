<?php
namespace Zf2datatable;

use InvalidArgumentException;

class Filter
{

    /**
     * The constant values are used for display on the usergrid filter
     * This is for help, how the data is filtered really
     *
     * @var string
     */
    const LIKE = '~ *%s*';

    // OK
    /**
     * @var string
     */
    const LIKE_LEFT = '~ *%s';

    // OK
    /**
     * @var string
     */
    const LIKE_RIGHT = '~ %s*';

    // OK
    /**
     * @var string
     */
    const NOT_LIKE = '!~ *%s*';

    // OK
    /**
     * @var string
     */
    const NOT_LIKE_LEFT = '!~ *%s';

    // OK
    /**
     * @var string
     */
    const NOT_LIKE_RIGHT = '!~ %s*';

    // OK
    /**
     * @var string
     */
    const EQUAL = '= %s';

    // OK
    /**
     * @var string
     */
    const NOT_EQUAL = '!= %s';

    // OK
    /**
     * @var string
     */
    const GREATER_EQUAL = '>= %s';

    // OK
    /**
     * @var string
     */
    const GREATER = '> %s';

    // OK
    /**
     * @var string
     */
    const LESS_EQUAL = '<= %s';

    // OK
    /**
     * @var string
     */
    const LESS = '< %s';

    // OK
    /**
     * @var string
     */
    const IN = '=(%s)';

    // OK
    /**
     * @var string
     */
    const NOT_IN = '!=(%s)';

    /**
     * @var string
     */
    const BETWEEN = '%s <> %s';

    /**
     *
     * @var Column\AbstractColumn
     */
    private $column;

    private $operator = self::LIKE;

    private $value;

    private $displayColumnValue;

    /**
     * Apply a filter based on a column
     *
     * @param Column\AbstractColumn $column
     * @param string  $inputFilterValue
     */
    public function setFromColumn(Column\AbstractColumn $column, $inputFilterValue)
    {
        $this->column = $column;
        $this->setColumnOperator($inputFilterValue, $column->getFilterDefaultOperation());
    }

    /**
     * Convert the input filter to operator + filter + display filter value
     *
     * Partly idea taken from ZfDatagrid
     *
     * @see https://github.com/zfdatagrid/grid/blob/master/library/Bvb/Grid.php#L1438
     *
     * @param  string $inputFilterValue
     * @param  mixed  $defaultOperator
     * @return array
     */
    private function setColumnOperator($inputFilterValue, $defaultOperator = self::LIKE)
    {
        $inputFilterValue = (string) $inputFilterValue;
        $inputFilterValue = trim($inputFilterValue);

        $operator = $defaultOperator;
        $value = $inputFilterValue;

        if (substr($inputFilterValue, 0, 2) == '=(') {
            $operator = self::IN;
            $value = substr($inputFilterValue, 2);
            if (substr($value, - 1) == ')') {
                $value = substr($value, 0, - 1);
            }
        } elseif (substr($inputFilterValue, 0, 3) == '!=(') {
            $operator = self::NOT_IN;
            $value = substr($inputFilterValue, 3);
            if (substr($value, - 1) == ')') {
                $value = substr($value, 0, - 1);
            }
        } elseif (substr($inputFilterValue, 0, 2) == '!=' || substr($inputFilterValue, 0, 2) == '<>') {
            $operator = self::NOT_EQUAL;
            $value = substr($inputFilterValue, 2);
        } elseif (substr($inputFilterValue, 0, 2) == '!~' || substr($inputFilterValue, 0, 1) == '!') {
            // NOT LIKE or NOT EQUAL
            if (substr($inputFilterValue, 0, 2) == '!~') {
                $value = trim(substr($inputFilterValue, 2));
            } else {
                $value = trim(substr($inputFilterValue, 1));
            }

            if (substr($inputFilterValue, 0, 2) == '!~' || (substr($value, 0, 1) == '%' || substr($value, - 1) == '%' || substr($value, 0, 1) == '*' || substr($value, - 1) == '*')) {
                // NOT LIKE
                if ((substr($value, 0, 1) == '*' && substr($value, - 1) == '*') || (substr($value, 0, 1) == '%' && substr($value, - 1) == '%')) {
                    $operator = self::NOT_LIKE;
                    $value = substr($value, 1);
                    $value = substr($value, 0, - 1);
                } elseif (substr($value, 0, 1) == '*' || substr($value, 0, 1) == '%') {
                    $operator = self::NOT_LIKE_LEFT;
                    $value = substr($value, 1);
                } elseif (substr($value, - 1) == '*' || substr($value, - 1) == '%') {
                    $operator = self::NOT_LIKE_RIGHT;
                    $value = substr($value, 0, - 1);
                } else {
                    $operator = self::NOT_LIKE;
                }
            } else {
                // NOT EQUAL
                $operator = self::NOT_EQUAL;
            }
        } elseif (substr($inputFilterValue, 0, 1) == '~' || substr($inputFilterValue, 0, 1) == '%' || substr($inputFilterValue, - 1) == '%' || substr($inputFilterValue, 0, 1) == '*' || substr($inputFilterValue, - 1) == '*') {
            // LIKE
            if (substr($inputFilterValue, 0, 1) == '~') {
                $value = substr($inputFilterValue, 1);
            }
            $value = trim($value);

            if ((substr($value, 0, 1) == '*' && substr($value, - 1) == '*') || (substr($value, 0, 1) == '%' && substr($value, - 1) == '%')) {
                $operator = self::LIKE;
                $value = substr($value, 1);
                $value = substr($value, 0, - 1);
            } elseif (substr($value, 0, 1) == '*' || substr($value, 0, 1) == '%') {
                $operator = self::LIKE_LEFT;
                $value = substr($value, 1);
            } elseif (substr($value, - 1) == '*' || substr($value, - 1) == '%') {
                $operator = self::LIKE_RIGHT;
                $value = substr($value, 0, - 1);
            } else {
                $operator = self::LIKE;
            }
        } elseif (substr($inputFilterValue, 0, 2) == '==') {
            $operator = self::EQUAL;
            $value = substr($inputFilterValue, 2);
        } elseif (substr($inputFilterValue, 0, 1) == '=') {
            $operator = self::EQUAL;
            $value = substr($inputFilterValue, 1);
        } elseif (substr($inputFilterValue, 0, 2) == '>=') {
            $operator = self::GREATER_EQUAL;
            $value = substr($inputFilterValue, 2);
        } elseif (substr($inputFilterValue, 0, 1) == '>') {
            $operator = self::GREATER;
            $value = substr($inputFilterValue, 1);
        } elseif (substr($inputFilterValue, 0, 2) == '<=') {
            $operator = self::LESS_EQUAL;
            $value = substr($inputFilterValue, 2);
        } elseif (substr($inputFilterValue, 0, 1) == '<') {
            $operator = self::LESS;
            $value = substr($inputFilterValue, 1);
        } elseif (strpos($inputFilterValue, '<>') !== false) {
            $operator = self::BETWEEN;
            $value = explode('<>', $inputFilterValue);
        }
        else{
            if(strpos($inputFilterValue,'-')!==false){
                $operator = self::BETWEEN;
            }
        }




        $this->operator = $operator;

        if ($value === false) {
            // NO VALUE applied...maybe only "="
            $value = '';
        }

        /*
         * Handle multiple values
         */
        if ($this->getColumn()->getType() instanceof Column\Type\DateTime && $this->getColumn()
            ->getType()
            ->isDaterangePickerEnabled() === true) {
             if(preg_match('# - #', $value))
                $value = explode(' - ', $value);
             elseif(preg_match('#,#', $value))
                $value = explode(',', $value);
             else
                $value = explode(' - ', $value);
        } elseif (! is_array($value)) {
            $value = explode(',', $value);
        }
        foreach ($value as &$val) {
            $val = trim($val);
        }


        if ($operator == self::BETWEEN) {

            // Check if column is a DateTime type and if Daterange picker is enabled
            if ($this->getColumn()->getType() instanceof Column\Type\DateTime && $this->getColumn()
                ->getType()
                ->isDaterangePickerEnabled() === true) {
                $this->displayColumnValue = implode(' - ', $value);
            } else {
                $value = array(
                    min($value),
                    max($value)
                );
                $this->displayColumnValue = sprintf($operator, $value[0], $value[1]);
            }
        } else {
            $this->displayColumnValue = sprintf($operator, implode(',', $value));
        }

        /*
         * The searched value must be converted maybe.... - Translation - Replace - DateTime - ...
         */

        $index = 0;
        foreach ($value as &$val) {

            $type = $this->getColumn()->getType();
            if($type instanceof  \Zf2datatable\Column\Type\DateTime){
                if($type->isDaterangePickerEnabled()){
                    if ($index == 0) $val = $type->getFilterValue($val." 00:00:00") .'.0000000';
                    else $val = $type->getFilterValue($val." 23:59:59") .'.999999';
//                    echo $val."<br />";
                }
            }
            else $val = $type->getFilterValue($val);
            // @TODO Translation + Replace
            $index++;
        }
        $this->value = $value;
    }

    /**
     * Is this a column filter
     *
     * @return boolean
     */
    public function isColumnFilter()
    {
        if ($this->getColumn() instanceof Column\AbstractColumn) {
            return $this->getColumn()->getFilterEnabled();
        } else {
            return false;
        }
    }

    /**
     * Only needed for column filter
     *
     * @return \ZfcDatagrid\Column\AbstractColumn
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     *
     * @return array
     */
    public function getValues()
    {
        return $this->value;
    }

    /**
     *
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Get the value displayed to the user
     *
     * @return string
     */
    public function getDisplayColumnValue()
    {
        return $this->displayColumnValue;
    }

    /**
     * Check if a value is the same (used for style, display actions)
     *
     * @param mixed  $currentValue
     *                              rowValue
     * @param mixed  $expectedValue
     *                              filterValue
     * @param string $operator
     *
     * @return boolean
     */
    public static function isApply($currentValue, $expectedValue, $operator = Filter::EQUAL)
    {
        list ($currentValue, $expectedValue) = self::convertValues($currentValue, $expectedValue, $operator);

        switch ($operator) {

            case Filter::LIKE:
                if (stripos($currentValue, $expectedValue) !== false) {
                    return true;
                }
                break;

            case Filter::LIKE_LEFT:
                $length = strlen($expectedValue);
                $start = 0 - $length;
                $searchedValue = substr($currentValue, $start, $length);
                if (stripos($searchedValue, $expectedValue) !== false) {
                    return true;
                }
                break;

            case Filter::LIKE_RIGHT:
                $length = strlen($expectedValue);
                $searchedValue = substr($currentValue, 0, $length);
                if (stripos($searchedValue, $expectedValue) !== false) {
                    return true;
                }
                break;

            case Filter::NOT_LIKE:
                if (stripos($currentValue, $expectedValue) === false) {
                    return true;
                }
                break;

            case Filter::NOT_LIKE_LEFT:
                $length = strlen($expectedValue);
                $start = 0 - $length;
                $searchedValue = substr($currentValue, $start, $length);
                if (stripos($searchedValue, $expectedValue) === false) {
                    return true;
                }
                break;

            case Filter::NOT_LIKE_RIGHT:
                $length = strlen($expectedValue);
                $searchedValue = substr($currentValue, 0, $length);
                if (stripos($searchedValue, $expectedValue) === false) {
                    return true;
                }
                break;

            case Filter::EQUAL:
            case Filter::IN:
                return $currentValue == $expectedValue;

            case Filter::NOT_EQUAL:
            case Filter::NOT_IN:
                return $currentValue != $expectedValue;

            case Filter::GREATER_EQUAL:
                return $currentValue >= $expectedValue;

            case Filter::GREATER:
                return $currentValue > $expectedValue;

            case Filter::LESS_EQUAL:
                return $currentValue <= $expectedValue;

            case Filter::LESS:
                return $currentValue < $expectedValue;

            case Filter::BETWEEN:
                if (count($expectedValue) >= 2) {
                    if ($currentValue >= $expectedValue[0] && $currentValue <= $expectedValue[1]) {
                        return true;
                    }
                } else {
                    throw new InvalidArgumentException('Between needs exactly an array of two expected values. Give: "' . print_r($expectedValue, true));
                }
                break;

            default:
                throw new InvalidArgumentException('currently not implemented filter type: "' . $operator . '"');
        }

        return false;
    }

    /**
     *
     * @param  unknown $currentValue
     * @param  unknown $expectedValue
     * @param  string  $operator
     * @return array
     */
    private static function convertValues($currentValue, $expectedValue, $operator = Filter::EQUAL)
    {
        switch ($operator) {

            case Filter::LIKE:
            case Filter::LIKE_LEFT:
            case Filter::LIKE_RIGHT:
            case Filter::NOT_LIKE:
            case Filter::NOT_LIKE_LEFT:
            case Filter::NOT_LIKE_RIGHT:
                $currentValue = (string) $currentValue;
                $expectedValue = (string) $expectedValue;
                break;
        }

        return array(
            $currentValue,
            $expectedValue
        );
    }
}
