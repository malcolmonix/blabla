<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Requests\CreateInventoryHistoryRequest;
use App\Http\Requests\UpdateInventoryHistoryRequest;
use App\Repositories\InventoryHistoryRepository;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Flash;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;
use App\Models\Equipment;
use App\Models\Situation;
use App\Models\Employee;
use App\Models\Project;
use Validator;
use File;



class InventoryHistoryController extends AppBaseController
{
    /** @var  InventoryHistoryRepository */
    private $inventoryHistoryRepository;

    public function __construct(InventoryHistoryRepository $inventoryHistoryRepo)
    {
        $this->inventoryHistoryRepository = $inventoryHistoryRepo;
        $this->middleware('auth');
    }


    /**
     * Display a listing of the InventoryHistory.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        return view('inventory_histories.index');
    }
    
    public function action(Request $request)
    {
            if($request->ajax())
            {
            $output = '';
            $query = $request->get('query');
            if($query != '')
            {
            $data = DB::table('inventory_histories')
            ->join('equipments','inventory_histories.equipment_id','=','equipments.id' )
            ->join('situations','inventory_histories.situation_id','=','situations.id' )
            ->join('projects','inventory_histories.project_id','=','projects.id' )
            ->join('employees','inventory_histories.employee_id','=','employees.id' )
            ->join('users','inventory_histories.user_id','=','users.id' )
            ->where('issue_date', 'like','%'. $query .'%')
            ->orWhere('approvedby', 'like','%'. $query .'%')
            ->orWhere('projects.name', 'like','%'. $query .'%')
            ->orWhere('situations.name', 'like','%'. $query .'%')
            ->orWhere('employees.name', 'like','%'. $query .'%')
            ->orWhere('equipments.name', 'like','%'. $query .'%')
            ->orWhere('users.name', 'like','%'. $query .'%')
            ->select('equipments.name as equipment','employees.name as employee', 'projects.name as project','situations.name as status','inventory_histories.id','inventory_histories.issue_date','inventory_histories.approvedby','inventory_histories.remarks','inventory_histories.created_at','inventory_histories.updated_at','users.name as postedby')
            ->orderBy('inventory_histories.id','desc')
            ->get();              
            }
            else
            {
            $data = DB::table('inventory_histories')
            ->join('equipments','inventory_histories.equipment_id','=','equipments.id' )
            ->join('situations','inventory_histories.situation_id','=','situations.id' )
            ->join('projects','inventory_histories.project_id','=','projects.id' )
            ->join('employees','inventory_histories.employee_id','=','employees.id' )
            ->join('users','inventory_histories.user_id','=','users.id' )
            ->select('equipments.name as equipment','employees.name as employee', 'projects.name as project','situations.name as status','inventory_histories.id','inventory_histories.issue_date','inventory_histories.approvedby','inventory_histories.remarks','inventory_histories.created_at','inventory_histories.updated_at','users.name as postedby')
            ->orderBy('inventory_histories.id','desc')
            ->paginate(20);
           
            }
           
            $total_row = 0;
            $pagination = 0;
            $total_row = $data->count();
            if($total_row > 0)
            {
                $i = 1;

                foreach($data as $row)
                {
                    $output .= '
                    <tr>
                    <td>  '. $i++ .' </td>
                    <td>'. $row->issue_date .' </td>
                    <td>'. $row->employee.' </td>
                    <td>'. $row->equipment.' </td>
                    <td>'. $row->project .'</td>
                    <td>'. $row->status .' </td>
                    <td>'. $row->approvedby .' </td>
                    <td>'. $row->postedby .' </td>
                    <td>
                         <div class=btn-group>
                             <button type="button" name="show" id="'.$row->id.'" class="btn btn-success show"><i class="glyphicon glyphicon-eye-open"></i></button>
                             <button type="button" name="edit" id="'.$row->id.'" class="btn btn-warning edit"><i class="glyphicon glyphicon-edit"></i></button>                  
                        </div>
                    </td>  
                    </tr>
                   
                    ';
                }
                $pagination = '
                <div class=d-flex justify-content-center>
                '. $data->links('vendor.pagination.default').'
                </div>
                ';
            }
            else
            {
            $output = '
            <tr>
                <td stlye=align:center colspan=5>No Data Found</td>
            </tr>
            ';
            }
            $data = array(
            'table_data'  => $output,
            'total_data'  => $total_row,
            'pagination'  => $pagination
            );

            echo json_encode($data);
            }
        
    }






    /**
     * Show the form for creating a new InventoryHistory.
     *
     * @return Response
     */
    public function create()
    {
        $equipment = Equipment::pluck('name','id');
        $situation = Situation::pluck('name','id');
        $employee = Employee::pluck('name','id');
        $project = Project::pluck('name','id');

        return view('inventory_histories.create')
            ->with('equipment', $equipment)
            ->with('situation',$situation)
            ->with('project',$project)
            ->with('employee',$employee);
    }

