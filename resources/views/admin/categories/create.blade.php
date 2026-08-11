@extends('layouts.admin')

@section('title', 'إضافة تصنيف')

@section('content')
    <form method="POST" action="{{ route('admin.categories.store') }}" data-submit-guard>
        @csrf
        @include('admin.categories._form')
    </form>
@endsection
