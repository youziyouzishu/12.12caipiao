<?php

namespace app\admin\model;

use Illuminate\Database\Eloquent\SoftDeletes;
use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property integer $id 主键(主键)
 * @property string $image 商品封面
 * @property string $name 商品名称
 * @property string $price 商品价格
 * @property string $original_price 商品原价
 * @property integer $sales 销量
 * @property string $images 轮播图
 * @property string $freight 运费
 * @property string $content 详情
 * @property string $tags 规格
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Goods newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Goods newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Goods query()
 * @property-read array $tags_text
 * @property int $status 状态:1=上架,2=下架
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\GoodsOrdersComment> $comment
 * @property \Illuminate\Support\Carbon|null $deleted_at 删除时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Goods onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Goods withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Goods withoutTrashed()
 * @mixin \Eloquent
 */
class Goods extends Base
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_goods';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $appends = [
        'tags_text'
    ];

    function getTagsTextAttribute($value): array
    {
        $value = $value ?: $this->tags;
        return explode('|', $value);
    }

    function comment()
    {
        return $this->hasManyThrough(GoodsOrdersComment::class, GoodsOrdersSubs::class, 'goods_id', 'sub_id', 'id', 'id');
    }


}
