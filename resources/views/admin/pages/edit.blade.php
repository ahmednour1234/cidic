@extends('layouts.admin')

@section('title', 'تعديل الصفحة')

@section('content')
    <form method="POST" action="{{ route('admin.pages.update', $page) }}" data-submit-guard>
        @csrf
        @method('PUT')
        @include('admin.pages._form')
    </form>
@endsection
