<?php


namespace app\validate;


use think\Validate;

class User extends Validate
{
    protected $rule = [
        'uname' => 'require|max:25|min:6',
        'password' => 'require|max:25|min:6',
        'email' => 'email'
    ];


    protected $message = [
        'uname' => '名称必须是6-25个字符',
        'password' => '密码必须是6-25个字符',
        'email' => '邮箱格式不对'
    ];
}