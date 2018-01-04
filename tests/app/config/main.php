<?php

$config = [
    'id' => 'yii2-softdelete',
    'name' => 'Yii2 SoftDelete',
    'basePath' => dirname(__DIR__),
    'vendorPath' => dirname(dirname(dirname(__DIR__))) . '/vendor',
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=test',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ],
    ],
];

$config['bootstrap'][] = 'gii';
$config['modules']['gii'] = [
    'class' => 'yii\gii\Module',
];

return $config;
