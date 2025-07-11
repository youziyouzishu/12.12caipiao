<?php

namespace app\api\controller;

use app\admin\model\GoodsOrders;
use app\admin\model\Sms;
use app\admin\model\User;
use app\admin\model\UsersLayer;
use app\admin\model\UsersScoreLog;
use app\admin\model\UsersShoper;
use app\admin\model\UsersWithdraw;
use app\admin\model\VipOrders;
use app\api\basic\Base;
use app\api\service\Pay;
use Carbon\Carbon;
use EasyWeChat\OpenPlatform\Application;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use plugin\admin\app\common\Util;
use plugin\admin\app\model\Option;
use support\Request;

class UserController extends Base
{

    protected array $noNeedLogin = ['changePassword'];

    #获取个人信息
    function getUserInfo(Request $request)
    {
        $user_id = $request->post('user_id');
        if (!empty($user_id)) {
            $request->user_id = $user_id;
        }
        $user = User::find($request->user_id);
        if ($user->first_buy_time && $user->vip_status == 1) {
            $days = (int)$user->first_buy_time->diffInDays(Carbon::now());
            $days += 1;
            $next_days = 30 - $days;
            $text = "尊敬的会员，今天是您第{$days}个幸运日，离下个幸运月还有{$next_days}天";
        } else {
            $text = '';
        }
        $user->setAttribute('text', $text);
        return $this->success('成功', $user);
    }

    #编辑个人信息
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

    #充值会员
    function createVipOrder(Request $request)
    {
        $pay_type = $request->post('pay_type');#1=微信,2=支付宝,3=数字人民币,4=余额支付
        $name = 'admin_config';
        $config = Option::where('name', $name)->value('value');
        $config = json_decode($config);
        $vip_price = $config->vip_price;
        $ordersn = Str::ulid()->toString();
        $order = VipOrders::create([
            'user_id' => $request->user_id,
            'pay_amount' => $vip_price,
            'pay_type' => $pay_type,
            'ordersn' => $ordersn,
        ]);
        if (in_array($pay_type, [1, 2, 3])) {
            try {
                $result = Pay::pay($pay_type, $vip_price, $ordersn, '充值会员', 'vip');
            } catch (\Throwable $e) {
                return $this->fail($e->getMessage());
            }
            $data = [
                'playload' => $result,
                'order_info' => $order
            ];
        } else {
            $user = User::find($request->user_id);
            if ($user->money < $vip_price) {
                return $this->fail('余额不足');
            }
            User::score(-$vip_price, $request->user_id, '充值会员', 'money');
            $notify = new NotifyController();
            $request->setParams('get', ['paytype' => 'balance', 'out_trade_no' => $ordersn, 'attach' => 'vip']);
            $res = $notify->balance($request);
            $res = json_decode($res->rawBody());
            if ($res->code == 1) {
                return $this->fail($res->msg);
            }
            $data = [
                'playload' => '',
                'order_info' => $order
            ];
        }
        return $this->success('成功', $data);
    }

    #获取账变记录
    function getMoneyList(Request $request)
    {
        $type = $request->post('type');#money = 余额
        $date = $request->post('date');
        $status = $request->post('status'); #0=全部 1=支出，2=收入
        $date = Carbon::parse($date);
        // 提取年份和月份
        $year = $date->year;
        $month = $date->month;
        $rows = UsersScoreLog::where(['type' => $type])
            ->when(!empty($status), function (Builder $query) use ($status) {
                if ($status == 1) {
                    $query->where('score', '<', 0);
                } else {
                    $query->where('score', '>', 0);
                }
            })
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('user_id', $request->user_id)
            ->orderByDesc('id')
            ->paginate()
            ->getCollection()
            ->each(function (UsersScoreLog $item) {
                if ($item->score > 0) {
                    $item->score = '+' . $item->score;
                }
            });
        return $this->success('获取成功', $rows);
    }

