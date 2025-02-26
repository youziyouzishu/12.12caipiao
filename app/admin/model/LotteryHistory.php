<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property integer $id 主键(主键)
 * @property string $date 日期
 * @property string $buy_amount 购买金额
 * @property string $win_amount 中奖金额
 * @property string $gain_amount 盈利金额
 * @property string $loss_amount 亏损金额
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LotteryHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LotteryHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LotteryHistory query()
 * @mixin \Eloquent
 */
class LotteryHistory extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_lottery_history';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    
    
}
