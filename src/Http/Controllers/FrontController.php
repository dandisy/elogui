<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Page;
use Illuminate\Support\Facades\DB;
use App\Repositories\DataQueryRepository;
use App\DataTables\GlobalDataTable;

/**
 * SUPPORT
 * 1. laravel query
 * for now only one level of sub query in (join) and (with) command
 *
 * 2. datatable
 * select columns in join table of (join) command
 * column alias on (select) command
 * column edit on (select) command
 * column addition on (selectRaw) command
 * only one datatable per page (because, for now only process the first array of data)
 *
 * NOT YET SUPPORTED
 * 1. laravel query
 * multi level of sub query
 *
 * 2. datatable
 * select columns in related model using eager loading (with) command
 * column alias in (selectRaw) command
 * multi datatable in one page
 *
 * USAGE
 * 1. laravel query
 * for raw query must select model in model dropdown selection
 *
 * 2. datatable
 * for column alias, add edit columns same as alias columns name
 * for select column in related table use (select) command and select some columns in column input multi-select form or
 * use (selectRaw) command
 * for date format use dd/mm/YYYY to change search column to date picker
 */
class FrontController extends Controller
{
    private $relations = [];
    private $dataAliasColumn = [];
    private $dataEditColumn = [];
    private $dataEditColumnRelation = [];
    private $dataAddColumn = [];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, GlobalDataTable $globalDataTable, DataQueryRepository $dataQueryRepository, $slug)
    {
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            return 'Connection to remote database server ERROR!';
        }

        $items = NULL;

        $page = Page::with('presentations')
            ->with('presentations.component')
            ->with('presentations.component.dataSource')
            ->with('presentations.component.dataSource.dataQuery')
            ->with('presentations.component.dataSource.columnAlias')
            ->where('slug', $slug)
            ->where('status', 'publish')
            ->first();

        $dataDashboard = $this->getDataScreenDashboard($page->tag);

        $presentations =  $this->getPresentations($page, $dataQueryRepository);

        $model = $presentations['dataTable']['model'];

        // remove sensitive data
        $presentations['dataTable']->forget('model');

        if($presentations) {
            return $globalDataTable
                ->with('data', ['model' => $model, 'dataTable' => $presentations['dataTable']])
                ->render('vendor.themes.'.str_replace('/', '.', $page->template), [
                    'items' => $presentations,
                    'dataDashboard' => $dataDashboard,
                    'display' => $request->display,
                    'key' => $request->key ? : NULL
                ]);
        }
    
        return abort(404);
    }

    private function getPresentations($page, DataQueryRepository $dataQueryRepository) {
        $presentations = NULL;

        if($page && $page->has('presentations') && count($page->presentations) > 0) {
            $model = NULL;
            $modelData = NULL;
            $data = NULL;
            $columnsUniqueData = [];

            foreach($page->presentations as $key => $presentation) {
                $component = $presentation['component'];

                if(isset($component['dataSource'])) {
                    $dataSource = $component['dataSource'];

                    if($dataSource) {
                        if($dataSource['model']) {
                            $queryData = $this->getQueryData($dataSource, $dataQueryRepository);
                            $model = $queryData['model'];
                            $modelData = gettype($model) === 'array' ? $model : $model->get();
                            $modelColumns = $queryData['columns'];

                            // collecting column alias & column edit for datatable
                            $this->getColumnsDataTable($modelColumns, $dataSource);
                        } else {
                            // not working, corection for $model->connection undefined if $dataSource['model'] is false
                            /*if(isset($dataSource['dataQuery'])) {
                                $dataQuery = $dataSource['dataQuery'];
                                if(isset($dataQuery[0]['command'])) {
                                    if($dataQuery[0]['command'] === 'raw') {
                                        $modelData = DB::connection($model->connection ? : 'mysql')->select(
                                            DB::raw(trim(preg_replace('/\s+/', ' ', $dataQuery[0]['value'])))
                                        );
                                    }
                                }
                            }*/
                        }
                    }
                }

                $data[$presentation->component_id] = $modelData;
            }

            // get unique columns data for datatable column filter dropdown
            // note : for now only support one datatable in a page
            foreach(array_column($this->dataAliasColumn, 'title') as $item) {
                $columnsUniqueData[$item] = array_unique(array_column($modelData->toArray(), $item));
            }

            $dataTable = [
                'model' => $model,
                'columns' => $this->dataAliasColumn,
                'editColumns' => $this->dataEditColumn,
                'editColumnsRelation' => $this->dataEditColumnRelation,
                'addColumns' => $this->dataAddColumn,
                'columnsUniqueData' => $columnsUniqueData
            ];

            // remove sensitive data
            $dataPage = collect($page->toArray())->recursive();

            foreach ($dataPage['presentations'] as $item) {
                if($item['component']['data_source']) {
                    $item['component']['data_source']->forget('data_query');
                    $item['component']['data_source']->forget('column_alias');
                }
            }
            // end remove sensitive data

            $presentations = [
                'data' => collect($data)->recursive(),
                'dataTable' => collect($dataTable),
                'page' => $dataPage
            ];
        }

        return collect($presentations);
    }

    private function getQueryData($dataSource, DataQueryRepository $dataQueryRepository) {
        $columns = [];
        $asSubQuery = NULL;

        $modelFQNS = 'App\Models\Remote\\'.str_replace('/', '\\', $dataSource['model']);

        $data = new $modelFQNS();

        foreach($dataSource['dataQuery'] as $query) {
            $id = $query['id'];
            $hasSubQuery = $dataQueryRepository->findWhere(['parent' => $id]);
            $asSubQuery = $query['parent'];

            $command = $query['command'];

            if($command === 'latest') {
                $data = $data->latest();
            } else if(
                $command === 'select' ||
                $command === 'addSelect' ||
                $command === 'groupBy' ||
                $command === 'whereNull' ||
                $command === 'whereNotNull' ||
                $command === 'avg' ||
                $command === 'max'
            ) {
                // for relevant command as sub query, don't process command, sub query command will be handle separately
                if(!$asSubQuery) {
                    if (isset($query['column'])) {
                        $columns = explode(',', $query['column']);
                    } else if ($command === 'select') {
                        // get all column name from selected model
                        $columns = $this->getColumnsName($dataSource['model']);
                    }

                    $columnsAlias = array_pluck($dataSource['columnAlias'], 'alias', 'name');

                    $columnsSelect = array_map(function ($value) use ($columnsAlias) {
                        if (isset($columnsAlias[$value])) {
                            if ($columnsAlias[$value] && $columnsAlias[$value] != 'null') {
                                return $value . ' AS ' . $columnsAlias[$value];
                            }
                        }

                        return $value;
                    }, $columns);

                    $data = $data->$command($columnsSelect);
                }
            } else if(
                $command === 'where' ||
                $command === 'orWhere' ||
                $command === 'whereDate' ||
                $command === 'whereMonth' ||
                $command === 'whereDay' ||
                $command === 'whereYear' ||
                $command === 'whereTime' ||
                $command === 'whereColumn' ||
                $command === 'having'
            ) {
                // for relevant command as sub query, don't process command, sub query command will be handle separately
                if(!$asSubQuery) {
                    $data = $data->$command(
                        $query['column'],
                        $query['operator'],
                        $query['value']
                    );
                }
            } else if(
                $command === 'orderBy' ||
                $command === 'whereIn' ||
                $command === 'whereNotIn' ||
                $command === 'whereBetween' ||
                $command === 'whereNotBetween'
            ) {
                // for relevant command as sub query, don't process command, sub query command will be handle separately
                if(!$asSubQuery) {
                    $value = explode(',', $query['value']);

                    if (count($value) > 1) {
                        $data = $data->$command($query['column'], $value);
                    } else {
                        $data = $data->$command($query['column'], $query['value']);
                    }
                }
            } else if(
                $command === 'offset' ||
                $command === 'limit' ||
                $command === 'whereRaw' ||
                $command === 'orWhereRaw' ||
                $command === 'orderByRaw' ||
                $command === 'havingRaw'
            ) {
                // for relevant command as sub query, don't process command, sub query command will be handle separately
                if(!$asSubQuery) {
                    $data = $data->$command($query['value']);
                }
            } else if($command === 'selectRaw') {
                // for relevant command as sub query, don't process command, sub query command will be handle separately
                if(!$asSubQuery) {
                    $data = $data->$command($query['value']);

                    // preparing add column on datatable
                    $addition = explode(',', $query['value']);

                    foreach ($addition as $item) {
                        $addItem = explode(' ', $item);

                        if(isset($addItem[2])) {
                            $this->dataAddColumn[$addItem[2]] = $addItem[0];
                        } else {
                            $this->dataAddColumn[$addItem[0]] = $addItem[0];
                        }
                    }
                }
            } else if($command === 'with') {
                // for relevant command as sub query, don't process command, sub query command will be handle separately
                if(!$asSubQuery) {
                    if (!$hasSubQuery) {
                        $data = $data->$command($query['value']);
                    } else {
                        // handling sub query of with command
                        $data = $data->$command($query['value'], function ($query) use ($hasSubQuery) {
                            foreach ($hasSubQuery as $sub) {
                                $subCommand = $sub['command'];

                                if (
                                    $subCommand === 'whereNull' ||
                                    $subCommand === 'whereNotNull'
                                ) {
                                    $query = $query->$subCommand($sub['column']);
                                } else if (
                                    $subCommand === 'on' ||
                                    $subCommand === 'orOn' ||
                                    $subCommand === 'where' ||
                                    $subCommand === 'orWhere' ||
                                    $subCommand === 'whereDate' ||
                                    $subCommand === 'whereMonth' ||
                                    $subCommand === 'whereDay' ||
                                    $subCommand === 'whereYear' ||
                                    $subCommand === 'whereTime' ||
                                    $subCommand === 'whereColumn'
                                ) {
                                    $query = $query->$subCommand($sub['column'], $sub['operator'], $sub['value']);
                                } else if (
                                    $subCommand === 'whereIn' ||
                                    $subCommand === 'whereNotIn' ||
                                    $subCommand === 'whereBetween' ||
                                    $subCommand === 'whereNotBetween'
                                ) {
                                    $query = $query->$subCommand($sub['column'], $sub['value']);
                                } else if (
                                    $subCommand === 'whereRaw' ||
                                    $subCommand === 'orWhereRaw'
                                ) {
                                    $query = $query->$subCommand($sub['value']);
                                }
                            }
                        });
                    }
                }
            } else if($command === 'join' || $command === 'leftJoin') {
                // for relevant command as sub query, don't process command, sub query command will be handle separately
                if(!$asSubQuery) {
                    $value = explode(',', $query['value']);
                    $joinModule = explode('/', $value[0]);
                    $joinModelNS = $joinModule[0];
                    $joinModelName = $joinModule[1];
                    $JoinModelFQNS = 'App\Models\Remote\\' . $joinModelNS . '\\' . $joinModelName;

                    $joinModel = new $JoinModelFQNS();

                    $joinTable = $joinModel->table;

                    if (isset($value[3])) {
                        $joinTable .= ' AS ' . $value[3];
                    }

                    if (!$hasSubQuery) {
                        $data = $data->$command($joinTable, $value[1], '=', $value[2]);
                    } else {
                        // handling sub query of join and leftJoin command
                        $data = $data->$command($joinTable, function ($query) use ($hasSubQuery, $value) {
                            $query = $query->on($value[1], '=', $value[2]);

                            foreach ($hasSubQuery as $sub) {
                                $subCommand = $sub['command'];

                                if (
                                    $subCommand === 'whereNull' ||
                                    $subCommand === 'whereNotNull'
                                ) {
                                    $query = $query->$subCommand($sub['column']);
                                } else if (
                                    $subCommand === 'where' ||
                                    $subCommand === 'orWhere' ||
                                    $subCommand === 'whereDate' ||
                                    $subCommand === 'whereMonth' ||
                                    $subCommand === 'whereDay' ||
                                    $subCommand === 'whereYear' ||
                                    $subCommand === 'whereTime' ||
                                    $subCommand === 'whereColumn'
                                ) {
                                    $query = $query->$subCommand($sub['column'], $sub['operator'], $sub['value']);
                                } else if (
                                    $subCommand === 'whereIn' ||
                                    $subCommand === 'whereNotIn' ||
                                    $subCommand === 'whereBetween' ||
                                    $subCommand === 'whereNotBetween'
                                ) {
                                    $query = $query->$subCommand($sub['column'], $sub['value']);
                                } else if (
                                    $subCommand === 'whereRaw' ||
                                    $subCommand === 'orWhereRaw'
                                ) {
                                    $query = $query->$subCommand($sub['value']);
                                }
                            }
                        });
                    }
                    // end handling sub query of join and leftJoin command

                    // preparing to get all column name of join table
                    array_push($this->relations, $query['value']);
                }
            } else if($command === 'raw') {
                // for relevant command as sub query, don't process command, sub query command will be handle separately
                if(!$asSubQuery) {
                    $data = DB::connection($data->connection ?: 'mysql')->select(
                        DB::raw(trim(preg_replace('/\s+/', ' ', $query['value'])))
                    );
                }
            }
        }

        if (array_key_exists('dataQuery', $dataSource)) {
            $lastCommand = end($dataSource['dataQuery'])[0]['command'];

            if($lastCommand === 'first') {
                $data = $data->first();
            } else if($lastCommand === 'inRandomOrder') {
                $data = $data->inRandomOrder();
            } else if($lastCommand === 'count') {
                // for relevant command as sub query, don't process command, sub query command will be handle separately
                if(!$asSubQuery) {
                    $data = $data->count();
                }
            } else if($lastCommand === 'max') {
                // for relevant command as sub query, don't process command, sub query command will be handle separately
                if(!$asSubQuery) {
                    $data = $data->max(explode(',', end($dataSource['dataQuery'])['column']));
                }
            } else if($lastCommand === 'avg') {
                // for relevant command as sub query, don't process command, sub query command will be handle separately
                if(!$asSubQuery) {
                    $data = $data->avg(explode(',', end($dataSource['dataQuery'])['column']));
                }
            } else {
                $data = $data;
            }
        } else {
            $data = $data;
        }

        return [
            'columns' => $columns,
            'model' => $data
        ];
    }

    private function getColumnsName($moduleName) {
        $module = explode('/', $moduleName);

        // get all column name from selected model
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
        // end get all column name from selected model

        // get all column name of join table
        if($this->relations) {
            $columns = array_map(function($value) use ($model) {
                return $model->table.'.'.$value;
            }, $columns);

            foreach($this->relations as $val) {
                $value = explode(',', $val);
                $joinModule = explode('/', $value[0]);
                $joinNS = $joinModule[0];
                $joinModelName = $joinModule[1];
                $JoinModelFQNS = 'App\Models\Remote\\'.$joinNS.'\\'.$joinModelName;

                $joinModel = new $JoinModelFQNS();

                if($joinNS === 'ADDON') {
                    $joinColumns = $joinModel->getTableColumns();
                } else {
                    $joinDb = $joinModel->connection;

                    $joinColumns = DB::connection($joinDb)->select(
                        DB::raw("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'".$joinModel->table."'")
                    );

                    $joinColumns = array_column($joinColumns, 'COLUMN_NAME');
                }

                $joinColumns = array_map(function($value) use ($joinModel) {
                    return $joinModel->table.'.'.$value;
                }, $joinColumns);

                $columns = array_merge($columns, $joinColumns);
            }
        }
        // end get all column name of join table

        return $columns;
    }

    private function getColumnsDataTable($columns, $dataSource) {
        if($columns) {
            // collecting column alias & column edit for datatable
            $alias = $dataSource['columnAlias']->pluck('alias', 'name')->toArray();
            $edit = $dataSource['columnAlias']->pluck('edit', 'name')->toArray();

            foreach ($columns as $value) {
                if (strpos($value, '.')) {
                    $columnName = trim(substr($value, strrpos($value, '.') + 1));
                    if (array_key_exists($value, $alias)) {
                        if ($alias[$value] && $alias[$value] != 'null') {
                            $this->dataAliasColumn[$alias[$value]] = [
                                'data' => $alias[$value],
                                'name' => $alias[$value],
                                'title' => $alias[$value]
                            ];
                        } else {
                            $this->dataAliasColumn[$value] = [
                                'data' => $columnName,
                                'name' => $value,
                                'title' => $columnName
                            ];
                        }
                    }
                } else {
                    if (array_key_exists($value, $alias)) {
                        if ($alias[$value] && $alias[$value] != 'null') {
                            $this->dataAliasColumn[$value] = ['title' => $alias[$value]];
                        }/* else {
                            $this->dataAliasColumn[$value]['title'] = $columnName;
                        }*/
                    } else {
                        array_push($this->dataAliasColumn, $value);
                    }
                }

                /*if(array_key_exists($value, $edit)) {
                    if($edit[$value] && $edit[$value] != 'null') {
                        if(!array_search($edit[$value], $dataAliasColumn)) {
                            $this->dataEditColumn[$columnName] = $edit[$value];
                        } else {
                            // $this->dataAddColumn[$columnName] = $edit[$value];
                        }
                    }
                }*/
            }
            // end collecting column alias & column edit for datatable

            // collecting additional column for datatable
            if ($this->dataAddColumn) {
                foreach ($this->dataAddColumn as $key => $item) {
                    $this->dataAliasColumn[$key] = ['data' => $key, 'name' => $item, 'title' => $key];
                }
            }

            // collecting edit column for datatable
            foreach ($edit as $key => $item) {
                $columnName = trim(substr($key, strrpos($key, '.') + 1));

                if (!array_key_exists($item, $this->dataAliasColumn) && $item && $item != 'null') {
                    $this->dataEditColumn[$columnName] = $item;
                } else if (array_key_exists($item, $this->dataAliasColumn)) {
                    $this->dataEditColumnRelation[$item] = $key;

                    $this->dataAliasColumn[$item]['name'] = $key;
                }
            }
        } else {
            // get all columns name and alias if no select command
            // get all column name from selected model
            $module = explode('/', $dataSource['model']);
            $modelNS = $module[0];
            $modelName = $module[1];
            $modelFQNS = 'App\Models\Remote\\' . $modelNS . '\\' . $modelName;

            $model = new $modelFQNS();

            if ($modelNS === 'ADDON') {
                $columns = $model->getTableColumns();
            } else {
                $db = $model->connection;

                $columns = DB::connection($db)->select(
                    DB::raw("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'" . $model->table . "'")
                );

                $columns = array_column($columns, 'COLUMN_NAME');
            }
            // end all column name from selected model

            // get all column name of join table
            if ($this->relations) {
                $columns = array_map(function ($value) use ($model) {
                    return $model->table . '.' . $value;
                }, $columns);

                foreach ($this->relations as $val) {
                    $value = explode(',', $val);
                    $joinModule = explode('/', $value[0]);
                    $joinNS = $joinModule[0];
                    $joinModelName = $joinModule[1];
                    $JoinModelFQNS = 'App\Models\Remote\\' . $joinNS . '\\' . $joinModelName;

                    $joinModel = new $JoinModelFQNS();

                    if ($joinNS === 'ADDON') {
                        $joinColumns = $joinModel->getTableColumns();
                    } else {
                        $joinDb = $joinModel->connection;

                        $joinColumns = DB::connection($joinDb)->select(
                            DB::raw("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'" . $joinModel->table . "'")
                        );

                        $joinColumns = array_column($joinColumns, 'COLUMN_NAME');
                    }

                    $joinColumns = array_map(function ($value) use ($joinModel) {
                        return $joinModel->table . '.' . $value;
                    }, $joinColumns);

                    $columns = array_merge($columns, $joinColumns);
                }
            }
            // end get all column name of join table
            // end get all columns name and alias if no select command

            foreach ($columns as $value) {
                if (strpos($value, '.')) {
                    $columnName = trim(substr($value, strrpos($value, '.') + 1));

                    array_push($this->dataAliasColumn, $columnName);
                } else {
                    array_push($this->dataAliasColumn, $value);
                }
            }
        }
    }

    // must be remove, and use query definition in admin instead
    private function getDataScreenDashboard($tag) {
        $dataDashboard = [];

        if($tag === 'screen-dashboard') {
// participant
            $query1 = <<<HEREDOC
                    SELECT
                        a2.ORG_CD_NM
                        ,(
                            SELECT
                                COUNT(aa2.ORG_NM) AS aa
                            FROM ACRPARTIM aa1
                                LEFT JOIN ACRORGM aa2 ON aa2.ORG_CD=aa1.ORG_CD
                                LEFT JOIN ACRFUNCTIONM aa3 ON aa3.FUNCTION_CD=aa1.FUNCTION_CD
                            WHERE aa3.FUNCTION_NM <> 'Athlete' AND aa2.ORG_CD_NM=a2.ORG_CD_NM AND (aa1.CANCEL_YN = '' OR aa1.CANCEL_YN IS NULL)
                            GROUP BY aa2.ORG_CD_NM
                        ) AS Athlete
                        ,(
                            SELECT
                                COUNT(ab2.ORG_NM) AS ab
                            FROM ACRPARTIM ab1
                                LEFT JOIN ACRORGM ab2 ON ab2.ORG_CD=ab1.ORG_CD
                                LEFT JOIN ACRFUNCTIONM ab3 ON ab3.FUNCTION_CD=ab1.FUNCTION_CD
                            WHERE ab3.FUNCTION_NM = 'Athlete' AND ab2.ORG_CD_NM=a2.ORG_CD_NM AND (ab1.CANCEL_YN = '' OR ab1.CANCEL_YN IS NULL)
                            GROUP BY ab2.ORG_CD_NM
                        ) AS Official
                        ,(
                            SELECT
                                COUNT(ac2.ORG_NM) AS ac
                            FROM ACRPARTIM ac1
                                LEFT JOIN ACRORGM ac2 ON ac2.ORG_CD=ac1.ORG_CD
                                LEFT JOIN ACRFUNCTIONM ac3 ON ac3.FUNCTION_CD=ac1.FUNCTION_CD
                                LEFT JOIN ACRCATEGORYM ac4 ON ac4.CATEGORY_CD=ac1.CATEGORY_CD
                            WHERE ac2.ORG_CD_NM=a2.ORG_CD_NM AND (ac1.CANCEL_YN = '' OR ac1.CANCEL_YN IS NULL) AND ac4.CATEGORY_CD_NM LIKE 'A-%'
                            GROUP BY ac2.ORG_CD_NM
                        ) AS VIP
                        , COUNT(a2.ORG_CD_NM) AS RO
                    FROM ACRPARTIM a1
                        LEFT JOIN ACRORGM a2 ON a2.ORG_CD=a1.ORG_CD
                        LEFT JOIN ACRFUNCTIONM a3 ON a3.FUNCTION_CD=a1.FUNCTION_CD
                    WHERE a2.ORG_CD_NM <> 'FIBA' OR a2.ORG_CD_NM <> 'FIBA ASIA' OR a2.ORG_CD_NM <> 'INASGOC' OR a2.ORG_CD_NM <> 'PERSILAT'
                    GROUP BY a2.ORG_CD_NM
                    ORDER BY a2.ORG_CD_NM ASC
HEREDOC;

            $dataDashboard['participants'] = ['all' => DB::connection('sqlsrv')->select(
                DB::raw($query1)
            )];

            $dataDashboard['participants']['athlete'] = DB::connection('sqlsrv')->table('ACRPARTIM')
                ->select('ACRORGM.ORG_CD_NM', DB::raw('count(*) as total'))
                ->leftJoin('ACRORGM', 'ACRORGM.ORG_CD', '=', 'ACRPARTIM.ORG_CD')
                ->leftJoin('ACRFUNCTIONM', 'ACRFUNCTIONM.FUNCTION_CD', '=', 'ACRPARTIM.FUNCTION_CD')
                ->groupBy('ACRORGM.ORG_CD_NM')
                ->where('ACRFUNCTIONM.FUNCTION_NM', '!=', 'Athlete')
                ->get();

            $dataDashboard['participants']['official'] = DB::connection('sqlsrv')->table('ACRPARTIM')
                ->select('ACRORGM.ORG_CD_NM', DB::raw('count(*) as total'))
                ->leftJoin('ACRORGM', 'ACRORGM.ORG_CD', '=', 'ACRPARTIM.ORG_CD')
                ->leftJoin('ACRFUNCTIONM', 'ACRFUNCTIONM.FUNCTION_CD', '=', 'ACRPARTIM.FUNCTION_CD')
                ->groupBy('ACRORGM.ORG_CD_NM')
                ->where('ACRFUNCTIONM.FUNCTION_NM', '!=', 'Athlete')
                ->get();

            $query = <<<HEREDOC
                    SELECT
                        a1.AD_NO AS GUESTID
                        , CONCAT(a1.FAMILY_NM,', ',a1.GIVEN_NM) AS GUESTNAME
                        , a1.GENDER
                        , a1.BIRTH_DT AS DOB
                        , a1.HEIGHT
                        , a1.WEIGHT
                        , a2.ORG_CD_NM
                        , a2.ORG_NM AS RESP_ORG
                        , a3.FUNCTION_NM
                        , a4.CATEGORY_CD_NM
                        , a1.BORN_CTRY_CD
                        , t1.CODE_NM1 AS NATIONALITY
                        , a1.ADDR_CTRY_CD
                        , t2.CODE_NM1 AS ORIGIN
                        , a1.PASSPORT_NO
                        , a1.ID_NUMBER1 AS KTP_NO
                        , a1.SPORT1_CD
                        , t3.CODE_NM1 AS SPORT
                        , at1.CHECKIN_YN
                        , at1.CHECKIN_DT
                        , at1.CHECKOUT_DT
                    FROM ACRPARTIM a1
                        LEFT JOIN ACRORGM a2 ON a2.ORG_CD=a1.ORG_CD
                        LEFT JOIN ACRFUNCTIONM a3 ON a3.FUNCTION_CD=a1.FUNCTION_CD
                        LEFT JOIN ACRCATEGORYM a4 ON a4.CATEGORY_CD=a1.CATEGORY_CD
                        LEFT JOIN TCOCODEM t1 ON t1.MINOR_CD=a1.BORN_CTRY_CD AND t1.MAJOR_CD='052'
                        LEFT JOIN TCOCODEM t2 ON t2.MINOR_CD=a1.ADDR_CTRY_CD AND t2.MAJOR_CD='052'
                        LEFT JOIN TCOCODEM t3 ON t3.MINOR_CD=a1.SPORT1_CD AND t3.MAJOR_CD='110'
                        LEFT JOIN ATVINOUTD at1 ON at1.AD_NO=a1.AD_NO
                    WHERE (a1.CANCEL_YN = '' OR a1.CANCEL_YN IS NULL) AND a4.CATEGORY_CD_NM LIKE 'A-%'
HEREDOC;

            $dataDashboard['participants']['vip'] = DB::connection('sqlsrv')->select(
                DB::raw($query)
            );
// end participant

// ses
            $query2 = <<<HEREDOC
                    SELECT
                        t3.CODE_NM1 AS SPORT
                    FROM ACRPARTIM a1
                        JOIN TCOCODEM t3 ON t3.MINOR_CD=a1.SPORT1_CD AND t3.MAJOR_CD='110'
                    WHERE (a1.CANCEL_YN = '' OR a1.CANCEL_YN IS NULL)
                    GROUP BY t3.CODE_NM1 
HEREDOC;

            $dataDashboard['sports'] = DB::connection('sqlsrv')->select(
                DB::raw($query2)
            );

            $query3 = <<<HEREDOC
                    SELECT
                        a2.ORG_CD_NM
                        , a3.FUNCTION_NM
                        , t3.CODE_NM1 AS SPORT
                        , COUNT(a2.ORG_CD_NM) AS total
                    FROM ACRPARTIM a1
                        LEFT JOIN ACRORGM a2 ON a2.ORG_CD=a1.ORG_CD
                        LEFT JOIN ACRFUNCTIONM a3 ON a3.FUNCTION_CD=a1.FUNCTION_CD
                        LEFT JOIN TCOCODEM t3 ON t3.MINOR_CD=a1.SPORT1_CD AND t3.MAJOR_CD='110'
                    WHERE (a1.CANCEL_YN = '' OR a1.CANCEL_YN IS NULL) AND 
                    (a2.ORG_CD_NM <> 'FIBA' OR a2.ORG_CD_NM <> 'FIBA ASIA' OR a2.ORG_CD_NM <> 'INASGOC' OR a2.ORG_CD_NM <> 'PERSILAT') AND
                    a3.FUNCTION_NM = 'Athlete'
                    GROUP BY a2.ORG_CD_NM, a3.FUNCTION_NM, t3.CODE_NM1
                    ORDER BY a2.ORG_CD_NM ASC
HEREDOC;

            $dataSport = DB::connection('sqlsrv')->select(
                DB::raw($query3)
            );

            $dataEnd = [];

            foreach($dataSport as $i => $v) {
                if(array_search($v->ORG_CD_NM, array_column($dataEnd, 'RO'))) {
                    $key = array_search($v->ORG_CD_NM, array_column($dataEnd, 'RO'));

                    if($v->SPORT) {
                        $dataEnd[$key][$v->SPORT] = $v->total;
                    }
                } else {
                    $idx = count($dataEnd);
                    $dataEnd[$idx] = ['RO' => $v->ORG_CD_NM];
                    if($v->SPORT) {
                        $dataEnd[$idx][$v->SPORT] = $v->total;
                    }
                }
            }

            $dataDashboard['sports2'] = $dataEnd;
// end ses

// svm
            $dataDashboard['volunteer'] = ['pos' => DB::table('users')
                ->select('profiles.position', 'role_user.role_id', DB::raw('count(*) as total'))
                ->leftJoin('role_user', 'role_user.user_id', '=', 'users.id')
                ->leftJoin('profiles', 'profiles.user_id', '=', 'users.id')
                ->groupBy('profiles.position')
                ->having('role_user.role_id', '=', '4')
                ->having('profiles.position', 'NOT LIKE', '%@%')
                ->get()];

            $dataDashboard['volunteer']['dept'] =  DB::table('users')
                ->select('profiles.department', 'role_user.role_id', DB::raw('count(*) as total'))
                ->leftJoin('role_user', 'role_user.user_id', '=', 'users.id')
                ->leftJoin('profiles', 'profiles.user_id', '=', 'users.id')
                ->groupBy('profiles.department')
                ->having('role_user.role_id', '=', '4')
                ->get();
// end svm
        }

        return $dataDashboard;
    }

}
