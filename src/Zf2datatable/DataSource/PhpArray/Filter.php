<?php
namespace Zf2datatable\DataSource\PhpArray;

use Zf2datatable\Filter as DatagridFilter;
use Zf2datatable\Column;

class Filter
{

    /**
     *
     * @var \Zf2datatable\Filter
     */
    private $filter;

    /**
     *
     * @param \Zf2datatable\Filter $filter
     */
    public function __construct(DatagridFilter $filter)
    {
        $this->filter = $filter;
    }

    /**
     *
     * @return \Zf2datatable\Filter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param  array      $row
     * @throws \Exception
     * @return boolean
     */
    public function applyFilter(array $row)
    {
        $wasTrueOneTime = false;
        $isApply = false;

        foreach ($this->getFilter()->getValues() as $filterValue) {
            $filter = $this->getFilter();
            $col = $filter->getColumn();
            $value = $row[$col->getUniqueId()];
            if ($col->getType() instanceof Column\Type\DateTime && $filter->getColumn()
                    ->getType()
                    ->isDaterangePickerEnabled() === true) {
                $value = $value->format($col->getType()->getOutputFilterDateType());

            } else {
                $value = $col->getType()->getFilterValue($value);
            }
                    

            if ($filter->getOperator() == DatagridFilter::BETWEEN) {
                return DatagridFilter::isApply($value, $this->getFilter()->getValues(), $filter->getOperator());
            } else {
                $isApply = DatagridFilter::isApply($value, $filterValue, $filter->getOperator());
            }
            if ($isApply === true) {
                $wasTrueOneTime = true;
            }

            switch ($filter->getOperator()) {
                case DatagridFilter::NOT_LIKE:
                case DatagridFilter::NOT_LIKE_LEFT:
                case DatagridFilter::NOT_LIKE_RIGHT:
                case DatagridFilter::NOT_EQUAL:
                case DatagridFilter::NOT_IN:
                    if ($isApply === false) {
                        // normally one "match" is okay -> so it's applied
                        // but e.g. NOT_LIKE is not allowed to match so even if the othere rules are true
                        // it has to fail!
                        return false;
                    }
                    break;
            }
        }

        if ($isApply === false && $wasTrueOneTime === true) {
            return true;
        }

        return $isApply;
    }
}
