<?php

namespace app\api\controller;

use app\admin\model\GoodsOrdersSubs;
use app\api\basic\Base;
use support\Db;
use support\Log;
use support\Request;

class OrdersSubsController extends Base
{

    function detail(Request $request)
    {
        $id = $request->post('id');
        $row = GoodsOrdersSubs::with(['goods'])->find($id);
        if (!$row) {
            return $this->fail('子订单不存在');
        }
        return $this->success('获取成功', $row);
    }

    function comment(Request $request)
    {
        $id = $request->post('id');
        $images = $request->post('images');
        $score = $request->post('score');
        $content = $request->post('content');
        $anonymity = $request->post('anonymity');#匿名:0=否,1=是
        $sub = GoodsOrdersSubs::find($id);
        if (!$sub) {
            return $this->fail('子订单不存在');
        }

        if ($sub->orders->status != 5) {
            return $this->fail('订单状态错误');
        }

        if ($sub->comment) {
            return $this->fail('该订单已评论');
        }
        Db::connection('plugin.admin.mysql')->beginTransaction();
        try {
            $sub->comment()->create([
                'user_id' => $request->user_id,
                'images' => $images,
                'score' => $score,
                'content' => $content,
                'goods_id' => $sub->goods_id,
                'anonymity' => $anonymity,
            ]);
            $sub->refresh();
            if ($sub->orders->subs->count() == $sub->orders->comment->count()){
                //评价足够了
                $sub->orders->status = 4;
                $sub->orders->save();
            }
            Db::connection('plugin.admin.mysql')->commit();
        } catch (\Throwable $e) {
            Db::connection('plugin.admin.mysql')->rollBack();
            Log::error('订单评价失败');
            Log::error($e->getMessage());
            return $this->fail('订单评价失败');
        }
        return $this->success();
    }

}
