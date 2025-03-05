<?php

namespace app\api\controller;

use app\admin\model\LotteryFootball;
use app\admin\model\LotteryFootballLog;
use app\admin\model\User;
use app\api\basic\Base;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use plugin\admin\app\model\Option;
use support\Log;
use support\Request;

class IndexController extends Base
{
    protected array $noNeedLogin = ['*'];

    function index(Request $request)
    {

    }

}
