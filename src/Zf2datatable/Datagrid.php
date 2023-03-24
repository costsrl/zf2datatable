<?php
namespace Zf2datatable;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
use Laminas\Db\Sql;
use Doctrine\Common\Collections\Collection;
use ArrayIterator;
use Laminas\Mvc\MvcEvent;
use Laminas\Http\PhpEnvironment\Request as HttpRequest;
use Laminas\Console\Request as ConsoleRequest;
use Laminas\View\Model\ViewModel;
use Laminas\Paginator\Paginator;
use Laminas\I18n\Translator\Translator;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Cache;
use Laminas\Session\Container as SessionContainer;
use Laminas\Db\Sql\Select as LaminasSelect;
use Laminas\Db\TableGateway\AbstractTableGateway as TableGateway;
use Laminas\View\Model\JsonModel;
use Laminas\Stdlib\ResponseInterface;
use Zf2datatable\Column\Style;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\Factory as InputFactory;
use Zf2datatable\DataSource\Doctrine2Collection;
use ZfcUser\Mapper\Exception\InvalidArgumentException;
use Laminas\Mvc\Router\Http\Method;

class Datagrid
{

    /**
     *
     * @var array
     */
    protected $options = array();

    /**
     *
     * @var ServiceLocatorInterface
     */
    private $serviceLocator;

    /**
     *
     * @var SessionContainer
     */
    protected $session;

    /**
     *
     * @var Laminas\Permission\Acl
     */
    protected $acl;


    /**
     *
     * @var Laminas\Permission\Acl
     */
    protected $defaultRole = 'Guest';


    /**
     *
     * @var Cache\Storage\StorageInterface
     */
    protected $cache;

    /**
     *
     * @var string
     */
    protected $cacheId;

    /**
     *
     * @var MvcEvent
     */
    protected $mvcEvent;

    /**
     *
     * @var array
     */
    protected $parameters = array();

    /**
     *
     * @var mixed
     */
    protected $url;

    /**
     *
     * @var HttpRequest
     */
    protected $request;

    /**
     * View or Response
     *
     * @var \Laminas\Http\Response\Stream
     */
    protected $response;

    /**
     *
     * @var Renderer\AbstractRenderer
     */
    private $renderer;

    /**
     *
     * @var Translator
     */
    protected $translator;

    /**
     *
     * @var string
     */
    protected $id;

    /**
     * The grid title
     *
     * @var string
     */
    protected $title = '';

    /**
     *
     * @var DataSource\DataSourceInterface
     */
    protected $dataSource = null;

    /**
     *
     * @var integer
     */
    protected $defaulItemsPerPage = 25;

    /**
     *
     * @var array
     */
    protected $columns = array();

    /**
     *
     * @var Style\AbstractStyle[]
     */
    protected $rowStyles = array();

    /**
     *
     * @var Column\Action\AbstractAction
     */
    protected $rowClickAction;

    /**
     *
     * @var Action\Mass
     */
    protected $massActions = array();

    /**
     * The prepared data
     *
     * @var array
     */
    protected $preparedData = array();

    /**
     *
     * @var array
     */
    protected $isUserFilterEnabled = true;

    /**
     *
     * @var Paginator
     */
    protected $paginator = null;

    /**
     *
     * @var array
     */
    protected $exportRenderers;

    /**
     *
     * @var string
     */
    protected $toolbarTemplate;

    /**
     *
     * @var ViewModel
     */
    protected $viewModel;

    /**
     *
     * @var boolean
     */
    protected $isInit = false;

    /**
     *
     * @var boolean
     */
    protected $isDataLoaded = false;

    /**
     *
     * @var boolean
     */
    protected $isRendered = false;

    /**
     *
     * @var string
     */
    protected $forceRenderer;

    private $specialMethods = array(
        'filterSelectOptions',
        'rendererParameter',
        'replaceValues',
        'select',
        'sortDefault'
    );

    /**
     *
     * @var boolean
     */
    protected $isCrud = false;

    /**
     *
     * @var \Laminas\Form\Form
     */
    protected $frmMainCrud = null;

    /**
     *
     * @var \Laminas\Form\Form
     */
    protected $frmFilterCrud = null;


    /**
     * @var array $validation gruops
     */
    protected $frmValidationGroup;



    /**
     *
     * @var array
     */
    protected $frmElementSource = array();


    /**
     *
     */
    protected $frmElementDefaultPriority =array();


    /**
     */
    protected $crudOption;

    /**
     *
     * @var \EventManager\EventManager\EventManagerInterface
     */
    protected $eventManager = null;

    /**
     *
     * @var Laminas\Mvc\Controller\PluginManager
     */
    protected $pluginControllerManager = null;

    protected $pluginViewHelperManager = null;

    protected $columnIdentity = array();

    protected $dataDetail = null;

    protected $crudDetail = null;

    /**
     *
     * @var boolean
     */
    protected $keepCacheFilter = false;

    /**
     *
     * @var boolean type
     */
    protected $isAllowView = true;

    /**
     *
     * @var boolean type
     */
    protected $isAllowAdd = true;

    /**
     *
     * @var boolean type
     */
    protected $isAllowEdit = true;

    /**
     *
     * @var boolean type
     */
    protected $isAllowDelete = true;


    /**
     *
     * @var string
     */
    protected $twitterBoostrapVersion='3.*';


    /**
     *
     * @var string
     */
    protected $injetJsCode = null;


    /**
     * @var string
     */
    protected $injetJsFormCode = null;


    /**
     *
     * @var ruote
     */
    protected $urlRouteRedirectCrud = null;



    /**
     *
     * @var string
     */
    protected $viewChild = null;


    /**
     *
     * @var string  top,bottom
     */
    public static $viewModelPosition = array('top'=>'childModelTop', 'bottom'=>'childModelBottom','middle'=>'childModelMiddle');




    /**
     *
     * @var array ["name"=>"",
     *             "id"=>"",
     *             "label"=>"" ,
     *             "href"=>""]
     */
    protected $additionalButton;


    /**
     *
     * @var Boolean
     */
    protected $additionalFilter= false;


    /**
     *
     * @var string path to upload file
     */
    protected $pathFileUpload;


    /**
     *
     * @var string set a redirect url
     */
    protected $defaultUriRedirect;



    protected $setFormFilterFromCrud = false;


    protected $showAddCrudColumn=true;

    protected $proxyElementReplace    = [];
    protected $proxyElementAdd        = [];
    protected $proxyRemoveElement = [];

    protected $enableFlashMessenger = true;

    protected $labelAddButton="Add Record";

    /**
     *
     * @var boolean type
     */
    protected $isAllowButtonFilter = false;


    /**
     *
     * @var array messaggio validazione
     */
    protected $msgValidatorError = array();


    /** DEFINISCO PROPAGAZIONE DEI FILTRI **/
    protected $filterMethod ='POST';


    /** MODALITA DI VISUALIZZAZIONE DETTAGLI **/
    protected $viewModeRender = 'detail';  // crudview alternativa

    /**
     * @var string
     */
    protected $redirectToRecordInsert = false; // consente di redirigere al record e non alla griglia


    /**
     * @var string
     */
    protected $redirectToRecordUpdate = false; // consente di redirigere al record e non alla griglia


    protected static $instanceNumber = 0;


    protected $buttonLabel = 'New Record';


    protected $hideDuplicate = false;


    protected $showAggregate = false;

    /**
     * @return bool
     */
    public function isShowAggregate(): bool
    {
        return $this->showAggregate;
    }

    /**
     * @param bool $showAggregate
     */
    public function setShowAggregate(bool $showAggregate): void
    {
        $this->showAggregate = $showAggregate;
    }  // gestisce i totali in maschera

    /**
     * @return bool
     */
    public function isHideDuplicate()
    {
        return $this->hideDuplicate;
    }

    /**
     * @param bool $hideDuplicate
     */
    public function setHideDuplicate($hideDuplicate)
    {
        $this->hideDuplicate = $hideDuplicate;
    }

    /**
     * @return string
     */
    public function getButtonLabel()
    {
        return $this->buttonLabel;
    }

    /**
     * @param string $buttonLabel
     */
    public function setButtonLabel($buttonLabel = 'New Record')
    {
        $this->buttonLabel = $buttonLabel;
    }



    /**
     * @return the $instanceNumber
     */
    public static function getInstanceNumber()
    {
        return self::$instanceNumber;
    }

    /**
     * @param number $instanceNumber
     */
    public static function setInstanceNumber()
    {
        self::$instanceNumber++;
    }

    /**
     * @return the $defaultRole
     */
    public function getDefaultRole()
    {
        return $this->defaultRole;
    }

    /**
     * @param \Zf2datatable\Laminas\Permission\Acl $defaultRole
     */
    public function setDefaultRole($defaultRole)
    {
        $this->defaultRole = $defaultRole;
    }

    /**
     * @return the $acl
     */
    public function getAcl()
    {
        return $this->acl;
    }

    /**
     * @param \Zf2datatable\Laminas\Permission\Acl $acl
     */
    public function setAcl($acl)
    {
        $this->acl = $acl;
    }

    /**
     * @return the $redirectToRecordInsert
     */
    public function getRedirectToRecordInsert()
    {
        return $this->redirectToRecordInsert;
    }

    /**
     * @return the $redirectToRecordUpdate
     */
    public function getRedirectToRecordUpdate()
    {
        return $this->redirectToRecordUpdate;
    }

    /**
     * @param boolean $redirectToRecordInsert
     */
    public function setRedirectToRecordInsert($redirectToRecordInsert)
    {
        $this->redirectToRecordInsert = $redirectToRecordInsert;
    }

    /**
     * @param boolean $redirectToRecordUpdate
     */
    public function setRedirectToRecordUpdate($redirectToRecordUpdate)
    {
        $this->redirectToRecordUpdate = $redirectToRecordUpdate;
    }




    /**
     * @return the $viewModeRender
     */
    public function getViewModeRender()
    {
        return $this->viewModeRender;
    }

    /**
     * @param string $viewModeRender
     */
    public function setViewModeRender($viewModeRender)
    {
        $this->viewModeRender = $viewModeRender;
    }

    /**
     * @return the $viewModelPosition
     */
    public static function getViewModelPosition()
    {
        return Datagrid::$viewModelPosition;
    }

    /**
     * @param string $viewModelPosition
     */
    public static function setViewModelPosition($viewModelPosition)
    {
        Datagrid::$viewModelPosition = $viewModelPosition;
    }

    /**
     * @return the $filterMethod
     */
    public function getFilterMethod()
    {
        return $this->filterMethod;
    }

    /**
     * @param string $filterMethod
     */
    public function setFilterMethod($filterMethod)
    {
        $this->filterMethod = $filterMethod;
    }

    /**
     * @return the $msgValidatorError
     */
    public function getMsgValidatorError()
    {
        return $this->msgValidatorError;
    }

    /**
     * @param multitype: $msgValidatorError
     */
    public function setMsgValidatorError($msgValidatorError)
    {
        $this->msgValidatorError = $msgValidatorError;
    }

    /**
     * @return the $isAllowButtonFilter
     */
    public function getIsAllowButtonFilter()
    {
        return $this->isAllowButtonFilter;
    }

    /**
     * @param boolean $isAllowButtonFilter
     */
    public function setIsAllowButtonFilter($isAllowButtonFilter)
    {
        $this->isAllowButtonFilter = $isAllowButtonFilter;
    }

    /**
     * @return the $labelAddButton
     */
    public function getLabelAddButton()
    {
        return $this->labelAddButton;
    }

    /**
     * @param field_type $labelAddButton
     */
    public function setLabelAddButton($labelAddButton)
    {
        $this->labelAddButton = $labelAddButton;
    }


    public function getshowAddCrudColumn()
    {
        return $this->showAddCrudColumn;
    }


    public function setshowAddCrudColumn($value)
    {
        $this->showAddCrudColumn = $value;
        return $this;
    }


    /**
     *
     */
    public function getViewChild()
    {
        return $this->viewChild;
    }

    /**
     *
     * @param unknown $viewChild
     */

    public function setViewChild(\Laminas\View\Model\ViewModel $viewChild, $position = 'childModelTop')
    {
        $this->viewChild['ViewModel'] = $viewChild;
        $this->viewChild['Position']  = $position;
        return $this;
    }


    /**
     *
     */
    protected $logger;

    /**
     * grid identyfier
     */
    protected $gridId ='Zf2datatableDataGrid';


    const paramsSeparator = '~';

    const stepPriority = 10;

    static $priorityStart = 1000;


    /**
     * @return the $frmElementDefaultPriority
     */
    public function getFrmElementDefaultPriority($key)
    {
        if (array_key_exists($key, $this->frmElementDefaultPriority))
            return $this->frmElementDefaultPriority[$key];
        else
            return false;
    }

	/**
     * @param multitype: $frmElementDefaultPriority
     */
    public function setFrmElementDefaultPriority(array $_frmElementDefaultPriority)
    {
        $this->frmElementDefaultPriority = $_frmElementDefaultPriority;
        return $this;
    }

