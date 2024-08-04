<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('pages.dashboard');
// });


Route::get('/dashboard', function () {
    return view('pages.dashboard', ['type_menu' => '']);
});
