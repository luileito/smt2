<?php

require_once(dirname(__FILE__) . '/../lib/PHPProxy.class.php');

$name = $_POST['name'];
$value = $_POST['value'];

$proxy = new PHPProxy(NULL);
$proxy->setPref($name, $value);


?>