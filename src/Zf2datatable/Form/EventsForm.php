<?php
namespace Zf2datatable\Form;

use Laminas\Form\Form;
use Laminas\Form\FormInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\ServiceLocatorInterface;

class EventsForm extends Form{

	/**
	 * @var EventManagerInterface
	 */
	protected $events;

        /**
         *
         * @var Laminas\ServiceManager\Servicelocator
         */
        protected $serviceLocator;


        /**
         *
         * @var array
         */
        protected $formOption;


        /**
         *
         * @return array
         */
        public function getformOption(){
            return $this->formOption;
        }

        /**
         *
         * @param array $formOption
         * @return \Zf2datatable\Form\EventsForm
         */
        public function setformOption($formOption){
             $this->formOption = $formOption;
             return $this;
        }



    /**
	 * Set service locator
	 *
	 * @param ServiceLocatorInterface $serviceLocator
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
		$this->serviceLocator = $serviceLocator;
	}

	/**
	 * Get service locator
	 *
	 * @return ServiceLocatorInterface
	 */
	public function getServiceLocator() {
		return $this->serviceLocator;
	}


	/**
	 * Set the event manager instance used by this context
	 *
	 * @param  EventManagerInterface $events
	 * @return mixed
	 */
	public function setEventManager(EventManagerInterface $events)
	{
		$this->events = $events;
		return $this;
	}

	/**
	 * Retrieve the event manager
	 *
	 * Lazy-loads an EventManager instance if none registered.
	 *
	 * @return EventManagerInterface
	 */
	public function getEventManager()
	{
		if (!$this->events instanceof EventManagerInterface) {
			$identifiers = array(__CLASS__, get_called_class());
			if (isset($this->eventIdentifier)) {
				if ((is_string($this->eventIdentifier))
						|| (is_array($this->eventIdentifier))
						|| ($this->eventIdentifier instanceof Traversable)
				) {
					$identifiers = array_unique($identifiers + (array) $this->eventIdentifier);
				} elseif (is_object($this->eventIdentifier)) {
					$identifiers[] = $this->eventIdentifier;
				}
				// silently ignore invalid eventIdentifier types
			}
			$appEventManager    = $this->getServiceLocator()->get('EventManager');
			$shareEventManager  = $appEventManager->getSharedManager();
			$this->setEventManager(new EventManager($shareEventManager,$identifiers));
		}
		return $this->events;
	}


   public function __construct($name = 'zf2datatableform', $options = array())
        {
            parent::__construct($name, $options);
        }


   /*** default **/
   public function bind($object, $flags = FormInterface::VALUES_NORMALIZED){
            $this->getEventManager()->trigger('pre.'.__FUNCTION__, $this,$object);
            parent::bind($object,$flags);
            $this->getEventManager()->trigger('post.'.__FUNCTION__, $this,$object);
  }

  

  /*** default **/
  public function setData($data){
      $results =$this->getEventManager()->trigger('pre.'.__FUNCTION__, $this,$data);
      if ($results->stopped()) {
          $data = $results->last();
      }
     
      parent::setData($data);
      $this->getEventManager()->trigger('post.'.__FUNCTION__, $this,$data);
  }


  /** default **/
  public function isValid(){
            $this->getEventManager()->trigger('pre.'.__FUNCTION__, $this,(array) $this->getformOption());
            $valid = parent::isValid();
            $this->getEventManager()->trigger('post.'.__FUNCTION__, $this,(array) $this->getformOption());
            return $valid;
        }


 /** getData **/
 public function getData($flag = FormInterface::VALUES_NORMALIZED){
            $this->getEventManager()->trigger('pre.'.__FUNCTION__, $this,(array) $this->getformOption());
            $data = parent::getData($flag = FormInterface::VALUES_NORMALIZED);
            $this->getEventManager()->trigger('post.'.__FUNCTION__, $this,(array) $this->getformOption());
            return $data;
 }



  /** prepare **/
  public function prepare(){
            $this->getEventManager()->trigger('pre.'.__FUNCTION__, $this,(array) $this->getformOption());
            parent::prepare();
            $this->getEventManager()->trigger('post.'.__FUNCTION__, $this,(array) $this->getformOption());
        }



   public function add($elementOrFieldset, array $flags = array()){
            parent::add($elementOrFieldset, $flags);
        }

}

?>