<?php

namespace Webcore\Elogui\Controllers;

use Webcore\Elogui\DataTables\ColumnAliasDataTable;
use App\Http\Requests;
use Webcore\Elogui\Requests\CreateColumnAliasRequest;
use Webcore\Elogui\Requests\UpdateColumnAliasRequest;
use Webcore\Elogui\Repositories\ColumnAliasRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;
use Illuminate\Support\Facades\Auth; // add by dandisy
use Illuminate\Support\Facades\Storage; // add by dandisy

class ColumnAliasController extends AppBaseController
{
    /** @var  ColumnAliasRepository */
    private $columnAliasRepository;

    public function __construct(ColumnAliasRepository $columnAliasRepo)
    {
        $this->middleware('auth');
        $this->columnAliasRepository = $columnAliasRepo;
    }

    /**
     * Display a listing of the ColumnAlias.
     *
     * @param ColumnAliasDataTable $columnAliasDataTable
     * @return Response
     */
    public function index(ColumnAliasDataTable $columnAliasDataTable)
    {
        return $columnAliasDataTable->render('elogui::column_aliases.index');
    }

    /**
     * Show the form for creating a new ColumnAlias.
     *
     * @return Response
     */
    public function create()
    {
        // add by dandisy
        $datasource = \Webcore\Elogui\Models\DataSource::all();
        

        // edit by dandisy
        //return view('column_aliases.create');
        return view('elogui::column_aliases.create')
            ->with('datasource', $datasource);
    }

    /**
     * Store a newly created ColumnAlias in storage.
     *
     * @param CreateColumnAliasRequest $request
     *
     * @return Response
     */
    public function store(CreateColumnAliasRequest $request)
    {
        $input = $request->all();

        $input['created_by'] = Auth::user()->id;

        $columnAlias = $this->columnAliasRepository->create($input);

        Flash::success('Column Alias saved successfully.');

        return redirect(route('columnAliases.index'));
    }

    /**
     * Display the specified ColumnAlias.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $columnAlias = $this->columnAliasRepository->findWithoutFail($id);

        if (empty($columnAlias)) {
            Flash::error('Column Alias not found');

            return redirect(route('columnAliases.index'));
        }

        return view('elogui::column_aliases.show')->with('columnAlias', $columnAlias);
    }

    /**
     * Show the form for editing the specified ColumnAlias.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        // add by dandisy
        $datasource = \Webcore\Elogui\Models\DataSource::all();
        

        $columnAlias = $this->columnAliasRepository->findWithoutFail($id);

        if (empty($columnAlias)) {
            Flash::error('Column Alias not found');

            return redirect(route('columnAliases.index'));
        }

        // edit by dandisy
        //return view('column_aliases.edit')->with('columnAlias', $columnAlias);
        return view('elogui::column_aliases.edit')
            ->with('columnAlias', $columnAlias)
            ->with('datasource', $datasource);
    }

    /**
     * Update the specified ColumnAlias in storage.
     *
     * @param  int              $id
     * @param UpdateColumnAliasRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateColumnAliasRequest $request)
    {
        $input = $request->all();

        $input['updated_by'] = Auth::user()->id;

        $columnAlias = $this->columnAliasRepository->findWithoutFail($id);

        if (empty($columnAlias)) {
            Flash::error('Column Alias not found');

            return redirect(route('columnAliases.index'));
        }

        $columnAlias = $this->columnAliasRepository->update($input, $id);

        Flash::success('Column Alias updated successfully.');

        return redirect(route('columnAliases.index'));
    }

    /**
     * Remove the specified ColumnAlias from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $columnAlias = $this->columnAliasRepository->findWithoutFail($id);

        if (empty($columnAlias)) {
            Flash::error('Column Alias not found');

            return redirect(route('columnAliases.index'));
        }

        $this->columnAliasRepository->delete($id);

        Flash::success('Column Alias deleted successfully.');

        return redirect(route('columnAliases.index'));
    }
}
