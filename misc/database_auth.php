<?php
    // Local
    define('host','localhost');
    define('username','root');
    define('password','');
    define('dbName','nla_kpi');
    
    // Remote
    // define('host','localhost');
    // define('username','root');
    // define('password','');
    // define('dbName','nla-kpi');

    $con = new mysqli(host,username,password,dbName);
?>