@extends('layouts.admin')

@section('title', 'تعديل الخدمة')

@section('content')
    <form method="POST" action="{{ route('admin.services.update', $service) }}" enctype="multipart/form-data" data-submit-guard>
        @csrf
        @method('PUT')
        @include('admin.services._form')
    </form>
@endsection
