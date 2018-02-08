<?php

Route::get('/getTableColumn', 'ColumnController@index');

/**
 * Support select some colums in join using query builder
 * join using eager loading (with) not yet support select some colums in related model
 */
Route::get('/page/{slug}', 'FrontController@index');