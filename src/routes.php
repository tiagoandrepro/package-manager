<?php

Route::group(['prefix' => 'packages', 'middleware'=>'web'], function (\Illuminate\Routing\Router $router) {

    $router->get('', fn() => \Inertia\Inertia::render('Manager::Index'));

});
