<?php

namespace plugin\admin\app\controller;


use app\admin\model\User;
use Carbon\Carbon;
use support\exception\BusinessException;
use support\Request;
use support\Response;
use Throwable;

/**
 * 用户管理
 */
class UserController extends Crud
{

    /**
     * @var User
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new User;
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
        $query = $this->doSelect($where, $field, $order)
            ->withCount(['children as children_vip_count'=>function ($query) {
                $query->where('vip_expire_time', '>', Carbon::now());
            }]);
        return $this->doFormat($query, $format, $limit);
    }

    /**
     * 浏览
     * @return Response
     * @throws Throwable
     */
    public function index(): Response
    {
        return raw_view('user/index');
    }

    /**
     * 插入
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::insert($request);
        }
        return raw_view('user/insert');
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function update(Request $request): Response
    {
        if ($request->method() === 'POST') {
            $param = $request->post();
            $user = $this->model->find($param['id']);
            if ($user->money != $param['money']) {
                //变了账户
                $difference = $param['money'] - $user->money;
                \app\admin\model\User::score($difference, $user->id, $difference > 0 ? '管理员增加' : '管理员扣除', 'money');
            }
            return parent::update($request);
        }
        return raw_view('user/update');
    }

}
