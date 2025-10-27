<?php
Route::get('/', 'HomeController@index')->name('home');
Route::POST('/contact-submit', 'HomeController@contactSubmit')->name('contactSubmit');
Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    return redirect('/')->with('success', trans('messages.success'));
 })->name('clear-cache');