	/**
     *
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     *
     * @param unknown $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
        return $this;
    }


    /**
     *
     */
    public function getUrlRouteRedirectCrud()
    {
        return $this->urlRouteRedirectCrud;
    }

    /**
     *
     * @param unknown $routeName
     * @param array $params  'controller' => $controller, 'action' => $action
     */
    public function setUrlRouteRedirectCrud($routeName,array $params, array $queryparams)
    {
        $plgmanager = $this->getPluginControllerManager();
        $plgurl = $plgmanager->get('url');
        $this->urlRouteRedirectCrud  = $plgurl->fromRoute($routeName, $params,$queryparams);
        return $this;
    }

    /**
     *
     * @param string $url
     */
    public function setUrlRedirectCrud($url)
    {
        $this->urlRouteRedirectCrud  = $url;
        return $this;
    }



    /**
     * @return the $injetJsFormCode
     */
    public function getInjetJsFormCode()
    {
        return $this->injetJsFormCode;
    }

	/**
     * @param string $injetJsFormCode
     */
    public function setInjetJsFormCode($injetJsFormCode)
    {
        $this->injetJsFormCode = $injetJsFormCode;
    }

	/**
     * @return the $injetJsCode
     */
    public function getInjetJsCode()
    {
        return $this->injetJsCode;
    }

	/**
     * @param string $injetJsCode
     */
    public function setInjetJsCode($injetJsCode)
    {
        $this->injetJsCode = $injetJsCode;
    }

	/**
     * @return the $twitterBoostrapVersion
     */
    public function getTwitterBoostrapVersion()
    {
        return $this->twitterBoostrapVersion;
    }

	/**
     * @param string $twitterBoostrapVersion
     */
    public function setTwitterBoostrapVersion($twitterBoostrapVersion)
    {
        $this->twitterBoostrapVersion = $twitterBoostrapVersion;
    }

	/**
     *
     */
    public function setResponse(\Laminas\Http\PhpEnvironment\Response $response){
        $this->response = $response;
        return $this->response;
    }

    /**
     *
     * @return the $isAllowDelete
     */
    public function getisAllowView()
    {
        return $this->isAllowView;
    }

    /**
     *
     * @param
     *            boolean isAllowAdd
     */
    public function setisAllowView($isAllowView)
    {
        $this->isAllowView = $isAllowView;
    }

    /**
     *
     * @return the $isAllowDelete
     */
    public function getisAllowAdd()
    {
        return $this->isAllowAdd;
    }

    /**
     *
     * @param boolean isAllowAdd
     * abilita /disabilita  pulsanta aggiungi
     */
    public function setisAllowAdd($isAllowAdd)
    {
        $this->isAllowAdd = $isAllowAdd;
    }

    /**
     *
     * @return the $isAllowDelete
     *
     */
    public function getisAllowDelete()
    {
        return $this->isAllowDelete;
    }

    /**
     *
     * @param boolean isAllowDelete
     * abilita /disabilita  pulsanta delete
     */
    public function setisAllowDelete($isAllowDelete)
    {
        $this->isAllowDelete = $isAllowDelete;
    }

    /**
     *
     * @return the $isAllowDelete
     */
    public function getisAllowEdit()
    {
        return $this->isAllowEdit;
    }

    /**
     *
     * @param boolean isAllowUpdate
     * abilita /disabilita  pulsanta edit
     */
    public function setisAllowEdit($isAllowEdit)
    {
        $this->isAllowEdit = $isAllowEdit;
    }

    /**
     *
     * @return the $keepCacheFilter
     */
    public function getKeepCacheFilter()
    {
        return $this->keepCacheFilter;
    }

    /**
     *
     * @param boolean $keepCacheFilter
     */
    public function setKeepCacheFilter($keepCacheFilter)
    {
        $this->keepCacheFilter = $keepCacheFilter;
    }

    /**
     *
     * @return the $crudDetail
     */
    public function getCrudDetail()
    {
        return $this->crudDetail;
    }

    /**
     *
     * @param field_type $crudDetail
     */
    public function setCrudDetail($crudDetail)
    {
        $this->crudDetail = $crudDetail;
    }

    /**
     *
     * @return the $frmElementSource
     */
    public function getFrmElementSource()
    {
        return $this->frmElementSource;
    }

    /**
     *
     * @param multitype: $frmElementSource
     */
    public function setFrmElementSource($frmElementSource)
    {
        $this->frmElementSource = $frmElementSource;
    }

    /**
     *
     * @return the $isCrud
     */
    public function getIsCrud()
    {
        return $this->isCrud;
    }

    /**
     *
     * @param boolean $isCrud
     */
    public function setIsCrud($isCrud)
    {
        $this->isCrud = $isCrud;
        return $this;
    }

    /**
     *
     * @return the $dataDetail
     */
    public function getDataDetail()
    {
        return $this->dataDetail;
    }

    /**
     *
     * @param field_type $dataDetail
     */
    public function setDataDetail($dataDetail)
    {
        $this->dataDetail = $dataDetail;
    }

    /**
     *
     * @return the $pluginControllerManager
     */
    public function getPluginControllerManager()
    {
        if ($this->pluginControllerManager == null) {
            $this->pluginControllerManager = $this->getServiceLocator()->get('ControllerPluginManager');
        }
        return $this->pluginControllerManager;
    }

    /**
     *
     * @param field_type $pluginControllerManager
     */
    public function setPluginControllerManager($pluginControllerManager)
    {
        $this->pluginControllerManager = $pluginControllerManager;
    }

    /**
     *
     * @return the $pluginViewHelperManager
     */
    public function getPluginViewHelperManager()
    {
        if ($this->pluginViewHelperManager == null) {
            $this->pluginViewHelperManager = $this->getServiceLocator()->get('ViewHelperManager');
        }
        return $this->pluginViewHelperManager;
    }

    /**
     *
     * @param field_type $pluginViewHelperManager
     */
    public function setPluginViewHelperManager($pluginViewHelperManager)
    {
        $this->pluginViewHelperManager = $pluginViewHelperManager;
    }

    /**
     *
     * @param boolean $isDataLoaded
     */
    public function setIsDataLoaded($isDataLoaded)
    {
        $this->isDataLoaded = $isDataLoaded;
    }

    /**
     *
     * @return the $frmMainCrud
     */
    public function getFrmMainCrud()
    {
        if (! $this->frmMainCrud instanceof \Laminas\Form\Form) {
            $this->frmMainCrud = $this->tryToCreateRawForm($this->getTitle());
        }

        return $this->frmMainCrud;
    }

    /**
     *
     * @return the $frmFilterCrud
     */
    public function getFrmFilterCrud()
    {
        if(!$this->frmFilterCrud instanceof \Laminas\InputFilter\InputFilterInterface){
          $this->frmFilterCrud = new InputFilter();
        }
        return $this->frmFilterCrud;
    }


    /**
     *
     * @param \Laminas\Form\Form $frmMainCrud
     */
    public function setFrmMainCrud($frmMainCrud,$isEntity=false)
    {
        if ($frmMainCrud instanceof \Laminas\Form\Form) {
            $this->frmMainCrud = $frmMainCrud;
        } elseif (is_array($frmMainCrud) || $frmMainCrud instanceof \Traversable) {
            $this->frmMainCrud = $this->createFormFromConfig($frmMainCrud);
        }elseif (is_object($frmMainCrud) && $isEntity) {
            $this->frmMainCrud = $this->createFormFromBuilder($frmMainCrud);
        } else {
            $this->frmMainCrud = $this->tryToCreateRawForm($frmMainCrud);
        }



    }

    /**
     *
     * @param \Laminas\Form\Form $frmFilterCrud
     */
    public function setFrmFilterCrud(\Laminas\InputFilter\InputFilterInterface  $inputFilter)
    {


        if ($inputFilter instanceof \Laminas\InputFilter\InputFilterInterface) {
            $this->frmFilterCrud =$inputFilter;
            $this->setFormFilterFromCrud = true;
        }
        else{
            throw new InvalidArgumentException('No Filter Exit', $code, $previous);
        }
    }




