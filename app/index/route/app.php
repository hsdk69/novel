<?php
use think\facade\Route;

Route::rule('/'.BOOKCTRL.'/:id', 'index/books/index');
Route::rule('/'.BOOKLISTACT, 'index/booklist/index');
Route::rule('/getBooks', 'index/booklist/getBooks');
Route::rule('/getOptions', 'index/booklist/getOptions');
Route::rule('/getCates', 'index/booklist/getCates');
Route::rule('/getRanks', 'index/rank/getRanks');
Route::rule('/'.CHAPTERCTRL.'/:id', 'index/chapters/index');
Route::rule('/'.SEARCHCTRL.'/[:keyword]', 'index/index/search');
Route::rule('/'.RANKCTRL, 'index/rank/index');
Route::rule('/'.UPDATEACT, 'index/update/index');
Route::rule('/'.AUTHORCTRL.'/:id', 'index/authors/index');
Route::rule('/'.TAGCTRL.'/:id', 'index/tag/index');
Route::rule('/addfavor', 'index/users/addfavor');
Route::rule('/commentadd', 'index/books/commentadd');
Route::rule('/login', 'index/account/login');
Route::rule('/register', 'index/account/register');
Route::rule('/logout', 'index/account/logout');

Route::rule('/ucenter', 'index/users/ucenter');
Route::rule('/bookshelf', 'index/users/bookshelf');
Route::rule('/getfavors', 'index/users/getfavors');
Route::rule('/history', 'index/users/history');
Route::rule('/userinfo', 'index/users/userinfo');
Route::rule('/delfavors', 'index/users/delfavors');
Route::rule('/delhistory', 'index/users/delhistory');
Route::rule('/updateUserinfo', 'index/users/update');
Route::rule('/bindphone', 'index/users/bindphone');
Route::rule('/userphone', 'index/users/userphone');
Route::rule('/sendcms', 'index/users/sendcms');
Route::rule('/verifyphone', 'index/users/verifyphone');
Route::rule('/recovery', 'index/account/recovery');
Route::rule('/resetpwd', 'index/users/resetpwd');
Route::rule('/leavemsg', 'index/users/leavemsg');
Route::rule('/message', 'index/users/message');
Route::rule('/promotion', 'index/users/promotion');
