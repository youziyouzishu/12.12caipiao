<?php

namespace app\api\controller;


use app\admin\model\UsersAddress;
use app\api\basic\Base;
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

        $fieldsToUpdate = [
            'name' => $name,
            'mobile' => $mobile,
            'province' => $province,
            'city' => $city,
            'region' => $region,
            'address' => $address,
            'default' => $default,
        ];

        if ($fieldsToUpdate['default'] == 1) {
            UsersAddress::where(['user_id' => $request->user_id, 'default' => 1])->where('id', '!=', $id)->update(['default' => 0]);
        }

        $row->fill($fieldsToUpdate);
        $row->save();
        return $this->success();
    }

    /**
     * 删除
     */
    function delete(Request $request)
    {
        $ids = $request->post('ids');
        UsersAddress::where(['user_id' => $request->user_id])->destroy($ids);
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
