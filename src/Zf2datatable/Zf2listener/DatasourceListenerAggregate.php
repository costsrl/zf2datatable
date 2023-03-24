<?php
namespace Zf2datatable\Zf2listener;

use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\EventInterface;
use Laminas\ServiceManager\ServiceLocatorInterface as ServiceLocatorInterface;

class DatasourceListenerAggregate{

    protected $listeners = array();
    protected $serviceLocator = null;

    /**
	/* (non-PHPdoc)
	 * @see \Laminas\ServiceManager\ServiceLocatorAwareInterface::setServiceLocator()
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
		// TODO: Auto-generated method stub
	    $this->serviceLocator = $serviceLocator;
	}


    /**
	/* (non-PHPdoc)
	 * @see \Laminas\ServiceManager\ServiceLocatorAwareInterface::getServiceLocator()
	 */
	public function getServiceLocator() {
		// TODO: Auto-generated method stub
	    return $this->serviceLocator;
	}

	/**
	 * @param \Laminas\EventManager\SharedEventManagerInterface $e
	 */
	public function attachShared(\Laminas\EventManager\SharedEventManagerInterface $e)
	{
	    if($this->getServiceLocator()->has ( 'zf2datatable_logger' )){
	            $logger  = $this->getServiceLocator()->get ( 'zf2datatable_logger' );
        	    $adapter = $this->getServiceLocator()->get ( 'zf2datatable_adapter' );
				$services = $this->getServiceLocator();

        	    // tablegateway datasource
        	    $this->listeners[] =$e->attach('Laminas\Db\TableGateway\TableGateway', 'preSelect', function ($e) use($logger, $adapter) {
        			$p = $e->getParams ();
        			$platform = $adapter->getPlatform ();
        			if ($p ['select'] instanceof \Laminas\Db\Sql\Select) {
        				$logger->info ( ' sql=' . $p ['select']->getSqlString ( $platform ) );
        			}
        		}, 100 );


        	    $this->listeners[] =$e->attach('Laminas\Db\TableGateway\TableGateway', 'preInsert', function ($e) use($logger, $adapter) {
        			$p = $e->getParams ();
        			$platform = $adapter->getPlatform ();
        			if ($p ['insert'] instanceof \Laminas\Db\Sql\Insert) {
        				$logger->info ( ' sql=' . $p ['insert']->getSqlString ( $platform ) );
        				// echo ' sql='.$p['insert']->getSqlString($platform);
        			}
        		}, 100 );


        	   $this->listeners[] =$e->attach('Laminas\Db\TableGateway\TableGateway', 'preUpdate', function ($e) use($logger, $adapter) {
        			$p = $e->getParams ();
        			$platform = $adapter->getPlatform ();
        			if ($p ['update'] instanceof \Laminas\Db\Sql\Update) {
        				$logger->info ( ' sql=' . $p ['update']->getSqlString ( $platform ) );

        			}
        		}, 100 );

        	   $this->listeners[] = $e->attach('Laminas\Db\TableGateway\TableGateway', 'preDelete', function ($e) use($logger, $adapter) {
        			$p = $e->getParams ();
        			$platform = $adapter->getPlatform ();
        			if ($p ['delete'] instanceof \Laminas\Db\Sql\Delete) {
        				$logger->info ( ' sql=' . $p ['delete']->getSqlString ( $platform ) );
        			}
        		}, 100 );


        	  // abstract datasource
        	  $this->listeners[] = $e->attach('Zf2datatable\DataSource\AbstractDataSource', 'pre.update', function ($event) use($services) {
                        $target = $event->getTarget ();
                        $params = $event->getParams();
                        // to do
                        return $params;
                    });

        	  $this->listeners[] = $e->attach('Zf2datatable\DataSource\AbstractDataSource', 'post.update', function ($event) use($services) {
                    $target = $event->getTarget ();
                    $params = $event->getParams();
                    // to do
                    return $params;
                  });

              $this->listeners[] = $e->attach('Zf2datatable\DataSource\AbstractDataSource', 'pre.insert', function ($event) use($services) {
                        $target = $event->getTarget ();
                        $params = $event->getParams();
                        // to do
                        return $params;
                    });

              $this->listeners[] = $e->attach('Zf2datatable\DataSource\AbstractDataSource', 'post.insert', function ($event) use($services) {
                      $target = $event->getTarget ();
                      $params = $event->getParams();
                       // to do
                      return $params;
                   });
	    }

	}


    public function detachShared(\Laminas\EventManager\SharedEventManagerInterface $e)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($e->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

}



?>