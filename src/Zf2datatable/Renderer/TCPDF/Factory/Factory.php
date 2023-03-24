<?php
namespace Zf2datatable\Renderer\TCPDF\Factory;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Zf2datatable\DataSource\LaminasTableGateway;
use Zf2datatable\Renderer\TCPDF\Renderer;

class Factory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
       return new Renderer();
    }
}