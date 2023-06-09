<?php
namespace Zf2datatable\Renderer;

use Zf2datatable\Datagrid;
use Laminas\Paginator\Paginator;
use Laminas\View\Model\ViewModel;
use Laminas\Mvc\MvcEvent;
use Laminas\I18n\Translator\Translator;
use Laminas\Http\PhpEnvironment\Request as HttpRequest;
use Laminas\Console\Request as ConsoleRequest;
use Laminas\Cache;
use Zf2datatable\Filter;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Laminas\Db\Sql\Ddl\Column\Boolean;

abstract class AbstractRenderer implements RendererInterface
{

    protected $options = array();

    protected $title;

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
     * @var Paginator
     */
    protected $paginator;

    protected $columns = array();

    protected $rowStyles = array();

    protected $sortConditions;

    protected $filters;

    protected $currentPageNumber;

    protected $dataGrid;

    protected $redirectToGet = false;

    protected $findException = true;

    protected $reasonException =array();

    /**
     *
     * @var array
     */
    protected $data = array();

    /**
     *
     * @var MvcEvent
     */
    protected $mvcEvent;

    /**
     *
     * @var ViewModel
     */
    protected $viewModel;


    protected $template;

    protected $templateToolbar;

    protected $isCrud = false;
    
    protected $userInfo;
    
    protected $dateTimeRender;

    static protected  $listOfTemplate = array('layout','detail','formcrud','formcrud2','formcrud3');

    static protected  $listOfTemplateArea = array('toolbar','layout','detail','crud');
    
    protected $gridID;

    /**
     *
     * @var Translator
     */
    protected $translator;
    
    
    /**
     * @return the $gridID
     */
    public function getGridID()
    {
        return $this->gridID;
    }

    /**
     * @param field_type $gridID
     */
    public function setGridID($gridID)
    {
        $this->gridID = $gridID;
    }

    public function __construct(){
        //echo "istanzio constrcut";
    }

    // todo
    public function setOption()
    {
        return $this;
    }


    public function getOption($key)
    {
        return $this->options[$key];
    }


    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     *
     * @return array
     */
    public function getOptionsRenderer()
    {
        $options = $this->getOptions();

        if (isset($options['renderer'][$this->getName()])) {
            return $options['renderer'][$this->getName()];
        } else {
            return array();
        }
    }

    /**
     * 
     * @param array $_options
     */
    public function setOptionsRenderer($_options)
    {
        $options = $this->getOptions();     
        $oRenderConfig              = new \Laminas\Config\Config($options);
        $oRenderConfigOverWrite     = new \Laminas\Config\Config($_options);
        $oRenderConfig->merge($oRenderConfigOverWrite);
        $this->setOptions($oRenderConfig->toArray());
        return $this;
    }


    /**
     *
     * @param ViewModel $viewModel
     */
    public function setViewModel(ViewModel $viewModel)
    {
        $this->viewModel = $viewModel;
    }

    /**
     *
     * @return \Laminas\View\Model\ViewModel
     */
    public function getViewModel()
    {
        return $this->viewModel;
    }

    /**
     * Set the view template
     *
     * @param string $name
     */
    public function setTemplate($templete)
    {
        $this->template =  $templete;
    }

    /**
     * Get the view template name
     *
     * @return string
     */
    public function getTemplate()
    {

        if ($this->template === null) {
                $this->template = $this->getTemplatePathDefault();
        }

        return $this->template;
    }

    /**
     * Get the default template path (if there is no own set)
     *
     * @param  string $type
     *                      layout or toolbar
     * @return string
     */
    protected function getTemplatePathDefault($type = 'layout')
    {
        $optionsRenderer = $this->getOptionsRenderer();
        if (isset($optionsRenderer['templatesOverwrite'][$type])) {
                if(is_array($optionsRenderer['templatesOverwrite'][$type])){
                    $path = $optionsRenderer['templatesOverwrite'][$type]['path'];
                    $name = $optionsRenderer['templatesOverwrite'][$type]['name'];
                    return $path.DIRECTORY_SEPARATOR.$name;
                }
                else
                    return 'zf2datatable/renderer/' . $this->getName() . '/' .$optionsRenderer['templatesOverwrite'][$type];
        }

        if (in_array($type, self::$listOfTemplate)) {
            return 'zf2datatable/renderer/' . $this->getName() . '/' . $type;
        } elseif ($type === 'toolbar') {
            return 'zf2datatable/toolbar/toolbar';
        }

        throw new \Exception('Unknown render template type: "' . $type . '"');
    }

