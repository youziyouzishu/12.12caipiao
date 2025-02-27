<?php

namespace app\api\controller;

use app\admin\model\Sms;
use app\admin\model\User;
use app\api\basic\Base;
use Carbon\Carbon;
use plugin\admin\app\common\Util;
use support\Request;
use Tinywan\Jwt\JwtToken;

class AccountController extends Base
{

    protected array $noNeedLogin = ['login','register','changePassword'];
    function login(Request $request)
    {
        $login_type = $request->post('login_type');# 1手机号登录 2密码登录
        $mobile = $request->post('mobile');
        $captcha = $request->post('captcha');
        $password = $request->post('password');
        if ($login_type == 1) {
            $captchaResult = Sms::check($mobile, $captcha, 'login');
            if (!$captchaResult) {
                return $this->fail('验证码错误');
            }
            $user = User::where('mobile', $mobile)->first();
            if (!$user) {
                return $this->fail('用户不存在');
            }
        }else{
            $user = User::where('username', $mobile)->first();
            if (!$user) {
                return $this->fail('用户不存在');
            }
            if (!Util::passwordVerify($password, $user->password)) {
                return $this->fail('密码错误');
            }
        }
        $user->last_time = Carbon::now()->toDateTimeString();
        $user->last_ip = $request->getRealIp();
        $user->save();
        $token = JwtToken::generateToken([
            'id' => $user->id,
            'client' => JwtToken::TOKEN_CLIENT_MOBILE
        ]);
        return $this->success('登录成功', ['user' => $user, 'token' => $token]);
    }

    function register(Request $request)
    {
        $mobile = $request->post('mobile');
        $captcha = $request->post('captcha');
        $password = $request->post('password');
        $password_confirm = $request->post('password_confirm');
        $invitecode = $request->post('invitecode');
        if ($password != $password_confirm) {
            return $this->fail('两次密码不一致');
        }
        if (strlen($password) < 6) {
            return $this->fail('密码长度不能小于6位');
        }
        if (!empty($invitecode) && !$parent = User::where('invitecode', $invitecode)->first()) {
            return $this->fail('邀请码不存在');
        }
        $captchaResult = Sms::check($mobile, $captcha, 'register');
        if (!$captchaResult) {
            return $this->fail('验证码错误');
        }
        $user = User::create([
            'nickname' => '用户' . Util::alnum(),
            'avatar' => '/app/admin/avatar.png',
            'join_time' => Carbon::now()->toDateTimeString(),
            'join_ip' => $request->getRealIp(),
            'last_time' => Carbon::now()->toDateTimeString(),
            'last_ip' => $request->getRealIp(),
            'username' => $mobile,
            'mobile' => $mobile,
            'password' => Util::passwordHash($password),
            'vip_expire_time' => Carbon::now()->addDays(25)->toDateTimeString(),
            'parent_id' => isset($parent) ? $parent->id : 0,
            'invitecode' => User::generateInvitecode(),
        ]);
        $token = JwtToken::generateToken([
            'id' => $user->id,
            'client' => JwtToken::TOKEN_CLIENT_MOBILE
        ]);
        return $this->success('注册成功', ['user' => $user, 'token' => $token]);
    }

    #更改密码
    function changePassword(Request $request)
    {
        $mobile = $request->post('mobile');
        $captcha = $request->post('captcha');
        $password = $request->post('password');
        $password_confirm = $request->post('password_confirm');
        if ($password != $password_confirm) {
            return $this->fail('两次密码不一致');
        }
        if (strlen($password) < 6) {
            return $this->fail('密码长度不能小于6位');
        }
        $captchaResult = Sms::check($mobile, $captcha, 'resetpwd');
        if (!$captchaResult) {
            return $this->fail('验证码错误');
        }
        $user = User::where('mobile', $mobile)->first();
        if (!$user) {
            return $this->fail('用户不存在');
        }
        $user->password = Util::passwordHash($password);
        $user->save();
        return $this->success('修改成功');
    }
}
