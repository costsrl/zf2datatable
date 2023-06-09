<?php
namespace Zf2datatable;

use Laminas\Console\Adapter\AdapterInterface as Console;
use Laminas\Console\Request as ConsoleRequest;
use Laminas\Mvc\MvcEvent;
use Doctrine\Common\EventArgs;
use Zf2datatable\Session\Zf2Container;
use Zf2datatable\DoctrineListener\Zf2datatablelistener;
use Zf2datatable\Zf2Listener\DatasourceListenerAggregate;

class Module
{

    public function onBootstrap(MvcEvent $e)
    {
        $app = $e->getApplication();
        $eventManager = $app->getEventManager();
        $services = $e->getApplication()->getServiceManager();
        $shareEventManager = $eventManager->getSharedManager(); // The shared event manager


        /*
         * $logger = $services->get ( 'zf2datatable_logger' );
         * $adapter = $services->get ( 'zf2datatable_adapter' );
         */

        // listener

        //$eventManager->attach(new \Zf2datatable\Strategy\ExceptionStrategy());
        $aStrategyListener = new \Zf2datatable\Strategy\ExceptionStrategy();
        $aStrategyListener->attach($eventManager);


        if ($services->has('zf2datatable.listener')) {
            $listenerDataSource = $services->get('zf2datatable.listener');
            $listenerDataSource->attachShared($shareEventManager);
            //$shareEventManager->attachAggregate($listenerDataSource);
        }

        $modules = $services->get('ModuleManager')->getModules();
        if (in_array('DoctrineModule', $modules)) {

            // / event manager doctrine
            $dem = $services->get('doctrine.entitymanager.orm_zfcDatagrid');
            // $dem =$services->get('doctrine.entitymanager.orm_default');
            /** @var \Doctrine\Common\EventManager $dem */
            $dsem = $dem->getEventManager();

            $doctrineListener = $services->get('zf2datatable_doctrine_listener');

            // listener
            $dsem->addEventListener(array(
                \Doctrine\ORM\Events::postLoad,
                \Doctrine\ORM\Events::prePersist,
                \Doctrine\ORM\Events::postPersist,
                \Doctrine\ORM\Events::preUpdate,
                \Doctrine\ORM\Events::postUpdate,
                \Doctrine\ORM\Events::preRemove
            ), $doctrineListener);


            if (class_exists('\Gedmo\Loggable\LoggableListener')) {
                $cache = new \Doctrine\Common\Cache\ArrayCache();
                $annotationReader = new \Doctrine\Common\Annotations\AnnotationReader();
                $cachedAnnotationReader = new \Doctrine\Common\Annotations\CachedReader(
                    $annotationReader, // use reader
                    $cache // and a cache driver
                );
                $usernameLoggable = ($services->has('loggable_doctrine_extension') && $services->get('loggable_doctrine_extension')['username']) ?: 'Automation';
                $loggableListener = new \Gedmo\Loggable\LoggableListener();
                $loggableListener->setAnnotationReader($cachedAnnotationReader);

                if ($services->get('Laminas\Authentication\AuthenticationService')->getIdentity()) {
                    $loggableListener->setUsername($services->get('Laminas\Authentication\AuthenticationService')
                        ->getIdentity());
                } else {
                    $loggableListener->setUsername($usernameLoggable);
                }
                $dsem->addEventSubscriber($loggableListener);
            }

            if (class_exists('\Gedmo\Sluggable\SluggableListener')) {
                $dsem->addEventSubscriber(new \Gedmo\Sluggable\SluggableListener());
            }

            if (class_exists('\Gedmo\Sluggable\TranslatableListener')) {
                $dsem->addEventSubscriber(new \Gedmo\Translatable\TranslatableListener());
            }

            if (class_exists('\Gedmo\Sluggable\LoggableListener')) {
                $dsem->addEventSubscriber(new \Gedmo\Loggable\LoggableListener());
            }

            if (class_exists('\Gedmo\Sluggable\TimestampableListener')) {
                $dsem->addEventSubscriber(new \Gedmo\Timestampable\TimestampableListener());
            }

            if (class_exists('\Gedmo\Sluggable\IpTraceableListener')) {
                $dsem->addEventSubscriber(new \Gedmo\IpTraceable\IpTraceableListener());
            }

            // doctrine profile
            if ($services->has('doctrine-profiler')) {
                $profiler = $services->get('doctrine-profiler');
                $dem->getConfiguration()->setSQLLogger($profiler);
            }
        }
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Laminas\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/../../autoload_classmap.php'
            ),

