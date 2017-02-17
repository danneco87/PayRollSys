@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>
                <div class="panel-body">
                    <div class="panel-heading">Get Payment Dates</div>
                    {!! Form::open(['route' => 'store', 'method' => 'POST']) !!}
                    @include('layouts.partials.fields')
                    <button type="submit" class="btn btn-default">Get Payment</button>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
