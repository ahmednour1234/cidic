@extends('layouts.admin')

@section('title', 'تعديل السيرة الذاتية')

@section('content')
    <form method="POST" action="{{ route('admin.candidates.update', $candidate) }}" enctype="multipart/form-data" data-submit-guard>
        @csrf
        @method('PUT')
        @include('admin.candidates._form', ['candidate' => $candidate])
    </form>
@endsection