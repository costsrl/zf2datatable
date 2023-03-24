<?php
namespace Zf2datatable\DoctrineListener\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Zf2datatable\DoctrineListener\Zf2datatablelistener;

class Zf2datatablelistenerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        $Zf2datatablelistener = new Zf2datatablelistener();
        $Zf2datatablelistener->setServiceLocator($container);
        return $Zf2datatablelistener;
    }
}

