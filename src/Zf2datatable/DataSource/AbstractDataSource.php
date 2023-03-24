<?php
namespace Zf2datatable\DataSource;

use Zf2datatable\Column;
use Zf2datatable\Filter;
use Laminas\Paginator\Adapter\AdapterInterface;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerInterface;
use Doctrine\Common\EventManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\ServiceLocatorAwareInterface;
use Laminas\EventManager\SharedEventManager;

abstract class AbstractDataSource implements DataSourceInterface, EventManagerAwareInterface
{

    /**
     *
     * @var array
     */
    protected $columns = array();

    /**
     *
     * @var array
     */
    protected $sortConditions = array();

    /**
     *
     * @var array
     */
    protected $filters = array();

    /**
     * The data result
     *
     * @var \Laminas\Paginator\Adapter\AdapterInterface
     */
    protected $paginatorAdapter;


    /**
     *
     * @var Laminas\EventManager\EventManagerInterface
     */
    protected $eventManager;


    /**
     *
     * @var Laminas\ServiceManager\Servicelocator
     */
    protected $serviceLocator;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $alias_table;

    /**
     * @var string
     */
    protected $entity;

    /**
     *
     * @var \Laminas\Cache\Storage\StorageInterface
     */
    protected $cache;

    /**
     *
     * @var array
     */
    protected $option = [];



    /**
     * @return the $option
     */
    public function getOption()
    {
        return $this->option;
    }

	/**
     * @param multitype: $option
     */
    public function setOption($option)
    {
        $this->option = $option;
    }

	/**
     * @return the $cache
     */
    public function getCache()
    {
        return $this->cache;
    }

	/**
     * @param \Laminas\Cache\Storage\StorageInterface $cache
     */
    public function setCache(\Laminas\Cache\Storage\StorageInterface $cache)
    {
        $this->cache = $cache;
    }

	/**
     * @return the $entity
     */
    public function getEntity()
    {
        return isset($this->entity) ? $this->entity : \ArrayObject::class;
    }

	/**
     * @param string $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

	/**
	 * @return the $eventManager
	 */
	public function getEventManager() {

	  if($this->eventManager == null){
			$this->eventManager = new \Laminas\EventManager\EventManager(null ,__CLASS__);
		}
		return $this->eventManager;
	}

	/**
	 * @param field_type $eventManager
	 */
	public function setEventManager(EventManagerInterface $eventManager) {
		$this->eventManager = $eventManager;
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
	 * @return the $table
	 */
	public function getTable() {
		return $this->table;
	}

	public function getTableAlias() {
	    return $this->alias_table;
	}

	/**
	 * @param string $table
	 */
	public function setTable($table,$alias_table) {
		$this->table = $table;
		$this->alias_table = $alias_table;
		return $this;
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
     *
     * @return Column\AbstractColumn[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Set sort conditions
     *
     * @param Column\AbstractColumn $column
     * @param string                $sortDirection
     */
    public function addSortCondition(Column\AbstractColumn $column, $sortDirection = 'ASC')
    {
        $this->sortConditions[] = array(
            'column' => $column,
            'sortDirection' => $sortDirection
        );
    }

    public function setSortConditions(array $sortConditions)
    {
        $this->sortConditions = $sortConditions;
    }

    /**
     *
     * @return array
     */
    public function getSortConditions()
    {
        return $this->sortConditions;
    }

    /**
     * Add a filter rule
     *
     * @param Filter $filter
     */
    public function addFilter(Filter $filter)
    {
        $this->filters[] = $filter;
    }

    public function setFilters(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     *
     * @return \ZfcDatagrid\Filter[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    public function setPaginatorAdapter(AdapterInterface $paginator)
    {
        $this->paginatorAdapter = $paginator;
    }

    /**
     *
     * @return \Laminas\Paginator\Adapter\AdapterInterface
     */
    public function getPaginatorAdapter()
    {
        return $this->paginatorAdapter;
    }

    protected function _init(){
        // to implemet
    }


    abstract public function getDefaultBindObject();


}
