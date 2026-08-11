@extends('layouts.admin')

@section('title', 'إضافة جنسية')

@section('content')
    <form method="POST" action="{{ route('admin.nationalities.store') }}" enctype="multipart/form-data" data-submit-guard>
        @csrf
        @include('admin.nationalities._form')
    </form>
@endsection
