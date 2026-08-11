@extends('layouts.admin')

@section('title', 'إضافة سيرة ذاتية')

@section('content')
    <form method="POST" action="{{ route('admin.candidates.store') }}" enctype="multipart/form-data" data-submit-guard>
        @csrf
        @include('admin.candidates._form', ['candidate' => new \App\Models\Candidate()])
    </form>
@endsection
