@extends('layouts.admin')

@section('title', 'إضافة سؤال')

@section('content')
    <form method="POST" action="{{ route('admin.faqs.store') }}" data-submit-guard>
        @csrf
        @include('admin.faqs._form')
    </form>
@endsection
