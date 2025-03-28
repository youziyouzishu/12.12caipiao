<?php

namespace app\api\controller;

use app\admin\model\LotteryFootball;
use app\admin\model\LotteryFootballLog;
use app\admin\model\LotteryHistory;
use app\admin\model\User;
use app\api\basic\Base;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use plugin\admin\app\model\Option;
use support\Log;
use support\Request;
use support\Response;
use Workerman\Coroutine;
use Workerman\Timer;

class IndexController extends Base
{
    protected array $noNeedLogin = ['*'];

    public function index()
    {
        $accessToken = 'dc7f1d377b24e052c7c5b870dbafa4c3e61f2e8b8c800d216c03311f2203e7fb';
        $secret = 'SEC72301ad86358439f47e1995c389411b1597448d7308dc292715555ec9b7b5664';
        $url = 'https://oapi.dingtalk.com/robot/send?access_token=' . $accessToken;
        [$s1, $s2] = explode(' ', microtime());
        $timestamp = (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
        $data = $timestamp . "\n" . $secret;
        $signStr = base64_encode(hash_hmac('sha256', $data, $secret, true));
        if(PHP_VERSION_ID < 82000){
            $signStr = mb_convert_encoding($signStr, 'UTF-8', 'ISO-8859-1');
        } else {
            $signStr = utf8_encode(urlencode($signStr));
        }
        $signStr = mb_convert_encoding(urlencode($signStr), 'UTF-8', 'ISO-8859-1');
        return $this->success('dingding',  $url . "&timestamp=$timestamp&sign=$signStr");
    }

}
