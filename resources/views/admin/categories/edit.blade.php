@extends('layouts.admin')

@section('title', 'تعديل التصنيف')

@section('content')
    <form method="POST" action="{{ route('admin.categories.update', $category) }}" data-submit-guard>
        @csrf
        @method('PUT')
        @include('admin.categories._form')
    </form>
@endsection
