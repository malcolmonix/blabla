<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Requests\CreateEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Repositories\EmployeeRepository;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Flash;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;
use App\Models\Project;

class EmployeeController extends AppBaseController
{
    /** @var  EmployeeRepository */
    private $employeeRepository;

    public function __construct(EmployeeRepository $employeeRepo)
    {
        $this->employeeRepository = $employeeRepo;
        $this->middleware('auth');
    }

    /**
     * Display a listing of the Employee.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->employeeRepository->pushCriteria(new RequestCriteria($request));
        $employees = $this->employeeRepository->all();

        return view('employees.index')
            ->with('employees', $employees);
    }

    /**
     * Show the form for creating a new Employee.
     *
     * @return Response
     */
    public function create()
    {
        $projects = Project::pluck('name', 'id');

        return view('employees.create')->with('projects', $projects);
    }

    /**
     * Store a newly created Employee in storage.
     *
     * @param CreateEmployeeRequest $request
     *
     * @return Response
     */
    public function store(CreateEmployeeRequest $request)
    {
        $input = $request->all();
        $input['active'] = '1';

        $emp = DB::table('employees')
        ->where('employee_id',request('employee_id'))
        ->where('deleted_at',null)
        ->exists();
        
        if($emp == false)
        {
            $date = date("Y-m-d");

            DB::table('employees')->insert(
                [
                     'employee_id'=>$input['employee_id'], 'name'=>$input['name'], 
                     'position'=>$input['position'], 'project_id'=>$input['project_id'],
                     'active'=>$input['active'],'created_at'=>$date,
                     'updated_at'=>$date
                ]
             );
           //$employee = $this->employeeRepository->create($input);

            Flash::success('Employee saved successfully.');

            return redirect(route('employees.index'));
        }
        else
        {
            Flash::error('Employee Already exist');
            return redirect(route('employees.index'));
        }
    }

    /**
     * Display the specified Employee.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $employee = $this->employeeRepository->findWithoutFail($id);

        if (empty($employee)) {
            Flash::error('Employee not found');

            return redirect(route('employees.index'));
        }

        return view('employees.show')->with('employee', $employee);
    }

    /**
     * Show the form for editing the specified Employee.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $employee = $this->employeeRepository->findWithoutFail($id);

        if (empty($employee)) {
            Flash::error('Employee not found');

            return redirect(route('employees.index'));
        }

        return view('employees.edit')->with('employee', $employee);
    }

    /**
     * Update the specified Employee in storage.
     *
     * @param  int              $id
     * @param UpdateEmployeeRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateEmployeeRequest $request)
    {
        $employee = $this->employeeRepository->findWithoutFail($id);

        if (empty($employee)) {
            Flash::error('Employee not found');

            return redirect(route('employees.index'));
        }

        $employee = $this->employeeRepository->update($request->all(), $id);

        Flash::success('Employee updated successfully.');

        return redirect(route('employees.index'));
    }

    /**
     * Remove the specified Employee from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $employee = $this->employeeRepository->findWithoutFail($id);

        if (empty($employee)) {
            Flash::error('Employee not found');

            return redirect(route('employees.index'));
        }

        $this->employeeRepository->delete($id);

        Flash::success('Employee deleted successfully.');

        return redirect(route('employees.index'));
    }
}
