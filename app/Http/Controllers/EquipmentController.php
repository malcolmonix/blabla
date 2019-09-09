<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateEquipmentRequest;
use App\Http\Requests\UpdateEquipmentRequest;
use App\Repositories\EquipmentRepository;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Flash;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;
use App\Models\EquipmentType;
use App\Models\Situation;
use App\Models\Brand;
use Illuminate\Support\Facades\DB;

class EquipmentController extends AppBaseController
{
    /** @var  EquipmentRepository */
    private $equipmentRepository;

    public function __construct(EquipmentRepository $equipmentRepo)
    {
        $this->equipmentRepository = $equipmentRepo;
        $this->middleware('auth');
    }

    /**
     * Display a listing of the Equipment.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {

        $data = DB::table('equipments')
                ->join('situations','equipments.situation_id','=','situations.id' )
                ->select('equipments.id as id','equipments.name	 as equipmentname','equipments.serialnumber as serialnumber','equipments.computer_name as computer_name', 'situations.Name as status')
                ->orderBy('equipments.name','asc')
                ->paginate(10);

        return view('equipment.index',compact('data'))->render();
    }
    public function fetch_data(Request $request)
    {
        if($request->ajax())
        {
            $sort_by = $request->get('sortby');
            $sort_type = $request->get('sorttype');
            $query = $request->get('query');
            $query = str_replace(" ", "%", $query);

            $data = DB::table('equipments')

                ->join('situations','equipments.situation_id','=','situations.id' )
                ->orWhere('equipments.name', 'like','%'. $query .'%')
                ->orWhere('equipments.serialnumber', 'like','%'. $query .'%')
                ->orWhere('equipments.computer_name', 'like','%'. $query .'%')
                ->orWhere('situations.Name', 'like','%'. $query .'%')
                ->select('equipments.id as id','equipments.name	 as equipmentname','equipments.serialnumber as serialnumber','equipments.computer_name as computer_name', 'situations.Name as status')
                ->orderBy('equipments.name','asc')

                ->paginate(10);      

            return view('equipment.pagination', compact('data'))->render();     

        }          
    }
    

          
    /**
     * Show the form for creating a new Equipment.
     *
     * @return Response
     */
    public function create()
    {
        $data['equipment_type'] = EquipmentType::pluck('name', 'id');
        $data['situation'] = Situation::pluck('name','id');

        $brand = Brand::pluck('name','id');
        
        return view('equipment.create')
                ->with('brand',$brand)
                ->with('data', $data);

    }

    /**
     * Store a newly created Equipment in storage.
     *
     * @param CreateEquipmentRequest $request
     *
     * @return Response
     */
    public function store(CreateEquipmentRequest $request)
    {
        $input = $request->all();

        $equipment = $this->equipmentRepository->create($input);

        Flash::success('Equipment saved successfully.');

        return redirect(route('equipment.index'));
    }

    /**
     * Display the specified Equipment.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $equipment = $this->equipmentRepository->findWithoutFail($id);

       
        $equipment_types = DB::table('equipment_types')->where('id',$equipment->equipment_type_id)->first();
        $situations = DB::table('situations')->where('id',$equipment->situation_id)->first();
        $brands = DB::table('brands')->where('id',$equipment->brand_id)->first();


        if (empty($equipment)) {
            Flash::error('Equipment not found');

            return redirect(route('equipment.index'));
        }

        return view('equipment.show')

        ->with('situation', $situations)
        ->with('brand',$brands)
        ->with('equipment_type',$equipment_types)

        ->with('situation', $situation)
        ->with('brand',$brand)
        ->with('equipment_type',$equipment_type)

        ->with('equipment', $equipment);
    }

    /**
     * Show the form for editing the specified Equipment.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $equipment = $this->equipmentRepository->findWithoutFail($id);
        $equipment_type = EquipmentType::pluck('name', 'id');
        $situation = Situation::pluck('name','id');
        $brand = Brand::pluck('name','id');

        if (empty($equipment)) {
            Flash::error('Equipment not found');

            return redirect(route('equipment.index'));
        }

        return view('equipment.edit')
        ->with('situation', $situation)
        ->with('brand',$brand)
        ->with('equipment_type',$equipment_type)
        ->with('equipment', $equipment);
    }

    /**
     * Update the specified Equipment in storage.
     *
     * @param  int              $id
     * @param UpdateEquipmentRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateEquipmentRequest $request)
    {
        $equipment = $this->equipmentRepository->findWithoutFail($id);

        if (empty($equipment)) {
            Flash::error('Equipment not found');

            return redirect(route('equipment.index'));
        }

        $equipment = $this->equipmentRepository->update($request->all(), $id);

        Flash::success('Equipment updated successfully.');

        return redirect(route('equipment.index'));
    }

    /**
     * Remove the specified Equipment from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $equipment = $this->equipmentRepository->findWithoutFail($id);

        if (empty($equipment)) {
            Flash::error('Equipment not found');

            return redirect(route('equipment.index'));
        }

        $this->equipmentRepository->delete($id);

        Flash::success('Equipment deleted successfully.');

        return redirect(route('equipment.index'));
    }
}
