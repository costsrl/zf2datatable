<?php
namespace Zf2datatable\Service;

//use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\EventManager\EventManager;
use Zf2datatable\Form\EventsForm as EventsForm;

class FormEventFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        $form = new EventsForm();
        $form->setServiceLocator($container);

        $appEventManager    = $container->get('EventManager');
        $shareEventManager  = $appEventManager->getSharedManager();
        $form->setEventManager(new EventManager($shareEventManager, array('Zf2datatable\EventsForm',get_class($form))));
        return $form;
    }
}

?>