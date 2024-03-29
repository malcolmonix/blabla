

<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Employee Name:') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'FirstName LastName']) !!}
</div>

<!-- Position Field -->
<div class="form-group col-sm-6">
        {!! Form::label('position', 'Employee Position:') !!}
        {!! Form::text('position', null, ['class' => 'form-control', 'placeholder' => 'Employee Position']) !!}
    </div>

<!-- Employee Id Field -->
<div class="form-group col-sm-6">
        {!! Form::label('employee_id', 'Employee ID #:') !!}
        {!! Form::text('employee_id', null, ['class' => 'form-control']) !!}
    </div>

<!-- Project Id Field -->
<div class="form-group col-sm-6">
        {!! Form::label('project_id', 'Project:') !!}
        {!! Form::select('project_id', $projects, null, ['class' => 'form-control']) !!}
    </div>



<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{!! route('employees.index') !!}" class="btn btn-default">Cancel</a>
</div>
