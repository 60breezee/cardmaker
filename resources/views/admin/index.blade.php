@extends('layouts.app')

@section('content')
<div class="mb-5">
    <p class="label-cap">Administration</p>
    <h1 class="text-[15px] font-semibold tracking-[0.08em] text-fog">VUE D’ENSEMBLE</h1>
</div>

<div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
    @foreach($stats as $label => $value)
        <div class="glass-soft rounded-[18px] p-5">
            <p class="label-cap">{{ $label }}</p>
            <p class="mt-2 text-3xl font-light text-fog">{{ $value }}</p>
        </div>
    @endforeach
</div>
@endsection