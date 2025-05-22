<?php

namespace app\api\controller;

use app\admin\model\GoodsOrders;
use app\admin\model\User;
use app\admin\model\UsersWithdraw;
use app\api\basic\Base;
use app\api\service\Pay;
use Carbon\Carbon;
use support\Request;

class IndexController extends Base
{
    protected array $noNeedLogin = ['*'];


    public function index(Request $request)
    {

    }

}
