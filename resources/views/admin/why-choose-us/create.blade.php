@extends('layouts.admin')

@section('title', 'إضافة عنصر')

@section('content')
    <form method="POST" action="{{ route('admin.why-choose-us.store') }}" data-submit-guard>
        @csrf
        @include('admin.partials.content-form', ['indexRoute' => 'admin.why-choose-us.index'])
    </form>
@endsection
