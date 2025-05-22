<?php

namespace app\admin\model;


use Illuminate\Database\Eloquent\SoftDeletes;
use plugin\admin\app\model\Base;
use support\Db;

/**
 * 
 *
 * @property int $id 主键
 * @property string $username 用户名
 * @property string $nickname 昵称
 * @property string $password 密码
 * @property string $sex 性别
 * @property string|null $avatar 头像
 * @property string|null $email 邮箱
 * @property string|null $mobile 手机
 * @property int $level 等级
 * @property string|null $birthday 生日
 * @property string $money 余额(元)
 * @property int $score 积分
 * @property string|null $last_time 登录时间
 * @property string|null $last_ip 登录ip
 * @property string|null $join_time 注册时间
 * @property string|null $join_ip 注册ip
 * @property string|null $token token
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property int $role 角色
 * @property int $status 禁用
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @property \Illuminate\Support\Carbon|null $vip_expire_time VIP过期时间
 * @property string $openid 微信标识
 * @property int $user_type 用户类型:0=普通用户,1=官方用户
 * @property int $parent_id 上级
 * @property string $invitecode 邀请码
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\UsersShoper> $shoper
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\VipOrders> $vipOrders
 * @property-read mixed $is_shoper 是否店长:false=否,true=是
 * @property-read mixed $vip_status 会员类型:0=普通用户,1=正式会员,2=体验会员
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $children
 * @property-read User|null $parent
 * @property \Illuminate\Support\Carbon|null $first_buy_time 第一次购买商品时间
 * @mixin \Eloquent
 */
class User extends Base
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_users';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'username',
        'nickname',
        'password',
        'sex',
        'avatar',
        'email',
        'mobile',
        'level',
        'birthday',
        'money',
        'score',
        'last_time',
        'last_ip',
        'join_time',
        'join_ip',
        'token',
        'created_at',
        'updated_at',
        'role',
        'status',
        'vip_expire_time',
        'user_type',
        'parent_id',
        'invitecode',
        'openid',
        'first_buy_time',
    ];

    protected $casts = [
        'vip_expire_time' => 'datetime',
        'first_buy_time' => 'datetime',
    ];

    protected $appends = ['is_shoper', 'vip_status'];


    /**
     * 变更会员积分
     * @param numeric $score 积分
     * @param int $user_id 会员ID
     * @param string $memo 备注
     * @param string $type
     * @throws \Throwable
     */
    public static function score($score, $user_id, $memo, $type)
    {
        Db::connection('plugin.admin.mysql')->beginTransaction();
        try {
            $user = self::lockForUpdate()->find($user_id);
            if ($user && $score != 0) {
                $before = $user->$type;
                $after = $user->$type + $score;
                //更新会员信息
                $user->$type = $after;
                $user->save();
                //写入日志
                UsersScoreLog::create(['user_id' => $user_id, 'score' => $score, 'before' => $before, 'after' => $after, 'memo' => $memo, 'type' => $type]);
            }
            Db::connection('plugin.admin.mysql')->commit();
        } catch (\Throwable $e) {
            Db::connection('plugin.admin.mysql')->rollback();
        }
    }

    public static function generateInvitecode()
    {
        do {
            $invitecode = mt_rand(10000, 99999);
        } while (self::where(['invitecode' => $invitecode])->exists());
        return $invitecode;
    }

    function parent()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    function children()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    function vipOrders()
    {
        return $this->hasMany(VipOrders::class, 'user_id', 'id');
    }

    function shoper()
    {
        return $this->hasMany(UsersShoper::class, 'user_id', 'id');
    }

    function getIsShoperAttribute($value)
    {
        return $this->shoper()->where('status', 1)->exists();
    }

    function getVipStatusAttribute($value)
    {
        if (empty($this->vip_expire_time)) {
            $vip_status = 0;
        } elseif ($this->vip_expire_time->isPast()) {
            $vip_status = 0;
        } else {
            $vip_status = 1;

        }
        return $vip_status;
    }


}
