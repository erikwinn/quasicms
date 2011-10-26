<?php
//define('QUASICMS',1);

//require('../quasi_config.php');
require('../Quasi.class.php');
//require('OrderPrinter.class.php');
$objOrder = Order::Load(6609);
$objPrinter = new OrderPrinter($objOrder);
$objPrinter->PrintInvoice();

?>