<?php
namespace Zf2datatable\Form\Element;

use Laminas\Form\Element;
use Laminas\InputFilter\InputProviderInterface;
use Laminas\Validator\Date as DateValidator;

class DateTimeCalendar extends Element implements  InputProviderInterface{

	static $DATE_FORMAT = 'Y-m-d H:i';
	static $DATE_FORMAT_IN = 'd/m/Y H:i:s';
	static $DATE_FORMAT_OUT = 'Y-m-d H:i:s';
	static $DATE_FORMAT_IN_MASK = '/^\d{2}\/\d{2}\/\d{4} [0-2][0-9]:[0-5][0-9]:[0-5][0-9]$/';
	static $DATE_FORMAT_OUT_MASK = '/^\d{4}-\d{2}-\d{2} [0-2][0-9]:[0-5][0-9]:[0-5][0-9]$/';

	protected $validator;

        /**
         *
         * @param type $format
         */
        static function setDateFormatIn($format){
            self::$DATE_FORMAT_IN = $format;
        }

        /**
         *
         * @param type $format
         */
        static function setDateFormatOut($format){
            self::$DATE_FORMAT_OUT= $format;
        }


        /**
         *
         * @param type $format
         */
        static function setDateFormatMaskIn($format){
            self::$DATE_FORMAT_IN_MASK = $format;
        }

        /**
         *
         * @param type $format
         */
        static function setDateFormatMaskOut($format){
            self::$DATE_FORMAT_OUT_MASK= $format;
        }


	/**
	 * Provide default input rules for this element
	 *
	 * Attaches default validators for the datetime input.
	 *
	 * @return array
	 */
	public function getInputSpecification()
	{
	    return array(
				'name' => $this->getName(),
				'required' => false,
				'filters' => array(
						array('name' => 'Laminas\Filter\StringTrim'),
						array('name' => 'Zf2datatable\Filter\DateTimeCalendar')
				),
				'validators' => $this->getValidators(),
		);
	}

	/**
	 * Get validators
	 *
	 * @return array
	 */
	protected function getValidators(){
		if(null === $this->validator){
			$this->validator[] = new DateValidator(array('format'=>self::$DATE_FORMAT_OUT,'messages'=>array(
                'dateFalseFormat'=>'Invalid date format, must be yyyy-mm-ddd',
                'dateInvalidDate'=>'Invalid date, must be '.self::$DATE_FORMAT_IN
            )));
		}
		return $this->validator;
	}


	/**
	 * Sets the validator to use for this element
	 *
	 * @param  ValidatorInterface $validator
	 * @return Application\Form\Element\Phone
	 */
	public function setValidator(ValidatorInterface $validator)
	{
		$this->validator = $validator;
		return $this;
	}


	public function getValue(){

      return DateTimeCalendar::converDate($this->value);
	}


	public function setValue($value)
	{
       $this->value = DateTimeCalendar::converDate($value);
	}


	public static function converDate($value){
	    if($value instanceof \DateTime){
	        return $value->format(self::$DATE_FORMAT_IN);
	    }



	    $match = array();
	    if(preg_match(DateTimeCalendar::$DATE_FORMAT_IN_MASK, $value,$match)){
	        $dateOut = \DateTime::createFromFormat(self::$DATE_FORMAT_IN,$value);
	        return $dateOut->format(self::$DATE_FORMAT_OUT);
	    }
	    elseif(preg_match(DateTimeCalendar::$DATE_FORMAT_OUT_MASK, $value,$match)){
	        $dateIn = \DateTime::createFromFormat(self::$DATE_FORMAT_OUT,$value);
            return $dateIn->format(DateTimeCalendar::$DATE_FORMAT_IN);
	    }
	    elseif(null === $value){
	        return $value;
	    }
	    else{
	        throw new \Exception('Bad DateTime Format:value'.$value, $code, $previous);
	    }

	    return $value;
	}
}

?>
