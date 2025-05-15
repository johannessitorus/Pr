@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Tambah Prodi Baru</h1>
    <form action="{{ route('admin.prodi.store') }}" method="POST">
        @csrf
        {{--@include('admin.prodi._form') {{-- Menggunakan partial form --}}
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('admin.prodi.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
