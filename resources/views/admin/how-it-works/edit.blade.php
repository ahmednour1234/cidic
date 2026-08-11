@extends('layouts.admin')

@section('title', 'تعديل خطوة')

@section('content')
    <form method="POST" action="{{ route('admin.how-it-works.update', $record) }}" data-submit-guard>
        @csrf
        @method('PUT')
        @include('admin.partials.content-form', ['indexRoute' => 'admin.how-it-works.index'])
    </form>
@endsection
