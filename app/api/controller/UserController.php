<?php

namespace app\api\controller;

use app\admin\model\RechargeOrders;
use app\admin\model\User;
use app\admin\model\VipOrders;
use app\api\basic\Base;
use app\api\service\Pay;
use Illuminate\Support\Str;
use plugin\admin\app\model\Option;
use support\Request;

class UserController extends Base
{

    protected array $noNeedLogin = [];

    function getUserInfo(Request $request)
    {
        $user_id = $request->post('user_id');
        if (!empty($user_id)) {
            $request->user_id = $user_id;
        }
        $row = User::find($request->user_id);
        if ($row->vip_expire_time->isPast()) {
            $vip_status = 0;
        } else {
            $order = VipOrders::where(['user_id' => $request->user_id, 'status' => 1])->exists();
            if ($order){
                $vip_status = 1;
            }else{
                $vip_status = 2;
            }
        }
        $row->setAttribute('vip_status',$vip_status);
        return $this->success('成功', $row);
    }

    function editUserInfo(Request $request)
    {
        $data = $request->post();
        $row = User::find($request->user_id);
        if (!$row) {
            return $this->fail('用户不存在');
        }

        $userAttributes = $row->getAttributes();
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $userAttributes) && (!empty($value) || $value === 0)) {
                $row->setAttribute($key, $value);
            }
        }
        $row->save();
        return $this->success('成功');
    }

    function createVipOrder(Request $request)
    {
        $pay_type = $request->post('pay_type');#1=微信,2=支付宝,3=数字人民币,4=余额支付
        $name = 'admin_config';
        $config = Option::where('name', $name)->value('value');
        $config = json_decode($config);
        $vip_price = $config->vip_price;
        $ordersn = Str::ulid()->toString();
        $order = VipOrders::create([
            'user_id'=>$request->user_id,
            'pay_amount'=>$vip_price,
            'pay_type'=>$pay_type,
            'ordersn'=>$ordersn,
        ]);
        if (in_array($pay_type,[1,2,3])){
            try {
                $result = Pay::pay($pay_type,$vip_price,$ordersn,'充值会员','vip');
            }catch (\Throwable $e){
                return $this->fail($e->getMessage());
            }
            $data = [
                'playload'=>$result,
                'order_info'=>$order
            ];
        }else{
            $user = User::find($request->user_id);
            if ($user->money < $vip_price){
                return $this->fail('余额不足');
            }
            User::score(-$vip_price,$request->user_id,'充值会员','money');
            $notify = new NotifyController();
            $request->setParams('get', ['paytype' => 'balance', 'out_trade_no' => $ordersn, 'attach' => 'vip']);
            $res = $notify->balance($request);
            $res = json_decode($res->rawBody());
            if ($res->code == 1) {
                return $this->fail($res->msg);
            }
            $data = [
                'playload'=>'',
                'order_info'=>$order
            ];
        }
        return $this->success('成功',$data);
    }


}
