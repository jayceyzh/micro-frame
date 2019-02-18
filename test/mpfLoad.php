<?php
require_once dirname(__DIR__) . '/src/core/Psr4AutoloaderClass.php';
mpf\core\Psr4AutoloaderClass::addNamespace('mpf',dirname(__DIR__) . '/src');
mpf\core\Psr4AutoloaderClass::register();