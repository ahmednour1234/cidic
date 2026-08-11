@extends('layouts.admin')

@section('title', 'إضافة مستخدم')

@section('content')
    <form method="POST" action="{{ route('admin.users.store') }}" data-submit-guard>
        @csrf
        @include('admin.users._form')
    </form>
@endsection
