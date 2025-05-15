@extends('layouts.app')

@section('title', 'Detail Jenis Dokumen')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="mb-0 h3">Detail Jenis Dokumen</h1>
                <div>
                    <a href="{{ route('admin.jenis-dokumen.edit', $jenisDokuman->id) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                    <a href="{{ route('admin.jenis-dokumen.index') }}" class="btn btn-outline-secondary btn-sm ms-2">
                        <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
                    </a>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    Informasi: {{ $jenisDokuman->nama_jenis }}
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th scope="row" style="width: 30%;">ID</th>
                                <td>{{ $jenisDokuman->id }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Nama Jenis Dokumen</th>
                                <td>{{ $jenisDokuman->nama_jenis }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Deskripsi</th>
                                <td>{{ $jenisDokuman->deskripsi ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Dibuat Pada</th>
                                <td>{{ $jenisDokuman->created_at->format('d M Y, H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Diperbarui Pada</th>
                                <td>{{ $jenisDokuman->updated_at->format('d M Y, H:i:s') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
