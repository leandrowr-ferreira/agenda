@extends('layouts.app')

@section('content')
  @include('partials.preloader')
  <!-- ===== Page Wrapper Start ===== -->
  <div class="flex h-screen overflow-hidden">
    @include('partials.sidebar')
    <!-- ===== Content Area Start ===== -->
    <div class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto">
      @include('partials.overlay')
      @include('partials.header')

      @yield('main-content')
    </div>
    <!-- ===== Content Area End ===== -->
  </div>
  <!-- ===== Page Wrapper End ===== -->
@endsection