    /**
     * Store a newly created InventoryHistory in storage.
     *
     * @param CreateInventoryHistoryRequest $request
     *
     * @return Response
     */
    public function store(CreateInventoryHistoryRequest $request)
    {
        $input = $request->all();
        $input['user_id'] = auth()->id();

                
        $employeeid = request('employee_id');
        $statusid = request('situation_id');
        $equipmentid = request('equipment_id');
        $input['document_url'] = "";
        $filename = request('filename');
        
        $new_name = $mess =  "";

        $validation = Validator::make($request->all(), [
            'document_url' => 'required|mimes:pdf|max:10000'
           ]);
                     
          
           if($validation->passes())
           {
                $image = $request->file('document_url');
                $new_name = rand() . '.' . $image->getClientOriginalExtension();
                $filename = $image->getClientOriginalName();
                $image->move(public_path('documents'),  $filename);
           }
           else
           {
               $mess = $validation->errors()->all();
           }         
           
           $date = date("Y-m-d");

           DB::table('inventory_histories')->insert(
            [
                 'issue_date'=>$input['issue_date'], 'employee_id'=>$input['employee_id'], 
                 'equipment_id'=>$input['equipment_id'], 'project_id'=>$input['project_id'],
                 'situation_id'=>$input['situation_id'],'projectTo_id'=>$input['projectTo_id'],
                 'approvedby'=>$input['approvedby'],'remarks'=>$input['remarks'],
                 'user_id'=>$input['user_id'],'document_url'=>$filename,
                 'created_at'=>$date,
                 'updated_at'=>$date
            ]
         );

           //$inventoryHistory = $this->inventoryHistoryRepository->create($input);

           $equipment = Equipment::find($equipmentid);
           $equipment->situation_id = $statusid;
           $equipment->save();

            // DB::table('equipments')
            //     ->where('id',$equipmentid)
            //     ->update(['situation_id'=>$statusid, 'user_id'=>$employeeid]);

            
            Flash::success('Inventory saved successfully.');
            return redirect(route('inventoryHistories.index'));
        
    }

    /**
     * Display the specified InventoryHistory.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $inventoryHistory = DB::table('inventory_histories as i')
                                ->join('equipments','i.equipment_id','=','equipments.id' )
                                ->join('situations','i.situation_id','=','situations.id' )
                                ->join('projects','i.project_id','=','projects.id' )
                                ->join('employees','i.employee_id','=','employees.id' )
                                ->join('users','i.user_id','=','users.id' )
                                ->where('i.id','=',$id)
                                ->select('equipments.name as equipment','employees.name as employee','employees.employee_id', 'projects.name as project','situations.name as status','i.id','i.issue_date','i.approvedby','i.remarks','i.created_at','i.updated_at','users.name as postedby')
                                ->orderBy('i.id','desc')->first();
                                

       $inventoryHistoryDoc = $this->inventoryHistoryRepository->findWithoutFail($id);
       $filename = $inventoryHistoryDoc->document_url;
       $documents = "/documents/" . $filename;



        if (empty($inventoryHistory)) {
            Flash::error('Inventory History not found');

            return redirect(route('inventoryHistories.index'));
        }

        return view('inventory_histories.show')
        ->with('document',$documents)
        ->with('inventoryHistory', $inventoryHistory);
    }
    /**
     * handle document download
     */
    public function download($id)
    {
        $inventoryHistory = $this->inventoryHistoryRepository->findWithoutFail($id);

        if (empty($inventoryHistory)) {
            Flash::error('Document not found');

            return redirect(route('inventoryHistories.index'));
        }

                  
        $document_url = $inventoryHistory->document_url;
        if($document_url != "")
        {
            $file = public_path(). "/documents/" . $document_url;
            
            $headers = array(
                'Content-Type: application/pdf',
                );

            return Response::download($file, $document_url, $headers); 
        }
        else
        {
            Flash::error('Document not found');

            return redirect(route('inventoryHistories.index'));
        }
    }

