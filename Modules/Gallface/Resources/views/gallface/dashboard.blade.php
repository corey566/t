@extends('layouts.app')

@section('title', __('Gallface'))

@section('content')
    @include('gallface::layouts.nav')
    
@include('gallface::setting')
@stop

