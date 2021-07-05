<?php
namespace App;

require_once __DIR__ . '/vendor/autoload.php';
use App\Parser;

$parser = new Parser('https://www.electronictoolbox.com/categories/');
$parser->createData();
