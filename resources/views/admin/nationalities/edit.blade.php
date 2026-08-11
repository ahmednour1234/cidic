@extends('layouts.admin')

@section('title', 'تعديل الجنسية')

@section('content')
    <form method="POST" action="{{ route('admin.nationalities.update', $nationality) }}" enctype="multipart/form-data" data-submit-guard>
        @csrf
        @method('PUT')
        @include('admin.nationalities._form')
    </form>
@endsection
