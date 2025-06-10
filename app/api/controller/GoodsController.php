<?php

namespace app\api\controller;

use app\admin\model\Goods;
use app\admin\model\GoodsOrders;
use app\admin\model\Shopcar;
use app\admin\model\UsersAddress;
use app\api\basic\Base;
use support\Db;
use support\Log;
use support\Request;
use support\Response;
use Webman\RedisQueue\Client;

class GoodsController extends Base
{

    protected array $noNeedLogin = ['detail','getCommentList'];
    function select(Request $request)
    {
        $order = $request->post('order');#排序:1=综合,2=销量升序,3=销量降序,4=价格升序,5=价格降序,6=最新
        $keyword = $request->post('keyword');
        $rows = Goods::where('status', 1)
            ->when(function ($query) use ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%');
            })
            ->when(!empty($order), function ($query) use ($order) {
                if ($order == 1) {
                    $query->orderByDesc('sales');
                } elseif ($order == 2) {
                    $query->orderBy('sales', 'asc');
                } elseif ($order == 3) {
                    $query->orderBy('sales', 'desc');
                } elseif ($order == 4) {
                    $query->orderBy('price', 'asc');
                } elseif ($order == 5) {
                    $query->orderBy('price', 'desc');
                } elseif ($order == 6) {
                    $query->orderByDesc('id');
                }
            })
            ->get();
        return $this->success('获取成功', $rows);
    }

    function detail(Request $request)
    {
        $id = $request->post('id');
        $row = Goods::find($id);
        if (!$row) {
            return $this->fail('商品不存在');
        }
        $comment_info = [];
        $comment_info['list'] = $row->comment()->with(['user','sub'])->orderByDesc('id')->take(2)->get();
        $comment_info['statistics'] = [
            'all' => $row->comment()->count(),
            'good' => $row->comment()->where('score', '>=', 4)->count(),
            'normal' => $row->comment()->where('score', '>=', 2)->where('score', '<', 4)->count(),
            'bad' => $row->comment()->where('score', '<', 2)->count(),
            'has_images' => $row->comment()->whereNotNull('images')->count(),
        ];
        $row->setAttribute('comment_info',$comment_info);
        return $this->success('获取成功', $row);
    }


    function getCommentList(Request $request)
    {
        $id = $request->post('id');
        $row = Goods::find($id);
        if (!$row) {
            return $this->fail('商品不存在');
        }
        $comment_info = [];
        $comment_info['list'] = $row->comment()->with(['user','sub'])->orderByDesc('id')->paginate()->items();
        $comment_info['statistics'] = [
            'all' => $row->comment()->count(),
            'good' => $row->comment()->where('score', '>=', 4)->count(),
            'normal' => $row->comment()->where('score', '>=', 2)->where('score', '<', 4)->count(),
            'bad' => $row->comment()->where('score', '<', 2)->count(),
            'has_images' => $row->comment()->whereNotNull('images')->count(),
        ];
        return $this->success('请求成功', $comment_info);
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
        $id = $request->post('id');
        $num = $request->post('num');
        $tag = $request->post('tag');
        $mark = $request->post('mark', '');
        $goods = Goods::find($id);
        if (empty($goods)) {
            return $this->fail('请选择商品');
        }
        $address = UsersAddress::find($address_id);
        if (!$address) {
            return $this->fail('地址不存在');
        }

        Db::connection('plugin.admin.mysql')->beginTransaction();
        try {
            $freight = $goods->freight * $num;
            $goods_amount = $goods->price * $num;
            $pay_amount = $goods_amount + $freight;
            $order = GoodsOrders::create([
                'user_id' => $request->user_id,
                'address_id' => $address->id,
                'ordersn' => GoodsOrders::generateOrderSn(),
                'pay_amount' => $pay_amount,
                'goods_amount' => $goods_amount,
                'freight' => $freight,
                'mark' => $mark,
                'status' => 0,
            ]);
            $order->subs()->createMany([
                [
                    'goods_id' => $id,
                    'num' => $num,
                    'amount' => $goods->price,
                    'tag' => $tag,
                    'total_amount' => $goods_amount,
                ]
            ]);
            Client::send('job', ['id' => $order->id, 'event' => 'order_expire'], 60 * 15);
            Db::connection('plugin.admin.mysql')->commit();
        } catch (\Throwable $e) {
            Db::connection('plugin.admin.mysql')->rollBack();
            Log::error('创建订单失败');
            Log::error($e->getMessage());
            return $this->fail('创建订单失败');
        }
        return $this->success('成功', $order);
    }


    /**
     * 计算价格
     * @param Request $request
     * @return Response
     */
    function getPrice(Request $request)
    {
        $id = $request->post('id');
        $num = $request->post('num');
        $goods = Goods::find($id);
        if (empty($goods)) {
            return $this->fail('请选择商品');
        }
        $freight = $goods->freight * $num;
        $goods_amount = $goods->price * $num;
        $pay_amount = $goods_amount + $freight;
        return $this->success('成功', [
            'freight' => $freight,
            'goods_amount' => $goods_amount,
            'pay_amount' => $pay_amount,
        ]);
    }



}
