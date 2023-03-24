<?php
namespace Zf2datatable;

use Laminas\ServiceManager\Proxy\LazyServiceFactory;
use Zf2datatable\DataSource\Factory\LaminasSelectFactory;
use Zf2datatable\DataSource\Factory\LaminasTableGatewayFactory;
use Zf2datatable\DoctrineListener\Factory\Zf2datatablelistenerFactory;
use Zf2datatable\Form\View\Helper\FormCKEditor;
use Zf2datatable\Renderer\BootstrapTable\View\Helper\TableAggregate;
use Zf2datatable\Renderer\BootstrapTable\View\Helper\TableRow;
use Zf2datatable\Renderer\PHPExcel\Factory\Factory as FactoryPhpExcel;
use Zf2datatable\Renderer\TCPDF\Factory\Factory as FactoryPhpTCPDF;
use Zf2datatable\Renderer\PrintHtml\Factory\Factory as FactoryPrintHtml;
use Zf2datatable\Renderer\LaminasTable\Factory\Factory as FactoryLaminasTable;
use Zf2datatable\Renderer\Csv\Factory\Factory as FactoryCsv;
use Zf2datatable\Service\DatagridFactory;
use Zf2datatable\Service\Doctrine2ServiceFactory;
use Zf2datatable\Service\FormEventFactory;
use Zf2datatable\Service\LaminasDbAdapterFactory;
use Zf2datatable\Service\LoggerServiceFactory;
use Zf2datatable\Service\SessionServiceFactory;
use Zf2datatable\Zf2listener\Factory\DatasourceListenerAggregateFactory;

