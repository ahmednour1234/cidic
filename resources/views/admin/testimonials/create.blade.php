@extends('layouts.admin')

@section('title', 'إضافة رأي')

@section('content')
    <form method="POST" action="{{ route('admin.testimonials.store') }}" enctype="multipart/form-data" data-submit-guard>
        @csrf
        @include('admin.testimonials._form')
    </form>
@endsection
