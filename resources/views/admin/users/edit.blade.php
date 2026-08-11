@extends('layouts.admin')

@section('title', 'تعديل المستخدم')

@section('content')
    <form method="POST" action="{{ route('admin.users.update', $user) }}" data-submit-guard>
        @csrf
        @method('PUT')
        @include('admin.users._form')
    </form>
@endsection
