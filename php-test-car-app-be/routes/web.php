<?php

$router->get('/health', function () {
    return response()->json(['status' => 'ok']);
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->group(['prefix' => 'sync'], function () use ($router) {
        $router->post('/cars', 'App\Http\Controllers\SyncController@syncCars');
        $router->post('/quotes', 'App\Http\Controllers\SyncController@syncQuotesForAllCars');
        $router->post('/quotes/{carId:[0-9]+}', 'App\Http\Controllers\SyncController@syncQuotesForCar');
    });

    $router->group(['prefix' => 'cars'], function () use ($router) {
        $router->get('/', 'App\Http\Controllers\CarController@index');
        $router->get('/{carId:[0-9]+}', 'App\Http\Controllers\CarController@show');
        $router->get('/{carId:[0-9]+}/quotes', 'App\Http\Controllers\CarController@quotes');
    });
});
