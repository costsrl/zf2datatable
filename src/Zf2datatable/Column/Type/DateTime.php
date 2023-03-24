<?php
namespace Zf2datatable\Column\Type;

use Zf2datatable\Filter;
use Locale;
use IntlDateFormatter;
use DateTime as PhpDateTime;
use DateTimeZone;
use Zend\Form\Annotation\Instance;

class DateTime extends AbstractType
{
    protected $daterangePickerEnabled = true;

    protected $sourceDateTimeFormat;

    protected $outputDateType;

    protected $outputFilterDateType;

    protected $outputUserDateType=null;

    protected $outputTimeType;

    static public $dateFormatOutput ='d/m/Y';

    protected $returnDateTimeObject = false;

    static $MSSQL_DATETIME2 = '/^\d{4}-\d{2}-\d{2} [0-2][0-9]:[0-5][0-9]:[0-5][0-9].[0-9]{0,7}$/';

    /**
     * Locale to use instead of the default.
     *
     * @var string
     */
    protected $locale;

    /**
     * Timezone to use.
     *
     * @var string
     */
    protected $sourceTimezone;

    /**
     * Timezone to use.
     *
     * @var string
     */
    protected $outputTimezone;

    protected $outputPattern;

    /**
     * @return the $outputFilterDateType
     */
    public function getOutputFilterDateType()
    {
        return $this->outputFilterDateType;
    }

    /**
     * @param field_type $outputFilterDateType
     */
    public function setOutputFilterDateType($outputFilterDateType)
    {
        $this->outputFilterDateType = $outputFilterDateType;
    }

    /**
     *
     * @param string  $sourceDateTimeFormat stringa fromattata nel datarange
     * @param string  $outputDateType string output nella griglia
     * @param string  $filterDateType trasformazione della string in formato compatibile sql
     * @param string  $locale
     * @param string  $sourceTimezone
     * @param string  $outputTimezone
     */
    public function __construct(
        $sourceDateTimeFormat = 'Y-m-d H:i:s',
        $outputDateType = IntlDateFormatter::MEDIUM,
        $filterDateType = 'Y-m-d H:i:s',
        $locale = null,
        $sourceTimezone = 'UTC',
        $outputTimezone = null)
    {
        $this->setSourceDateTimeFormat($sourceDateTimeFormat);
        $this->setOutputDateType($outputDateType);
        $this->setOutputFilterDateType($filterDateType);
        $this->setLocale($locale);
        $this->setSourceTimezone($sourceTimezone);
        $this->setOutputTimezone($outputTimezone);
    }

    public function getTypeName()
    {
        return 'dateTime';
    }

    /**
     * Set Daterange Filter enabled true/false
     * @param bool $val
     */
    public function setDaterangePickerEnabled($val = true)
    {
        $this->daterangePickerEnabled = $val;
    }

    /**
     * Check if the Daterange Filter is enabled
     */
    public function isDaterangePickerEnabled()
    {
        return $this->daterangePickerEnabled;
    }

    public function setSourceDateTimeFormat($format = 'Y-m-d H:i:s')
    {
        $this->sourceDateTimeFormat = $format;
    }

    public function getSourceDateTimeFormat()
    {
        return $this->sourceDateTimeFormat;
    }

    public function setOutputDateType($dateType = IntlDateFormatter::MEDIUM)
    {
        $this->outputDateType = $dateType;
    }

    public function getOutputDateType()
    {
        return $this->outputDateType;
    }

    public function setOutputTimeType($timeType = IntlDateFormatter::NONE)
    {
        $this->outputTimeType = $timeType;
    }

    public function getOutputTimeType()
    {
        return $this->outputTimeType;
    }

    public function setLocale($locale = null)
    {
        $this->locale = $locale;
    }

    public function getLocale()
    {
        if ($this->locale === null) {
            $this->locale = Locale::getDefault();
        }

        return $this->locale;
    }

    public function setSourceTimezone($timezone = 'UTC')
    {
        $this->sourceTimezone = $timezone;
    }

    public function getSourceTimezone()
    {
        return $this->sourceTimezone;
    }

    public function setOutputTimezone($timezone = null)
    {
        $this->outputTimezone = $timezone;
    }

    public function getOutputTimezone()
    {
        if ($this->outputTimezone === null) {
            $this->outputTimezone = date_default_timezone_get();
        }

        return $this->outputTimezone;
    }

    /**
     * ATTENTION: IntlDateTimeFormatter FORMAT!
     *
     * @param string $pattern
     */
    public function setOutputPattern($pattern = null)
    {
        $this->outputPattern = $pattern;
    }

    public function getOutputPattern()
    {
        return $this->outputPattern;
    }

    public function getFilterDefaultOperation()
    {
        return Filter::GREATER_EQUAL;
    }

    /**
     *
     * @param  string $val
     * @return string
     */
    public function getFilterValue($val)
    {
        $dateFake = date($this->getSourceDateTimeFormat());
        $date = PhpDateTime::createFromFormat($this->getSourceDateTimeFormat(), substr($val,0,strlen($dateFake)-1), new DateTimeZone($this->getSourceTimezone()));
        if ($date === false) {
            return null;
        }
        
        if($this->isReturnDateTimeObject())
            return $date;
        else
            return $date->format($this->getOutputFilterDateType());
    }

    /**
     * Convert the value from the source to the value, which the user will see in the column
     *
     * @param  mixed  $val
     * @return string
     */
    public function getUserValue($val)
    {
        if ($val == '') {
            return '';
        }



        if ($val instanceof PhpDateTime) {
            $date = $val;
            $date->setTimezone(new DateTimeZone($this->getSourceTimezone()));
            $date->setTimezone(new DateTimeZone($this->getOutputTimezone()));
            return  $date->format($this->getOutputDateType());
        } else {
            $date = PhpDateTime::createFromFormat($this->getOutputDateType(), $val, new DateTimeZone($this->getSourceTimezone()));
            if (! $date instanceof \DateTime) {
                if(strstr($val,'1900-01-01')) return '-';//fix null date
                if (preg_match(self::$MSSQL_DATETIME2, $val, $match)) {
                    $date = PhpDateTime::createFromFormat($this->getOutputDateType(), substr($val, 0, 19), new DateTimeZone($this->getSourceTimezone()));
                    if ($date instanceof \DateTime) {
                        return $date->format($this->getSourceDateTimeFormat());
                    }
                    return $val;
                } else
                    return $val;
            }


            $date->setTimezone(new DateTimeZone($this->getOutputTimezone()));
            return  $date->format($this->getSourceDateTimeFormat());
        }
    }

    public function getOutputUserDateType()
    {
        return $this->outputUserDateType;
    }

    public function setOutputUserDateType($outputUserDateType)
    {
        $this->outputUserDateType = $outputUserDateType;
        return $this;
    }

    /**
     * @return bool
     */
    public function isReturnDateTimeObject()
    {
        return $this->returnDateTimeObject;
    }

    /**
     * @param bool $returnDateTimeObject
     */
    public function setReturnDateTimeObject($returnDateTimeObject)
    {
        $this->returnDateTimeObject = $returnDateTimeObject;
    }

}
