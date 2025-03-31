<?php

namespace app\api\controller;

use app\admin\model\GoodsOrders;
use app\admin\model\Shopcar;
use app\admin\model\UsersAddress;
use app\api\basic\Base;
use support\Db;
use support\Log;
use support\Request;
use support\Response;
use Webman\RedisQueue\Client;

class ShopcarController extends Base
{
    function select(Request $request)
    {
       $shopcars = Shopcar::where(['user_id' => $request->user_id])->with(['goods'])->get();
       return $this->success('成功', $shopcars);
    }

    /**
     * 加入购物车
     * @param Request $request
     * @return Response
     */
    function insert(Request $request)
    {
        $goods_id = $request->post('goods_id');
        $num = $request->post('num');
        $tag = $request->post('tag');
        $row = Shopcar::where(['user_id' => $request->user_id, 'goods_id' => $goods_id,'tag'=>$tag])->first();
        if ($row) {
            $row->num = $row->num + $num;
            $row->save();
        } else {
            Shopcar::create([
                'user_id' => $request->user_id,
                'goods_id' => $goods_id,
                'num' => $num,
                'tag'=>$tag,
            ]);
        }
        return $this->success();
    }


    function update(Request $request)
    {
        $id = $request->post('id');
        $num = $request->post('num');
        $tag = $request->post('tag');
        $row = Shopcar::find($id);
        if (empty($row)) {
            return $this->fail('购物车不存在');
        }
        if (!empty($num)){
            $row->num = $num;
        }
        if (!empty($tag)){
            $row->tag = $tag;
        }
        $row->save();
        return $this->success();
    }

    function delete(Request $request)
    {
        $shopcar_ids = $request->post('shopcar_ids');
        $rows = Shopcar::whereIn('id', $shopcar_ids)->delete();
        return $this->success();
    }


    /**
     * 计算价格
     * @param Request $request
     * @return Response
     */
    function getPrice(Request $request)
    {
        $shopcar_ids = $request->post('shopcar_ids');
        $rows = Shopcar::with(['goods'])->whereIn('id', $shopcar_ids)->get();
        $freight = 0;
        $goods_amount = 0;
        foreach ($rows as $row){
            $freight += $row->goods->freight * $row->num;
            $goods_amount = $row->goods->price * $row->num;
        }
        $pay_amount = $goods_amount + $freight;
        return $this->success('成功', [
            'freight' => $freight,
            'goods_amount' => $goods_amount,
            'pay_amount' => $pay_amount,
            'list'=>$rows
        ]);
    }

    /**
     * 创建订单
     * @param Request $request
     * @return Response
     * @throws \Throwable
     */
    function createOrder(Request $request)
    {
        $address_id = $request->post('address_id');
        $shopcar_ids = $request->post('shopcar_ids');
        $mark = $request->post('mark', '');
        $address = UsersAddress::find($address_id);
        if (!$address) {
            return $this->fail('地址不存在');
        }

        Db::connection('plugin.admin.mysql')->beginTransaction();
        try {
            $rows = Shopcar::where(['user_id'=>$request->user_id])->whereIn('id', $shopcar_ids)->get();
            $freight = 0;
            $goods_amount = 0;
            $subs = [];
            foreach ($rows as $row){
                $freight += $row->goods->freight * $row->num;
                $goods_amount = $row->goods->price * $row->num;
                $subs[] = ['goods_id'=>$row->goods_id,'num'=>$row->num,'amount'=>$row->goods->price,'total_amount'=>$goods_amount];
                $row->delete();
            }
            $pay_amount = $goods_amount + $freight;

            $order = GoodsOrders::create([
                'user_id' => $request->user_id,
                'address_id' => $address->id,
                'ordersn' => GoodsOrders::generateOrderSn(),
                'pay_amount' => $pay_amount,
                'goods_amount' => $goods_amount,
                'freight' => $freight,
                'mark' => $mark,
            ]);
            $order->subs()->createMany($subs);
            Client::send('job', ['order_id' => $order->id, 'event' => 'order_expire'], 60*15);
            Db::connection('plugin.admin.mysql')->commit();
        } catch (\Throwable $e) {
            Db::connection('plugin.admin.mysql')->rollBack();
            Log::error('创建订单失败');
            Log::error($e->getMessage());
            return $this->fail('创建订单失败');
        }
        return $this->success('成功', $order);
    }





}
