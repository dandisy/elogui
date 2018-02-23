<?php

Route::get('/getTableColumn', 'Webcore\Elogui\Controllers\ColumnController@index');
Route::resource('dataSources', 'Webcore\Elogui\Controllers\DataSourceController');
Route::resource('dataQueries', 'Webcore\Elogui\Controllers\DataQueryController');
Route::resource('columnAliases', 'Webcore\Elogui\Controllers\ColumnAliasController');