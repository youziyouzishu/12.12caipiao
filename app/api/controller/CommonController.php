<?php

namespace app\api\controller;

use app\admin\model\Banner;
use app\api\basic\Base;
use support\Request;

class CommonController extends Base
{
    protected array $noNeedLogin = ['*'];

    #获取轮播图
    function getBannerList(Request $request)
    {
        $rows = Banner::orderByDesc('weigh')->get();
        return $this->success('成功',$rows);
    }

}
