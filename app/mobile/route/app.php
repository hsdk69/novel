<?php
use think\facade\Route;

Route::rule('/'.BOOKCTRL.'/:id', 'mobile/books/index');
Route::rule('/'.BOOKLISTACT, 'mobile/booklist/index');
Route::rule('/getBooks', 'mobile/booklist/getBooks');
Route::rule('/getOptions', 'mobile/booklist/getOptions');
Route::rule('/getCates', 'index/booklist/getCates');
Route::rule('/getRanks', 'index/rank/getRanks');
Route::rule('/'.CHAPTERCTRL.'/:id', 'mobile/chapters/index');
Route::rule('/'.SEARCHCTRL.'/[:keyword]', 'mobile/index/search');
Route::rule('/'.RANKCTRL, 'mobile/rank/index');
Route::rule('/'.UPDATEACT, 'mobile/update/index');
Route::rule('/getUpdate', 'mobile/update/getBooks');
Route::rule('/'.AUTHORCTRL.'/:id', 'mobile/authors/index');
Route::rule('/'.TAGCTRL.'/:id', 'index/tag/index');
Route::rule('/addfavor', 'mobile/books/addfavor');
Route::rule('/commentadd', 'mobile/books/commentadd');
Route::rule('/login', 'mobile/account/login');
Route::rule('/register', 'mobile/account/register');
Route::rule('/logout', 'mobile/account/logout');
Route::rule('/taillist', 'index/tails/list');
Route::rule('/tail/:id', 'index/tails/index');

Route::rule('/ucenter', 'mobile/users/ucenter');
Route::rule('/bookshelf', 'mobile/users/bookshelf');
Route::rule('/getfavors', 'mobile/users/getfavors');
Route::rule('/history', 'mobile/users/history');
Route::rule('/userinfo', 'mobile/users/userinfo');
Route::rule('/delfavors', 'mobile/users/delfavors');
Route::rule('/delhistory', 'mobile/users/delhistory');
Route::rule('/updateUserinfo', 'mobile/users/update');
Route::rule('/bindphone', 'mobile/users/bindphone');
Route::rule('/userphone', 'mobile/users/userphone');
Route::rule('/sendcms', 'mobile/users/sendcms');
Route::rule('/verifyphone', 'mobile/users/verifyphone');
Route::rule('/recovery', 'mobile/account/recovery');
Route::rule('/resetpwd', 'mobile/users/resetpwd');
