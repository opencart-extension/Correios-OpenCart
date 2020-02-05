<?php

require('correios/autoload.php');

$box = new \ValdeirPsr\Correios\Box;
$box->setWeight(PHP_INT_MAX);


var_dump(number_format(PHP_INT_MAX, 2, ".", ""), $box->getWeight());