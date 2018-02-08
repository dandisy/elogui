<?php

namespace App\Http\Controllers;

use App\Models\DataQuery;
use App\Models\ColumnAlias;
use App\DataTables\DataSourceDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateDataSourceRequest;
use App\Http\Requests\UpdateDataSourceRequest;
use App\Repositories\DataSourceRepository;
use App\Repositories\DataQueryRepository;
use App\Repositories\ColumnAliasRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // add by dandisy
use Illuminate\Support\Facades\Storage; // add by dandisy

class DataSourceController extends AppBaseController
{
    /** @var  DataSourceRepository */
    private $dataSourceRepository;

    /** @var  DataQueryRepository */
    private $dataQueryRepository;

    private $queryUpdated = [];

    public function __construct(
        DataSourceRepository $dataSourceRepo, 
        DataQueryRepository $dataQueryRepo,
        ColumnAliasRepository $columnAliasRepo
    )
    {
        $this->middleware('auth');
        $this->dataSourceRepository = $dataSourceRepo;
        $this->dataQueryRepository = $dataQueryRepo;
        $this->columnAliasRepository = $columnAliasRepo;
    }

    /**
     * Display a listing of the DataSource.
     *
     * @param DataSourceDataTable $dataSourceDataTable
     * @return Response
     */
    public function index(DataSourceDataTable $dataSourceDataTable)
    {
        return $dataSourceDataTable->render('data_sources.index');
    }

    /**
     * Show the form for creating a new DataSource.
     *
     * @return Response
     */
    public function create(/*DataQueryDataTable $dataQueryDataTable*/)
    {
        // add by dandisy
        
        $models = array_map(function ($file) {
            $fileName = explode('.', $file);
            if(count($fileName) > 0) {
                return $fileName[0];
            }
        }, Storage::disk('model')->allFiles());

        $models = array_combine($models, $models);

        // edit by dandisy
        //return view('data_sources.create');
        return view('data_sources.create')
            ->with('models', $models);

        //return $dataQueryDataTable->render('data_sources.create', ['models' => $models]);
    }

    /**
     * Store a newly created DataSource in storage.
     *
     * @param CreateDataSourceRequest $request
     *
     * @return Response
     */
    public function store(CreateDataSourceRequest $request)
    {
        $input = $request->all();

        // handling data query
        $query = array_merge_recursive(
            $input['command'],
            array_key_exists('column', $input) ? $input['column'] : [],
            array_key_exists('operator', $input) ? $input['operator'] : [], 
            array_key_exists('value', $input) ? $input['value'] : []
        );

        unset($input['command']);
        if(array_key_exists('column', $input)) {
            unset($input['column']);
        }
        if(array_key_exists('operator', $input)) {
            unset($input['operator']);
        }
        if(array_key_exists('value', $input)) {
            unset($input['value']);
        }
        // end handling data query

        // get columns alias
        $alias = NULL;
        if(isset($input['alias'])) {
            $alias = $input['alias'];

            unset($input['alias']);
        }

        $input['created_by'] = Auth::user()->id;

        $dataSource = $this->dataSourceRepository->create($input);

        // handling data query
        foreach($query as $item) {
            $subQuery = NULL;
            if(isset($item['subquery'])) {
                $subQuery = $item['subquery'];

                unset($item['subquery']);
            }

            $dataQuery = array_map(function ($val) {
                if (is_array($val)) {
                    return implode(',', $val);
                }
        
                return $val;
            }, $item);

            $dataQuery['data_source_id'] = $dataSource->id;

            $dataQuery['created_by'] = Auth::user()->id;

            $query = $this->dataQueryRepository->create($dataQuery);

            $this->saveSubQuery($subQuery, $dataSource->id, $query->id);

            // handling sub query one level
//            if($subQuery) {
//                foreach ($subQuery as $sub) {
//                    $dataSubQuery = array_map(function ($val) {
//                        if (is_array($val)) {
//                            return implode(',', $val);
//                        }
//
//                        return $val;
//                    }, $sub);
//
//                    $dataSubQuery['data_source_id'] = $dataSource->id;
//
//                    $dataSubQuery['parent'] = $query->id;
//
//                    $dataSubQuery['created_by'] = Auth::user()->id;
//
//                    $query = $this->dataQueryRepository->create($dataSubQuery);
//                }
//            }
        }
        // end handling data query

        // handling column alias
        if($alias) {
            foreach($alias as $item) {
                if($item['alias'] || array_key_exists('select', $item)) {
                    $item['data_source_id'] = $dataSource->id;
                    $item['created_by'] = Auth::user()->id;

                    $this->columnAliasRepository->create($item);
                }
            }
        }
        // end handling column alias

        Flash::success('Data Source saved successfully.');

        return redirect(route('dataSources.index'));
    }

