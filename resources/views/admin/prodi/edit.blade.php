@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Edit Prodi: {{ $prodi->nama_prodi }}</h1>
    <form action="{{ route('admin.prodi.update', $prodi->id) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.prodi._form', ['prodi' => $prodi]) {{-- Menggunakan partial form dan passing data prodi --}}
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('admin.prodi.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
