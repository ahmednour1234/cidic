@extends('layouts.admin')

@section('title', 'إضافة صفحة')

@section('content')
    <form method="POST" action="{{ route('admin.pages.store') }}" data-submit-guard>
        @csrf
        @include('admin.pages._form')
    </form>
@endsection
