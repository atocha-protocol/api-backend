<?php

use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

//Route::get('atocha/register', 'App\Http\Controllers\Admin\Auth\RegisterController@register')->name('backpack.auth.register');
//Route::get('atocha/register', 'App\Http\Controllers\Admin\Auth\RegisterController@showRegistrationForm')->name('backpack.auth.register');
Route::post('atocha/register', 'App\Http\Controllers\Admin\Auth\RegisterController@register');

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('user', 'UserCrudController');
    Route::crud('task-request', 'TaskRequestCrudController');
    Route::crud('task-reward', 'TaskRewardCrudController');
}); // this should be the absolute last line of this file


