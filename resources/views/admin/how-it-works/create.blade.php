@extends('layouts.admin')

@section('title', 'إضافة خطوة')

@section('content')
    <form method="POST" action="{{ route('admin.how-it-works.store') }}" data-submit-guard>
        @csrf
        @include('admin.partials.content-form', ['indexRoute' => 'admin.how-it-works.index'])
    </form>
@endsection
