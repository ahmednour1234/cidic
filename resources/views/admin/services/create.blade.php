@extends('layouts.admin')

@section('title', 'إضافة خدمة')

@section('content')
    <form method="POST" action="{{ route('admin.services.store') }}" enctype="multipart/form-data" data-submit-guard>
        @csrf
        @include('admin.services._form')
    </form>
@endsection
