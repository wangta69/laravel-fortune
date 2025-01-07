<?php
Route::get('manse/{ymdhi}/{sl?}/{leap?}', array('uses'=>'ManseController@manse'))->name('manse');
Route::get('saju/{ymdhi}/{sl?}/{leap?}', array('uses'=>'SajuController@saju'))->name('saju');