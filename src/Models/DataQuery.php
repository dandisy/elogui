<?php

namespace Webcore\Elogui\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @SWG\Definition(
 *      definition="DataQuery",
 *      required={"data_source_id", "key"},
 *      @SWG\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="data_source_id",
 *          description="data_source_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="command",
 *          description="command",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="column",
 *          description="column",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="operator",
 *          description="operator",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="value",
 *          description="value",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="parent",
 *          description="parent",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="created_by",
 *          description="created_by",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="updated_by",
 *          description="updated_by",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="created_at",
 *          description="created_at",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @SWG\Property(
 *          property="updated_at",
 *          description="updated_at",
 *          type="string",
 *          format="date-time"
 *      )
 * )
 */
class DataQuery extends Model
{
    use SoftDeletes;

    public $table = 'data_queries';
    

    protected $dates = ['deleted_at'];


    public $fillable = [
        'data_source_id',
        'command',
        'column',
        'operator',
        'value',
        'parent',
        'created_by',
        'updated_by'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'data_source_id' => 'integer',
        'command' => 'command',
        'column' => 'column',
        'operator' => 'operator',
        'value' => 'string',
        'parent' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'data_source_id' => 'required',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function dataSource()
    {
        return $this->belongsTo(\Webcore\Elogui\Models\DataSource::class, 'data_source_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function dataQuery()
    {
        return $this->belongsTo(\Webcore\Elogui\Models\DataQuery::class, 'parent', 'id');
    }
}
