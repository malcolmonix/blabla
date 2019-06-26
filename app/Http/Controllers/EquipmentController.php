<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
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

class EquipmentController extends AppBaseController
{
    /** @var  EquipmentRepository */
    private $equipmentRepository;

    public function __construct(EquipmentRepository $equipmentRepo)
    {
        $this->equipmentRepository = $equipmentRepo;
    }

    /**
     * Display a listing of the Equipment.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->equipmentRepository->pushCriteria(new RequestCriteria($request));
        $equipment = DB::table('equipments as e')
                    ->join('situations as s','e.situation_id','=','s.id' )
                    ->join('employees as em','e.user_id','=','em.id' )
                    ->join('equipment_types as et','e.equipment_type_id','=','et.id' )
                    ->select('e.name as name','e.model','e.serialnumber','e.id','et.name as equipment_type','em.name as employee','s.name as status','e.deleted_at','e.created_at','e.updated_at')
                    ->orderBy('e.name','asc')
                    ->get();

       

           return view('equipment.index')
            ->with('equipment', $equipment);
    }

    /**
     * Show the form for creating a new Equipment.
     *
     * @return Response
     */
    public function create()
    {
        $equipment_type = EquipmentType::pluck('name', 'id');
        $situation = Situation::pluck('name', 'id');

        return view('equipment.create')
            ->with('situation', $situation)
            ->with('equipment_type', $equipment_type);
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
        
        if (DB::table('equipments')->where('name',request('name'))->where('deleted_at',null)->exists() == false) {
            $equipment = $this->equipmentRepository->create($input);
            Flash::success('Equipment saved successfully.');

            
            return redirect(route('equipment.index'));
        }
        else
        {
            Flash::error('Already existing equipment');
            return redirect(route('equipment.index'));
        }
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
        $equipment_type = EquipmentType::pluck('name', 'id');
        $situation = Situation::pluck('name', 'id');

        if (empty($equipment)) {
            Flash::error('Equipment not found');

            return redirect(route('equipment.index'));
        }

        return view('equipment.show')
        ->with('equipment', $equipment)
        ->with('situation', $situation)
        ->with('equipment_type', $equipment_type);
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
        $situation = Situation::pluck('name', 'id');

        if (empty($equipment)) {
            Flash::error('Equipment not found');

            return redirect(route('equipment.index'));
        }

        return view('equipment.edit')
        ->with('equipment', $equipment)
        ->with('situation', $situation)
        ->with('equipment_type', $equipment_type);
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
        
        if (DB::table('inventory_histories')->where('equipment_id',$id)->exists() == false) 
        {

            if (empty($equipment)) {
                Flash::error('Equipment not found');

                return redirect(route('equipment.index'));
            }

            $this->equipmentRepository->delete($id);

            Flash::success('Equipment deleted successfully.');

            return redirect(route('equipment.index'));
        }
        else
        {
            Flash::error('Equipment has history cannot be deleted.');

            return redirect(route('equipment.index'));
        }
    }
}
