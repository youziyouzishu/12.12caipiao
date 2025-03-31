<?php

namespace app\admin\model;


use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use plugin\admin\app\model\Base;
use support\Db;

/**
 *
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property int $address_id 收货地址
 * @property string $ordersn 订单编号
 * @property string $pay_amount 支付金额
 * @property string $goods_amount 商品金额
 * @property string $freight 运费
 * @property string $mark 备注
 * @property int $pay_type 支付方式:0=无,1=微信,2=支付宝,3=数字人民币,4=余额支付
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\GoodsOrdersSubs> $subs
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsOrders newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsOrders newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsOrders query()
 * @property int $status 状态:0=待支付,1=待发货,2=取消,3=待收货,4=完成,5=待评价
 * @property-read \app\admin\model\UsersAddress|null $address
 * @property-read mixed $status_text
 * @property-read \app\admin\model\User|null $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\GoodsOrdersComment> $comment
 * @property \Illuminate\Support\Carbon|null $pay_time 支付时间
 * @property \Illuminate\Support\Carbon|null $confirm_time 确认时间
 * @property \Illuminate\Support\Carbon|null $deleted_at 删除时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsOrders onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsOrders withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsOrders withoutTrashed()
 * @mixin \Eloquent
 */
class GoodsOrders extends Base
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_goods_orders';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $appends = ['status_text'];

    protected $casts = [
        'pay_time' => 'datetime',
        'confirm_time' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'address_id',
        'ordersn',
        'pay_amount',
        'goods_amount',
        'freight',
        'status',
        'mark',
        'pay_type',
        'pay_time',
        'confirm_time',
    ];

    function getStatusTextAttribute($value)
    {
        $value = $value ? $value : $this->status;
        $list = $this->getStatusList();
        return $list[$value];
    }

    function getStatusList()
    {
        return [
            0 => '待支付',
            1 => '待发货',
            2 => '取消',
            3 => '待收货',
            4 => '完成',
            5 => '待评价',
        ];
    }

    public static function generateOrderSn()
    {
        return date('Ymd') . mb_strtoupper(uniqid());
    }

    function subs()
    {
        return $this->hasMany(GoodsOrdersSubs::class, 'order_id', 'id');
    }

    function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    function address()
    {
        return $this->belongsTo(UsersAddress::class, 'address_id', 'id');
    }

    function comment()
    {
        return $this->hasManyThrough(GoodsOrdersComment::class, GoodsOrdersSubs::class, 'order_id', 'sub_id', 'id', 'id');
    }



}
