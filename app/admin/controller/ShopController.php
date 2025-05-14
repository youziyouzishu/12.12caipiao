<?php

namespace app\admin\controller;

use support\Request;
use support\Response;
use app\admin\model\Shop;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 商店外链 
 */
class ShopController extends Crud
{
    
    /**
     * @var Shop
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new Shop;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('shop/index');
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
        return view('shop/insert');
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
            return parent::update($request);
        }
        return view('shop/update');
    }

}
