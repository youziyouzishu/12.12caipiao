<?php

namespace app\admin\controller;

use FFMpeg\FFMpeg;
use support\Request;
use support\Response;
use app\admin\model\LotteryKnow;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 知识讲座 
 */
class LotteryKnowController extends Crud
{
    
    /**
     * @var LotteryKnow
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new LotteryKnow;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('lottery-know/index');
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
            $video = $request->post('video');
            // 创建 FFMpeg 实例
            $ffmpeg = FFMpeg::create();

            // 打开视频文件
            $video = $ffmpeg->open($video);



            return parent::insert($request);
        }
        return view('lottery-know/insert');
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
        return view('lottery-know/update');
    }

}
