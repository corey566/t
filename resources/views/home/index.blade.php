@extends('layouts.app')
@section('title', __('home.home'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('home.welcome_message', ['name' => Session::get('user.first_name')])</h1>
</section>

<!-- Main content -->
<section class="content">
    @can('dashboard.data')
    <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="ion ion-ios-cart-outline"></i></span>

                <div class="info-box-content">
                    <span class="info-box-text">@lang('home.total_purchase')</span>
                    <span class="info-box-number total_purchase"><i class="fa fa-refresh fa-spin fa-fw margin-bottom"></i></span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
        <!-- /.col -->
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="ion ion-ios-cart-outline"></i></span>

                <div class="info-box-content">
                    <span class="info-box-text">@lang('home.total_sell')</span>
                    <span class="info-box-number total_sell"><i class="fa fa-refresh fa-spin fa-fw margin-bottom"></i></span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
        <!-- /.col -->
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-yellow"><i class="fa fa-dollar"></i></span>

                <div class="info-box-content">
                    <span class="info-box-text">@lang('home.purchase_due')</span>
                    <span class="info-box-number purchase_due"><i class="fa fa-refresh fa-spin fa-fw margin-bottom"></i></span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
        <!-- /.col -->
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-red"><i class="ion ion-ios-paper-outline"></i></span>

                <div class="info-box-content">
                    <span class="info-box-text">@lang('home.invoice_due')</span>
                    <span class="info-box-number invoice_due"><i class="fa fa-refresh fa-spin fa-fw margin-bottom"></i></span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
        <!-- /.col -->
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('home.sales_current_month')</h3>
                </div>
                <div class="box-body">
                    {!! $sells_chart_1->container() !!}
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('home.purchase_current_month')</h3>
                </div>
                <div class="box-body">
                    {!! $sells_chart_2->container() !!}
                </div>
            </div>
        </div>
    </div>
    @endcan
</section>
<!-- /.content -->
@stop

@section('javascript')
    <script src="{{ asset('js/home.js?v=' . $asset_v) }}"></script>
    @can('dashboard.data')
        {!! $sells_chart_1->script() !!}
        {!! $sells_chart_2->script() !!}
    @endcan
@endsection