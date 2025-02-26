<?php

namespace app\api\controller;

use app\admin\model\LotteryFootball;
use app\admin\model\LotteryFootballLog;
use app\admin\model\LotteryHistory;
use app\api\basic\Base;
use Carbon\Carbon;
use support\Request;

class LotteryController extends Base
{

    protected array $noNeedLogin = ['getHistoryList'];

    function getFootballList(Request $request)
    {
        $type = $request->post('type',2);#类型:1=早场,2=晚场
        // 获取今天和昨天的日期范围
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        // 查询今天和昨天的信息并按日期分组
        $rows = LotteryFootball::whereDate('created_at', '>=', $yesterday)
            ->where('type', $type)
            ->whereDate('created_at', '<=', $today)
            ->orderByDesc('id')
            ->get()
            ->groupBy(function ($row) {
                return $row->created_at->toDateString();
            });
        // 获取所有 lottery_football_id
        $lotteryFootballIds = $rows->flatten()->pluck('id')->toArray();

        // 获取这些 id 在 LotteryFootballLog 中的记录
        $logRecords = LotteryFootballLog::whereIn('lottery_football_id', $lotteryFootballIds)
            ->where('user_id',$request->user_id)
            ->pluck('lottery_football_id')
            ->toArray();
        // 将分组后的结果转换为所需的格式
        $groupedRows = $rows->map(function ($list, $date)use($logRecords) {
            return [
                'date' => $date,
                'list' => $list->map(function ($item) use ($logRecords) {
                    $item->has_log = in_array($item->id, $logRecords);
                    return $item;
                })
            ];
        })->values()->toArray();
        return $this->success('获取成功', $groupedRows);
    }

    function setFootballLog(Request $request)
    {
        $lottery_football_ids = $request->post('lottery_football_ids');
        if (!$lottery_football_ids) {
            return $this->fail('请选择比赛');
        }
        foreach ($lottery_football_ids as $football_id){
            LotteryFootballLog::firstOrCreate(['user_id' => $request->user_id, 'lottery_football_id' => $football_id]);
        }
        return $this->success('成功');
    }

    function getHistoryList(Request $request)
    {
        // 获取当前日期
        $today = Carbon::today();
        $startDate = Carbon::today()->subDays(250);;
        // 查询近250天的数据
        $rows = LotteryHistory::whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $today)
            ->orderByDesc('date')
            ->get();
        return $this->success('获取成功', ['list'=>$rows,'sum'=>[
            'total_buy_amount'=>$rows->sum('buy_amount'),
            'total_win_amount'=>$rows->sum('win_amount'),
            'total_gain_amount'=>$rows->sum('gain_amount'),
            'total_loss_amount'=>$rows->sum('loss_amount'),
        ]]);
    }



}
