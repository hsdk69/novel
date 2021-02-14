<?php
use think\facade\Route;

Route::rule('/'.BOOKCTRL.'/:id', 'books/index');
Route::rule('/'.BOOKLISTACT, 'booklist/index');
Route::rule('/getBooks', 'booklist/getBooks');
Route::rule('/getOptions', 'booklist/getOptions');
Route::rule('/getCates', 'booklist/getCates');
Route::rule('/getRanks', 'rank/getRanks');
Route::rule('/'.CHAPTERCTRL.'/:id', 'chapters/index');
Route::rule('/'.SEARCHCTRL.'/[:keyword]', 'index/search');
Route::rule('/'.RANKCTRL, 'rank/index');
Route::rule('/'.UPDATEACT, 'update/index');
Route::rule('/getUpdate', 'update/getBooks');
Route::rule('/'.AUTHORCTRL.'/:id', 'authors/index');
Route::rule('/'.TAGCTRL.'/:id', 'tag/index');
Route::rule('/addfavor', 'books/addfavor');
Route::rule('/commentadd', 'books/commentadd');
Route::rule('/login', 'account/login');
Route::rule('/register', 'account/register');
Route::rule('/logout', 'account/logout');
Route::rule('/taillist', 'tails/list');
Route::rule('/tail/:id', 'tails/index');

Route::rule('/ucenter', 'users/ucenter');
Route::rule('/bookshelf', 'users/bookshelf');
Route::rule('/getfavors', 'users/getfavors');
Route::rule('/history', 'users/history');
Route::rule('/userinfo', 'users/userinfo');
Route::rule('/delfavors', 'users/delfavors');
Route::rule('/delhistory', 'users/delhistory');
Route::rule('/updateUserinfo', 'users/update');
Route::rule('/bindphone', 'users/bindphone');
Route::rule('/userphone', 'users/userphone');
Route::rule('/sendcms', 'users/sendcms');
Route::rule('/verifyphone', 'users/verifyphone');
Route::rule('/recovery', 'account/recovery');
Route::rule('/resetpwd', 'users/resetpwd');
