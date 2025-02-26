<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property int $lottery_football_id 竞彩足球
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LotteryFootballLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LotteryFootballLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LotteryFootballLog query()
 * @mixin \Eloquent
 */
class LotteryFootballLog extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_lottery_football_log';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'lottery_football_id',
    ];
    
    
}
