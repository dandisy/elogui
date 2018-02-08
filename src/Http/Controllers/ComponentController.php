<?php

namespace App\Http\Controllers;

use App\DataTables\ComponentDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateComponentRequest;
use App\Http\Requests\UpdateComponentRequest;
use App\Repositories\ComponentRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;
use Illuminate\Support\Facades\Auth; // add by dandisy
use Illuminate\Support\Facades\Storage; // add by dandisy

class ComponentController extends AppBaseController
{
    /** @var  ComponentRepository */
    private $componentRepository;

    public function __construct(ComponentRepository $componentRepo)
    {
        $this->middleware('auth');
        $this->componentRepository = $componentRepo;
    }

    /**
     * Display a listing of the Component.
     *
     * @param ComponentDataTable $componentDataTable
     * @return Response
     */
    public function index(ComponentDataTable $componentDataTable)
    {
        return $componentDataTable->render('components.index');
    }

    /**
     * Show the form for creating a new Component.
     *
     * @return Response
     */
    public function create()
    {
        // add by dandisy
        $datasource = \App\Models\DataSource::all();
        
$components = array_map(function ($file) {
            $fileName = explode('.', $file);
            if(count($fileName) > 0) {
                return $fileName[0];
            }
        }, Storage::disk('component')->allFiles());

        $components = array_combine($components, $components);

        // edit by dandisy
        //return view('components.create');
        return view('components.create')
            ->with('datasource', $datasource)
            ->with('components', $components);
    }

    /**
     * Store a newly created Component in storage.
     *
     * @param CreateComponentRequest $request
     *
     * @return Response
     */
    public function store(CreateComponentRequest $request)
    {
        $input = $request->all();

        $input['created_by'] = Auth::user()->id;

        $component = $this->componentRepository->create($input);

        Flash::success('Component saved successfully.');

        return redirect(route('components.index'));
    }

    /**
     * Display the specified Component.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $component = $this->componentRepository->findWithoutFail($id);

        if (empty($component)) {
            Flash::error('Component not found');

            return redirect(route('components.index'));
        }

        return view('components.show')->with('component', $component);
    }

    /**
     * Show the form for editing the specified Component.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        // add by dandisy
        $datasource = \App\Models\DataSource::all();
        
$components = array_map(function ($file) {
            $fileName = explode('.', $file);
            if(count($fileName) > 0) {
                return $fileName[0];
            }
        }, Storage::disk('component')->allFiles());

        $components = array_combine($components, $components);

        $component = $this->componentRepository->findWithoutFail($id);

        if (empty($component)) {
            Flash::error('Component not found');

            return redirect(route('components.index'));
        }

        // edit by dandisy
        //return view('components.edit')->with('component', $component);
        return view('components.edit')
            ->with('component', $component)
            ->with('datasource', $datasource)
            ->with('components', $components);
    }

    /**
     * Update the specified Component in storage.
     *
     * @param  int              $id
     * @param UpdateComponentRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateComponentRequest $request)
    {
        $input = $request->all();

        $input['updated_by'] = Auth::user()->id;

        $component = $this->componentRepository->findWithoutFail($id);

        if (empty($component)) {
            Flash::error('Component not found');

            return redirect(route('components.index'));
        }

        $component = $this->componentRepository->update($input, $id);

        Flash::success('Component updated successfully.');

        return redirect(route('components.index'));
    }

    /**
     * Remove the specified Component from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $component = $this->componentRepository->findWithoutFail($id);

        if (empty($component)) {
            Flash::error('Component not found');

            return redirect(route('components.index'));
        }

        $this->componentRepository->delete($id);

        Flash::success('Component deleted successfully.');

        return redirect(route('components.index'));
    }
}
