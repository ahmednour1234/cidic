@extends('layouts.admin')

@section('title', 'تعديل عنصر')

@section('content')
    <form method="POST" action="{{ route('admin.why-choose-us.update', $record) }}" data-submit-guard>
        @csrf
        @method('PUT')
        @include('admin.partials.content-form', ['indexRoute' => 'admin.why-choose-us.index'])
    </form>
@endsection
