<?php

namespace app\admin\controller;

use support\Request;
use support\Response;
use app\admin\model\Version;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 版本管理 
 */
class VersionController extends Crud
{
    
    /**
     * @var Version
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new Version;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('version/index');
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
        return view('version/insert');
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
        return view('version/update');
    }

}
