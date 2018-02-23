<?php

namespace Webcore\Elogui\Repositories;

use Webcore\Elogui\Models\DataQuery;
use Webcore\Generator\Common\BaseRepository;

/**
 * Class DataQueryRepository
 * @package App\Repositories
 * @version January 12, 2018, 11:34 am UTC
 *
 * @method DataQuery findWithoutFail($id, $columns = ['*'])
 * @method DataQuery find($id, $columns = ['*'])
 * @method DataQuery first($columns = ['*'])
*/
class DataQueryRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'data_source_id',
        'command',
        'column',
        'operator',
        'value',
        'parent',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return DataQuery::class;
    }
}
