<?php

namespace App\Repositories;

use App\Models\ColumnAlias;
use Webcore\Generator\Common\BaseRepository;

/**
 * Class ColumnAliasRepository
 * @package App\Repositories
 * @version January 29, 2018, 6:02 pm UTC
 *
 * @method ColumnAlias findWithoutFail($id, $columns = ['*'])
 * @method ColumnAlias find($id, $columns = ['*'])
 * @method ColumnAlias first($columns = ['*'])
*/
class ColumnAliasRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'data_source_id',
        'name',
        'alias',
        'edit',
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
        return ColumnAlias::class;
    }
}
