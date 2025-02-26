<?php

namespace app\api\controller;

use app\admin\model\LotteryFootball;
use app\admin\model\LotteryFootballLog;
use app\admin\model\User;
use app\api\basic\Base;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use support\Request;

class IndexController extends Base
{
    protected array $noNeedLogin = ['*'];

    function index(Request $request)
    {
        $request->user_id = 1;
        $user = User::find($request->user_id);
        $user->vip_expire_time->addDays(1);
        dump($user->vip_expire_time->toDateTimeString());
        dump($user->vip_expire_time->addDays(1)->toDateTimeString());
    }

}
