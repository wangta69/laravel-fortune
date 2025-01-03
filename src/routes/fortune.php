<?php
Route::get('manse/{ymd}/{sl?}/{leap?}', array('uses'=>'ManseController@manse'))->name('manse');