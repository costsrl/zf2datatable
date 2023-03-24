<?php
namespace Zf2datatable\Zf2listener\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use CostBase\Service\Invokables\TableGateway;
use Interop\Container\ContainerInterface;
use Zf2datatable\Zf2Listener\DatasourceListenerAggregate;

class DatasourceListenerAggregateFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        $DatasourceListenerAggregate = new \Zf2datatable\Zf2listener\DatasourceListenerAggregate();
        $DatasourceListenerAggregate->setServiceLocator($container);
        return $DatasourceListenerAggregate;
    }
}

