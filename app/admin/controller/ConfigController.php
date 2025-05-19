<?php

namespace app\admin\controller;

use plugin\admin\app\common\Util;
use plugin\admin\app\model\Option;
use support\Request;
use support\Response;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 系统配置
 */
class ConfigController extends Crud
{

    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('config/index');
    }

    /**
     * 插入
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::insert($request);
        }
        return view('config/insert');
    }

    /**
     * 更改
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function update(Request $request): Response
    {
        $post = $request->post();
        $data['user_agreement'] = $post['user_agreement'] ?? '';
        $data['privacy_policy'] = $post['privacy_policy'] ?? '';
        $data['notice_text'] = $post['notice_text'] ?? '';
        $data['tiyu_url'] = $post['tiyu_url'] ?? '';
        $data['today_plan'] = $post['today_plan'] ?? '';
        $data['early_time'] = $post['early_time'] ?? '';
        $data['end_time'] = $post['end_time'] ?? '';
        $data['early_stop_time'] = $post['early_stop_time'] ?? '';
        $data['end_stop_time'] = $post['end_stop_time'] ?? '';
        $data['vip_rule'] = $post['vip_rule'] ?? '';
        $data['pay_rule'] = $post['pay_rule'] ?? '';
        $data['vip_original'] = $post['vip_original'] ?? '';
        $data['vip_price'] = $post['vip_price'] ?? '';
        $data['kefu_qr'] = $post['kefu_qr'] ?? '';
        $data['kefu_wechat'] = $post['kefu_wechat'] ?? '';
        $data['kefu_mobile'] = $post['kefu_mobile'] ?? '';
        $data['poster_image'] = $post['poster_image'] ?? '';
        $data['invite_rule'] = $post['invite_rule'] ?? '';
        $data['law_rule'] = $post['law_rule'] ?? '';
        $data['law_title'] = $post['law_title'] ?? '';
        $data['shop_require'] = $post['shop_require'] ?? '';
        $data['shop_name'] = $post['shop_name'] ?? '';
        $data['shop_image'] = $post['shop_image'] ?? '';
        $data['shop_images'] = $post['shop_images'] ?? '';
        $data['layer2'] = $post['layer2'] ?? '';
        $data['layer1'] = $post['layer1'] ?? '';

        $name = 'admin_config';
        Option::where('name', $name)->update([
            'value' => json_encode($data)
        ]);
        return $this->json(0);
    }

    /**
     * 获取配置
     * @return Response
     */
    public function get(): Response
    {
        $name = 'admin_config';
        $config = Option::where('name', $name)->value('value');
        if ($config === null){
            $config = Option::insert([
                'name'=>$name,
                'value' => ''
            ]);
        }
        $config = json_decode($config,true) ?: [];

        return $this->success('成功', $config);
    }




}
