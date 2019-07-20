@extends('layouts.app')
@section('title', 'Equipment')
@section('content')
    <section class="content-header">
        <h1>
            Equipment
        </h1>
    </section>
    <div class="content">
        @include('adminlte-templates::common.errors')
        <div class="box box-primary">

            <div class="box-body">
                <div class="row">
                    {!! Form::open(['route' => 'equipment.store']) !!}

                        @include('equipment.fields')

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
