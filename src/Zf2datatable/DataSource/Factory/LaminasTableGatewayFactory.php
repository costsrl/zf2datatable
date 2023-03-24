<?php
namespace Zf2datatable\DataSource\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Zf2datatable\DataSource\LaminasTableGateway;

class LaminasTableGatewayFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        $LaminasTableGateway = new LaminasTableGateway();
        
        if(method_exists($LaminasTableGateway, "setEventManager")){
           $appEventManager    = $container->get('EventManager');
            $shareEventManager  = $appEventManager->getSharedManager();
            $eventManager       = new \Laminas\EventManager\EventManager($shareEventManager ,[__CLASS__]);
            $LaminasTableGateway->setEventManager($eventManager);
            $LaminasTableGateway->setServiceLocator($container);
        }
        
       
        return $LaminasTableGateway;
    }
}

