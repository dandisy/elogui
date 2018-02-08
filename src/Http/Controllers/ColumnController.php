<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ColumnController extends Controller
{
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
    public function index(Request $request)
    {
        $data = NULL;

        if(!$request->model) {
            return [];
        }

        $module = explode('/', $request->model);
        $modelNS = $module[0];
        $modelName = $module[1];
        $modelFQNS = 'App\Models\Remote\\'.$modelNS.'\\'.$modelName;

        $model = new $modelFQNS();
    
        if($modelNS === 'ADDON') {
            $columns = $model->getTableColumns();
        } else {
            $db = $model->connection;

            // get all column name of table
            $columns = DB::connection($db)->select(
                DB::raw("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'".$model->table."'")
            );

            $columns = array_column($columns, 'COLUMN_NAME');
        }
    
        if($request->joinModel) {
            $columns = array_map(function($value) use ($model) {
                return $model->table.'.'.$value;
            }, $columns);
    
            foreach($request->joinModel as $item) {
                $items = explode(',', $item);
                $joinModule = explode('/', $items[0]);
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
    
                $joinColumns = array_map(function($value) use ($joinModel, $items) {
                    if(isset($items[3])) {
                        return $items[3].'.'.$value;
                    }

                    return $joinModel->table.'.'.$value;
                }, $joinColumns);
    
                $columns = array_merge($columns, $joinColumns);
            }
        }
    
        return $columns;
    }
}
