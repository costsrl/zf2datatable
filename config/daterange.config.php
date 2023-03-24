<?php
return array(
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
                         'locale' => 'it_IT',
                        'format' => 'DD/MM/YYYY HH:mm:ss',
                        'timePicker'=> true,
                    )
                )
            )
        )
    )
);
