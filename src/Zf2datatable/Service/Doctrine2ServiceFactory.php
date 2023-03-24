<?php

namespace Zf2datatable\Service;
//use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Log\Logger;
use Laminas\Log\Writer;
use Laminas\Log\Filter;
use Laminas\Log\Formatter;
use Laminas\Log;

class Doctrine2ServiceFactory implements FactoryInterface{

	public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
	    // TODO: Auto-generated method stub
	    $Doctrine2Service = new Doctrine2Service();
	    $Doctrine2Service->setServiceLocator($container);
	    return $Doctrine2Service;
	}

}

?>