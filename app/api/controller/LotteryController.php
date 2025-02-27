<?php

namespace app\api\controller;

use app\admin\model\LotteryFootball;
use app\admin\model\LotteryFootballLog;
use app\admin\model\LotteryHistory;
use app\admin\model\LotteryKnow;
use app\admin\model\User;
use app\admin\model\UsersShoper;
use app\api\basic\Base;
use Carbon\Carbon;
use plugin\admin\app\model\Option;
use support\Request;

class LotteryController extends Base
{

    protected array $noNeedLogin = [];

    #获取竞彩足球列表
    function getFootballList(Request $request)
    {
        $type = $request->post('type');#类型:1=早场,2=晚场
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

    #标记竞彩足球
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

    #获取历史数据
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

    #获取知识讲座
    function getKnowList(Request $request)
    {
        $rows = LotteryKnow::orderByDesc('id')->get();
        return $this->success('获取成功', $rows);
    }

    #获取知识讲座列表详情
    function getKnowDetail(Request $request)
    {
        $lottery_know_id = $request->get('lottery_know_id');
        $row = LotteryKnow::find($lottery_know_id);
        return $this->success('获取成功', $row);
    }

    #管理员上传图片号码
    function uploadFootball(Request $request)
    {
        $image = $request->post('image');
        $early_stop_time = $request->post('early_stop_time');
        $end_stop_time = $request->post('end_stop_time');
        if (!$image) {
            return $this->fail('请上传图片');
        }
        $user = User::find($request->user_id);
        if ($user->user_type != 1) {
            return $this->fail('权限不足');
        }
        $name = 'admin_config';
        $row = Option::where('name', $name)->value('value');
        $config = json_decode($row);
        $early_time_range = explode(' - ', $config->early_time); // ["00:00", "06:59"]
        $end_time_range = explode(' - ', $config->end_time); // ["07:00", "23:59"]
        $current_time = Carbon::now();
        $early_time_start = Carbon::parse($current_time->toDateString() . ' ' . $early_time_range[0]);
        $early_time_end = Carbon::parse($current_time->toDateString() . ' ' . $early_time_range[1]);
        $end_time_start = Carbon::parse($current_time->toDateString() . ' ' . $end_time_range[0]);
        $end_time_end = Carbon::parse($current_time->toDateString() . ' ' . $end_time_range[1]);
        if ($current_time->between($early_time_start, $early_time_end)) {
            $type = 1;
        } // 判断当前时间是否在 end_time 范围内
        elseif ($current_time->between($end_time_start, $end_time_end)) {
            $type = 2;
        } else {
            return $this->fail('当前时间不在配置早晚场时间范围');
        }
        LotteryFootball::create([
            'image' => $image,
            'type' => $type,
        ]);
        empty($early_stop_time)??$config->early_stop_time = $early_stop_time;
        empty($end_stop_time)??$config->end_stop_time = $end_stop_time;
        Option::where('name', $name)->update(['value' => json_encode($config)]);
        return $this->success('上传成功');
    }

    #获取管理员彩票店
    function getAdminShoperList(Request $request)
    {
        $user = User::find($request->user_id);
        if ($user->user_type != 1) {
            return $this->fail('权限不足');
        }
        $user_ids = User::where('user_type', 1)->pluck('id')->toArray();
        $rows = UsersShoper::whereIn('user_id',$user_ids)->where('status',1)->get();
        return $this->success('获取成功', $rows);
    }

    #添加管理员彩票店
    function addAdminShoper(Request $request)
    {
        $image = $request->post('image');
        $name = $request->post('name');
        $wechat = $request->post('wechat');
        $user = User::find($request->user_id);
        if ($user->user_type != 1) {
            return $this->fail('权限不足');
        }
        UsersShoper::create([
            'user_id' => $request->user_id,
            'image' => $image,
            'name' => $name,
            'wechat' => $wechat,
            'status' => 1,
        ]);
        return $this->success('添加成功');
    }

    #编辑管理员彩票店
    function editAdminShoper(Request $request)
    {
        $id = $request->post('id');
        $image = $request->post('image');
        $name = $request->post('name');
        $wechat = $request->post('wechat');
        $user = User::find($request->user_id);
        if ($user->user_type != 1) {
            return $this->fail('权限不足');
        }
        UsersShoper::where('id',$id)->update([
            'image' => $image,
            'name' => $name,
            'wechat' => $wechat,
        ]);
        return $this->success('修改成功');
    }

    #删除管理员彩票店
    function delAdminShoper(Request $request)
    {
        $id = $request->post('id');
        $user = User::find($request->user_id);
        if ($user->user_type != 1) {
            return $this->fail('权限不足');
        }
        UsersShoper::where('id',$id)->delete();
        return $this->success('删除成功');
    }





}