    #提现
    function doWithdraw(Request $request)
    {
        $withdraw_amount = $request->post('withdraw_amount');
        if (empty($withdraw_amount)) {
            return $this->fail('请输入提现金额');
        }
        if ($withdraw_amount < 1) {
            return $this->fail('提现金额不能小于1元');
        }
        if ($withdraw_amount > 200) {
            return $this->fail('提现金额不能大于200元');
        }
        $user = User::find($request->user_id);
        if ($user->vip_status != 1) {
            return $this->fail('非会员不能提现');
        }
        if (empty($user->openid)) {
            return $this->fail('请先绑定微信');
        }

        if ($user->money < $withdraw_amount) {
            return $this->fail('余额不足');
        }
        $chance_rate = 0.06;
        $chance_amount = $withdraw_amount * $chance_rate;
        $into_amount = $withdraw_amount - $chance_amount;
        User::score(-$withdraw_amount, $request->user_id, '用户提现', 'money');
        UsersWithdraw::create([
            'user_id' => $request->user_id,
            'withdraw_amount' => $withdraw_amount,
            'chance_amount' => $chance_amount,
            'into_amount' => $into_amount,
            'chance_rate' => $chance_rate,
            'ordersn' => GoodsOrders::generateOrderSn(),
            'openid' => $user->openid,
            'mchid' => config('payment.wechat.default.mch_id'),
            'appid' => config('payment.wechat.default.app_id'),
        ]);
        return $this->success('提交成功');
    }

    #获取提现记录
    function getWithdrawList(Request $request)
    {
        $rows = UsersWithdraw::where('user_id', $request->user_id)
            ->with(['user'])
            ->orderByDesc('id')
            ->paginate()
            ->items();
        return $this->success('获取成功', $rows);
    }

    #绑定微信
    function bindWechat(Request $request)
    {
        $code = $request->post('code');
        $config = config('wechat');
        $app = new Application($config);
        $oauth = $app->getOauth();
        try {
            $response = $oauth->userFromCode($code);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
        $user = User::find($request->user_id);
        $user->openid = $response->getId();
        $user->save();

        return $this->success('绑定成功');
    }

    #获取邀请海报
    function getPoster(Request $request)
    {
        $user = User::find($request->user_id);

        $writer = new PngWriter();
        $qrCode = new QrCode(
            data: 'https://zhying.top/register/register.html#/?invitecode=' . $user->invitecode,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 100,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255)
        );
        $base64 = $writer->write($qrCode)->getDataUri();

        return $this->success('获取成功', [
            'base64' => $base64,
        ]);
    }

    #我的团队
    function getTeamList(Request $request)
    {
        $user = User::find($request->user_id);
        $team_count = UsersLayer::where('parent_id', $user->id)->has('user')->where('layer', '<=', 2)->count();#团队人数
        $team_vip_count = UsersLayer::where('parent_id', $user->id)->has('user')->where('layer', '<=', 2)->whereHas('user', function ($query) {
            $query->where('vip_expire_time', '>', Carbon::now());
        })->count();#团队会员数
        $direct_count = UsersLayer::where('parent_id', $user->id)->has('user')->where('layer', 1)->count();#直推人数
        $direct_vip_count = UsersLayer::where('parent_id', $user->id)->has('user')->where('layer', 1)->whereHas('user', function ($query) {
            $query->where('vip_expire_time', '>', Carbon::now());
        })->count();#直推会员数
        $other_count = UsersLayer::where('parent_id', $user->id)->has('user')->where('layer', 2)->count();#间推人数
        $other_vip_count = UsersLayer::where('parent_id', $user->id)->has('user')->where('layer', 2)->whereHas('user', function ($query) {
            $query->where('vip_expire_time', '>', Carbon::now());
        })->count();#间推会员数

        return $this->success('获取成功', [
            'team_count' => $team_count,
            'team_vip_count' => $team_vip_count,
            'direct_count' => $direct_count,
            'direct_vip_count' => $direct_vip_count,
            'other_count' => $other_count,
            'other_vip_count' => $other_vip_count,
        ]);
    }

    /**
     * 获取直推团队
     * @param Request $request
     * @return \support\Response
     */
    function getDirectTeamList(Request $request)
    {
        $user = User::find($request->user_id);
        $children = $user->children;
        $data = [];
        foreach ($children as $child) {
            $children_count = $child->children()->count();

            $data[] = [
                'id' => $child->id,
                'name' => $child->nickname,
                'type' => $child->vip_status ? '会员' : '用户',
                'children_count' => $children_count,
            ];
        }
        return $this->success('获取成功', $data);
    }

