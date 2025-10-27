<?php

Route::group(['namespace' => 'Frontend'], function () {

    // Single Page 
    Route::get('/page/contact-us', 'HomeController@ContactUsPage')->name('contact-us');
    Route::post('/page/post-contact-us', 'HomeController@submitContactUsPage')->name('post-contact-us');
    Route::get('/page/{slug}', 'HomeController@SinglePage')->name('single-page');
    
    

});

