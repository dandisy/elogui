<?php

namespace Webcore\Elogui\Controllers;

use Webcore\Elogui\DataTables\DataQueryDataTable;
use App\Http\Requests;
use Webcore\Elogui\Requests\CreateDataQueryRequest;
use Webcore\Elogui\Requests\UpdateDataQueryRequest;
use Webcore\Elogui\Repositories\DataQueryRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;
use Illuminate\Support\Facades\Auth; // add by dandisy
use Illuminate\Support\Facades\Storage; // add by dandisy

class DataQueryController extends AppBaseController
{
    /** @var  DataQueryRepository */
    private $dataQueryRepository;

    public function __construct(DataQueryRepository $dataQueryRepo)
    {
        $this->middleware('auth');
        $this->dataQueryRepository = $dataQueryRepo;
    }

    /**
     * Display a listing of the DataQuery.
     *
     * @param DataQueryDataTable $dataQueryDataTable
     * @return Response
     */
    public function index(DataQueryDataTable $dataQueryDataTable)
    {
        return $dataQueryDataTable->render('elogui::data_queries.index');
    }

    /**
     * Show the form for creating a new DataQuery.
     *
     * @return Response
     */
    public function create()
    {
        // add by dandisy
        $datasource = \Webcore\Elogui\Models\DataSource::all();
        $dataquery = \Webcore\Elogui\Models\DataQuery::all();
        

        // edit by dandisy
        //return view('data_queries.create');
        return view('elogui::data_queries.create')
            ->with('datasource', $datasource)
            ->with('dataquery', $dataquery);
    }

    /**
     * Store a newly created DataQuery in storage.
     *
     * @param CreateDataQueryRequest $request
     *
     * @return Response
     */
    public function store(CreateDataQueryRequest $request)
    {
        $input = $request->all();

        $input['created_by'] = Auth::user()->id;

        $dataQuery = $this->dataQueryRepository->create($input);

        Flash::success('Data Query saved successfully.');

        return redirect(route('dataQueries.index'));
    }

    /**
     * Display the specified DataQuery.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $dataQuery = $this->dataQueryRepository->findWithoutFail($id);

        if (empty($dataQuery)) {
            Flash::error('Data Query not found');

            return redirect(route('dataQueries.index'));
        }

        return view('elogui::data_queries.show')->with('dataQuery', $dataQuery);
    }

    /**
     * Show the form for editing the specified DataQuery.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        // add by dandisy
        $datasource = \Webcore\Elogui\Models\DataSource::all();
        $dataquery = \Webcore\Elogui\Models\DataQuery::all();
        

        $dataQuery = $this->dataQueryRepository->findWithoutFail($id);

        if (empty($dataQuery)) {
            Flash::error('Data Query not found');

            return redirect(route('dataQueries.index'));
        }

        // edit by dandisy
        //return view('data_queries.edit')->with('dataQuery', $dataQuery);
        return view('elogui::data_queries.edit')
            ->with('dataQuery', $dataQuery)
            ->with('datasource', $datasource)
            ->with('dataquery', $dataquery);
    }

    /**
     * Update the specified DataQuery in storage.
     *
     * @param  int              $id
     * @param UpdateDataQueryRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateDataQueryRequest $request)
    {
        $input = $request->all();

        $input['updated_by'] = Auth::user()->id;

        $dataQuery = $this->dataQueryRepository->findWithoutFail($id);

        if (empty($dataQuery)) {
            Flash::error('Data Query not found');

            return redirect(route('dataQueries.index'));
        }

        $dataQuery = $this->dataQueryRepository->update($input, $id);

        Flash::success('Data Query updated successfully.');

        return redirect(route('dataQueries.index'));
    }

    /**
     * Remove the specified DataQuery from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $dataQuery = $this->dataQueryRepository->findWithoutFail($id);

        if (empty($dataQuery)) {
            Flash::error('Data Query not found');

            return redirect(route('dataQueries.index'));
        }

        $this->dataQueryRepository->delete($id);

        Flash::success('Data Query deleted successfully.');

        return redirect(route('dataQueries.index'));
    }
}
