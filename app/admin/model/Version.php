<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * @property integer $id 主键(主键)
 * @property string $url 下载地址
 * @property string $version 版本号
 * @property integer $type 更新类型:1=全量更新,2=增量更新
 * @property integer $must 强制更新:0=否,1=是
 * @property string $remark 更新说明
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class Version extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_version';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'url',
        'version',
        'type',
        'must',
        'remark',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'type_text',
        'must_text',
    ];

    function getTypeTextAttribute($value)
    {
        $value = $value ? $value : $this->type;
        $list = $this->getTypeList();
        return $list[$value]??'';
    }

    function getTypeList()
    {
        return [
            1=>'全量更新',
            2=>'增量更新'
        ];
    }

    function getMustTextAttribute($value)
    {
        $value = $value ? $value : $this->must;
        $list = $this->getMustList();
        return $list[$value]??'';
    }

    function getMustList()
    {
        return [
            0=>'否',
            1=>'是'
        ];
    }
    
    
    
}
