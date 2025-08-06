<?php
$container->set('db_settings', function(){
    return (object)[
        'host' => "localhost",
        'dbname' => "testv2",
        'user'=> "root",
        'password' => '',
    ];
});