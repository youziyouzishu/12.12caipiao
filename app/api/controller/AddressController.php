<?php

namespace app\api\controller;


use app\admin\model\UsersAddress;
use app\api\basic\Base;
use support\Db;
use support\Request;

class AddressController extends Base
{

    /**
     * 添加地址
     */
    function insert(Request $request)
    {
        $name = $request->post('name');
        $mobile = $request->post('mobile');
        $province = $request->post('province');
        $city = $request->post('city');
        $region = $request->post('region');
        $address = $request->post('address');
        $default = $request->post('default', 0);

        $data = [
            'user_id' => $request->user_id,
            'name' => $name,
            'mobile' => $mobile,
            'province' => $province,
            'city' => $city,
            'region' => $region,
            'address' => $address,
            'default' => $default,
        ];

        if ($data['default'] == 0) {
            $existingDefault = UsersAddress::where(['user_id' => $request->user_id, 'default' => 1])->first();
            if (!$existingDefault) {
                $data['default'] = 1;
            }
        } else {
            UsersAddress::where(['user_id' => $request->user_id, 'default' => 1])->update(['default' => 0]);
        }

        UsersAddress::create($data);
        return $this->success();
    }

    /**
     * 设置默认地址
     */
    function setDefault(Request $request)
    {
        $id = $request->post('id');
        UsersAddress::where(['user_id' => $request->user_id,'default' => 1])->update(['default' => 0]);
        UsersAddress::where(['id' => $id])->update(['default' => 1]);
        return $this->success();
    }

    /**
     * 获取默认地址
     */
    function getDefault(Request $request)
    {
        $row = UsersAddress::where(['user_id' => $request->user_id, 'default' => 1])->first();
        return $this->success('成功', $row);
    }

    /**
     * 详情
     */
    function detail(Request $request)
    {
        $id = $request->post('id');
        $row = UsersAddress::find($id);
        if (!$row) {
            return $this->fail('地址不存在');
        }
        return $this->success('成功', $row);
    }

    /**
     * 更新
     */
    function update(Request $request)
    {
        $id = $request->post('id');
        $name = $request->post('name');
        $mobile = $request->post('mobile');
        $province = $request->post('province');
        $city = $request->post('city');
        $region = $request->post('region');
        $address = $request->post('address');
        $default = $request->post('default', 0);



        $row = UsersAddress::find($id);
        if (!$row) {
            return $this->fail('地址不存在');
        }


        // 使用事务管理
        Db::connection('plugin.admin.mysql')->transaction(function () use ($request, $row, $name, $mobile, $province, $city, $region, $address, $default) {
            // 删除旧记录并创建新记录
            $row->delete();
            $newRow = UsersAddress::create([
                'user_id' => $request->user_id,
                'name' => $name,
                'mobile' => $mobile,
                'province' => $province,
                'city' => $city,
                'region' => $region,
                'address' => $address,
                'default' => $default,
            ]);
            // 如果设置为默认地址，则将其他默认地址取消
            if ($default == 1) {
                UsersAddress::where([
                    ['user_id', $request->user_id],
                    ['default', 1],
                    ['id', '<>', $newRow->id]
                ])->update(['default' => 0]);
            }
        }, 3); // 设置重试次数以应对死锁等异常情况
        return $this->success();
    }

    /**
     * 删除
     */
    function delete(Request $request)
    {
        $ids = $request->post('ids');
        UsersAddress::where(['user_id' => $request->user_id])->whereIn('id',$ids)->delete();
        return $this->success();
    }

    /**
     * 地址列表
     */
    function select(Request $request)
    {
        $rows = UsersAddress::where(['user_id' => $request->user_id])
            ->orderByDesc('id')
            ->paginate()
            ->items();
        return $this->success('成功', $rows);
    }

}
