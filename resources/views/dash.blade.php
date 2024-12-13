@extends('layout')
@section('title','Dash')
    @section('content')
        <div id="dashboard-root"></div>
    @endsection

    @push('scripts')
        @viteReactRefresh
        @vite(['resources/js/react-app.jsx'])
    @endpush
