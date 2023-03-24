<?php
namespace Zf2datatable\Renderer\LaminasTable\Factory;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Zf2datatable\DataSource\LaminasTableGateway;
use Zf2datatable\Renderer\LaminasTable\Renderer;

class Factory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
       return new Renderer();
    }
}