return array(
    'zf2datatable' => array(
        'settings' => array(
            'default' =>[
                // If no specific rendere given, use this renderes for HTTP / console
                'renderer' => [
                    'http' => 'bootstrapTable',
                    'console' => 'LaminasTable'
                ]
            ],
            'export' => array(
                // Export is enabled?
                'enabled' => true,
                'formats' => [
                    'PHPExcel' => 'Excel',
                    //'printHtml' => 'Print',
                    //'tcpdf' => 'PDF',
                    'csv' => 'CSV'
                ],

                // type => Displayname (Toolbar - you can use here HTML too...)
                // 'printHtml' => 'Print',
                // 'tcpdf' => 'PDF',
                // 'csv' => 'CSV',
                // 'PHPExcel' => 'Excel',
                // The output+save directory
                'path' => realpath(dirname(__FILE__)."/../../../../data"),
                // mode can be:
                // direct = PHP handles header + file reading
                // @TODO iframe = PHP generates the file and a hidden <iframe> sends the document (ATTENTION: your webserver must enable "force-download" for excel/pdf/...)
                'mode' => 'direct'
            )
        ),
        // The cache is used to save the filter + sort and other things for exporting
//        'cache-datatablefilter' => array(
//            'adapter' => array(
//                'name' => 'filesystem',
//                'options' => array(
//                    'ttl' => 100000, // cache with 200 hours,
//                    'cache_dir' => realpath(dirname(__FILE__) . '/../../../data/cache/')
//                )
//            ),
//            'plugins' => array(
//                'exception_handler' => array(
//                    'throw_exceptions' => false
//                ),
//                'Serializer'
//            )
//        ),
        'metadata_cache' => array(
            'adapter' => array(
                'name' => 'Filesystem',
                'options' => array(
                    'ttl' => 80000000, // cache with 200 hours,
                    'cache_dir' => realpath(dirname(__FILE__) . '/../../../../data/cache/metadata')
                )
            ),
            'plugins' => array(
                'exception_handler' => array(
                    'throw_exceptions' => false
                ),
                'Serializer'
            )
        ),
        /*'cache' => array(
            'adapter' => array(
                'name' => 'Filesystem',
                'options' => array(
                    'ttl' => 100000, // cache with 200 hours,
                    'cache_dir' => realpath(dirname(__FILE__) . '/../../../data')
                )
            ),
            'plugins' => array(
                'exception_handler' => array(
                    'throw_exceptions' => false
                ),
                'Serializer'
            )
        ),*/
        'renderer' => array(
            'bootstrapTable' => array(
                'parameterNames' => array(
                    // Internal => bootstrapTable
                    'currentPage' => 'currentPage',
                    'sortColumns' => 'sortByColumns',
                    'sortDirections' => 'sortDirections',
                    'massIds' => 'ids'
                ),
                'templatesOverwrite'=>array(
                    'layout'=>null,
                    'detail'=>null,
                    'formcrud'=>[
                        'path'=>'zf2datatable/renderer/bootstrapTable',
                        'name' => 'formcrud2'
                    ]
                    //'formcrud'=>'formcrud'   //zf2datatable/renderer/bootstrapTable/zf2datatable/renderer/bootstrapTable
                )
            ),
            'jqGrid' => array(
                'parameterNames' => array(

                    // Internal => jqGrid
                    'currentPage' => 'currentPage',
                    'itemsPerPage' => 'itemsPerPage',
                    'sortColumns' => 'sortByColumns',
                    'sortDirections' => 'sortDirections',
                    'isSearch' => 'isSearch',

                    'columnsHidden' => 'columnsHidden',
                    'columnsGroupByLocal' => 'columnsGroupBy',

                    'massIds' => 'ids'
                )
            ),
            'LaminasTable' => array(
                'parameterNames' => array(
                    // Internal => LaminasTable (console)
                    'currentPage' => 'page',
                    'itemsPerPage' => 'items',
                    'sortColumns' => 'sortBys',
                    'sortDirections' => 'sortDirs',
                    'filterColumns' => 'filterBys',
                    'filterValues' => 'filterValues'
                )
            ),

            'PHPExcel' => array(
                'papersize' => 'A4',
                // landscape / portrait (we preferr landscape, because datagrids are often wide)
                'orientation' => 'landscape',
                // The worksheet name (will be translated if possible)
                'sheetName' => 'Data',
                // If you only want to export data, set this to false
                'displayTitle' => true,
                'rowTitle' => 1,
                'startRowData' => 3
            ),

            'TCPDF' => array(
                'papersize' => 'A4',
                // landscape / portrait (we preferr landscape, because datagrids are often wide)
                'orientation' => 'landscape',
                'margins' => array(
                    'header' => 5,
                    'footer' => 10,

                    'top' => 20,
                    'bottom' => 11,
                    'left' => 10,
                    'right' => 10
                ),
                'icon' => array(
                    // milimeter...
                    'size' => 16
                ),
                'header' => array(
                    // define your logo here, please be aware of the relative path...
                    'logo' => '',
                    'logoWidth' => 35
                ),
                'style' => array(
                    'header' => array(
                        'font' => 'helvetica',
                        'size' => 11,
                        'color' => array(
                            0,
                            0,
                            0
                        ),
                        'background-color' => array(
                            255,
                            255,
                            200
                        )
                    ),
                    'data' => array(
                        'font' => 'helvetica',
                        'size' => 11,
                        'color' => array(
                            0,
                            0,
                            0
                        ),
                        'background-color' => array(
                            255,
                            255,
                            255
                        )
                    )
                )
            ),

            'csv' => array(
                // draw a header with all column labels?
                'header' => true,
                'delimiter' => ',',
                'enclosure' => '"'
            )
        ),
        'form' => array(
            'elements' => array(
                'DateTimePicker' => array(
                    'status' => 'enabled',
                    'options' => array(
                        'datetime_format_in' => 'd-m-Y H:i',
                        'datetime_format_out' => 'Y-m-d H:i:s',
                        'datetime_format_mask_in' => '/^\d{2}-\d{2}-\d{4} [0-2][0-3]:[0-5][0-9]:[0-5][0-9]$/',
                        'datetime_format_mask_out' => '/^\d{4}-\d{2}-\d{2} [0-2][0-3]:[0-5][0-9]:[0-5][0-9]$/',
                        'datetime_js_properties' => 'language:\'it\'',
                        'date_format_in' => 'd-m-Y',
                        'date_format_out' => 'Y-m-d',
                        'date_format_mask_in' => '/^\d{1,2}-\d{1,2}-\d{4}$/',
                        'date_format_mask_out' => '/^\d{4}-\d{1,2}-\d{1,2}$/',
                        'date_js_properties' => 'language:\'it\',pickTime: false'
                    )
                )
            )
        ),
        // General parameters
        'generalParameterNames' =>[
            'rendererType' => 'rendererType'
        ]
    ),

    'service_manager' =>[
            'invokables' =>[
                // HTML renderer
                'Zf2datatable.renderer.bootstrapTable'      => 'Zf2datatable\Renderer\BootstrapTable\Renderer',
                'Zf2datatable.renderer.bootstrapTable.form' => 'Zf2datatable\Renderer\BootstrapTable\FormRenderer',
                'Zf2datatable.renderer.bootstrapTable.view' => 'Zf2datatable\Renderer\BootstrapTable\ViewRenderer',
                'Zf2datatable.renderer.jqgrid'              => 'Zf2datatable\Renderer\JqGrid\Renderer',

                // CLI renderer
                'Zf2datatable.renderer.LaminasTable' => 'Zf2datatable\Renderer\LaminasTable\Renderer',

                // Export renderer
                /*'Zf2datatable.renderer.printHtml' => 'Zf2datatable\Renderer\PrintHtml\Renderer',
                'Zf2datatable.renderer.PHPExcel' => 'Zf2datatable\Renderer\PHPExcel\Renderer',
                'Zf2datatable.renderer.tcpdf' => 'Zf2datatable\Renderer\TCPDF\Renderer',
                'Zf2datatable.renderer.csv' => 'Zf2datatable\Renderer\Csv\Renderer',*/


                // Datasources example
                'zf2datatable.examples.data.phpArray' => 'Zf2datatable\Examples\Data\PhpArray',
                'zf2datatable.examples.data.doctrine2' => 'Zf2datatable\Examples\Data\Doctrine2',
                'zf2datatable.examples.data.LaminasSelect' => 'Zf2datatable\Examples\Data\LaminasSelect',
                'zf2datatable.examples.data.LaminasTableGateway' => 'Zf2datatable\Examples\Data\LaminasTableGateway',

                // FORM
                //'zf2datatable.form' => 'Zf2datatable\Form\EventsForm',

                //SERVICE INVOKABLES


                //LISTENER  AGGREGATE
                //'zf2datatable.listener'=>'\Zf2datatable\Zf2listener\DatasourceListenerAggregate'
            ],
            'factories' => [
                'zf2datatable\Datagrid'                         => DatagridFactory::class,
                'zf2datatable_adapter'                          => LaminasDbAdapterFactory::class,
                'zf2datatable_logger'                           => LoggerServiceFactory::class,
                'zf2datatable_doctrine_listener'                => Zf2datatablelistenerFactory::class,
                'zf2session_container'                          => SessionServiceFactory::class,
                'zf2datatable.form'                             => FormEventFactory::class,

                //LISTENER  AGGREGATE
                'zf2datatable.listener'                         => DatasourceListenerAggregateFactory::class,

                // Datasource
                'Zf2datatable.datasource.LaminasTableSelect'   => LaminasTableGatewayFactory::class,
                'Zf2datatable.datasource.LaminasSelect'        => LaminasSelectFactory::class,

                //DOctrine Service
                'doctrine2service'                              => Doctrine2ServiceFactory::class,

                // Export renderer
                'Zf2datatable.renderer.PHPExcel'                => FactoryPhpExcel::class,
                'Zf2datatable.renderer.tcpdf'                   => FactoryPhpTCPDF::class,
                'Zf2datatable.renderer.printHtml'               => FactoryPrintHtml::class,
                'Zf2datatable.renderer.csv'                     => FactoryCsv::class,

                // CLI renderer
                'Zf2datatable.renderer.LaminasTable'          => FactoryLaminasTable::class
            ],
        'lazy_services' => [
            'class_map' => [
//                'Zf2datatable.renderer.tcpdf'                   => FactoryPhpTCPDF::class,
//                'Zf2datatable.renderer.PHPExcel'                => FactoryPhpExcel::class,
            ]
        ],
        'delegators' => [
//            'Zf2datatable.renderer.tcpdf' => [
//                LazyServiceFactory::class
//            ],
//            'Zf2datatable.renderer.PHPExcel' => [
//                LazyServiceFactory::class
//            ],
        ],
        'aliases' =>[
            'zf2datatablegrid' => 'zf2datatable\Datagrid'
        ],
        'services' => [
            'logger_config' =>[
                'path_filename' => realpath(dirname(__FILE__)."/../../../../data/logs"),
                'log_filename' => 'ZF2_LOGGER_' . date('ymd', time()),
                'priority' => '7'
            ],
            'cache_metadata'=>['mode'=>'enabled'],  // nembled -disabled
       ],
        'shared'=>[
            'zf2datatable\Datagrid'                         =>false,
            'Zf2datatable.renderer.bootstrapTable'          =>false,
            //'Zf2datatable.datasource.LaminasSelect'         =>false,
            'Zf2datatable.datasource.LaminasTableSelect'    =>false,
            'zf2datatable.form'                             =>false
        ]
    ],
    'view_helpers' =>[
        'invokables' => [
            'bootstrapTableRow' => TableRow::class,
            'bootstrapTableAggregate' => TableAggregate::class,
            'jqgridColumns'     => 'Zf2datatable\Renderer\JqGrid\View\Helper\Columns',
            'formckeditor'      => FormCKEditor::class
        ]
   ],
    'view_manager' => [
            'strategies' =>['ViewJsonStrategy'],
            'template_map' =>[
                'zf2datatable/renderer/bootstrapTable/layout' => __DIR__ . '/../view/zf2datatable/renderer/bootstrapTable/layout.phtml',
                'zf2datatable/renderer/printHtml/layout' => __DIR__ . '/../view/zf2datatable/renderer/printHtml/layout.phtml',
                'zf2datatable/renderer/printHtml/table' => __DIR__ . '/../view/zf2datatable/renderer/printHtml/table.phtml',
                'zf2datatable/renderer/jqGrid/layout' => __DIR__ . '/../view/zf2datatable/renderer/jqGrid/layout.phtml',
                'error/500' => __DIR__ . '/../view/zf2datatable/error/500.phtml',
            ],
            'template_path_stack' =>['Zf2datatable' => __DIR__ . '/../view']
    ],
    /**
     * ONLY EXAMPLE CONFIGURATION BELOW!!!!!!
     */
    'controllers' => array(
        'invokables' => array(
            'Zf2datatable\Examples\Controller\Person' => 'Zf2datatable\Examples\Controller\PersonController',
            'Zf2datatable\Examples\Controller\PersonDoctrine2' => 'Zf2datatable\Examples\Controller\PersonDoctrine2Controller',
            'Zf2datatable\Examples\Controller\PersonLaminas' => 'Zf2datatable\Examples\Controller\PersonLaminasController',
            'Zf2datatable\Examples\Controller\Minimal' => 'Zf2datatable\Examples\Controller\MinimalController',
            'Zf2datatable\Examples\Controller\Category' => 'Zf2datatable\Examples\Controller\CategoryController'
        )
    ),

    'router' => array(
        'routes' => array(
            'Zf2datatable' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/zf2datatable',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Zf2datatable\Examples\Controller',
                        'controller' => 'person',
                        'action' => 'bootstrap'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array()
                        ),

                        'may_terminate' => true,
                        'child_routes' => array(
                            'wildcard' => array(
                                'type' => 'Wildcard',
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'wildcard' => array(
                                        'type' => 'Wildcard'
                                    )
                                )
                            )
                        )
                    )
                )
            )
        )
    ),
    'console' => array(
        'router' => array(
            'routes' => array(
                'datagrid-example' => array(
                    'options' => array(
                        'route' => 'show grid [--page=] [--items=] [--filterBys=] [--filterValues=] [--sortBys=] [--sortDirs=] [--controller=] [--action=]',
                        'defaults' => array(
                            'controller' => 'AdminApplication\Controller\Index',
                            'action' => 'language'
                        )
                    )
                ),
                'datagrid-crud-example' => array(
                    'options' => array(
                        'route' => 'crud grid  [--crudOpt=] [--crudFieldValue=] [--controller=] [--action=]',
                        'defaults' => array(
                            'controller' => 'AdminApplication\Controller\Index',
                            'action' => 'console'
                        )
                    )
                )
            )
        )
    ),

    /**
     * The ZF2 DbAdapter + Doctrine2 connection is must for examples!
     */
    'zf2datatable_dbAdapter' => [],
    'doctrine' => [
        'connection' => array(
            /*'orm_zfcDatagrid' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOSqlite\Driver',
                'params' => array(
                    'charset' => 'utf8',
                    'path' => __DIR__ . '/../src/Zf2datatable/Examples/Data/examples.sqlite'
                )
            )*/
            'orm_zfcDatagrid' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                'params' => array(
                    'host'     => 'localhost',
                    'port'     => '3306',
                    'user'     => 'root',
                    'password' => 'poi890',
                    'dbname'   => 'derev',
                    'charset' => 'utf8', // extra
                    'driverOptions' => array(
                        1002=>'SET NAMES utf8'
                    )
                )
            )

        ),
        'configuration' => array(
            'orm_zfcDatagrid' => array(
                'metadata_cache' => 'array',
                'query_cache' => 'array',
                'result_cache' => 'array',
                'driver' => 'orm_zfcDatagrid',
                'generate_proxies' => true,
                'proxy_dir' => 'data/Zf2datatable/Proxy',
                'proxy_namespace' => 'Zf2datatable\Proxy',
                'filters' => array()
            )
        ),
        'driver' => array(
            'ZfcDatagrid_Driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(//__DIR__ . '/../../../FileUpload/src/FileUpload/Entity',
                )
            ),

            'orm_zfcDatagrid' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\DriverChain',
                'drivers' => array(//'FileUpload\Entity' => 'ZfcDatagrid_Driver',
                )
            )
        ),

        // now you define the entity manager configuration
        'entitymanager' => array(
            // This is the alternative config
            'orm_zfcDatagrid' => array(
                'connection' => 'orm_zfcDatagrid',
                'configuration' => 'orm_zfcDatagrid'
            )
        ),
        'eventmanager' => array(
            'orm_zfcDatagrid' => array(
                'subscribers' => array(
                    'Gedmo\Tree\TreeListener',
                    'Gedmo\Timestampable\TimestampableListener',
                    'Gedmo\Sluggable\SluggableListener',
                    'Gedmo\Loggable\LoggableListener',
                    'Gedmo\Sortable\SortableListener',
                    'Gedmo\IpTraceable\IpTraceableListener'
                ),
            )
        ),

        'sql_logger_collector' => array(
            'orm_zfcDatagrid' => array()
        ),

        'entity_resolver' => array(
            'orm_zfcDatagrid' => array()
        )
    ]
);
