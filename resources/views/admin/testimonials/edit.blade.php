@extends('layouts.admin')

@section('title', 'تعديل الرأي')

@section('content')
    <form method="POST" action="{{ route('admin.testimonials.update', $record) }}" enctype="multipart/form-data" data-submit-guard>
        @csrf
        @method('PUT')
        @include('admin.testimonials._form')
    </form>
@endsection
