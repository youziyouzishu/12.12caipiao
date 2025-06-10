<?php

namespace plugin\admin\app\controller;


use app\admin\model\User;
use app\admin\model\UsersLayer;
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
            }])
            ->withCount(['children']);
        return $this->doFormat($query, $format, $limit);
    }

    /**
     * 执行真正查询，并返回格式化数据
     * @param $query
     * @param $format
     * @param $limit
     * @return Response
     */
    protected function doFormat($query, $format, $limit): Response
    {
        $methods = [
            'select' => 'formatSelect',
            'tree' => 'formatTree',
            'table_tree' => 'formatTableTree',
            'normal' => 'formatNormal',
        ];
        $paginator = $query->paginate($limit);
        $total = $paginator->total();
        $items = $paginator->items();
        foreach ($items as $item){
            $other_count = UsersLayer::where('parent_id', $item['id'])->has('user')->where('layer', 2)->count();#间推人数
            $other_vip_count = UsersLayer::where('parent_id', $item['id'])->has('user')->where('layer', 2)->whereHas('user', function ($query) {
                $query->where('vip_expire_time', '>', Carbon::now());
            })->count();#间推会员数
            $item->jiantui_count = $other_count;
            $item->jiantui_vip_count = $other_vip_count;
        }
        if (method_exists($this, 'afterQuery')) {
            $items = call_user_func([$this, 'afterQuery'], $items);
        }
        $format_function = $methods[$format] ?? 'formatNormal';
        return call_user_func([$this, $format_function], $items, $total);
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
            $user_type = $request->post('user_type');
            $user = $this->model->find($param['id']);
            if ($user->money != $param['money']) {
                //变了账户
                $difference = $param['money'] - $user->money;
                \app\admin\model\User::score($difference, $user->id, $difference > 0 ? '管理员增加' : '管理员扣除', 'money');
            }
            if ($user_type == 1 && $user->user_type == 0){
                $request->setParams('post',[
                    'first_buy_time' => Carbon::now(),
                    'vip_expire_time' => Carbon::now()->addDays(365),
                ]);
            }
            return parent::update($request);
        }
        return raw_view('user/update');
    }

}
