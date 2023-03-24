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

class LoggerServiceFactory implements FactoryInterface{

	public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
	    // TODO: Auto-generated method stub
	    $params=$container->get('logger_config');
	    $path_filename = $params['path_filename'];
	    $file_name = $params['log_filename'];
	    $priority =  $params['priority'];

	    $pathFile = ($params['path_filename']) ? $params['path_filename'] : realpath(dirname(__FILE__)."/../../../../../../data/logs");
	    $writer = new Writer\Stream($pathFile.DIRECTORY_SEPARATOR.$params['log_filename'].".txt","a",null,511);

	    $formatter = new Formatter\Simple('%timestamp% | %message%');
	    $filter	= new Filter\Priority((int) $priority);
	    $writer->setFormatter($formatter);
	    $writer->addFilter($filter);

	    $logger = new Logger();
	    $logger->addWriter($writer);


	    return $logger;
	}

}

?>