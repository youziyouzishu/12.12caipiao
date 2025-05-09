<?php

namespace app\api\controller;

use app\admin\model\Banner;
use app\admin\model\Version;
use app\api\basic\Base;
use plugin\admin\app\model\Option;
use support\Request;
use support\Response;

class CommonController extends Base
{
    protected array $noNeedLogin = ['*'];

    #获取轮播图
    function getBannerList(Request $request)
    {
        $rows = Banner::orderByDesc('weigh')->get();
        return $this->success('成功',$rows);
    }

    #获取配置
    function getConfig()
    {
        $name = 'admin_config';
        $config = Option::where('name', $name)->value('value');
        $config = json_decode($config);
        $config->switch = true;
        return $this->success('成功', $config);
    }


    function getPrivacyPolicy(Request $request)
    {
        $name = 'admin_config';
        $config = Option::where('name', $name)->value('value');
        $config = json_decode($config);

        return response($config->privacy_policy);
    }

    function getUserAgreement(Request $request)
    {
        $name = 'admin_config';
        $config = Option::where('name', $name)->value('value');
        $config = json_decode($config);
        return response($config->user_agreement);
    }

    function getVersion()
    {
        $version = Version::latest()->first();
        return $this->success('获取成功',$version);
    }



}
