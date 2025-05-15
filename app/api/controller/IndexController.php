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

        $order = GoodsOrders::where(['ordersn' => '20250515682580FFDB8D7', 'status' => 0])->first();
        if (!$order) {
            throw new \Exception('订单不存在');
        }
        $order->status = 1;
        $order->pay_time = Carbon::now();
        $order->pay_type = 4;
        $order->save();

        //增加用户会员时间
        if ($order->user->vip_expire_time->isPast()) {
            $order->user->vip_expire_time = $order->pay_time->addMonths(1);
        } else {
            $order->user->vip_expire_time = $order->user->vip_expire_time->addMonths(1);
        }
        $order->user->save();
        if ($order->user->parent){
            User::score(100, $order->user->parent->id, '推荐返佣', 'money');


        }

        return $this->success();
    }

}
