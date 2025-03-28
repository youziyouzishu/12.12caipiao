<?php

namespace app\admin\model;


use Illuminate\Database\Eloquent\SoftDeletes;
use plugin\admin\app\model\Base;
use support\Db;


/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property int $sub_id 子订单
 * @property int $goods_id 商品
 * @property int $score 评分(1-5)
 * @property string $images 图片
 * @property string $content 内容
 * @property int $anonymity 匿名:0=否,1=是
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsOrdersComment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsOrdersComment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsOrdersComment query()
 * @property-read \app\admin\model\Goods|null $goods
 * @property-read \app\admin\model\GoodsOrders|null $orders
 * @property-read \app\admin\model\GoodsOrdersSubs|null $sub
 * @property-read \app\admin\model\User|null $user
 * @mixin \Eloquent
 */
class GoodsOrdersComment extends Base
{


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_goods_orders_comment';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'sub_id',
        'goods_id',
        'score',
        'images',
        'content',
        'anonymity',
    ];

    function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    function sub()
    {
        return $this->belongsTo(GoodsOrdersSubs::class, 'sub_id', 'id');
    }

    function goods()
    {
        return $this->belongsTo(Goods::class, 'goods_id', 'id');
    }

    function orders()
    {
        return $this->belongsTo(GoodsOrders::class, 'order_id', 'id');
    }

    


}
