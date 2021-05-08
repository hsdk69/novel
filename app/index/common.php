<?php
function getBooks($order, $where, $num)
{
    $books = cache('books:' . $order . $where . $num);
    if ($books == false) {
        $bookModel = app('bookModel');
        $books = $bookModel->getBooks($order, $where, $num);
        cache('book:' . $order . $where . $num, $books, null, 'redis');
    }
    return $books;
}

function getPagedBooks($order, $where, $pagesize)
{
    $bookModel = app('bookModel');

    $data = $bookModel->getPagedBooks($order, $where, $pagesize);
    unset($data['page']['query']['page']);
    $param = '';
    foreach ($data['page']['query'] as $k => $v) {
        $param .= '&' . $k . '=' . $v;
    }
    $pagedBooks = array();
    $pagedBooks['books'] = $data['books'];
    $pagedBooks['page'] = $data['page'];
    $pagedBooks['param'] = $param;
    return $pagedBooks;
}

function getCates($order, $where, $num)
{
    $cates = cache('cates:' . $order . $where . $num);
    if ($cates == false) {
        $cateModel = app('cateModel');
        $cates = $cateModel->getCates($order, $where, $num);
        cache('cates:' . $order . $where . $num, $cates, null, 'redis');
    }
    return $cates;
}

function getBanners($order, $where, $num)
{
    $banners = cache('banners:' . $order . $where . $num);
    if ($banners == false) {
        $bannerModel = app('bannerModel');
        $banners = $bannerModel->getBanners($order, $where, $num);
        cache('banners:' . $order . $where . $num, $banners, null, 'redis');
    }
    return $banners;
}

function getComments($order, $where, $num)
{
    $comments = cache('comments:' . $order . $where . $num);
    if ($comments == false) {
        $commentModel = app('commentModel');
        $comments = $commentModel->getComments($order, $where, $num);
        cache('comments:' . $order . $where . $num, $comments, null, 'redis');
    }
    return $comments;
}

function getPagedComments($order, $where, $pagesize)
{
    $commentModel = app('commentModel');
    $data = $commentModel->getPagedComments($order, $where, $pagesize);
    unset($data['page']['query']['page']);
    $param = '';
    foreach ($data['page']['query'] as $k => $v) {
        $param .= '&' . $k . '=' . $v;
    }
    $paged = array();
    $paged['comments'] = $data['comments'];
    $paged['page'] = $data['page'];
    $paged['param'] = $param;
    return $paged;
}

function getChapters($order, $where, $num)
{
    $chapters = cache('chapters:' . $order . $where . $num);
    if ($chapters == false) {
        $chapterModel = app('chapterModel');
        $chapters = $chapterModel->getChapters($order, $where, $num);
        cache('chapters:' . $order . $where . $num, $chapters, null, 'redis');
    }
    return $chapters;
}

function getPagedChapters($order, $where, $pagesize)
{
    $chapterModel = app('chapterModel');
    $data = $chapterModel->getPagedChapters($order, $where, $pagesize);
    unset($data['page']['query']['page']);
    $param = '';
    foreach ($data['page']['query'] as $k => $v) {
        $param .= '&' . $k . '=' . $v;
    }
    $paged = array();
    $paged['books'] = $data['books'];
    $paged['page'] = $data['page'];
    $paged['param'] = $param;
    return $paged;
}

function getFavors($order, $where, $pagesize)
{
    $favorModel = app('favorModel');
    $data = $favorModel->getFavors($order, $where, $pagesize);
    unset($data['page']['query']['page']);
    $param = '';
    foreach ($data['page']['query'] as $k => $v) {
        $param .= '&' . $k . '=' . $v;
    }
    $paged = array();
    $paged['favors'] = $data['favors'];
    $paged['page'] = $data['page'];
    $paged['param'] = $param;
    return $paged;
}
