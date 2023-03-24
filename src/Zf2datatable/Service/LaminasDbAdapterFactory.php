<?php
namespace Zf2datatable\Service;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Db\Adapter\Adapter;

class LaminasDbAdapterFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        $defaultAdapter = $container->get(AdapterInterface::class);
        if($defaultAdapter instanceof AdapterInterface){
            return $defaultAdapter;
        }

        $config = $container->get('config');
        return new Adapter($config['zf2datatable_dbAdapter']);
    }

}
