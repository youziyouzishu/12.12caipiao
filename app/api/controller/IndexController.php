<?php

namespace app\api\controller;

use app\admin\model\User;
use app\api\basic\Base;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use support\Request;

class IndexController extends Base
{
    protected array $noNeedLogin = [];

    function index()
    {
        dump(public_path() . '/404.html');
        dump(base_path('plugin' . DIRECTORY_SEPARATOR. 'admin' . DIRECTORY_SEPARATOR . 'public') . '\demos\error/404.html');
    }

}
