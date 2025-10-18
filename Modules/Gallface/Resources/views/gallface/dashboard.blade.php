@extends('layouts.app')

@section('title', __('Gallface'))

@section('content')
    @include('gallface::layouts.nav')
    
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group pull-right">
                        <div class="input-group">
                        <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-sm" id="dashboard_date_filter">
                            <span>
                            <i class="fa fa-calendar"></i> {{ __('messages.filter_by_date') }}
                            </span>
                            <i class="fa fa-caret-down"></i>
                        </button>
                        </div>
                </div>
            </div>
        </div>
        

    </section>
@stop

