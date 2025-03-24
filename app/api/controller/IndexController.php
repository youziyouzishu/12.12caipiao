<?php

namespace app\api\controller;

use app\admin\model\LotteryFootball;
use app\admin\model\LotteryFootballLog;
use app\admin\model\LotteryHistory;
use app\admin\model\User;
use app\api\basic\Base;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use plugin\admin\app\model\Option;
use support\Log;
use support\Request;
use support\Response;
use Workerman\Coroutine;
use Workerman\Timer;

class IndexController extends Base
{
    protected array $noNeedLogin = ['*'];

    public function index()
    {
        $today = \Carbon\Carbon::today();
        $startDate = Carbon::today()->subDays(250);
        // 查询近250天的数据
        $rows = LotteryHistory::whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $today)
            ->orderByDesc('date')
            ->get();
        return $this->success('获取成功', ['list'=>$rows,'sum'=>[
            'total_count'=>$rows->count(),
            'total_buy_amount'=>round($rows->sum('buy_amount'),2),
            'total_win_amount'=>round($rows->sum('win_amount'),2),
            'total_gain_amount'=>round($rows->sum('gain_amount'),2),
            'total_loss_amount'=>round($rows->sum('loss_amount'),2),
        ]]);
    }

}
