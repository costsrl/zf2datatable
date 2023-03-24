<?php
namespace Zf2datatable\Service;

//use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Session\SessionManager;
use Laminas\Session\Config\SessionConfig;
use Zf2datatable\Session\Zf2Container;
use Interop\Container\ContainerInterface;

class SessionServiceFactory implements FactoryInterface{

	/**
	 * @var string
	 */
	const CONTAINER ='zf2SessionContainer';

	public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
	    $config = $container->get('custom_namespace');
	    if(isset($config['container'])){
	        $container = $config['container'];
	    }
	    else
	        $container = self::CONTAINER;
	    
	        return new Zf2Container($container);
	}

}
?>