    /**
     * Set the toolbar view template name
     *
     * @param string $name
     */
    public function setToolbarTemplate($name)
    {
        $this->templateToolbar = (string) $name;
    }

    public function getToolbarTemplate()
    {
        if ($this->templateToolbar === null) {
            $this->templateToolbar = $this->getTemplatePathDefault('toolbar');
        }

        return $this->templateToolbar;
    }

    /**
     * Paginator is here to retreive the totalItemCount, count pages, current page
     * NOT FOR THE ACTUAL DATA!!!!
     *
     * @param \Laminas\Paginator\Paginator $paginator
     */
    public function setPaginator(Paginator $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     *
     * @return \Laminas\Paginator\Paginator
     */
    public function getPaginator()
    {
        return $this->paginator;
    }

    /**
     * Set the columns
     *
     * @param array $columns
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;
    }

    /**
     * Get all columns
     *
     * @return \ZfcDatagrid\Column\AbstractColumn[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     *
     * @param array $rowStyles
     */
    public function setRowStyles($rowStyles = array())
    {
        $this->rowStyles = $rowStyles;
    }

    /**
     *
     * @return array
     */
    public function getRowStyles()
    {
        return $this->rowStyles;
    }

    /**
     * Calculate the sum of the displayed column width to 100%
     *
     * @param array $columns
     */
    protected function calculateColumnWidthPercent(array $columns)
    {
        $widthAllColumn = 0;
        foreach ($columns as $column) {
            /* @var $column \ZfcDatagrid\Column\AbstractColumn */
            $widthAllColumn += $column->getWidth();
        }

        $widthSum = 0;
        // How much 1 percent columnd width is really "one" percent...
        $relativeOnePercent = $widthAllColumn / 100;

        foreach ($columns as $column) {
            $widthSum += (($column->getWidth() / $relativeOnePercent));
            $column->setWidth(($column->getWidth() / $relativeOnePercent));
        }
    }

    /**
     * The prepared data
     *
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     *
     * @return array
     */
    public function getCacheData()
    {

        if($this->getCache() instanceof  \Laminas\Cache\Storage\StorageInterface){
            return $this->getCache()->getItem($this->getCacheId());
        }
        else
             throw new \Exception('Cache is not available from cache are missing!');
    }

    /**
     *
     * @return array
     */
    public function setCacheData(){
        return true;
    }



    /**
     *
     * @throws \Exception
     * @return array
     */
    private function getCacheSortConditions()
    {
        $cacheData = $this->getCacheData();
        if (! isset($cacheData['sortConditions'])) {
            $this->setFindException(true);
            $this->setReasonException('error-cache-ordering-expired', 'Sort conditions from cache are missing!');
            //throw new \Exception('Sort conditions from cache are missing!');
        }

        return $cacheData['sortConditions'];
    }

    /**
     *
     * @throws \Exception
     * @return array
     */
    private function getCacheFilters()
    {
        $cacheData = $this->getCacheData();
        if (!isset($cacheData['filters'])) {
            $this->setFindException(true);
            $this->setReasonException('error-cache-filter-expired', 'Filters from cache are missing!');
            //throw new \Exception('Filters from cache are missing!');
        }

        return $cacheData['filters'];
    }

    /**
     * Not used ATM...
     *
     * @see \ZfcDatagrid\Renderer\RendererInterface::setMvcEvent()
     */
    public function setMvcEvent(MvcEvent $mvcEvent)
    {
        $this->mvcEvent = $mvcEvent;
    }

    /**
     * Not used ATM...
     *
     * @return MvcEvent
     */
    public function getMvcEvent()
    {
        return $this->mvcEvent;
    }

    /**
     *
     * @return \Laminas\Stdlib\RequestInterface
     */
    public function getRequest()
    {
        return $this->getMvcEvent()->getRequest();
    }

    /**
     *
     * @param  Translator                $translator
     * @throws \InvalidArgumentException
     */
    public function setTranslator($translator)
    {
        if (! $translator instanceof Translator && ! $translator instanceof \Laminas\I18n\Translator\TranslatorInterface) {
            throw new \InvalidArgumentException('Translator must be an instanceof "Laminas\I18n\Translator\Translator" or "Laminas\I18n\Translator\TranslatorInterface"');
        }

        $this->translator = $translator;
    }

    /**
     *
     * @return \Laminas\I18n\Translator\Translator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * Set the title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
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
     *
     * @param string $cacheId
     */
    public function setCacheId($cacheId)
    {
        $this->cacheId = $cacheId;
    }

    /**
     *
     * @return string
     */
    public function getCacheId()
    {
        return $this->cacheId;
    }

    /**
     * Set the sort conditions explicit (e.g.
     * from a custom form)
     *
     * @param array $sortConditions
     */
    public function setSortConditions(array $sortConditions)
    {
        foreach ($sortConditions as $sortCondition) {
            if (! is_array($sortCondition)) {
                throw new InvalidArgumentException('Sort condition have to be an array');
            }

            if (! array_key_exists('column', $sortCondition)) {
                throw new InvalidArgumentException('Sort condition missing array key column');
            }
        }

        $this->sortConditions = $sortConditions;
    }

    /**
     *
     * @return array
     */
    public function getSortConditions()
    {
        if (is_array($this->sortConditions)) {
            return $this->sortConditions;
        }

        if ($this->isExport() === true) {
            // Export renderer should always retrieve the sort conditions from cache!
            $this->sortConditions = $this->getCacheSortConditions();

            return $this->sortConditions;
        }

        $this->sortConditions = $this->getSortConditionsDefault();

        return $this->sortConditions;
    }

    /**
     * Get the default sort conditions defined for the columns
     *
     * @return array
     */
    public function getSortConditionsDefault()
    {
        $sortConditions = array();
        foreach ($this->getColumns() as $column) {
            /* @var $column \ZfcDatagrid\Column\AbstractColumn */
            if ($column->hasSortDefault() === true) {
                $sortDefaults = $column->getSortDefault();

                $sortConditions[$sortDefaults['priority']] = array(
                    'column' => $column,
                    'sortDirection' => $sortDefaults['sortDirection']
                );

                $column->setSortActive($sortDefaults['sortDirection']);
            }
        }

        ksort($sortConditions);

        return $sortConditions;
    }

    /**
     * Set filters explicit (e.g.
     * from a custom form)
     *
     * @param array $filters
     */
    public function setFilters(array $filters)
    {
        foreach ($filters as $filter) {
            if (! $filter instanceof Filter) {
                throw new InvalidArgumentException('Filter have to be an instanceof ZfcDatagrid\Filter');
            }
        }

        $this->filters = $filters;
    }

    /**
     *
     * @return array
     */
    public function getFilters($keepCache=false)
    {
        
        //var_dump($this->filters);
        if (is_array($this->filters)) {
            return $this->filters;
        }


        if ($keepCache || $this->isExport() === true || $this->isCrud) {
            // Export renderer should always retrieve the filters from cache!
            $this->filters = $this->getCacheFilters();
            if(is_array($this->filters))
                return $this->filters;
        }
        $this->filters = $this->getFiltersDefault();

        return $this->filters;
    }

    /**
     * Get the default filter conditions defined for the columns
     *
     * @return array
     */
    public function getFiltersDefault()
    {
        $filters = array();

        // @todo skip this, if $grid->isUserFilterEnabled() ?

        if ($this->getRequest() instanceof ConsoleRequest || ($this->getRequest() instanceof HttpRequest && ! $this->getRequest()->isPost())) {

            foreach ($this->getColumns() as $column) {
                /* @var $column \ZfcDatagrid\Column\AbstractColumn */
                if ($column->hasFilterDefaultValue() === true) {

                    $filter = new Filter();
                    $filter->setFromColumn($column, $column->getFilterDefaultValue());
                    $filters[] = $filter;

                    $column->setFilterActive($filter->getDisplayColumnValue());
                }
            }
        }

        return $filters;
    }

    /**
     * Set the current page number
     *
     * @param integer $page
     */
    public function setCurrentPageNumber($page)
    {
        $this->currentPageNumber = (int) $page;
    }

    /**
     * Should be implemented for each renderer itself (just default)
     *
     * @return integer
     */
    public function getCurrentPageNumber()
    {
        if ($this->currentPageNumber === null) {
            $this->currentPageNumber = 1;
        }

        return (int) $this->currentPageNumber;
    }

    /**
     * Should be implemented for each renderer itself (just default)
     *
     * @return integer
     */
    public function getItemsPerPage($defaultItems = 25)
    {
        if ($this->isExport() === true) {
            return (int) - 1;
        }

        return $defaultItems;
    }

    /**
     * VERY UGLY DEPENDECY...
     *
     * @todo Refactor :-)
     *
     * @see \ZfcDatagrid\Renderer\RendererInterface::prepareViewModel()
     */
    public function prepareViewModel(Datagrid $grid)
    {
        $this->setDataGrid($grid);

        $viewModel = $this->getViewModel();
        $viewModel->setVariable('gridId', $grid->getId());
        $viewModel->setVariable('title', $this->getTitle());
        $viewModel->setVariable('parameters', $grid->getParameters());
        $viewModel->setVariable('overwriteUrl', $grid->getUrl());

        $viewModel->setVariable('templateToolbar', $this->getToolbarTemplate());
        $viewModel->setVariable('rendererName', $this->getName());

        $options = $this->getOptions();
        $generalParameterNames = $options['generalParameterNames'];
        $viewModel->setVariable('generalParameterNames', $generalParameterNames);

        $viewModel->setVariable('columns', $this->getColumns());

        $columnsHidden = array();
        foreach ($this->getColumns() as $column) {
            if ($column->isHidden()) {
                $columnsHidden[] = $column->getUniqueId();
            }
        }
        $viewModel->setVariable('columnsHidden', $columnsHidden);

        $viewModel->setVariable('rowStyles', $grid->getRowStyles());

        $viewModel->setVariable('paginator', $this->getPaginator());
        $viewModel->setVariable('data', $this->getData());
        $viewModel->setVariable('filters', $this->getFilters());

        $viewModel->setVariable('rowClickAction', $grid->getRowClickAction());
        $viewModel->setVariable('massActions', $grid->getMassActions());

        $viewModel->setVariable('isUserFilterEnabled', $grid->isUserFilterEnabled());

        //
        /*
         * renderer specific parameter names
         */
        $optionsRenderer = $this->getOptionsRenderer();
        $viewModel->setVariable('optionsRenderer', $optionsRenderer);
        if ($this->isExport() === false) {
            $parameterNames = $optionsRenderer['parameterNames'];
            $viewModel->setVariable('parameterNames', $parameterNames);

            $activeParameters = array();
            $activeParameters[$parameterNames['currentPage']] = $this->getCurrentPageNumber();
            {
                $sortColumns = array();
                $sortDirections = array();

                if(is_array($this->getSortConditions())){
                    foreach ($this->getSortConditions() as $sortCondition) {
                        $sortColumns[] = $sortCondition['column']->getUniqueId();
                        $sortDirections[] = $sortCondition['sortDirection'];
                    }
                }
                $activeParameters[$parameterNames['sortColumns']] = implode(',', $sortColumns);
                $activeParameters[$parameterNames['sortDirections']] = implode(',', $sortDirections);
            }
            $viewModel->setVariable('activeParameters', $activeParameters);
        }

        $viewModel->setVariable('exportRenderers', $grid->getExportRenderers());
    }

    public function getDataGrid()
    {
        return $this->dataGrid;
    }

    public function setDataGrid(Datagrid $dataGrid)
    {
        $this->dataGrid = $dataGrid;
        return $this;
    }

    protected function raiseError($message,$error){
        $e = $this->getMvcEvent();
        $application = $e->getApplication();
        $match = $e->getRouteMatch();
        $controller = $match->getParam('controller');
        $action     = $match->getParam('action');
        $e->setError($error)
        ->setParam('controller', $controller)
        ->setParam('action', $action)
        ->setParam('msg', $message);
        return $application->getEventManager()->trigger('dispatch.error', $e);
    }

    public function getFindException()
    {
        return $this->findException;
    }

    public function setFindException($findException)
    {
        $this->findException = $findException;
        return $this;
    }

    public function getReasonException($key)
    {
        return $this->reasonException[$key];
    }

    public function getReasonsException()
    {
        return $this->reasonException;
    }

    public function setReasonException($key,$reasonException)
    {
        $this->reasonException[$key] = $reasonException;
        return $this;
    }
    
    public function getUserInfo()
    {
        return $this->userInfo;
    }
    
    public function setUserInfo($userInfo)
    {
        $this->userInfo = $userInfo;
        return $this;
    }
    
    public function getDateTimeRender()
    {
        return $this->dateTimeRender;
    }
    
    public function setDateTimeRender($dateTimeRender)
    {
        $this->dateTimeRender = $dateTimeRender;
        return $this;
    }
    



}
