<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property int $pay_amount 支付金额
 * @property int $pay_type 支付方式:0=无,1=微信,2=支付宝
 * @property int $status 状态:0=未支付,1=已支付
 * @property string $ordersn 订单号
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VipOrders newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VipOrders newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VipOrders query()
 * @property-read \app\admin\model\User|null $user
 * @property \Illuminate\Support\Carbon|null $pay_time 支付时间
 * @mixin \Eloquent
 */
class VipOrders extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_vip_orders';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $casts = [
        'pay_time' => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'pay_amount',
        'pay_type',
        'status',
        'ordersn',
        'pay_time',
    ];

    function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    
    
}
