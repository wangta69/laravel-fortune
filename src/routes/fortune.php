<?php
Route::get('manse/{ymdhi}/{sl?}/{leap?}', array('uses'=>'ManseController@manse'))->name('manse');
Route::get('saju/{ymdhi}/{sl?}/{leap?}', array('uses'=>'SajuController@saju'))->name('saju');
Route::get('tojung/{ymdhi}/{sl?}/{leap?}', array('uses'=>'TojungController@create'))->name('tojung');