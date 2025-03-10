<?php

namespace app\admin\model;


use GuzzleHttp\Client;
use plugin\admin\app\common\Util;
use plugin\admin\app\model\Base;
use plugin\admin\app\model\User;


/**
 *
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property string $image 微信二维码
 * @property string $name 彩票店名称
 * @property string $wechat 彩票店微信号
 * @property int $status 状态:0=待审核,1=审核通过,2=拒绝
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersShoper newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersShoper newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersShoper query()
 * @mixin \Eloquent
 */
class UsersShoper extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_users_shoper';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id', 'image', 'name', 'wechat', 'status'
    ];

    function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
