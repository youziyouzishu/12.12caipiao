<?php

namespace app\api\controller;

use app\admin\model\GoodsOrders;
use app\admin\model\User;
use app\api\basic\Base;
use app\api\service\Pay;
use Carbon\Carbon;
use support\Request;

class IndexController extends Base
{
    protected array $noNeedLogin = ['*'];


    public function index(Request $request)
    {

        $user_id = 8;
        if (!empty($user_id)) {
            $request->user_id = $user_id;
        }
        $user = User::find($request->user_id);
        if ($user->first_buy_time && $user->vip_status == 1){
            $days = (int)$user->first_buy_time->diffInDays(Carbon::now());
            $next_days = (int)$user->first_buy_time->diffInDays($user->vip_expire_time);
            $text = "尊敬的会员，今天是您第{$days}个幸运日离下个幸运日还有{$next_days}天";
        }else{
            $text = '';
        }
        $user->setAttribute('text', $text);
        return $this->success('成功', $user);
    }

}
