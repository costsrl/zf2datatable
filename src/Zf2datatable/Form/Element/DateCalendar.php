<?php
namespace Zf2datatable\Form\Element;

use Laminas\Form\Element;
use Laminas\InputFilter\InputProviderInterface;
use Laminas\Validator\Date as DateValidator;
use DoctrineExtensions\Versionable\Exception;

class DateCalendar extends Element implements  InputProviderInterface{

	static $DATE_FORMAT = 'Y-m-d';
	static $DATE_FORMAT_IN = 'd-m-Y';
	static $DATE_FORMAT_OUT = 'Y-m-d';
	static $DATE_FORMAT_IN_MASK = '/^\d{1,2}-\d{1,2}-\d{4}$/';
	static $DATE_FORMAT_OUT_MASK = '/^\d{4}-\d{1,2}-\d{1,2}$/';

	//static $DATE_FORMAT_IN_MASK = '/^\d{1,2}/\d{1,2}/\d{4}$/';
	//static $DATE_FORMAT_OUT_MASK = '/^\d{4}/\d{1,2}/\d{1,2}\$/';
	// /^\d{4}-\d{2}-\d{2} [0-2][0-3]:[0-5][0-9]:[0-5][0-9]$/
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
						array('name' => 'Zf2datatable\Filter\DateCalendar')
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
                //secho self::$DATE_FORMAT_OUT .'-'.self::$DATE_FORMAT_IN.'<br />';
                //die();
		if(null === $this->validator){
			$this->validator[] = new DateValidator(
            			    array('format'=>self::$DATE_FORMAT_OUT,'messages'=>
            			         array(
                                'dateFalseFormat'=>'Invalid date format, must be yyyy-mm-dd',
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
            //echo "getValue return value".$this->value.'<br />';
            return $this->value;
	}


	public function setValue($value)
	{
        //$this->value = DateCalendar::converDate($value);
        $this->value = $value;
		//echo "<br> call  setValue  $value:$this->value <br/>";
	}


	public static function converDate($value){

	    if($value instanceof \DateTime){
	        return $value->format(self::$DATE_FORMAT_IN);
	    }

	    $match = array();
	    if(preg_match(self::$DATE_FORMAT_IN_MASK, $value,$match)){
	        $dateOut = \DateTime::createFromFormat(self::$DATE_FORMAT_IN,$value);
	        return $dateOut->format(self::$DATE_FORMAT_OUT);
	    }
	    elseif(preg_match(self::$DATE_FORMAT_OUT_MASK, $value,$match)){
	        $dateIn = \DateTime::createFromFormat(self::$DATE_FORMAT_OUT,$value);
            return $dateIn->format(self::$DATE_FORMAT_IN);
	    }
	    elseif(null === $value){
	        return $value;
	    }
	    else{
	        throw new \Exception('Bad Date Format:value'.$value, $code, $previous);
	    }


		return $value;
	}
}

?>