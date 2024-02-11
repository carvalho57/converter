<?php

use Converter\Converter;

require_once 'vendor/autoload.php';

$filename = 'files/csv-example.csv';


echo Converter::fromCsvToJson($filename);
