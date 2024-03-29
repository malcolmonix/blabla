@extends('layouts.app')
@section('title', 'Employee')
@section('content')
    <section class="content-header">
        <h1>
            Employee
        </h1>
    </section>
    <div class="content">
        <div class="box box-primary">
            <div class="box-body">
                <div class="row" style="padding-left: 20px">
                    @include('employees.show_fields')
                    <a href="{!! route('employees.index') !!}" class="btn btn-default">Back</a>
                </div>
            </div>
        </div>
    </div>
@endsection
