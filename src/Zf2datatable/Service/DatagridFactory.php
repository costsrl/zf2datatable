<?php
namespace Zf2datatable\Service;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\EventManager\EventManager;
use Zf2datatable\Datagrid;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Doctrine\Common\EventManager as DoctrineEventManager;

class DatagridFactory implements FactoryInterface
{

     public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');


        if (! isset($config['zf2datatable'])) {
            throw new InvalidArgumentException('Config key "zf2datatable" is missing');
        }

        /* @var $application \Laminas\Mvc\Application */
        $application            = $container->get('application');
        $eventManager           = $application->getEventManager();
        $shareEventManager      = $eventManager->getSharedManager();

        $grid = new Datagrid();
        \Zf2datatable\Datagrid::setInstanceNumber();
        $grid->setOptions($config['zf2datatable']);
        $grid->setMvcEvent($application->getMvcEvent());
        $grid->setServiceLocator($container);
        $grid->setEventManager(new EventManager($shareEventManager, array(\Zf2datatable\Datagrid::class,get_class($grid))));
        
        /** injection acl **/
        if($container->has('aclDoctrine')){
            $grid->setAcl($container->get('aclDoctrine'));
        }
        elseif($container->has('aclArray')){
            $grid->setAcl($container->get('aclArray'));
        }
        elseif($container->has('acl')){
             $grid->setAcl($container->get('acl'));
        }

        if ($container->has('translator') === true) {
                $grid->setTranslator($container->get('translator'));
        }

        if ($container->has('zf2datatable_logger') === true) {
            $grid->setLogger($container->get('zf2datatable_logger'));
        }

        $grid->init();
        return $grid;
    }
}
