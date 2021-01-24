@extends('layouts.master')

@section('sidebar')
    @parent

    <p>This is appended to the master sidebar.</p>
@stop

@section('content')
    <p>This is my body content.</p>

    @switch('a')
        @case('a')
        A
        @break

        @case('b')
        B
        @break
    @endswitch

@stop
