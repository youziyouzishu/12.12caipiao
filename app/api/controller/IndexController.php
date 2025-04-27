<?php

namespace app\api\controller;

use app\admin\model\GoodsOrders;
use app\api\basic\Base;
use app\api\service\Pay;
use support\Request;

class IndexController extends Base
{
    protected array $noNeedLogin = ['*'];


    public function index(Request $request)
    {

        return $this->success('成功');
    }

}
