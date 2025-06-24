@extends('layouts.client')

@section('main-content')
  <main>
    <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
      <div class="grid grid-cols-12 gap-4 md:gap-6">
        <div class="col-span-12 space-y-6">
          @include('partials.metric-group.metric-group-01')
          @include('partials.chart.chart-01')
        </div>
      </div>
    </div>
  </main>
@endsection
