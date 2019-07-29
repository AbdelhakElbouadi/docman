<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::redirect('/', 'documents');

Auth::routes();

//Route::get('/home', 'HomeController@index')->name('home');

Route::resource('documents', 'DocumentController')->middleware('auth');
Route::resource('reviews', 'ReviewController')->middleware('auth');

Route::post('documents/getDeptsAjax', 'DocumentController@deptsAjax');

Route::post('getDeptUsersAjax', 'DocumentController@deptUsersAjax')->name('deptUsers');

Route::get('mydocs', 'DocumentController@getMydocs')->name('mydocs')->middleware('auth');

Route::post('reupload', 'DocumentController@reupload')->name('reupload');

Route::get('archives/{id}', 'DocumentController@documentHistory')->name('history')->middleware('auth');

Route::post('loadversion', 'DocumentController@loadVersion')->name('loadversion');

Route::post('deleteversion', 'DocumentController@deleteVersion')->name('deleteversion');

Route::get("notify", "DocumentController@notify")->name('notify')->middleware('auth');

Route::post("markRead", "DocumentController@markAsRead")->name("mark");

?>