            'Laminas\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
                )
            )
        );
    }

    public function getConfig()
    {
        $config = include __DIR__ . '/../../config/module.config.php';
        $configNoCache = array(
            'zf2datatable' => array(
                'renderer' => array(
                    'bootstrapTable' => array(
                        // Daterange bootstrapTable filter configuration example
                        'daterange' => array(
                            'enabled' => true,
                            'options' => array(
                                'ranges' => array(
                                    'Today' => new \Laminas\Json\Expr("[moment().startOf('day'), moment().endOf('day')]"),
                                    'Yesterday' => new \Laminas\Json\Expr("[moment().subtract('days', 1), moment().subtract('days', 1)]"),
                                    'Last 7 Days' => new \Laminas\Json\Expr("[moment().subtract('days', 6), moment()]"),
                                    'Last 30 Days' => new \Laminas\Json\Expr("[moment().subtract('days', 29), moment()]"),
                                    'This Month' => new \Laminas\Json\Expr("[moment().startOf('month'), moment().endOf('month')]"),
                                    'Last Month' => new \Laminas\Json\Expr("[moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]")
                                ),
                                'locale' => \Locale::getDefault(),
                                'format' => 'DD/MM/YYYY'
                            )
                        )
                    )
                ),
                'cache' => array(
                    'adapter' => array(
                        'name' => 'Session',
                        'options' => array(
                            'ttl' => 100000, // cache with 200 hours,
                            'session_container' => new Zf2Container('zfcache')
                        )
                    ),
                    'plugins' => array(
                        'exception_handler' => array(
                            'throw_exceptions' => false
                        ),
                        //'Serializer'
                    )
                ),
            )
        );

        return array_merge_recursive($config, $configNoCache);
    }

    public function getServiceConfig()
    {
        if (class_exists('DoctrineORMModule\Service\DBALConnectionFactory')) {
            return array(
                'factories' => array(
                    'doctrine.connection.orm_zfcDatagrid' => new \DoctrineORMModule\Service\DBALConnectionFactory('orm_zfcDatagrid'),
                    'doctrine.configuration.orm_zfcDatagrid' => new \DoctrineORMModule\Service\ConfigurationFactory('orm_zfcDatagrid'),
                    'doctrine.entitymanager.orm_zfcDatagrid' => new \DoctrineORMModule\Service\EntityManagerFactory('orm_zfcDatagrid'),
                    'doctrine.driver.orm_zfcDatagrid' => new \DoctrineModule\Service\DriverFactory('orm_zfcDatagrid'),
                    'doctrine.eventmanager.orm_zfcDatagrid' => new \DoctrineModule\Service\EventManagerFactory('orm_zfcDatagrid'),
                    'doctrine.entity_resolver.orm_zfcDatagrid' => new \DoctrineORMModule\Service\EntityResolverFactory('orm_zfcDatagrid'),
                    'doctrine.sql_logger_collector.orm_zfcDatagrid' => new \DoctrineORMModule\Service\SQLLoggerCollectorFactory('orm_zfcDatagrid')
                )
            );
        }
    }

    /**
     * formelement service
     */
    public function getFormElementConfig()
    {
        return array(
            'invokables' => array(
                'DateCalendar' => 'Zf2datatable\Form\Element\DateCalendar',
                'DateTimeCalendar' => 'Zf2datatable\Form\Element\DateTimeCalendar',
                'CKEditor' => 'Zf2datataable\Form\Element\CKEditor'
            )
        );
    }

    /**
     * view hwlper service
     */
    public function getViewHelperConfig()
    {
        return array(
            'invokables' => array(
                'zf2formdatecalendar' => 'Zf2datatable\Form\View\Helper\FormDateCalendar',
                'zf2formdatetimecalendar' => 'Zf2datatable\Form\View\Helper\FormDateTimeCalendar',
                'formckeditor' => 'Zf2datatable\Form\View\Helper\FormCKEditor'
            ),
            'shared' => array(
                'zf2formdatecalendar' => false
            )
        );
    }

    /**
     *
     * @return multitype:multitype:string
     */
    public function getFilterConfig()
    {
        return array(
            'invokables' => array(
                'DateCalendar' => 'Zf2datatable\Filter\DateCalendar',
                'DateTimeCalendar' => 'Zf2datatable\Filter\DateTimeCalendar'
            )
        );
    }

    /**
     *
     * @param Console $console
     * @return string
     */
    public function getConsoleBanner(Console $console)
    {
        $element = '*******************';
        $element .= 'zf2datatable 1.0.1';
        $element .= '*******************';
    }

    /**
     *
     * @param Console $console
     * @return multitype:string multitype:string
     */
    public function getConsoleUsage(Console $console)
    {
        return array(
            'Zf2datatable Command line',
            'show grid' => 'Show datagrid',
            'crud grid' => 'Execute grid operation',

            'Options:',
            array(
                '--controller=CONTROLLER',
                'optional controller name redirect '
            ),
            array(
                '--action=ACTION',
                'optional action name redirect '
            ),
            array(
                '--page=NUMBER',
                'Number of the page to display [1...n]'
            ),
            array(
                '--items=NUMBER',
                'How much items to display per page [1...n]'
            ),
            array(
                '--sortBys=COLUMN',
                'Unique id of the column(s) to sort (split with: ,)'
            ),
            array(
                '--sortDirs=DIRECTION',
                'Sort direction of the columns [ASC|DESC] (split with: ,)'
            ),
            array(
                '--crudOpt=INSERT',
                'Crud Option  [INSERT|UPDATE|DELETE] '
            ),
            array(
                '--crudFieldValue=FIELD1|VALUE1',
                'Crud Field|Values  [FIELD1|VALUE1;FIELD2|VALUE2] '
            )
        );
    }
}
