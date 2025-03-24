<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property integer $id 主键(主键)
 * @property string $title 标题
 * @property string $content 介绍
 * @property string $video 视频
 * @property string $duration 时长
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LotteryKnow newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LotteryKnow newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LotteryKnow query()
 * @property string $image 封面
 * @mixin \Eloquent
 */
class LotteryKnow extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_lottery_know';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    
    
}
