<?php

namespace app\admin\model;




use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property string $withdraw_amount 提现金额
 * @property string $chance_amount 手续费
 * @property string $into_amount 到账金额
 * @property string $chance_rate 手续费比例
 * @property string $reason 驳回原因
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersWithdraw newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersWithdraw newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersWithdraw query()
 * @property-read User|null $user
 * @property string $ordersn 订单编号
 * @property int $status 状态:0=待审核,1=待收款,2=驳回,3=已打款
 * @property string|null $package_info pkg
 * @mixin \Eloquent
 */
class UsersWithdraw extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_users_withdraw';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'withdraw_amount',
        'chance_amount',
        'into_amount',
        'chance_rate',
        'status',
        'reason',
        'ordersn',
        'package_info'
    ];

    protected $appends = ['status_text'];

    function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    function getStatusTextAttribute($value)
    {
        $value = $value ? $value : $this->status;
        $list = $this->getStatusList();
        return $list[$value]??'';
    }

    function getStatusList()
    {
        return [
            0 => '待审核',
            1 => '待收款',
            2 => '驳回',
            3 => '已打款',
        ];
    }
}
