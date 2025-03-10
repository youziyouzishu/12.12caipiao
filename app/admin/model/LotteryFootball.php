<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property integer $id 主键(主键)
 * @property integer $type 类型:1=早场,2=晚场
 * @property string $image 图片
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property-read mixed $id_text
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LotteryFootball newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LotteryFootball newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LotteryFootball query()
 * @mixin \Eloquent
 */
class LotteryFootball extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_lottery_football';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'type',
        'image',
    ];
    
    
    
}
