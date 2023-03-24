<?php
namespace Zf2datatable\DataSource\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Zf2datatable\DataSource\LaminasSelect;

class LaminasSelectFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        $LaminasSelect = new LaminasSelect();
        if(method_exists($LaminasSelect, "setEventManager")){
            $appEventManager    = $container->get('EventManager');
            $shareEventManager  = $appEventManager->getSharedManager();
            $eventManager       = new \Laminas\EventManager\EventManager($shareEventManager ,[__CLASS__]);
            $LaminasSelect->setServiceLocator($container);
            $LaminasSelect->setEventManager($eventManager);
        }
        return $LaminasSelect;
    } 
}

