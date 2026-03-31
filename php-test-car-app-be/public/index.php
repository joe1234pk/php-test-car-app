<?php

require_once __DIR__ . '/../vendor/autoload.php';

try {
    (Dotenv\Dotenv::createImmutable(dirname(__DIR__)))->safeLoad();
} catch (Throwable $exception) {
}

$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->run();
