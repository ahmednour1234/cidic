@extends('layouts.admin')

@section('title', 'تعديل السؤال')

@section('content')
    <form method="POST" action="{{ route('admin.faqs.update', $record) }}" data-submit-guard>
        @csrf
        @method('PUT')
        @include('admin.faqs._form')
    </form>
@endsection
