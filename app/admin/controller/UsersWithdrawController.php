<?php

namespace app\admin\controller;

use app\admin\model\User;
use support\Request;
use support\Response;
use app\admin\model\UsersWithdraw;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 提现列表 
 */
class UsersWithdrawController extends Crud
{
    
    /**
     * @var UsersWithdraw
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new UsersWithdraw;
    }

    /**
     * 查询
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function select(Request $request): Response
    {
        [$where, $format, $limit, $field, $order] = $this->selectInput($request);
        $query = $this->doSelect($where, $field, $order)->with(['user']);
        return $this->doFormat($query, $format, $limit);
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('users-withdraw/index');
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
        return view('users-withdraw/insert');
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     * @throws BusinessException
    */
    public function update(Request $request): Response
    {
        if ($request->method() === 'POST') {
            $id = $request->post('id');
            $status = $request->post('status');
            $row = UsersWithdraw::find($id);
            if (!$row) {
                return $this->fail('记录不存在');
            }
            if ($row->status == 0 && $status == 1) {
                //转账
                User::score($row->withdraw_amount, $row->user_id, '提现驳回', 'money');
            }
            if ($row->status == 0 && $status == 2) {
                //驳回 返回余额
                User::score($row->withdraw_amount, $row->user_id, '提现驳回', 'money');
            }
            return parent::update($request);
        }
        return view('users-withdraw/update');
    }

}