    /**
     * Display the specified DataSource.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $dataSource = $this->dataSourceRepository->findWithoutFail($id);

        if (empty($dataSource)) {
            Flash::error('Data Source not found');

            return redirect(route('dataSources.index'));
        }

        return view('data_sources.show')->with('dataSource', $dataSource);
    }

    /**
     * Show the form for editing the specified DataSource.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        // add by dandisy

        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            Flash::error('Connection to database server ERROR!');

            return view('data_sources.edit')
                ->with('dataSource', [])
                ->with('models', [])
                ->with('dataQuery', [])
                ->with('columns', [])
                ->with('columnAlias', []);
        }

        $models = array_map(function ($file) {
            $fileName = explode('.', $file);
            if(count($fileName) > 0) {
                return $fileName[0];
            }
        }, Storage::disk('model')->allFiles());

        $models = array_combine($models, $models);

        $dataSource = $this->dataSourceRepository->findWithoutFail($id);

        $dataQuery = $this->dataQueryRepository->findWhere(['data_source_id' => $id]);

        // get all column name of table
        $columns = [];
        if(isset($dataSource->model)) {
            $module = explode('/', $dataSource->model);
            $modelNS = $module[0];
            $modelName = $module[1];
            $modelFQNS = 'App\Models\Remote\\'.$modelNS.'\\'.$modelName;

            $model = new $modelFQNS();

            if($modelNS === 'ADDON') {
                $columns = $model->getTableColumns();
            } else {
                $db = $model->connection;

                $columns = DB::connection($db)->select(
                    DB::raw("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'".$model->table."'")
                );

                $columns = array_column($columns, 'COLUMN_NAME');
            }

            $relations = $dataQuery->whereIn('command', ['join', 'leftJoin']);

            if(count($relations)) {
                $columns = array_map(function($value) use ($model) {
                    return $model->table.'.'.$value;
                }, $columns);

                foreach($relations as $relation) {
                    $relValue = explode(',', $relation['value']);
                    $joinModule = explode('/', $relValue[0]);
                    $joinModelNS = $joinModule[0];
                    $joinModelName = $joinModule[1];
                    $joinModelFQNS = 'App\Models\Remote\\'.$joinModelNS.'\\'.$joinModelName;

                    $joinModel = new $joinModelFQNS();

                    if($joinModelNS === 'ADDON') {
                        $joinColumns = $joinModel->getTableColumns();
                    } else {
                        $joinDb = $joinModel->connection;

                        $joinColumns = DB::connection($joinDb)->select(
                            DB::raw("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'".$joinModel->table."'")
                        );

                        $joinColumns = array_column($joinColumns, 'COLUMN_NAME');
                    }

                    $joinColumns = array_map(function($value) use ($joinModel, $relValue) {
                        if(isset($relValue[3])) {
                            return $relValue[3].'.'.$value;
                        }

                        return $joinModel->table.'.'.$value;
                    }, $joinColumns);

                    $columns = array_merge($columns, $joinColumns);
                }
            }

            $columns = array_combine($columns, $columns);
        }
        // get all column name of table

        // get column alias
        $columnAlias = $this->columnAliasRepository->findWhere(['data_source_id' => $id]);
        // end get column alias

        if (empty($dataSource)) {
            Flash::error('Data Source not found');

            return redirect(route('dataSources.index'));
        }

        // edit by dandisy
        //return view('data_sources.edit')->with('dataSource', $dataSource);
        return view('data_sources.edit')
            ->with('dataSource', $dataSource)
            ->with('models', $models)
            ->with('dataQuery', $dataQuery)
            ->with('columns', $columns)
            ->with('columnAlias', $columnAlias);
    }

    /**
     * Update the specified DataSource in storage.
     *
     * @param  int              $id
     * @param UpdateDataSourceRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateDataSourceRequest $request)
    {
        $input = $request->all();

        // start handling data query
        $query = array_merge_recursive(
            $input['command'], 
            array_key_exists('column', $input) ? $input['column'] : [], 
            array_key_exists('operator', $input) ? $input['operator'] : [], 
            array_key_exists('value', $input) ? $input['value'] : [], 
            array_key_exists('index', $input) ? $input['index'] : []
        );

        unset($input['command']);
        if(array_key_exists('column', $input)) {
            unset($input['column']);
        }
        if(array_key_exists('operator', $input)) {
            unset($input['operator']);
        }
        if(array_key_exists('value', $input)) {
            unset($input['value']);
        }
        if(array_key_exists('index', $input)) {
            unset($input['index']);
        }

        foreach($query as $item) {
            $subQuery = NULL;
            if(isset($item['subquery'])) {
                $subQuery = $item['subquery'];

                unset($item['subquery']);
            }

            $dataQuery = array_map(function ($val) {
                if (is_array($val)) {
                    return implode(',', $val);
                }
        
                return $val;
            }, $item);

            if(array_key_exists('index', $dataQuery)) {
                $dataQuery['updated_by'] = Auth::user()->id;

                if(empty($dataQuery['column'])) {
                    $dataQuery['column'] = NULL;
                }

                $query = $this->dataQueryRepository->update($dataQuery, $dataQuery['index']);

                array_push($this->queryUpdated, $dataQuery['index']);
            } else {
                $dataQuery['data_source_id'] = $id;

                $dataQuery['created_by'] = Auth::user()->id;

                $query = $this->dataQueryRepository->create($dataQuery);

                array_push($this->queryUpdated, $query->id);
            }

            $this->saveSubQuery($subQuery, $id, $query->id);
        }

        if($this->queryUpdated) {
            DataQuery::where('data_source_id', $id)->whereNotIn('id', $this->queryUpdated)->delete();
        }
        // end handling data query        

        // handling columns alias
        if(isset($input['alias'])) {
            $alias = $input['alias'];

            unset($input['alias']);

            $aliasUpdated = [];

            foreach($alias as $item) {
                if(array_key_exists('index', $item)) {
                    if(!$item['alias']) {
                        unset($item['alias']);
                    }
                    if(!$item['edit']) {
                        unset($item['edit']);
                    }

                    $item['updated_by'] = Auth::user()->id;

                    $this->columnAliasRepository->update($item, $item['index']);

                    array_push($aliasUpdated, $item['index']);
                } else if($item['alias'] || $item['edit']){
                    if(!$item['alias']) {
                        unset($item['alias']);
                    }
                    if(!$item['edit']) {
                        unset($item['edit']);
                    }

                    $item['data_source_id'] = $id;
                    $item['created_by'] = Auth::user()->id;

                    $newAlias = $this->columnAliasRepository->create($item);

                    array_push($aliasUpdated, $newAlias->id);
                } else {
                    if(array_key_exists('select', $item)){
                        if(!$item['alias']) {
                            unset($item['alias']);
                        }
                        if(!$item['edit']) {
                            unset($item['edit']);
                        }

                        $item['data_source_id'] = $id;
                        $item['created_by'] = Auth::user()->id;
    
                        $newAlias = $this->columnAliasRepository->create($item);
    
                        array_push($aliasUpdated, $newAlias->id);
                    } else {
                        ColumnAlias::where('data_source_id', $id)->delete();
                    }
                }
            }

            if($aliasUpdated) {
                ColumnAlias::where('data_source_id', $id)->whereNotIn('id', $aliasUpdated)->delete();
            }
        }
        // end handling columns alias

        $input['updated_by'] = Auth::user()->id;

        $dataSource = $this->dataSourceRepository->findWithoutFail($id);

        if (empty($dataSource)) {
            Flash::error('Data Source not found');

            return redirect(route('dataSources.index'));
        }

        $dataSource = $this->dataSourceRepository->update($input, $id);

        Flash::success('Data Source updated successfully.');

        return redirect(route('dataSources.index'));
    }

    /**
     * Remove the specified DataSource from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $dataSource = $this->dataSourceRepository->findWithoutFail($id);

        if (empty($dataSource)) {
            Flash::error('Data Source not found');

            return redirect(route('dataSources.index'));
        }

        $this->dataSourceRepository->delete($id);

        // handling related data query
        $dataQuery = $this->dataQueryRepository->findWhere(['data_source_id' => $id]);

        foreach ($dataQuery as $item) {
            $this->dataQueryRepository->delete($item->id);
        }
        // end handling related data query

        Flash::success('Data Source deleted successfully.');

        return redirect(route('dataSources.index'));
    }

    private function arrayToStringe($val) {
        if (is_array($val)) {
            return implode(',',$val);
        }

        return $val;
    }

    private function saveSubQuery($query, $dataSourceId, $parentId) {
        if($query) {
            foreach ($query as $item) {
                $subQuery = NULL;

                if(isset($item['subquery'])) {
                    $subQuery = $item['subquery'];

                    unset($item['subquery']);
                }

                $dataQuery = array_map(function ($val) {
                    if (is_array($val)) {
                        return implode(',', $val);
                    }

                    return $val;
                }, $item);

                if(array_key_exists('index', $dataQuery)) {
                    $dataQuery['data_source_id'] = $dataSourceId;

                    $dataQuery['parent'] = $parentId;

                    $dataQuery['updated_by'] = Auth::user()->id;

                    if(empty($dataQuery['column'])) {
                        $dataQuery['column'] = NULL;
                    }

                    $query = $this->dataQueryRepository->update($dataQuery, $dataQuery['index']);

                    array_push($this->queryUpdated, $dataQuery['index']);
                } else {
                    $dataQuery['data_source_id'] = $dataSourceId;

                    $dataQuery['parent'] = $parentId;

                    $dataQuery['created_by'] = Auth::user()->id;

                    $query = $this->dataQueryRepository->create($dataQuery);

                    array_push($this->queryUpdated, $query->id);
                }

                $this->saveSubQuery($subQuery, $dataSourceId, $query->id);
            }
        }
    }
}
