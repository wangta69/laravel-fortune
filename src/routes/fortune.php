<?php
Route::get('manse/{ymdhi}/{sl?}/{leap?}', array('uses'=>'ManseController@manse'))->name('manse');
Route::get('saju/{ymdhi}/{sl?}/{leap?}', array('uses'=>'SajuController@saju'))->name('saju');
Route::get('tojung/{ymdhi}/{sl?}/{leap?}', array('uses'=>'TojungController@create'))->name('tojung');
Route::get('calendar/lunar/{yyyymm}/', array('uses'=>'CalendarController@lunar'))->name('calendar.lunar');
Route::get('calendar/season-24/{yyyy}/', array('uses'=>'CalendarController@season24'))->name('calendar.season24');
Route::get('calendar/move/{yyyymm}/', array('uses'=>'CalendarController@move'))->name('calendar.move');
Route::get('calendar/marriage/{yyyymm}/', array('uses'=>'CalendarController@marriage'))->name('calendar.marriage');
Route::get('calendar/samjae/{yyyy}/', array('uses'=>'CalendarController@samjae'))->name('calendar.samjae');


Route::get('term-update', array('uses'=>'TermController@update'))->name('term.update');
Route::get('term/{term}', array('uses'=>'TermController@term'))->name('term');