<?php

namespace app\api\controller;

use app\admin\model\GoodsOrders;
use app\admin\model\GoodsOrdersSubs;
use app\admin\model\User;
use app\api\basic\Base;
use app\api\service\Pay;
use Carbon\Carbon;
use GuzzleHttp\Client;
use support\Db;
use support\Log;
use support\Request;

class OrdersController extends Base
{
    /**
     * 订单支付
     * @param Request $request
     * @return \support\Response
     */
    function pay(Request $request)
    {
        $pay_type = $request->post('pay_type');#支付方式:1=微信,2=支付宝,3=数字人民币,4=余额支付
        $ordersn = $request->post('ordersn');
        $order = GoodsOrders::where(['ordersn' => $ordersn, 'user_id' => $request->user_id])->first();
        if (!$order) {
            return $this->fail('订单不存在');
        }
        if ($order->status != 0) {
            return $this->fail('订单状态错误');
        }
        if (in_array($pay_type, [1, 2, 3])) {
            try {
                $ret = Pay::pay($pay_type, $order->pay_amount, $order->ordersn, '购买商品', 'goods');
            } catch (\Throwable $e) {
                Log::error('订单支付失败');
                Log::error($e->getMessage());
                return $this->fail('订单支付失败');
            }
        } elseif ($pay_type == 4) {
            $user = User::find($request->user_id);
            if ($user->money < $order->pay_amount) {
                return $this->fail('余额不足');
            }
            Db::connection('plugin.admin.mysql')->beginTransaction();
            try {
                User::score(-$order->pay_amount, $user->id, '购买商品', 'money');
                // 创建一个新的请求对象 直接调用支付
                $notify = new NotifyController();
                $request->setParams('get', ['paytype' => 'balance', 'out_trade_no' => $ordersn, 'attach' => 'goods']);
                $res = $notify->balance($request);
                $res = json_decode($res->rawBody());
                if ($res->code == 1) {
                    throw new \Exception($res->msg);
                }
                $ret = [];
                Db::connection('plugin.admin.mysql')->commit();
            } catch (\Throwable $e) {
                Db::connection('plugin.admin.mysql')->rollBack();
                Log::error('订单支付失败');
                Log::error($e->getMessage());
                return $this->fail('订单支付失败');
            }
        } else {
            return $this->fail('支付方式错误');
        }
        return $this->success('成功', $ret);
    }

    function select(Request $request)
    {
        $status = $request->post('status');#状态:0=全部,1=待付款,2=待发货,3=待收货,4=已完成
        $rows = GoodsOrders::where(['user_id' => $request->user_id])
            ->when(!empty($status), function ($query) use ($status) {
                if ($status == 1) {
                    $query->where('status', 0);
                }
                if ($status == 2) {
                    $query->where('status', 1);
                }
                if ($status == 3) {
                    $query->where('status', 3);
                }
                if ($status == 4) {
                    $query->whereIn('status', [4, 5]);
                }
            })
            ->with(['subs' => function ($query) {
                $query->with(['goods', 'comment']);
            }])
            ->orderByDesc('id')
            ->paginate()
            ->getCollection()
            ->each(function (GoodsOrders $item) {
                if ($item->status == 0) {
                    $item->setAttribute('expire_time', Carbon::now()->diffInSeconds($item->created_at->addMinutes(15)));
                }
            });
        return $this->success('成功', $rows);
    }


    function detail(Request $request)
    {
        $id = $request->post('id');
        $order = GoodsOrders::with([
            'address',
            'subs' => function ($query) {
                $query->with(['goods','comment']);
            }])->find($id);
        if (!$order) {
            return $this->fail('订单不存在');
        }
        return $this->success('成功', $order);
    }

    function cancel(Request $request)
    {
        $id = $request->post('id');
        $order = GoodsOrders::find($id);
        if (!$order) {
            return $this->fail('订单不存在');
        }
        if ($order->status != 0) {
            return $this->fail('订单状态错误');
        }
        $order->status = 2;
        $order->save();
        return $this->success();
    }

    function confirm(Request $request)
    {
        $id = $request->post('id');
        $order = GoodsOrders::find($id);
        if (!$order) {
            return $this->fail('订单不存在');
        }
        if ($order->status != 3) {
            return $this->fail('订单状态错误');
        }
        $order->status = 5;
        $order->confirm_time = Carbon::now();
        $order->save();
        return $this->success();
    }

    /**
     * 查询快递
     * @param Request $request
     * @return \support\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    function getExpressQuery(Request $request)
    {
        $waybill = $request->post('waybill');
        // 参数设置
        $key = 'RytoIHjI5725';                        // 客户授权key
        $customer = 'FEDC7FB59D03DF9A5B4BE4C66ACBD42D';                   // 查询公司编号
        $param = [
            'num' => $waybill
        ];
        $post_data = array();
        $post_data['customer'] = $customer;
        $post_data['param'] = json_encode($param, JSON_UNESCAPED_UNICODE);
        $sign = md5($post_data['param'].$key.$post_data['customer']);
        $post_data['sign'] = strtoupper($sign);
        $url = 'https://poll.kuaidi100.com/poll/query.do';
        $client = new Client();
        $response = $client->post($url, [
            'form_params' => $post_data,
        ]);
        $result = $response->getBody()->getContents();
        $result = json_decode($result);
        $result = $result->data;
        return $this->success('成功',$result);
    }

    function getTabs(Request $request)
    {
        $status1 = GoodsOrders::where(['user_id'=>$request->user_id,'status'=>0])->count();
        $status2 = GoodsOrders::where(['user_id'=>$request->user_id,'status'=>1])->count();
        $status3 = GoodsOrders::where(['user_id'=>$request->user_id,'status'=>3])->count();
        $status4 = GoodsOrders::where(['user_id'=>$request->user_id,'status'=>4])->count();
        return $this->success('成功',[
            'status1'=>$status1,
            'status2'=>$status2,
            'status3'=>$status3,
            'status4'=>$status4,
        ]);
    }


}