    /**
     *  function to destroy document if it is change
     */
    public function destroy_document($file)
    {
        $file = base64_decode($file);
        File::delete($file);
    }
    /**
     * display the uploaded document
     */
    public function getdocument($id)
    {
        $inventoryHistory = $this->inventoryHistoryRepository->findWithoutFail($id);
        $filename = $inventoryHistory->document_url;
        $file = public_path(). "/documents/" . $filename;
        header('Content-type:application/pdf');
        header('Content-Disposition: inline; filename="' .$filename .'"');
        header('Accept-Ranges: bytes');
        readfile($file);
    }


    /**
     * Show the form for editing the specified InventoryHistory.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
       
        $inventoryHistory = $this->inventoryHistoryRepository->findWithoutFail($id);
        $equipment = Equipment::pluck('name','id');
        $situation = Situation::pluck('name','id');
        $employee = Employee::pluck('name','id');
        $project = Project::pluck('name','id');
        $filename = $inventoryHistory->document_url;
        $documents = "/documents/" . $filename;
       
        
        if (empty($inventoryHistory)) {
            Flash::error('Inventory not found');

            return redirect(route('inventoryHistories.index'));
        }
      
        return view('inventory_histories.edit')
            ->with('inventoryHistory', $inventoryHistory)
            ->with('equipment', $equipment)
            ->with('situation',$situation)
            ->with('project',$project)
            ->with('document',$documents)
            ->with('employee',$employee);
    }

    /**
     * Update the specified InventoryHistory in storage.
     *
     * @param  int              $id
     * @param UpdateInventoryHistoryRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateInventoryHistoryRequest $request)
    {
               
        $inventoryHistory = $this->inventoryHistoryRepository->findWithoutFail($id);

        if (empty($inventoryHistory)) {
            Flash::error('Inventory not found');

            return redirect(route('inventoryHistories.index'));
        }

        $date = date("Y-m-d");
        $fileurl = request('document_url');
              
        $new_name = $mess = $filename =  "";

        if($fileurl != "")
        {
            $validation = Validator::make($request->all(), [
            'document_url' => 'required|mimes:pdf|max:10000'
           ]);
                  
           
           if($validation->passes())
           {
                $image = $request->file('document_url');
                $new_name = rand() . '.' . $image->getClientOriginalExtension();
                $filename = $image->getClientOriginalName();
                $image->move(public_path('documents'),  $filename);
           }
           else
           {
                $mess = $validation->errors()->all();
           }     
        }    
              

       DB::table('inventory_histories')
            ->where('id',$id)
            ->update(['document_url'=>$filename]);

       $inventoryHistory = $this->inventoryHistoryRepository->update($request->all(), $id);

       $equipment = Equipment::find($inventoryHistory->equipment_id);
       $situation = Situation::find($request->situation_id);

       if($situation->name == 'RETURNED') {           
           $sit = Situation::where('name', 'IN-STOCK')->first();           
            $equipment->situation_id = $sit->id;
        } else {
            $equipment->situation_id = $request->situation_id;
        }

       
      
       $equipment->save();

       Flash::success('Inventory updated successfully.');
       return redirect(route('inventoryHistories.index'));
    }

    /**
     * Remove the specified InventoryHistory from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $inventoryHistory = $this->inventoryHistoryRepository->findWithoutFail($id);

        if (empty($inventoryHistory)) {
            Flash::error('Inventory not found');

            return redirect(route('inventoryHistories.index'));
        }

        //$this->inventoryHistoryRepository->delete($id);
        DB::table('inventory_histories')
            ->where('id',$id)
            ->delete(); 

        Flash::success('Inventory deleted successfully.');

        return redirect(route('inventoryHistories.index'));
    }
}