    function getDirectTeamDetail(Request $request)
    {
        $id = $request->post('id');
        $user = User::find($id);
        $children_count = $user->children()->count();
        $children_vip_count = $user->children()->where('vip_expire_time', '>', Carbon::now())->count();
        $children_user_count = $children_count - $children_vip_count;
        return $this->success('获取成功', [
            'children_count' => $children_count,
            'children_vip_count' => $children_vip_count,
            'children_user_count' => $children_user_count,
        ]);
    }

    #获取间推团队
    function getOtherTeamList(Request $request)
    {
        $user = User::find($request->user_id);

        $children_count = UsersLayer::where('parent_id', $user->id)->has('user')->where('layer', 2)->count();
        $children_vip_count = UsersLayer::where('parent_id', $user->id)->has('user')->where('layer', 2)->whereHas('user',function ($query){
            $query->where('vip_expire_time', '>', Carbon::now());
        })->count();
        $children_user_count = $children_count - $children_vip_count;
        $data = [
            'children_count' => $children_count,
            'children_vip_count' => $children_vip_count,
            'children_user_count' => $children_user_count,
        ];

        return $this->success('获取成功', $data);
    }

    #申请成为店长
    function applyShoper(Request $request)
    {
        $image = $request->post('image');
        $name = $request->post('name');
        $wechat = $request->post('wechat');
        $address = $request->post('address');

        $user = User::find($request->user_id);
        if ($user->vip_status != 1) {
            return $this->fail('会员功能，请充值会员');
        }
        $count = UsersLayer::where(['parent_id' => $request->user_id])->whereHas('user', function ($query) {
            $query->whereHas('vipOrders', function ($query) {
                $query->where('status', 1);
            })->where('vip_expire_time', '>', Carbon::now());
        })->count();


        $configname = 'admin_config';
        $config = Option::where('name', $configname)->value('value');
        $config = json_decode($config);
        $shop_require = $config->shop_require;
        if ($count < $shop_require) {
            return $this->fail('未满足条件');
        }

        $row = UsersShoper::where(['user_id' => $request->user_id])->where('status', 0)->exists();
        if ($row) {
            return $this->fail('您已经提交过申请，请耐心等待审核');
        }
        $row = UsersShoper::where(['user_id' => $request->user_id])->where('status', 1)->exists();
        if ($row) {
            return $this->fail('您已经申请过，请勿重复申请');
        }

        UsersShoper::create([
            'address' => $address,
            'user_id' => $request->user_id,
            'image' => $image,
            'name' => $name,
            'wechat' => $wechat,
        ]);
        return $this->success('申请成功，请耐心等待审核');
    }

    #修改手机号
    function changeMobile(Request $request)
    {
        $old_mobile = $request->post('old_mobile');
        $old_captcha = $request->post('old_captcha');
        $new_mobile = $request->post('new_mobile');
        $new_captcha = $request->post('new_captcha');

        $ret = Sms::check($old_mobile, $old_captcha, 'checkmobile');
        if (!$ret) {
            return $this->fail('验证码错误');
        }
        $ret = Sms::check($new_mobile, $new_captcha, 'changemobile');
        if (!$ret) {
            return $this->fail('验证码错误');
        }
        $thisuser = User::find($request->user_id);
        $user = User::where(['mobile' => $old_mobile])->first();
        if (!$user || $user->id != $thisuser->id) {
            return $this->fail('号码与当前用户不一致');
        }
        $user->mobile = $new_mobile;
        $user->username = $new_mobile;
        $user->save();
        return $this->success('修改成功');
    }

    #获取彩票店列表
    function getShoperList(Request $request)
    {
        $user = User::find($request->user_id);

        if ($user->is_shoper) {
            $rows = UsersShoper::where(['user_id' => $request->user_id, 'status' => 1])->get();
        } elseif ($user->parent && $user->parent->is_shoper) {
            //上级是店长
            $rows = UsersShoper::where(['user_id' => $user->parent->id, 'status' => 1])->get();
        } else {
            $guanfang = User::where('user_type', 1)->pluck('id')->toArray();
            $rows = UsersShoper::whereIn('user_id', $guanfang)->where(['status' => 1])->get();
        }
        return $this->success('获取成功', $rows);
    }


    /**
     * 注销
     * @param Request $request
     * @return \support\Response
     */
    function logout(Request $request)
    {
        $user = User::find($request->user_id);
        if ($user->money > 0) {
            return $this->fail('账户有余额不能注销');
        }
        $user->delete();
        return $this->success();
    }


}