    /**
     *
     * @param \Zf2datatable\Entity\Entity $entity
     * @return Ambigous <\Laminas\Form\Form, \Laminas\Form\ElementInterface, ElementInterface, \Laminas\Form\FormInterface, \Laminas\Form\FieldsetInterface>
     */
    protected function createFormFromBuilder($entity)
    {
        if($this->getDataSource() instanceof QueryBuilder ||
           $this->getDataSource() instanceof  Doctrine2Collection){
            $entityManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_zfcDatagrid');

            $form = (new \DoctrineORMModule\Form\Annotation\AnnotationBuilder($entityManager))->createForm($entity);

            $form->setHydrator(new DoctrineHydrator($this->getDataSource()
                ->getEntityManager(), $this->getDataSource()
                ->getEntity()));
            foreach ($form->getElements() as $element) {
                    if (method_exists($element, 'getProxy')) {
                        $proxy = $element->getProxy();
                        if (method_exists($proxy, 'setObjectManager')) {
                            $proxy->setObjectManager($entityManager);
                        }
                    }
                }
        }
        else
                $form = (new \Laminas\Form\Annotation\AnnotationBuilder())->createForm($entity);


        if(!$form instanceof \Laminas\Form\Form){
            $form = $this->tryToCreateRawForm(get_class($entity));
        }

        $form->add(array(
            'name' => 'gridID',
            'type' => 'hidden',
            'attributes' => array(
                'type' => 'hidden',
                'id' => 'gridID',
                'value' => $this->getId()
            )
        ));



        $form->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Save',
                'id' => 'submitbutton'
            )
        ));
        $form->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'button',
                'value' => 'Cancel',
                'id' => 'cancelbutton'
            )
        ));
        return $form;
    }

    /**
     *
     * @param array $formConfig
     * @return \Laminas\Form\Factory
     */
    protected function createFormFromConfig(array $formConfig)
    {
        $Ofactory = new \Laminas\Form\Factory();
        $form = $Ofactory->create($formConfig);

        $form->setAttribute("class", "form-horizontal");
        $form->setAttribute("id", "crud");

        /*if($this->getFrmFilterCrud() instanceof \Laminas\InputFilter\InputFilterInterface){
            $form->setInputFilter($this->getFrmFilterCrud());
        }*/


        $form->add(array(
            'name' => 'gridID',
            'type' => 'hidden',
            'attributes' => array(
                'type' => 'hidden',
                'id' => 'gridID',
                'value' => $this->getId()
            )
        ));

        $form->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Save',
                'id' => 'submitbutton'
            )
        ));
        $form->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'button',
                'value' => 'Cancel',
                'id' => 'cancelbutton'
            )
        ));

        return $form;
    }


    /**
     *
     * @param type $elementName
     * @throws Exception
     */
    public function getFormElement($elementName)
    {
        if (! $this->frmMainCrud instanceof \Laminas\Form\Form) {
            throw new Exception('You must define a Form object', $code, $previous);
        }

        return $this->frmMainCrud->get($elementName);;
    }


    /**
     *
     * @param type $elementName
     * @param array $elementSpec
     * @param type $priority
     * @throws Exception
     */
    protected function _replaceFormElement($elementName, array $elementSpec, $priority = 100, $defaultValue = null)
    {

        if(! $this->isLoadCrudSettings())
            return false;

        if (! $this->frmMainCrud instanceof \Laminas\Form\Form) {
            throw new \Exception('You must define a Form object', $code, $previous);
        }
        if (! $priority)
            $priority = $this->frmMainCrud->get($elementName)->getOption('priority');

        $this->frmMainCrud->remove($elementName);
        $this->frmMainCrud->add($elementSpec, array(
            'priority' => $priority
        ));

        $element = $this->frmMainCrud->get($elementName);
        $element->setOption('priority', $priority);

        if($elementSpec['type']== \Zf2datatable\Form\Element\CKEditor::class){
            $basePath = $this->getPluginViewHelperManager()->get('BasePath')->__invoke();
            $this->getPluginViewHelperManager()->get('HeadScript')->appendFile($basePath.'/ckeditor/samples/js/sample.js');
            $this->getPluginViewHelperManager()->get('HeadScript')->appendFile($basePath.'/ckeditor/ckeditor.js');

            $this->getPluginViewHelperManager()->get('HeadLink')->appendStylesheet($basePath.'/ckeditor/samples/css/samples.css');
            $this->getPluginViewHelperManager()->get('HeadLink')->appendStylesheet($basePath.'/ckeditor/samples/toolbarconfigurator/lib/codemirror/neo.css');
        }

        if($element instanceof \DoctrineModule\Form\Element\ObjectSelect){
            if (method_exists($element, 'getProxy')) {
                $entityManager =$this->getDataSource()->getEntityManager();
                $proxy = $element->getProxy();
                if (method_exists($proxy, 'setObjectManager')) {
                    $proxy->setObjectManager($entityManager);
                }
            }
        }

        if ($defaultValue !== null) {
            $element->setValue($defaultValue);
        }

        $filter = $this->generateDefaultImputFiler($element);
        if ($filter instanceof \Laminas\InputFilter\Input) {
            $this->getFrmFilterCrud()->add($filter);
        }
    }

        /**
     *
     * @param type $elementName
     * @throws Exception
     */
    protected function _removeFormElement($elementName)
    {
        if(! $this->isLoadCrudSettings())
            return false;

        if (! $this->frmMainCrud instanceof \Laminas\Form\Form) {
            throw new Exception('You must define a Form object', $code, $previous);
        }

        $this->frmMainCrud->remove($elementName);
        return true;
    }

    /**
     *
     * @param type $elementName
     * @throws Exception$form = new \Laminas\Form\Form ( $frmName );
     */
    protected function _addFormElement($elementName, array $elementSpec, $priority = 100, $defaultValue=null)
    {
        if(! $this->isLoadCrudSettings())
            return false;

        if (! $this->frmMainCrud instanceof \Laminas\Form\Form) {
            throw new Exception('You must define a Form object');
        }

        if (! $priority)
            $priority = $this->frmMainCrud->get($elementName)->getOption('priority');

        $this->frmMainCrud->add($elementSpec, array(
            'priority' => $priority
        ));
        $element = $this->frmMainCrud->get($elementName);
        $element->setOption('priority', $priority);

        if($elementSpec['type']==\Zf2datatable\Form\Element\CKEditor::class){
            $basePath = $this->getPluginViewHelperManager()->get('BasePath')->__invoke();
            $this->getPluginViewHelperManager()->get('HeadScript')->appendFile($basePath.'/ckeditor/samples/js/sample.js');
            $this->getPluginViewHelperManager()->get('HeadScript')->appendFile($basePath.'/ckeditor/ckeditor.js');
            $this->getPluginViewHelperManager()->get('HeadLink')->appendStylesheet($basePath.'/ckeditor/samples/css/samples.css');
            $this->getPluginViewHelperManager()->get('HeadLink')->appendStylesheet($basePath.'/ckeditor/samples/toolbarconfigurator/lib/codemirror/neo.css');
        }


        if($defaultValue){
            $element->setValue($defaultValue);
        }



        if($element instanceof \DoctrineModule\Form\Element\ObjectSelect){
            if (method_exists($element, 'getProxy')) {
                $entityManager =$this->getDataSource()->getEntityManager();
                $proxy = $element->getProxy();
                if (method_exists($proxy, 'setObjectManager')) {
                    $proxy->setObjectManager($entityManager);
                }
            }
         }
    }


    /**
     *
     * @param type $elementName
     * @param array $elementSpec
     * @param type $priority
     * @throws Exception
     */
    public function replaceFormElement($elementName, array $elementSpec, $priority = 100, $defaultValue = null)
    {
        $this->proxyElementReplace[$elementName]=[
            'elementSpec'     => $elementSpec,
            'priority'        => $priority,
            'defaultValue'   => $defaultValue
        ];
    }



    /**
     *
     * @param type $elementName
     * @param array $elementSpec
     * @param type $priority
     * @throws Exception
     */
    public function addFormElement($elementName, array $elementSpec, $priority = 100, $defaultValue = null)
    {
        $this->proxyElementAdd[$elementName]=[
            'elementSpec'     => $elementSpec,
            'priority'        => $priority,
            'defaultValue'   => $defaultValue
        ];
    }



    /**
     * @string $elementName
     *
     */
    public function removeFormElement($elementName)
    {
        $this->proxyRemoveElement[$elementName] = $elementName;
    }


    /**
     * @throws \Exception
     * proxy element
     */
    protected function replaceFormElementFromProxy(){
        if(is_array($this->proxyElementReplace)) {
            foreach ($this->proxyElementReplace as $key => $elm) {
                $this->_replaceFormElement($key, $elm['elementSpec'], $elm['priority'], $elm['defaultValue']);
            }

        }

    }

    protected function addFormElementFromProxy(){
        if(is_array($this->proxyElementAdd)) {
            foreach ($this->proxyElementAdd as $key => $elm) {
                $this->_addFormElement($key, $elm['elementSpec'], $elm['priority'], $elm['defaultValue']);
            }
        }
    }

    protected function removeFormElementFromProxy()
    {
        if(is_array($this->proxyRemoveElement)) {
            foreach ($this->proxyRemoveElement as $key => $elm) {
                $this->_removeFormElement($key);
            }
        }
    }



    /**
     *
     * @param string $elementName
     * @param arry $elementSpec
     */
    public function addFormFilterElements(array $elementSpec){
        $inputFilter = $this->getFrmFilterCrud();
        $factory = new InputFactory ();
        foreach($elementSpec as $elemntFilter) {
            $inputFilter->add($factory->createInput($elemntFilter));
        }
        $this->getFrmMainCrud()->setInputFilter($inputFilter);
        return $this;
    }

    /** todo **/
    public function addFormFilterElement(array $elementSpec){
        return $this;
    }


    protected function tryToCreateRawForm($frmName = 'RawForm')
    {
        // $form = new \Laminas\Form\Form ( $frmName );
        $defaultElementPriority = self::$priorityStart;
        $form = $this->getServiceLocator()->get('zf2datatable.form');
        $form->setName($frmName);

        if ($this->getDataSource() instanceof \Zf2datatable\DataSource\LaminasSelect || $this->getDataSource() instanceof \Zf2datatable\DataSource\LaminasTableGateway) {
            $tableInfo = $this->getDataSource()->getMetaDataInfo();
            $foreignKey = $this->getDataSource()->getForeignKey();
        } elseif ($this->getDataSource() instanceof \Zf2datatable\DataSource\Doctrine2) {
            $tableInfo = $this->getDataSource()->getMetaDataInfo();
            $foreignKey = $this->getDataSource()->getForeignKey();
            $form->setHydrator(new DoctrineHydrator($this->getDataSource()
                ->getEntityManager(), $this->getDataSource()
                ->getEntity()));
        } else {
            throw new \Exception('DataSource is not database adapter');
        }

        if(isset($tableInfo[0]['columns'])){
            foreach ($tableInfo[0]['columns'] as $col) {
            $defaultElementPriority = (((int) $this->getFrmElementDefaultPriority($col['name']) > 0)) ? (int) $this->getFrmElementDefaultPriority($col['name'])  : self::$priorityStart;
            $this->getFrmElementDefaultPriority($col['name']);
            if (array_key_exists($col['name'], $this->getFrmElementSource())) {
                $elm = $this->generateFormElementFromSource($col['name'], $col['datatype'], $this->getFrmElementSource()[$col['name']], $defaultElementPriority);
            } else {
                $elm = $this->generateFormElement($col['name'], $col['datatype'], null, $defaultElementPriority);
            }

            $priority = $elm->getOption('priority');

            $filter = $this->generateDefaultImputFiler($elm);
            if ($filter instanceof \Laminas\InputFilter\Input) {
                $this->getFrmFilterCrud()->add($filter);
            }

            $form->add($elm, array(
                'priority' => $defaultElementPriority
            ));
            // $form->get($col['name'])->setOption('priority',70);
            self::$priorityStart -= self::stepPriority;

            }
        }


        $targetEntity = '';
        $sourceEntity = '';
        $property     = '';

        if(isset($tableInfo[1]['entity_associations'])){
            foreach ($tableInfo[1]['entity_associations'] as $nameEntity => $Entity) {
                foreach ($Entity as $name => $attributes){
                    switch ($name){
                        case 'fieldName':
                            $property = $attributes;
                            break;
                        case 'targetEntity':
                            $targetEntity = $attributes;
                            break;
                        case 'sourceEntity':
                            $sourceEntity = $attributes;
                            break;
                    }
                }


              //echo $property.'-'.$targetEntity.'-'.$sourceEntity.'<br />';
              $elm = $this->generateDoctrineFormElement($nameEntity, $targetEntity, $property) ;

              if ($elm instanceof \Laminas\Form\Element){
                     $priority = $elm->getOption('priority');
                      self::$priorityStart = $priority - self::stepPriority;
                      $defaultElementPriority = ((int) $this->getFrmElementDefaultPriority($elm->getName())) ? (int) $this->getFrmElementDefaultPriority($elm->getName())  : self::$priorityStart;


                      $filter = $this->generateDefaultImputFiler($elm);
                      if ($filter instanceof \Laminas\InputFilter\Input) {
                          $this->getFrmFilterCrud()->add($filter);
                      }

                      $form->add($elm, array(
                          'priority' => $defaultElementPriority
                      ));
                }
            }
        }


        /*if($this->getFrmFilterCrud() instanceof \Laminas\InputFilter\InputFilterInterface){
            $form->setInputFilter($this->getFrmFilterCrud());
        }*/

        $form->add(array(
            'name' => 'gridID',
            'type' => 'hidden',
            'attributes' => array(
                'type' => 'hidden',
                'id' => 'gridID',
                'value' => $this->getId()
            )
        ));

        $form->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Save',
                'id' => 'submitbutton'
            )
        ));
        $form->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'button',
                'value' => 'Cancel',
                'id' => 'cancelbutton'
            )
        ));

        $form->setAttribute('class', 'form-horizontal');

        return $form;
    }

    /**
     * get default default input filter *
     */
    protected function generateDefaultImputFiler(\Laminas\Form\Element $element)
    {
        $inputFactory = $this->getFrmFilterCrud()->getFactory();

        if (method_exists($element, 'getInputSpecification')) {
            //$filter = new Input($element->getName());
            //$filter->setRequired(false);
            $spec  = $element->getInputSpecification();
            $input = $inputFactory->createInput($spec);
            $input->setRequired(true);
            return $input;
        }
    }

    /**
     *
     * @param string $name
     * @param strin $type
     * @param array $reference
     */
    protected function generateFormElementFromSource($name, $type, $reference = array(), $priority = 100)
    {
        if ($reference['source'] == 'db') {
            $datasource = $this->getDataSource();
            $value_source = $datasource->executeExternalQuery($source, null);
        } elseif ($reference['source'] == 'array') {
            $value_source = $reference['array_value'];
        } else {
            // todo
        }
        return $this->generateFormElementByType($name, $reference['type'], $value_source, $priority);
    }


    protected function generateDoctrineFormElement($name, $targetEntity, $property, $priority = 100)
    {

        $options = $this->getOptions();
        $option_elements = $options['form']['doctrine_elements'];
        $entityManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_zfcDatagrid');
        if(array_key_exists($name, $option_elements)){
            $elm = new \DoctrineModule\Form\Element\ObjectSelect($name);
            $elm->setLabel(strtoupper($name));
            $elm->setOptions(
                array(
                    'object_manager' => $entityManager,
                    'target_class'  =>$targetEntity,
                    'property'      =>  $option_elements[$name]['fieldName']
                    )
                );


            if($option_elements[$name]['required'])
                $elm->setAttributes(array('required'=>$option_elements[$name]['required']));

            if($option_elements[$name]['multiple'])
                $elm->setAttribute('multiple',true);

            $elm->setOption('priority', $priority);

            // NON NECESSARIO
            if (method_exists($elm, 'getProxy')) {
                $proxy = $elm->getProxy();
                if (method_exists($proxy, 'setObjectManager')) {
                    $proxy->setObjectManager($entityManager);
                }
            }


            return $elm;

        }

        return false;

    }

    /**
     *
     * @param string $name
     * @param string $type
     * @param array $values
     */
    protected function generateFormElement($name, $type, $values = array(), $priority = 100)
    {
        $options = $this->getOptions();
        $option_elements = $options['form']['elements'];
        switch (strtoupper($type)) {
            case 'TEXT':
            case 'CHAR':
            case 'VARCHAR':
                $elm = new Element($name);
                $elm->setLabel(strtoupper($name));
                $elm->setAttribute('id', $name . '_ID');
                $elm->setAttributes(array(
                    'type' => 'text'
                ));
                break;
            case 'ENUM':
                $elm = new Element\Select($name);
                $elm->setLabel(strtoupper($name));
                $elm->setAttribute('id', $name . '_ID');
                if ($values instanceof \Traversable)
                    $elm->setValueOptions($values);
                break;
            case 'INT':
            case 'INTEGER':
            case 'NUMERIC(10, 0)':
            case 'DECIMAL':
            case 'NUMERIC':
            case 'DOUBLE':
                $elm = new Element($name);
                $elm->setLabel(strtoupper($name));
                $elm->setAttribute('id', $name . '_ID');
                $elm->setAttributes(array(
                    'type' => 'number'
                ));
                break;
            case 'DATE':
                if (isset($option_elements['DateTimePicker']) && $option_elements['DateTimePicker']['status'] == 'enabled') {
                    $elm = new \Zf2datatable\Form\Element\DateCalendar($name);
                    $elm->setAttribute('id', $name . '_ID');
                    $elm->setAttribute('class', 'form-control');
                    $elm->setLabel($name);
                    $elm->setAttribute('jsOption', $option_elements['DateTimePicker']['options']['date_js_properties']);
                    \Zf2datatable\Form\Element\DateCalendar::setDateFormatIn($option_elements['DateTimePicker']['options']['date_format_in']);
                    \Zf2datatable\Form\Element\DateCalendar::setDateFormatOut($option_elements['DateTimePicker']['options']['date_format_out']);
                    \Zf2datatable\Form\Element\DateCalendar::setDateFormatMaskIn($option_elements['DateTimePicker']['options']['date_format_mask_in']);
                    \Zf2datatable\Form\Element\DateCalendar::setDateFormatMaskOut($option_elements['DateTimePicker']['options']['date_format_mask_out']);

                } else {
                    $elm = new Element\Date($name);
                    $elm->setLabel(strtoupper($name));
                    $elm->setAttributes(array(
                        'type' => 'date'
                    ));
                    $elm->setFormat('Y-m-d');
                }
                break;
            case 'DATETIME':
                if (isset($option_elements['DateTimePicker']) && $option_elements['DateTimePicker']['status'] == 'enabled') {
                    $elm = new \Zf2datatable\Form\Element\DateTimeCalendar($name);
                    $elm->setAttribute('id', $name . '_ID');
                    $elm->setAttribute('class', 'form-control');
                    $elm->setLabel($name);
                    $elm->setAttribute('jsOption', $option_elements['DateTimePicker']['options']['datetime_js_properties']);
                    \Zf2datatable\Form\Element\DateTimeCalendar::setDateFormatIn($option_elements['DateTimePicker']['options']['datetime_format_in']);
                    \Zf2datatable\Form\Element\DateTimeCalendar::setDateFormatOut($option_elements['DateTimePicker']['options']['datetime_format_out']);
                    \Zf2datatable\Form\Element\DateTimeCalendar::setDateFormatMaskIn($option_elements['DateTimePicker']['options']['datetime_format_mask_in']);
                    \Zf2datatable\Form\Element\DateTimeCalendar::setDateFormatMaskOut($option_elements['DateTimePicker']['options']['datetime_format_mask_out']);

                } else {
                    $elm = new Element\DateTimeSelect($name);
                    $elm->setLabel(strtoupper($name));
                }
                break;
            case 'FILE':
                $elm = new Element\File();
                $elm->setLabel(strtoupper($name));
                $elm->setAttribute('id', $name . '_ID');
                break;
            default:
                $elm = new Element($name);
                $elm->setLabel(strtoupper($name));
                $elm->setAttribute('id', $name . '_ID');
                $elm->setAttributes(array(
                    'type' => 'text'
                ));
        }
        $elm->setOption('priority', $priority);
        $elm->setAttribute("class","form-control");
        return $elm;
    }

    protected function generateFormElementByType($name, $type, $values = array(), $priority)
    {
        switch ($type) {
            case 'FILE':
                $elm = new Element\File();
                $elm->setLabel(strtoupper($name));
                break;
            case 'SELECT':
                $elm = new Element\Select($name);
                $elm->setLabel(strtoupper($name));
                $elm->setAttribute('id', $name . '_ID');
                if ($values instanceof \Traversable || is_array($values)) {
                    $elm->setValueOptions($values);
                }
                break;
            case 'DATE':
                if (isset($option_elements['DateTimePicker']) && $option_elements['DateTimePicker']['status'] == 'enabled') {
                    $elm = new \Zf2datatable\Form\Element\DateCalendar($name);
                    $elm->setAttribute('id', $name . '_ID');
                    $elm->setAttribute('class', 'form-control');
                    $elm->setLabel($name);
                    $elm->setAttribute('jsOption', $option_elements['DateTimePicker']['options']['date_js_properties']);
                   \Zf2datatable\Form\Element\DateCalendar::setDateFormatIn($option_elements['DateTimePicker']['options']['date_format_in']);
                   \Zf2datatable\Form\Element\DateCalendar::setDateFormatOut($option_elements['DateTimePicker']['options']['date_format_out']);
                } else {
                    $elm = new Element\Date($name);
                    $elm->setLabel(strtoupper($name));
                    $elm->setAttributes(array(
                        'type' => 'date'
                    ));
                    $elm->setFormat('Y-m-d');
                }
                break;
            case 'DATETIME':
                if (isset($option_elements['DateTimePicker']) && $option_elements['DateTimePicker']['status'] == 'enabled') {
                    $elm = new \Zf2datatable\Form\Element\DateTimeCalendar($name);
                    $elm->setAttribute('id', $name . '_ID');
                    $elm->setAttribute('class', 'form-control');
                    $elm->setLabel($name);
                    $elm->setAttribute('jsOption', $option_elements['DateTimePicker']['options']['datetime_js_properties']);
                    \Zf2datatable\Form\Element\DateTimeCalendar::setDateFormatIn($option_elements['DateTimePicker']['options']['datetime_format_in']);
                    \Zf2datatable\Form\Element\DateTimeCalendar::setDateFormatOut($option_elements['DateTimePicker']['options']['datetime_format_out']);
                } else {
                    $elm = new Element\DateTimeSelect($name);
                    $elm->setLabel(strtoupper($name));
                }
                break;
            default:
                $elm = new Element($name);
                $elm->setLabel(strtoupper($name));
                $elm->setAttributes(array(
                    'type' => 'text'
                ));
                break;
        }
        $elm->setOption('priority', $priority);
        return $elm;
    }

    /**
     *
     * @return the $eventManager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     *
     * @param field_type $eventManager
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
        return $this;
    }

    /**
     * Init method is called automatically with the service creation
     */
    public function init()
    {
        if ($this->getCache() === null) {
            $options = $this->getOptions();
            $this->setCache(Cache\StorageFactory::factory($options['cache']));
            //$this->getCache()->clearExpired();
            if(!$this->getServiceLocator()->has('service_cache')){
                $this->getServiceLocator()->setService('service_cache',$this->getCache());
                $this->getServiceLocator()->setService('metadata_service_cache',Cache\StorageFactory::factory($options['metadata_cache']));
            }

        }
        $this->isInit = true;

        $acl = $this->getAcl();
        if($acl instanceof \Laminas\Permissions\Acl\Acl){
            if($acl->hasResource('Grid_'.$this->getId())){

                if(!$acl->allow($this->getDefaultRole(),'Grid_'.$this->getId(),['insert'])){
                    $this->setisAllowAdd(false);
                }

                if(!$acl->allow($this->getDefaultRole(),'Grid_'.$this->getId(),['edit'])){
                    $this->setisAllowEdit(false);
                }


                if(!$acl->allow($this->getDefaultRole(),'Grid_'.$this->getId(),['delete'])){
                    $this->setisAllowDelete(false);
                }

                if(!$acl->allow($this->getDefaultRole(),'Grid_'.$this->getId(),['view'])){
                    $this->setisAllowView(false);
                }
            }
        }
    }


    protected function checkUrlPermission($op){
        $acl = $this->getAcl();
        if(!$acl instanceof \Laminas\Permissions\Acl\Acl){
            return true;
        }
        if($acl->hasResource('Grid_'.$this->getId())){
        switch ($op){
            case 'i':
                if(!$acl->allow($this->getDefaultRole(),'Grid_'.$this->getId(),['insert'])){
                    return false;
                }
                break;
            case 'u':
                if(!$acl->allow($this->getDefaultRole(),'Grid_'.$this->getId(),['edit'])){
                    return false;
                }
                 break;
            case 'd':
                if(!$acl->allow($this->getDefaultRole(),'Grid_'.$this->getId(),['delete'])){
                    return false;
                }
                break;
            case 'v':
                if(!$acl->allow($this->getDefaultRole(),'Grid_'.$this->getId(),['view'])){
                    return false;
                }
                break;
            case 'f':
               break;
        }

       }

     return true;
    }

    /**
     *
     * @return boolean
     */
    public function isInit()
    {
        return (bool) $this->isInit;
    }

    /**
     * Set the options from config
     *
     * @param array $config
     */
    public function setOptions(array $config, $overwrite = false)
    {
        if($overwrite)
            $this->options = $config;
        else
            $this->options = \Laminas\Stdlib\ArrayUtils::merge($this->options, $config, true);
    }

    /**
     * Get the config options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set the grid id
     *
     * @param string $id
     */
    public function setId($id = null)
    {
        if ($id !== null) {
            $id = preg_replace("/[^a-z0-9_\\\d]/i", '_', $id);
            $this->id = (string) $id;
        }
    }

    /**
     * Get the grid id
     *
     * @return string
     */
    public function getId()
    {
        if ($this->id === null) {
            $this->id = 'defaultGrid';
        }

        return $this->id;
    }

    /**
     * Set the session
     *
     * @param \Laminas\Session\Container $session
     */
    public function setSession(SessionContainer $session)
    {
        $this->session = $session;

        return $this;
    }

    /**
     * Get session container
     *
     * Instantiate session container if none currently exists
     *
     * @return SessionContainer
     */
    public function getSession()
    {
        if (null === $this->session) {
            // Using fully qualified name, to ensure polyfill class alias is used
            $this->session = new SessionContainer($this->getId());
        }

        return $this->session;
    }

    public function setCache(Cache\Storage\StorageInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     *
     * @return Cache\Storage\StorageInterface
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Set the cache id
     *
     * @param string $id
     */
    public function setCacheId($id)
    {
        $this->cacheId = (string) $id;
    }

    /**
     * Get the cache id
     *
     * @return string
     */
    public function getCacheId()
    {
        if ($this->cacheId === null) {
            $this->cacheId = $this->getSession()
                ->getManager()
                ->getId() . '_' . $this->getId();
        }

        return $this->cacheId;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function setMvcEvent(MvcEvent $mvcEvent)
    {
        $this->mvcEvent = $mvcEvent;
        $this->request = $mvcEvent->getRequest();
    }

    /**
     *
     * @return MvcEvent
     */
    public function getMvcEvent()
    {
        return $this->mvcEvent;
    }

    /**
     *
     * @return HttpRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set the translator
     *
     * @param Translator $translator
     * @throws \InvalidArgumentException
     */
    public function setTranslator($translator = null)
    {
        if (! $translator instanceof Translator && ! $translator instanceof \Laminas\I18n\Translator\TranslatorInterface) {
            throw new \InvalidArgumentException('Translator must be an instanceof "Laminas\I18n\Translator\Translator" or "Laminas\I18n\Translator\TranslatorInterface"');
        }

        $this->translator = $translator;
    }

    /**
     *
     * @return Translator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     *
     * @return boolean
     */
    public function hasTranslator()
    {
        if ($this->translator !== null) {
            return true;
        }

        return false;
    }

    /**
     * Set the data source
     *
     * @param mixed $data
     * @throws \Exception
     */
    public function setDataSource($data)
    {

        if ($data instanceof DataSource\DataSourceInterface) {
            $this->dataSource = $data;
        } elseif (is_array($data)) {
            $this->dataSource = new DataSource\PhpArray($data);
        } elseif ($data instanceof QueryBuilder) {
            $this->dataSource = new DataSource\Doctrine2($data);
            $this->dataSource->setEntityManager($this->getServiceLocator()
                ->get('doctrine.entitymanager.orm_zfcDatagrid'));
        } elseif ($data instanceof LaminasSelect) {
            $args = func_get_args();
            if (count($args) === 1 || (! $args[1] instanceof \Laminas\Db\Adapter\Adapter && ! $args[1] instanceof \Laminas\Db\Sql\Sql)) {
                throw new \InvalidArgumentException('For "Laminas\Db\Sql\Select" also a "Laminas\Db\Adapter\Sql" or "Laminas\Db\Sql\Sql" is needed.');
            }
            // $this->dataSource = new DataSource\LaminasSelect ( $data );

            $this->dataSource = $this->getServiceLocator()->get('Zf2datatable.datasource.LaminasSelect');
            $this->dataSource->init($data);
            $this->dataSource->setAdapter($args[1]);
        } elseif ($data instanceof TableGateway) {
            $args = func_get_args();
            if (count($args) === 1 || (! $args[1] instanceof \Laminas\Db\Adapter\Adapter && ! $args[1] instanceof \Laminas\Db\TableGateway\TableGateway)) {
                throw new \InvalidArgumentException('For "Laminas\Db\TableGateway\TableGateway" also a "Laminas\Db\Adapter\Sql" or "Laminas\Db\Sql\Sql" is needed.');
            }
            $this->dataSource = $this->getServiceLocator()->get('Zf2datatable.datasource.LaminasTableSelect');
            $this->dataSource->init($data);
            $this->dataSource->setAdapter($args[1]);
        } elseif ($data instanceof Collection) {
            $args = func_get_args();
            if (count($args) === 1 || ! $args[1] instanceof \Doctrine\ORM\EntityManager) {
                throw new \InvalidArgumentException('If providing a Collection, also the Doctrine\ORM\EntityManager is needed as a second parameter');
            }
            $this->dataSource = new DataSource\Doctrine2Collection($data);
            $this->dataSource->setEntityManager($args[1]);

        } else {
            throw new \InvalidArgumentException('$data must implement the interface Zf2datatable\DataSource\DataSourceInterface');
        }


        if(method_exists($this->dataSource, "setEventManager")){
            $appEventManager = $this->getServiceLocator()->get('EventManager');
            $shareEventManager = $appEventManager->getSharedManager();
            $eventManager = new \Laminas\EventManager\EventManager($shareEventManager ,[\Zf2datatable\DataSource\AbstractDataSource::class]);
            $this->dataSource->setEventManager($eventManager);
        }


        return $this->dataSource;
    }

    /**
     *
     * @return \Zf2datatable\DataSource\DataSourceInterface
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }

    /**
     * Datasource defined?
     *
     * @return boolean
     */
    public function hasDataSource()
    {
        if ($this->dataSource !== null) {
            return true;
        }

        return false;
    }

    /**
     * Set default items per page (-1 for unlimited)
     *
     * @param integer $count
     */
    public function setDefaultItemsPerPage($count = 25)
    {
        $this->defaulItemsPerPage = (int) $count;
    }

    /**
     *
     * @return integer
     */
    public function getDefaultItemsPerPage()
    {
        return (int) $this->defaulItemsPerPage;
    }

    /**
     * Set the title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = (string) $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Add a external parameter
     *
     * @param string $name
     * @param mixed $value
     */
    public function addParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * These parameters are handled to the view + over all grid actions
     *
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Has parameters?
     *
     * @return boolean
     */
    public function hasParameters()
    {
        if (count($this->getParameters()) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Set the base url
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the export renderers (overwrite the config)
     *
     * @param array $renderers
     */
    public function setExportRenderers(array $renderers = array())
    {
        $this->exportRenderers = $renderers;
    }

    /**
     * Get the export renderers
     *
     * @return array
     */
    public function getExportRenderers()
    {
        if ($this->exportRenderers === null) {
            $options = $this->getOptions();
            $this->exportRenderers = $options['settings']['export']['formats'];
        }

        return $this->exportRenderers;
    }

    /**
     * Create a column from array instanceof
     *
     * @param mixed $col
     *
     * @return Column\AbstractColumn
     */
    private function createColumn($config)
    {
        if ($config instanceof Column\AbstractColumn) {
            return $config;
        }

        if (! is_array($config) && ! $config instanceof Column\AbstractColumn) {
            throw new \InvalidArgumentException('createColumn()
            orts only a config array or instanceof Column\AbstractColumn as a parameter');
        }

        $colType = isset($config['colType']) ? $config['colType'] : 'Select';
        if (class_exists($colType, true)) {
            $class = $colType;
        } elseif (class_exists('Zf2datatable\\Column\\' . $colType, true)) {
            $class = 'Zf2datatable\\Column\\' . $colType;
        } else {
            throw new \InvalidArgumentException('Column type: "' . $colType . '" not found!');
        }

        if ($class == \Zf2datatable\Column\Select::class) {
            if (! isset($config['select']['column'])) {
                throw new \InvalidArgumentException('For "Zf2datatable\Column\Select" the option select[column] must be defined!');
            }
            $table = isset($config['select']['table']) ? $config['select']['table'] : null;

            $instance = new $class($config['select']['column'], $table);
        } else {
            $instance = new $class();
        }

        foreach ($config as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($instance, $method)) {

                if (in_array($key, $this->specialMethods)) {
                    if (! is_array($value)) {
                        $value = array(
                            $value
                        );
                    }
                    call_user_func_array(array(
                        $instance,
                        $method
                    ), $value);
                } else {
                    call_user_func(array(
                        $instance,
                        $method
                    ), $value);
                }
            }
        }

        return $instance;
    }

    /**
     * Set multiple columns by array (willoverwrite all existing)
     *
     * @param array $columns
     */
    public function setColumns(array $columns)
    {
        $useColumns = array();

        foreach ($columns as $col) {
            $col = $this->createColumn($col);
            $useColumns[$col->getUniqueId()] = $col;
        }

        $this->columns = $useColumns;
    }

    /**
     * Add a column by array config or instanceof Column\AbstractColumn
     *
     * @param array|Column\AbstractColumn $col
     */
    public function addColumn($col)
    {
        $col = $this->createColumn($col);
        $this->columns[$col->getUniqueId()] = $col;
    }

    /**
     * Add a column by array config or instanceof Column\AbstractColumn
     *
     * @param array|Column\AbstractColumn $col
     */
    public function appendColumn($col)
    {
        $col = $this->createColumn($col);
        $this->columns[$col->getUniqueId()] = $col;
    }

    /**
     * Add a column by array config or instanceof Column\AbstractColumn
     *
     * @param array|Column\AbstractColumn $col
     */
    public function prependColumn($col)
    {
        $col = $this->createColumn($col);
        $this->columns = array(
            $col->getUniqueId() => $col
        ) + $this->columns;
    }

    /**
     *
     * @return \Zf2datatable\Column\AbstractColumn[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     *
     * @return \Zf2datatable\Column\AbstractColumn[]
     */
    public function getColumn($key)
    {
        return $this->columns[$key];
    }



    public function getIdentyColumns()
    {
        // var_dump( $this->getColumns ());
        $this->columnIdentity = null;
        foreach ($this->getColumns() as $column) {
            if ($column->isIdentity()) {
                $this->columnIdentity[] = $column->getSelectPart2();
            }
        }
        return $this->columnIdentity;
    }

    /**
     *
     * @param string $id
     * @return Column\AbstractColumn null
     */
    public function getColumnByUniqueId($id)
    {
        if (isset($this->columns[$id])) {
            return $this->columns[$id];
        }

        return null;
    }

    /**
     *
     * @param unknown $style
     */
    public function addRowStyle(Style\AbstractStyle $style)
    {
        $this->rowStyles[] = $style;
    }

    /**
     *
     * @return Style\AbstractStyle[]
     */
    public function getRowStyles()
    {
        return $this->rowStyles;
    }

    /**
     *
     * @return boolean
     */
    public function hasRowStyles()
    {
        if (count($this->rowStyles) > 0) {
            return true;
        }

        return false;
    }

    /**
     * If disabled, the toolbar filter will not be shown to the user
     *
     * @param boolean $mode
     */
    public function setUserFilterDisabled($mode = true)
    {
        $this->isUserFilterEnabled = (bool) ! $mode;
    }

    /**
     *
     * @return boolean
     */
    public function isUserFilterEnabled()
    {
        return (bool) $this->isUserFilterEnabled;
    }

    /**
     * Set the row click action - identity will be automatically appended!
     *
     * @param Column\Action\AbstractAction $action
     */
    public function setRowClickAction(Column\Action\AbstractAction $action)
    {
        $this->rowClickAction = $action;
    }

    /**
     *
     * @return null Column\Action\AbstractAction
     */
    public function getRowClickAction()
    {
        return $this->rowClickAction;
    }

    /**
     *
     * @return boolean
     */
    public function hasRowClickAction()
    {
        if (is_object($this->rowClickAction)) {
            return true;
        }

        return false;
    }

    /**
     * Add a mass action
     *
     * @param Action\Mass $action
     */
    public function addMassAction(Action\Mass $action)
    {
        $this->massActions[] = $action;
    }

    /**
     *
     * @return Action\Mass[]
     */
    public function getMassActions()
    {
        return $this->massActions;
    }

    /**
     *
     * @return boolean
     */
    public function hasMassAction()
    {
        if (count($this->massActions) > 0) {
            return true;
        }

        return false;
    }

    /**
     *
     * @deprecated use setRendererName()
     */
    public function setRenderer($name = null)
    {
        trigger_error('setRenderer() is deprecated, please use setRendererName() instead', E_USER_DEPRECATED);

        $this->forceRenderer = $name;
    }

    /**
     * Overwrite the render
     * F.x.
     * if you want to directly render a PDF
     *
     * @param string $name
     */
    public function setRendererName($name = null)
    {
        $this->forceRenderer = $name;
    }

    /**
     * Get the current renderer name
     *
     * @return string
     */
    public function getRendererName()
    {
        $options = $this->getOptions();
        $parameterName = $options['generalParameterNames']['rendererType'];

        if ($this->forceRenderer !== null) {
            // A special renderer was given -> use is
            $rendererName = $this->forceRenderer;
        } else {
            // DEFAULT
            if ($this->getRequest() instanceof ConsoleRequest) {
                $rendererName = $options['settings']['default']['renderer']['console'];
            } else {
                $rendererName = $options['settings']['default']['renderer']['http'];
            }
        }

        // From request
        if ($this->getRequest() instanceof HttpRequest && $this->getRequest()->getQuery($parameterName) != '') {
            $rendererName = $this->getRequest()->getQuery($parameterName);
        }

        return $rendererName;
    }

    /**
     * Return the current renderer
     *
     * @return \Zf2datatable\Renderer\AbstractRenderer
     */
    public function getRenderer()
    {
        if ($this->renderer === null) {

            $options = $this->getOptions();

            $rendererName = 'Zf2datatable.renderer.' . $this->getRendererName();

            if ($this->getServiceLocator()->has($rendererName) === true) {
                /* @var $renderer \Zf2datatable\Renderer\AbstractRenderer */
                $renderer = $this->getServiceLocator()->get($rendererName);
                if (! $renderer instanceof Renderer\AbstractRenderer) {
                    throw new \Exception('Renderer service must implement "Zf2datatable\Renderer\AbstractRenderer"');
                }
                $renderer->setOptions($this->getOptions());
                $renderer->setMvcEvent($this->getMvcEvent());
                $renderer->setDataGrid($this);
                if ($this->getToolbarTemplate() !== null) {
                    $renderer->setToolbarTemplate($this->getToolbarTemplate());
                }
                $renderer->setViewModel($this->getViewModel());
                $renderer->setTranslator($this->getTranslator());
                $renderer->setTitle($this->getTitle());
                $renderer->setColumns($this->getColumns());
                $renderer->setRowStyles($this->getRowStyles());
                $renderer->setCacheId($this->getCacheId());
                $renderer->setCache($this->getCache());
                $renderer->setGridID($this->getId());
                // $renderer->setCacheData ( $this->getCache ()->getItem ( $this->getCacheId () ) );

                $this->renderer = $renderer;
            } else {
                throw new \Exception('Renderer service was not found, please register it: "' . $rendererName . '"');
            }
        }

        return $this->renderer;
    }

    /**
     * Return the current renderer
     *
     * @return \Zf2datatable\Renderer\AbstractRenderer
     */
    public function getFormRenderer()
    {
        $this->renderer = null;
        if ($this->renderer === null) {

            $options = $this->getOptions();

            $rendererName = 'Zf2datatable.renderer.' . $this->getRendererName() . '.form';

            if ($this->getServiceLocator()->has($rendererName) === true) {
                /* @var $renderer \Zf2datatable\Renderer\AbstractRenderer */
                $renderer = $this->getServiceLocator()->get($rendererName);
                if (! $renderer instanceof Renderer\AbstractRenderer) {
                    throw new \Exception('Renderer service must implement "Zf2datatable\Renderer\AbstractRenderer"');
                }
                $renderer->setOptions($this->getOptions());
                $renderer->setMvcEvent($this->getMvcEvent());
                if ($this->getToolbarTemplate() !== null) {
                    $renderer->setToolbarTemplate($this->getToolbarTemplate());
                }
                $renderer->setViewModel($this->getViewModel());
                $renderer->setTranslator($this->getTranslator());
                $renderer->setTitle($this->getTitle());
                $renderer->setColumns($this->getColumns());
                $renderer->setRowStyles($this->getRowStyles());
                $renderer->setCacheId($this->getCacheId());
                $renderer->setCache($this->getCache());

                $this->renderer = $renderer;
            } else {
                throw new \Exception('Renderer service was not found, please register it: "' . $rendererName . '"');
            }
        }

        return $this->renderer;
    }

    /**
     */
    public function getViewRenderer()
    {
        unset($this->renderer);
        if ($this->renderer === null) {

            $options = $this->getOptions();

            $rendererName = 'Zf2datatable.renderer.' . $this->getRendererName() . '.view';

            if ($this->getServiceLocator()->has($rendererName) === true) {
                /* @var $renderer \Zf2datatable\Renderer\AbstractRenderer */
                $renderer = $this->getServiceLocator()->get($rendererName);
                if (! $renderer instanceof Renderer\AbstractRenderer) {
                    throw new \Exception('Renderer service must implement "Zf2datatable\Renderer\AbstractRenderer"');
                }
                $renderer->setOptions($this->getOptions());
                $renderer->setMvcEvent($this->getMvcEvent());
                if ($this->getToolbarTemplate() !== null) {
                    $renderer->setToolbarTemplate($this->getToolbarTemplate());
                }
                $renderer->setViewModel($this->getViewModel());
                $renderer->setTranslator($this->getTranslator());
                $renderer->setTitle($this->getTitle());
                $renderer->setColumns($this->getColumns());
                $renderer->setRowStyles($this->getRowStyles());
                $renderer->setCacheId($this->getCacheId());
                $renderer->setCacheData($this->getCache()
                    ->getItem($this->getCacheId()));

                $this->renderer = $renderer;
            } else {
                throw new \Exception('Renderer service was not found, please register it: "' . $rendererName . '"');
            }
        }

        return $this->renderer;
    }

    public function isDataLoaded()
    {
        return (bool) $this->isDataLoaded;
    }

    /**
     * Load the data
     */
    public function loadData($render = 'getRenderer')
    {
        if ($this->isDataLoaded === true) {

            return true;
        }

        if ($this->isInit() !== true) {
            throw new \Exception('The init() method has to be called, before you can call loadData()!');
        }

        if ($this->hasDataSource() === false) {
            throw new \Exception('No datasource defined! Please call "setDataSource()" first"');
        }

        /**
         * Apply cache
         */
        $renderer = $this->$render();

        /**
         * Step 1) Apply needed columns + filters + sort
         * - from Request (HTML View) -> and save in cache for export
         * - or from cache (Export PDF / Excel) -> same view like HTML (without LIMIT/Pagination)
         */

        {
            /**
             * Step 1.1) Only select needed columns (performance)
             */
            $this->getDataSource()->setColumns($this->getColumns());

            /**
             * Step 1.2) Sorting
             */
            foreach ($renderer->getSortConditions() as $condition) {
                $this->getDataSource()->addSortCondition($condition['column'], $condition['sortDirection']);
            }

            /**
             * Step 1.3) Filtering
             */


            foreach ($renderer->getFilters($this->getKeepCacheFilter(),$this->getId()) as $filter) {
                $this->getDataSource()->addFilter($filter);
            }
        }

        /*
         * Step 2) Load the data (Paginator)
         */
        {
            $this->getDataSource()->execute();
            $paginatorAdapter = $this->getDataSource()->getPaginatorAdapter();

            \Laminas\Paginator\Paginator::setDefaultScrollingStyle('Sliding');

            $this->paginator = new Paginator($paginatorAdapter);
            $this->paginator->setCurrentPageNumber($renderer->getCurrentPageNumber());
            $this->paginator->setItemCountPerPage($renderer->getItemsPerPage($this->getDefaultItemsPerPage()));

            /* @var $currentItems \ArrayIterator */
            $data = $this->paginator->getCurrentItems();
            if (! is_array($data)) {
                if ($data instanceof \Laminas\Db\ResultSet\ResultSet) {
                    $data = $data->toArray();
                } elseif ($data instanceof ArrayIterator) {
                    $data = $data->getArrayCopy();
                } else {
                    $add = '';
                    if (is_object($data)) {
                        $add = get_class($data);
                    } else {
                        $add = '[no object]';
                    }
                    throw new \Exception('The paginator returned an unknow result: ' . $add . ' (allowed: \ArrayIterator or a plain php array)');
                }
            }
        }

        /*
         * Save cache
         */

        if ($renderer->isExport() === false) {
            $cacheData = array(
                'sortConditions' => $renderer->getSortConditions(),
                'filters' => $renderer->getFilters(),
                'currentPage' => $this->getPaginator()->getCurrentPageNumber()
            );

            $success = $this->getCache()->setItem($this->getCacheId(), $cacheData);
            if ($success !== true) {
                $options = $this->getCache()->getOptions();
                if(isset($options['cache_dir']))
                    throw new \Exception('Could not save the datagrid cache. Does the directory "' . $options->getCacheDir() . '" exists and is writeable?');
                else
                    throw new \Exception('Could not save the datagrid cache.Check configuration file');

            }
        }

        /*
         * Step 3) Format the data - Translate - Replace - Date / time / datetime - Numbers - ...
         */
        $prepareData = new PrepareData($data, $this->getColumns());
        $prepareData->setRendererName($this->getRendererName());
        $prepareData->setTranslator($this->getTranslator());
        $prepareData->prepare();
        $this->preparedData = $prepareData->getData();

        $this->isDataLoaded = true;
    }

    /**
     * Load the data detail
     */
    public function loadDataDetail($filter, $render = 'getViewRenderer')
    {
        if ($this->hasDataSource() === false) {
            throw new \Exception('No datasource defined! Please call "setDataSource()" first"');
        }

        // var_dump($this->getDataSource ()->executeDetail ( $filter ));
        $renderer = $this->$render();

        $this->setDataDetail($this->getDataSource()
            ->executeDetail($filter));
        return $this;
    }

    /**
     * Load the data detail
     */
    public function loadDataCrud($identity, $render = 'getFormRenderer', $action = 'editCrud')
    {
        if ($this->hasDataSource() === false) {
            throw new \Exception('No datasource defined! Please call "setDataSource()" first"');
        }


        // var_dump($this->getDataSource ()->executeDetail ( $filter ));
        $renderer = $this->$render();

        /**
         * Step 1.1) Filtering Column
         */
        foreach ($renderer->getFilters() as $filter) {
            $this->getDataSource()->addFilter($filter);
        }

        /**
         * Step 1.2) Filtering from Crud
         */
        $filterCrudColumn = $this->getConditionCrudColumn()[$action];
        if (is_array($filterCrudColumn)) {
            $oFilter = new Filter();
            $oFilter->setFromColumn($filterCrudColumn['field'], $filterCrudColumn['filter_operator'] . $filterCrudColumn['filter_crud']);
            $this->getDataSource()->addFilter($oFilter);
        }



        $entityObject = $this->getDataSource()->findByIdentity($identity);
        $entityObject = (is_object($entityObject) || is_array($entityObject)) ? $entityObject : $this->getDataSource()->getDefaultBindObject();


        /** permette di fare degli attach di eventi per manipolare l'oggetto / array prima del bind*/
        $event = $this->getMvcEvent();
        $shortCircuit = function ($r) use ($event)  {
            if ( is_object($r) || is_array($r) ) {
                return true;
            }

            if ($event->getError()) {
                return true;
            }
            return false;
        };
        $result = $this->getEventManager()->trigger('before.execute.bind',$entityObject,array('gridName'=>$this->getGridId()),$shortCircuit);
        if ($result->stopped()) {
                $entityObject = $result->last();
        }

        $this->setCrudDetail($entityObject);
        return $this;
    }

    /***  ***/
    public function loadJsonData($params)
    {
        $params['isExpression'] = false;
        $columns = $this->getColumns();
        $column = $columns[$params['column']];

        if(! $column instanceof \Zf2datatable\Column\AbstractColumn)
                return false;

        if(! $column->getType() instanceof \Zf2datatable\Column\Type\AutoComplete)
                return false;

        $adapter = \Zf2datatable\Column\Type\Adapter\StaticAdapterType::getAdapter($column->getUniqueId());


        if(! $adapter instanceof \Zf2datatable\Column\Type\Adapter\AdapterInterface ){
            return false;
        }

        if($column->getSelectExpression()==''){
            $params['column']= $column->getSelectPart2();
        }
        else{
            $params['column']= $column->getSelectExpression();
            $params['isExpression'] = true;
        }

        $dataJson = $adapter->getDataAutocomplete($params);
        return \Laminas\Json\Json::encode($dataJson);
    }


    /**
     *
     * @deprecated use render() instead!
     */
    public function execute()
    {
        trigger_error('execute() is deprecated, please use render() instead', E_USER_DEPRECATED);

        if ($this->isRendered() === false) {
            $this->render();
        }
    }

    protected function getIdentityColumnCrud($identity, $identityValue)
    {
        $Aidentity_crud_columns = array();
        $Aidentity = explode(self::paramsSeparator, $identity);
        $AidentityValue = explode(self::paramsSeparator, $identityValue);
        $Aidentity_crud_columns = @array_combine($Aidentity, $AidentityValue);

        return $Aidentity_crud_columns;
    }

    /**
     * console rendering
     */
    public function renderConsole(){
        $datasource = $this->getDataSource();
        $plgmanager = $this->getPluginControllerManager();
        $request = $this->getMvcEvent()->getRequest();
        $routeName = $this->getMvcEvent()->getRouteMatch()->getMatchedRouteName();
        $controller = substr($this->getMvcEvent()->getRouteMatch()->getParam('controller'), strrpos($this->getMvcEvent()
                ->getRouteMatch()
                ->getParam('controller'), '\\') + 1);

        $action = $this->getMvcEvent()->getRouteMatch()->getParam('action');


        $renderType = 'render';
        $plgredirect = $plgmanager->get('redirect');
        $plgpostredirectget = $plgmanager->get('PostRedirectGet');

        $plgurl = $plgmanager->get('url');
        $plgflashmessanger = $plgmanager->get('flashmessenger');

        $identity = $this->formatIdentityColumns();
        //$identityValue = $request->getQuery($identity, 0);
        $form = $this->getFrmMainCrud();

        $crudIdentity = $this->getIdentityColumnCrud($identity, $identityValue);

        if ($this->isDataLoaded() === false) {
            $this->loadData();
        }

        $this->renderGrid();
    }

    /**
     * Render the grid
     */


    public function isLoadCrudSettings(){
        $request = $this->getMvcEvent()->getRequest();
        $op = $request->getQuery('op');
        if(in_array($op,array('i','u','f','d')))
            return true;
        else
            return false;
    }

    public function render()
    {
        $datasource         = $this->getDataSource();
        $plgmanager         = $this->getPluginControllerManager();
        $plgWmanager        = $this->getPluginViewHelperManager();
        $request            = $this->getMvcEvent()->getRequest();

        $plgredirect        = $plgmanager->get('redirect');
        $plgpostredirectget = $plgmanager->get('PostRedirectGet');
        $plgurl             = $plgmanager->get('url');
        $plgflashmessanger  = $plgmanager->get('flashmessenger');


        if ($request instanceof ConsoleRequest) {
            return $this->renderConsole();
            //throw new \RuntimeException('You can only use this action from a console!');
        }

        $op                 = $request->getQuery('op');
        $key                = $request->getQuery('key');
        $gridID             = $request->getQuery('gridID','defaultGrid');
        $rendererType       = $request->getQuery('rendererType');
        // $data = $request->getPost ()->toArray ();



        // if(isset($request->getQuery ('keepCache')))
        if ((int) ($request->getQuery('keepCache') > 0))
            $this->setKeepCacheFilter($request->getQuery('keepCache'));

        $controller = substr($this->getMvcEvent()
            ->getRouteMatch()
            ->getParam('controller'), strrpos($this->getMvcEvent()
            ->getRouteMatch()
            ->getParam('controller'), '\\') + 1);

        $action = $this->getMvcEvent()
            ->getRouteMatch()
            ->getParam('action');

        $routeName = $this->getMvcEvent()
            ->getRouteMatch()
            ->getMatchedRouteName();

        $renderType = 'render';

        $identity = $this->formatIdentityColumns();
        $identityValue = $request->getQuery($identity, 0);

        if(null !== $this->getUrlRouteRedirectCrud()){
            $url = $this->getUrlRouteRedirectCrud();
        }
        else{
            $url = $plgurl->fromRoute($routeName, array(
                'controller' => $controller,
                'action' => $action
            ));
        }


        //redirect if acl is set
        if(!$this->checkUrlPermission($op)){
              $response = $this->getMvcEvent()->getResponse();
              $response->getHeaders()->addHeaderLine('Location', $url);
                            // The HTTP response status code 302 Found is a common way of performing a redirection.
                            // http://en.wikipedia.org/wiki/HTTP_302
              $response->setStatusCode(302);
              $response->sendHeaders();
              exit;
        }

        if(null === $this->getDefaultUriRedirect())
               $this->setDefaultUriRedirect($url);

        if(empty($op)) {

            if($gridID != $this->getId() && in_array($rendererType,['PHPExcel','printHtml','tcpdf','csv','printHtml'])) return false;

            if ($this->isCrud) {
                $this->addCrudColumn();
            }

            if ($this->isDataLoaded() === false) {
                $this->loadData();
            }
            /**
             * Step 4) Render the data to the defined output format (HTML, PDF...)
             * - Styling the values based on column (and value)
             */
            $this->renderGrid();
        }
        else
        {
            if($request->isXmlHttpRequest()){
                if ($op =='j' && $request->isGet()) {//jsoncall
                    // view datails
                    $params = $this->getRequest()->getQuery()->toArray();
                    unset($params['op']);
                    $response = $this->getMvcEvent()->getResponse();
                    $response->setContent($this->loadJsonData($params));
                    return $response;

                }
            }
            else
            {
            $form = $this->getFrmMainCrud();

            /**  section overwrite element form**/
            $this->removeFormElementFromProxy();
            $this->addFormElementFromProxy();
            /**  section overwrite element form**/

            $crudIdentity = $this->getIdentityColumnCrud($identity, $identityValue);

            if (is_array($crudIdentity)) {
                if ($op == 'u'){
                    $result = $datasource->findByIdentity($crudIdentity);
                    if (is_array($result) || is_object($result)) {
                        $form->bind($result);
                    }
                    else
                        $form->bind($datasource->getDefaultBindObject());
                }
                else{
                    $form->bind($datasource->getDefaultBindObject());
                }

            } else {
                $Obind = $datasource->getEntity();

                $form->bind(new $Obind());
            }
            //move after bind object
            $this->replaceFormElementFromProxy();


            if ($op != '' && $request->isPost()  && $request->getPost("gridID") == $this->getId()) { // crud insert/update

                if(
                    method_exists($this->getDataSource(),'getSourceObject') &&
                    method_exists($this->getDataSource()->getSourceObject(),'getInputFilter')){
                    $this->getFrmMainCrud()->setPreferFormInputFilter(true);
                    $this->getFrmMainCrud()->setInputFilter($this->getDataSource()->getSourceObject()->getInputFilter());
                }
                else{
                    if($this->getSetFormFilterFromCrud()){
                        $this->getFrmMainCrud()->setPreferFormInputFilter(true);
                        $this->getFrmMainCrud()->setInputFilter($this->getFrmFilterCrud());
                    }
                }

                $postDataFiles      = $this->checkIfPostFileArray($request->getFiles ()->toArray ());
                $postDataImputs     = $request->getPost ()->toArray ();
                $postData = array_merge_recursive (
                    $postDataImputs,
                    // Notice: make certain to merge the Files also to the post data
                    $postDataFiles
                    );



               //$this->getFrmMainCrud()->setData($request->getPost());

                $this->getFrmMainCrud()->setData($postData);


                if ($this->getFrmMainCrud()->isValid()) {
                    $uploadFiles = $request->getFiles ()->toArray ();
                    $pathDoc = $this->getPathFileUpload();
                    $postUpload = array();

                    $adapter = new \Laminas\File\Transfer\Adapter\Http();
                    //var_dump($adapter);

                    foreach ( $uploadFiles as $key => $uploadFile ) {
                        $fileUploadName[] = $key;
                    }

                    $indiceFile = 0;
                    foreach ($adapter->getFileInfo() as $info) {

                        $originalFileName = $info['name'];
                        //echo $originalFileName."<br />";

                        $estensione = substr ( $originalFileName, strrpos ( $originalFileName, '.' ) + 1 );
                        $uniqueToken = md5 ( uniqid ( mt_rand (), true ) );
                        $file_name = $uniqueToken . '.' . $estensione;
                        $target_position = $pathDoc . DIRECTORY_SEPARATOR . $file_name;

                        //echo $target_position."<br />";
                        $adapter->addFilter('Rename', array('target' => $target_position,
                            'overwrite' => true));

                        if ($adapter->receive($originalFileName)) {
                            $postUpload[$fileUploadName[$indiceFile]]=$file_name;
                        }
                        else{
                            $plgflashmessanger->addSuccessMessage($this->getTranslator()
                                ->translate('File Not Recived'));
                        }

                        $indiceFile++;
                    }


                    $object_validate_filter = $this->prepareCrudData($this->getFrmMainCrud()
                        ->getData(), $postUpload, $crudIdentity);



                    if (is_array($result) || is_object($result)) {
                        $result = $datasource->update($object_validate_filter, $crudIdentity,[
                            'controller'=>$controller,
                            'action'=>$action,
                            'postData'=>$postData
                        ]);
                        //$log = $this->getServiceLocator()->get('zf2datatable_logger');
                        //$log->info('update:'.$op);
                        if ($result) {
                            $plgflashmessanger->addSuccessMessage($this->getTranslator()
                                ->translate('Record Updated'));
                        }

                        if($this->getRedirectToRecordUpdate()){
                            if(method_exists($datasource, "getPrimaryValueData")){
                                $url = "?op=u&{__Identity__}";
                                $primary_key =  http_build_query($crudIdentity);
                                if($primary_key)
                                    $url = str_replace("{__Identity__}", $primary_key, $url);
                            }
                        }


                        if(strpos($url,"?")!==false){
                            $url .= '&keepCache=1';
                        }
                        else
                            $url .= '?keepCache=1';
                        //$url = $->getRouter()->assemble($redirect_route['params'], $redirect_route['options']);
                        $response = $this->getMvcEvent()->getResponse();
                        $response->getHeaders()->addHeaderLine('Location', $url);
                        // The HTTP response status code 302 Found is a common way of performing a redirection.
                        // http://en.wikipedia.org/wiki/HTTP_302
                        $response->setStatusCode(302);
                        $response->sendHeaders();
                        exit;

                    } else {
                        //$log = $this->getServiceLocator()->get('zf2datatable_logger');
                        //$log->info('insert:'.$op);
                        $result = $datasource->insert($object_validate_filter,[
                            'controller'=>$controller,
                            'action'=>$action,
                            'postData'=>$postData
                        ]
                            );
                        if ($result) {
                            $plgflashmessanger->addSuccessMessage('Record Insert');
                        }


                        if($this->getRedirectToRecordInsert()){
                            if(method_exists($datasource, "getPrimaryValueData")){
                                $url = "?op=u&{__Identity__}";
                                $primary_key = $datasource->getPrimaryValueData($object_validate_filter,$this->getIdentyColumns());
                                if($primary_key)
                                    $url = str_replace("{__Identity__}", $primary_key, $url);
                            }
                        }


                        //$url = $->getRouter()->assemble($redirect_route['params'], $redirect_route['options']);
                        if(strpos($url,"?")!==false){
                            $url .= '&keepCache=1';
                        }
                        else
                            $url .= '?keepCache=1';
                        $response = $this->getMvcEvent()->getResponse();
                        $response->getHeaders()->addHeaderLine('Location', $url);
                        // The HTTP response status code 302 Found is a common way of performing a redirection.
                        // http://en.wikipedia.org/wiki/HTTP_302
                        $response->setStatusCode(302);
                        $response->sendHeaders();
                        exit;
                    }
                    $this->setIsDataLoaded(false);
                } else {
                    $plgflashmessanger->addErrorMessage($this->getFrmMainCrud()
                        ->getMessages());
                    return $this->renderFormGrid($form); // render crud form
                }
            } elseif ($op == 'd' && $request->isGet()) { // crud delete

                if($gridID !== $this->getId()) return false;  // skip delete multigird

                    $this->loadDataCrud($crudIdentity, "getFormRenderer");
                if ($this->getCrudDetail() === null || ! $this->getisAllowDelete()) {
                    $plgflashmessanger->addSuccessMessage($this->getTranslator()
                        ->translate('Not Allowed Delete Record'));
                    return $plgredirect->toRoute($routeName, array(
                        'controller' => $controller,
                        'action' => $action
                    ));
                }
                $result = $datasource->delete($crudIdentity);

                if ($result) {
                    $plgflashmessanger->addSuccessMessage($this->getTranslator()
                        ->translate('Record Delete'));
                }

                $this->setIsDataLoaded(false);
                /*return $plgredirect->toRoute($routeName, array(
                    'controller' => $controller,
                    'action' => $action
                ));*/


                $response = $this->getMvcEvent()->getResponse();
                $response->getHeaders()->addHeaderLine('Location', $url);
                // The HTTP response status code 302 Found is a common way of performing a redirection.
                // http://en.wikipedia.org/wiki/HTTP_302
                $response->setStatusCode(302);
                $response->sendHeaders();
                exit;

            }
            elseif ($op == 'u' && $request->isGet()) {
                if($gridID !== $this->getId()) return false;  // skip delete multigird
                // show form
                $form = $this->getFrmMainCrud();
                if (is_array($crudIdentity)) {
                    $this->loadDataCrud($crudIdentity, "getFormRenderer");

                    if ($this->getCrudDetail() === null) {
                        $plgflashmessanger->addSuccessMessage($this->getTranslator()
                            ->translate('Not Allowed Edit Record'));

                        // $url = $this->url
                        return $plgredirect->toRoute($routeName, array(
                            'controller' => $controller,
                            'action' => $action
                        ));
                    } else {}

                    $form->bind($this->getCrudDetail());
                }
                $this->renderFormGrid($form); // render crud form
            } elseif ($op == 'i' && $request->isGet()) {
                if($gridID !== $this->getId()) return false;  // skip delete multigird
                /*$form = $this->getFrmMainCrud();
                if (is_array($crudIdentity)) {
                    $this->loadDataCrud($crudIdentity, "getFormRenderer");
                    $form->bind($this->getCrudDetail());
                }*/
                $this->renderFormGrid($form); // render crud form
            } elseif ($op == 'v' && $request->isGet()) {
                if($gridID !== $this->getId()) return false;  // skip delete multigird
                // view datails
                if($this->getViewModeRender()=='crudview'){
                    $form = $this->getFrmMainCrud();
                    if (is_array($crudIdentity)) {
                        $this->loadDataCrud($crudIdentity, "getFormRenderer");
                        if ($this->getCrudDetail() === null) {
                            $plgflashmessanger->addSuccessMessage($this->getTranslator()
                                ->translate('Not Allowed Edit Record'));

                            // $url = $this->url
                            return $plgredirect->toRoute($routeName, array(
                                'controller' => $controller,
                                'action' => $action
                            ));
                        } else {}
                       $this->disabledField($form); // disabilito i campi form
                       $form->bind($this->getCrudDetail());

                    }
                    $this->renderFormGrid($form); // render crud form
                }
                else{
                    $this->loadDataDetail(array(
                        $identity => $identityValue
                    ));
                    $this->renderViewGrid();
                }
            }
            elseif ($op == 'f' && $request->isGet()) {
                if($gridID !== $this->getId()) return false;  // skip delete multigird
                // view datail
                $load =$this->loadattach();
                if($load instanceof \Laminas\Http\Response\Stream){
                    return $load;
                }
                else
                    throw new \Exception('Load Fails');

           }
        }
      }
    }


    protected function checkIfPostFileArray($postDataFiles){
        foreach ($postDataFiles as $key => $file){
            if ($file['name']==''){
                unset($postDataFiles[$key]);
            }
        }

        return $postDataFiles;
    }

    /**
     *
     * @param array or enetity $data
     * @param array $_FILES $postUpload
     */
    protected function prepareCrudData($data = [], $postUpload = [], $crudIdentity = [])
    {
        if (is_array($data) || $data instanceof \ArrayObject) {
            unset($data['submit']);
            if(isset($data['cancel'])) unset($data['cancel']);
            if(isset($data['gridID'])) unset($data['gridID']); // grid identification
            if(isset($data['noFieldCrud'])) unset($data['noFieldCrud']); // fields not present in the crud
            if($crudIdentity){
                foreach ($crudIdentity as $key => $identity) {
                    if (isset($data[$key])) unset($data[$key]);
                }
            }
        }

        foreach ($postUpload as $uploadKey => $upload){
            if(array_key_exists($uploadKey, $data)){
                    $data[$uploadKey] = $upload.self::paramsSeparator.$data[$uploadKey]['name'].self::paramsSeparator.$data[$uploadKey]['type'];
                }
            elseif(is_object($data) && method_exists($data, 'set'.$uploadKey)){
                    $methodSet = 'set'.$uploadKey;
                    $methodGet = 'get'.$uploadKey;
                    $data->$methodSet($upload.self::paramsSeparator.$data->$methodGet()['name'].self::paramsSeparator.$data->$methodGet()['type']);
                }
         }



        return $data;
    }


    protected function disabledField(&$form){
        //return true;
        foreach ($form as $element){
            if($element instanceof \Laminas\Form\Element\Collection){
                $this->disabledField( $element->getTargetElement());
            }
            elseif($element instanceof \Laminas\Form\Fieldset){
                $this->disabledField($element);
            }
            else{
                if($element instanceof \Laminas\Form\Element\Select){
                    $element->setAttribute('disabled','disabled');
                }
                else{
                    $element->setAttribute('readonly','readonly');
                    $element->setAttribute('disabled','disabled');
                }
            }
        }

        return $form;
    }


    public function loadattach() {
        //error_reporting(E_ALL);
        //ini_set('display_errors',1);

        $request    = $this->getMvcEvent()->getRequest();
        $stream     = new \Laminas\Http\Response\Stream();;
        $file       = $request->getQuery ( "file" );
        $path       = $this->getPathFileUpload();

        if ($file == '') {
            throw new \Exception ( 'Allegato non specificato');
        }

        $fileDetail = explode(self::paramsSeparator, $file);
        $mimeType = $this->validateMimeType ( $fileDetail[0]);

        $stream->setStream(fopen($path.DIRECTORY_SEPARATOR.$fileDetail[0], 'r'));
        $stream->setStatusCode(200);

        $headers = new \Laminas\Http\Headers();
        $headers->addHeaderLine('Content-Type',$mimeType)
            ->addHeaderLine('Content-Disposition','attachment;filename="' . $fileDetail[1] . '"')
            ->addHeaderLine('Content-Length', filesize($path.DIRECTORY_SEPARATOR.$fileDetail[0]));
        ob_end_clean();
        $stream->setHeaders($headers);
        return $stream;
    }


    protected function validateMimeType($filename){

        $type_custom = function ($filename) {
            $mime_types = array(

                'txt' => 'text/plain',
                'htm' => 'text/html',
                'html' => 'text/html',
                'php' => 'text/html',
                'css' => 'text/css',
                'js' => 'application/javascript',
                'json' => 'application/json',
                'xml' => 'application/xml',
                'swf' => 'application/x-shockwave-flash',
                'flv' => 'video/x-flv',

                // images
                'png' => 'image/png',
                'jpe' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'jpg' => 'image/jpeg',
                'gif' => 'image/gif',
                'bmp' => 'image/bmp',
                'ico' => 'image/vnd.microsoft.icon',
                'tiff' => 'image/tiff',
                'tif' => 'image/tiff',
                'svg' => 'image/svg+xml',
                'svgz' => 'image/svg+xml',

                // archives
                'zip' => 'application/zip',
                'rar' => 'application/x-rar-compressed',
                'exe' => 'application/x-msdownload',
                'msi' => 'application/x-msdownload',
                'cab' => 'application/vnd.ms-cab-compressed',

                // audio/video
                'mp3' => 'audio/mpeg',
                'qt' => 'video/quicktime',
                'mov' => 'video/quicktime',

                // adobe
                'pdf' => 'application/pdf',
                'psd' => 'image/vnd.adobe.photoshop',
                'ai' =>  'application/postscript',
                'eps' => 'application/postscript',
                'ps' => 'application/postscript',

                // ms office
                'doc' => 'application/msword',
                'rtf' => 'application/rtf',
                'xls' => 'application/vnd.ms-excel',
                'ppt' => 'application/vnd.ms-powerpoint',

                // open office
                'odt' => 'application/vnd.oasis.opendocument.text',
                'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            );

            $ext = strtolower(array_pop(explode('.',$filename)));

            if (array_key_exists(trim($ext), $mime_types)) {

                return $mime_types[trim($ext)];
            }
            elseif (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME);
                $mimetype = finfo_file($finfo, $filename);
                finfo_close($finfo);
                return $mimetype;
            }
            else {
                return 'application/octet-stream';
            }
        };
        return $type_custom($filename);
    }


    protected function renderGrid()
    {
        $renderer = $this->getRenderer();
        $renderer->setTitle($this->getTitle());
        $renderer->setPaginator($this->getPaginator());
        $renderer->setData($this->getPreparedData());
        $renderer->prepareViewModel($this);
        $event = $renderer->getMvcEvent();


        $result = $this->getEventManager()->trigger('before.execute.render',$renderer,array('gridName'=>$this->getGridId()), $shortCircuit = function ($r) use ($event) {
            if ($r instanceof ResponseInterface) {
                return true;
            }
            if ($event->getError()) {
                return true;
            }
            return false;
        });

       if ($result->stopped()) {
                $response = $result->last();
                return $response;
       }

        $this->response = $renderer->execute();
        $this->getEventManager()->trigger('after.execute.render',$this->response);
        $this->isRendered = true;
    }

    protected function renderViewGrid()
    {
        $renderer = $this->getViewRenderer();
        $renderer->setTitle($this->getTitle());
        $renderer->prepareViewModel($this);
        $this->getEventManager()->trigger('before.execute.render',$renderer,array('gridName'=>$this->getGridId()));
        $this->response = $renderer->execute();
        $this->getEventManager()->trigger('after.execute.render',$this->response);
        $this->isRendered = true;
    }

    protected function renderFormGrid($form)
    {
        $renderer = $this->getFormRenderer();
        $renderer->setTitle($this->getTitle());
        $renderer->setFormCrud($form);

        $renderer->prepareViewModel($this);
        $this->getEventManager()->trigger('before.execute.render',$renderer,array('gridName'=>$this->getGridId()));
        $this->response = $renderer->execute();
        $this->getEventManager()->trigger('after.execute.render',$this->response);
        $this->isRendered = true;

    }

    /**
     * Is already rendered?
     *
     * @return boolean
     */
    public function isRendered()
    {
        return (bool) $this->isRendered;
    }

    /**
     *
     * @throws \Exception
     * @return Paginator
     */
    public function getPaginator()
    {
        if ($this->paginator === null) {
            throw new \Exception('Paginator is only available after calling "loadData()"');
        }

        return $this->paginator;
    }

    /**
     *
     * @return array
     */
    public function getPreparedData()
    {
        return $this->preparedData;
    }

    /**
     * Set the toolbar view template
     *
     * @param unknown $name
     */
    public function setToolbarTemplate($name)
    {
        $this->toolbarTemplate = (string) $name;
    }

    /**
     * Get the toolbar template name
     * Return null if nothing custom set
     *
     * @return string null
     */
    public function getToolbarTemplate()
    {
        return $this->toolbarTemplate;
    }

    /**
     * Set a custom ViewModel...generally NOT necessary!
     *
     * @param ViewModel $viewModel
     */
    public function setViewModel(ViewModel $viewModel)
    {
        if ($this->viewModel !== null) {
            throw new \Exception('A viewModel is already set. Did you already called $grid->render() or $grid->getViewModel() before?');
        }

        $this->viewModel = $viewModel;
    }

    /**
     *
     * @return ViewModel
     */
    public function getViewModel()
    {
        if ($this->viewModel === null) {
            $this->viewModel = new ViewModel();
        }

        return $this->viewModel;
    }

    /**
     *
     * @return Ambigous <\Laminas\Stdlib\ResponseInterface, \Laminas\Http\Response\Stream, \Laminas\View\Model\ViewModel>
     */
    public function getResponse()
    {
        if (! $this->isRendered()) {
            $this->render();
        }

        return $this->response;
    }

    /**
     * Is this a HTML "init" response?
     * YES: loading the HTML for the grid
     * NO: AJAX loading OR it's an export
     *
     * @return boolean
     */
    public function isHtmlInitReponse()
    {
        if (! $this->getResponse() instanceof JsonModel && ! $this->getResponse() instanceof ResponseInterface) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param array() $option
     */
    public function setConditionCrudColumn($option)
    {
        $this->crudOption = $option;
    }

    public function getConditionCrudColumn()
    {
        return $this->crudOption;
    }

    protected function formatIdentityColumns()
    {
        return implode(self::paramsSeparator, $this->getIdentyColumns());
    }

    public function addCrudColumn($class="btn btn-primary btn-xs")
    {
       if ($this->getTwitterBoostrapVersion()=='2.3.*'){
            $iconEdit   ='icon-pencil';
            $iconView   ='icon-eye-open';
            $iconDelete ='icon-trash';
        }
        else{
            $iconEdit   ='oi oi-pencil';
            $iconView   ='oi oi-eye';
            $iconDelete ='oi oi-trash';
        }

        $identity = $this->formatIdentityColumns();
        $actionviewCrud = new Column\Action\Icon();
        $actionviewCrud->setIconTheme($this->getTwitterBoostrapVersion());
        $actionviewCrud->setIconClass($iconView);
        $actionviewCrud->setAttribute('id', 'viewCrud');
        $actionviewCrud->setAttribute('class', $class);
        $actionviewCrud->setAttribute('href', sprintf('?op=v&%s=', $identity) . $actionviewCrud->getRowIdPlaceholder()."&gridID=".$this->getId());

        if (isset($this->crudOption['viewCrud'])) {
            $actionviewCrud->addShowOnValue($this->crudOption['viewCrud']['field'], $this->crudOption['viewCrud']['filter']);
            if (isset($this->crudOption['viewCrud']['js'])) {
                $actionviewCrud->setAttribute('onclick', sprintf('%s', $this->crudOption['viewCrud']['js']));

            }
        }

        // $action = new Column\Action\Button ();
        $actionedit = new Column\Action\Icon();
        // $action->setLabel ( $this->getTranslator ()->translate ( 'Edit' ) );
        $actionedit->setIconTheme($this->getTwitterBoostrapVersion());
        $actionedit->setIconClass($iconEdit);
        $actionedit->setAttribute('id', 'editCrud');
        $actionedit->setAttribute('class', $class);
        $actionedit->setAttribute('href', sprintf('?op=u&%s=', $identity) . $actionedit->getRowIdPlaceholder()."&gridID=".$this->getId());

        if (isset($this->crudOption['editCrud'])) {
            $actionedit->addShowOnValue($this->crudOption['editCrud']['field'], $this->crudOption['editCrud']['filter']);
            if (isset($this->crudOption['editCrud']['js'])) {
                $actionedit->setAttribute('onclick', sprintf('%s', $this->crudOption['editCrud']['js']));
            }
        }

        $actiondel = new Column\Action\Icon();
        // $actiondel->setLabel ( $this->getTranslator ()->translate ( 'Delete' ) );
        $actiondel->setIconClass($iconDelete);
        $actiondel->setIconTheme($this->getTwitterBoostrapVersion());
        $actiondel->setAttribute('id', 'deleteCrud');
        $actiondel->setAttribute('class',$class);
        $actiondel->setAttribute('href', sprintf('?op=d&%s=', $identity) . $actiondel->getRowIdPlaceholder()."&gridID=".$this->getId());
        // $actiondel->setAttribute ( 'href', "#");
        if (isset($this->crudOption['deleteCrud'])) {
            $actiondel->addShowOnValue($this->crudOption['deleteCrud']['field'], $this->crudOption['deleteCrud']['filter']);
            if (isset($this->crudOption['deleteCrud']['js'])) {
                $actiondel->setAttribute('onclick', sprintf('%s', $this->crudOption['deleteCrud']['js']));
            }
        }

        $col = new Column\Action();
        $col->setLabel($this->getTranslator()
            ->translate('Action'));
        $col->setWidth(0);

        if ($this->getisAllowView())
            $col->addAction($actionviewCrud);
        if ($this->getisAllowEdit())
            $col->addAction($actionedit);
        if ($this->getisAllowDelete())
            $col->addAction($actiondel);
        $this->prependColumn($col);
    }

    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    public function getDefaulItemsPerPage()
    {
        return $this->defaulItemsPerPage;
    }

    public function setDefaulItemsPerPage(integer $defaulItemsPerPage)
    {
        $this->defaulItemsPerPage = $defaulItemsPerPage;
        return $this;
    }

    public function setRowStyles($rowStyles)
    {
        $this->rowStyles = $rowStyles;
        return $this;
    }

    public function setMassActions($massActions)
    {
        $this->massActions = $massActions;
        return $this;
    }

    public function setPreparedData(array $preparedData)
    {
        $this->preparedData = $preparedData;
        return $this;
    }

    public function getIsUserFilterEnabled()
    {
        return $this->isUserFilterEnabled;
    }

    public function setIsUserFilterEnabled(array $isUserFilterEnabled)
    {
        $this->isUserFilterEnabled = $isUserFilterEnabled;
        return $this;
    }

    public function setPaginator(Paginator $paginator)
    {
        $this->paginator = $paginator;
        return $this;
    }

    public function getIsInit()
    {
        return $this->isInit;
    }

    public function setIsInit(boolean $isInit)
    {
        $this->isInit = $isInit;
        return $this;
    }

    public function getIsDataLoaded()
    {
        return $this->isDataLoaded;
    }

    public function getIsRendered()
    {
        return $this->isRendered;
    }

    public function setIsRendered(boolean $isRendered)
    {
        $this->isRendered = $isRendered;
        return $this;
    }

    public function getForceRenderer()
    {
        return $this->forceRenderer;
    }

    public function setForceRenderer($forceRenderer)
    {
        $this->forceRenderer = $forceRenderer;
        return $this;
    }

    public function getSpecialMethods()
    {
        return $this->specialMethods;
    }

    public function setSpecialMethods($specialMethods)
    {
        $this->specialMethods = $specialMethods;
        return $this;
    }

    public function getCrudOption()
    {
        return $this->crudOption;
    }

    public function setCrudOption($crudOption)
    {
        $this->crudOption = $crudOption;
        return $this;
    }

    public function getColumnIdentity()
    {
        return $this->columnIdentity;
    }

    public function setColumnIdentity($columnIdentity)
    {
        $this->columnIdentity = $columnIdentity;
        return $this;
    }

    public function getAdditionalButton()
    {
        return $this->additionalButton;
    }

    public function setAdditionalButton(array $additionalButton)
    {
        $this->additionalButton = $additionalButton;
        return $this;
    }

    public function getPathFileUpload()
    {
        return $this->pathFileUpload;
    }

    public function setPathFileUpload($pathFileUpload)
    {
        $this->pathFileUpload = $pathFileUpload;
        return $this;
    }

    public function getDefaultUriRedirect()
    {
        return $this->defaultUriRedirect;
    }

    public function setDefaultUriRedirect($defaultUriRedirect)
    {
        $this->defaultUriRedirect = $defaultUriRedirect;
        return $this;
    }

    public function getViewChildPosition()
    {
        return $this->viewChildPosition;
    }

    public function setViewChildPosition($viewChildPosition='bottom')
    {
        $this->viewChildPosition = $viewChildPosition;
        return $this;
    }

    public function getAdditionalFilter()
    {
        return $this->additionalFilter;
    }

    public function setAdditionalFilter($additionalFilter)
    {
        $this->additionalFilter = $additionalFilter;
        return $this;
    }

    public function getSetFormFilterFromCrud()
    {
        return $this->setFormFilterFromCrud;
    }

    public function getGridId()
    {
        return $this->gridId;
    }

    public function setGridId($gridId)
    {
        $this->gridId = $gridId;
        return $this;
    }

    public function getEnableFlashMessenger()
    {
        return $this->enableFlashMessenger;
    }

    /**
     *
     * @param boolean $enableFlashMessenger
     * abilita / disabilita messaggi
     */
    public function setEnableFlashMessenger($enableFlashMessenger)
    {
        $this->enableFlashMessenger = $enableFlashMessenger;
        return $this;
    }














}
