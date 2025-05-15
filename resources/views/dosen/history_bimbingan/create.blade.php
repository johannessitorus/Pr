@extends('layouts.app') {{-- Sesuaikan dengan nama layout utama dosen Anda --}}

@section('title', 'Proses History Bimbingan')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
        <a href="{{ route('dosen.history-bimbingan.show', $historyBimbingan->id) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Batal dan Kembali ke Detail
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Update Status & Catatan Bimbingan Mahasiswa: {{ $historyBimbingan->mahasiswa && $historyBimbingan->mahasiswa->user ? $historyBimbingan->mahasiswa->user->name : 'N/A' }}
            </h6>
            <small>Tanggal Bimbingan: {{ $historyBimbingan->tanggal_bimbingan->format('d F Y, H:i') }}</small>
        </div>
        <div class="card-body">
            <form action="{{ route('dosen.history-bimbingan.update', $historyBimbingan->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="status_kehadiran" class="form-label">Update Status Kehadiran <span class="text-danger">*</span></label>
                    <select name="status_kehadiran" id="status_kehadiran" class="form-select @error('status_kehadiran') is-invalid @enderror" required>
                        <option value="">-- Pilih Status --</option>
                        @if(isset($availableStatuses))
                            @foreach($availableStatuses as $value => $label)
                                <option value="{{ $value }}" {{ old('status_kehadiran', $historyBimbingan->status_kehadiran) == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    @error('status_kehadiran')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="catatan_dosen" class="form-label">Catatan Dosen (Feedback/Hasil Bimbingan):</label>
                    <textarea name="catatan_dosen" id="catatan_dosen" class="form-control @error('catatan_dosen') is-invalid @enderror" rows="5">{{ old('catatan_dosen', $historyBimbingan->catatan_dosen) }}</textarea>
                    @error('catatan_dosen')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Berikan catatan mengenai hasil bimbingan, progres mahasiswa, atau arahan selanjutnya.</small>
                </div>

                {{-- Jika Anda ingin mengaktifkan kembali resume_bimbingan, uncomment ini --}}
                {{-- <div class="mb-3">
                    <label for="resume_bimbingan" class="form-label">Resume Bimbingan (Opsional):</label>
                    <textarea name="resume_bimbingan" id="resume_bimbingan" class="form-control @error('resume_bimbingan') is-invalid @enderror" rows="3">{{ old('resume_bimbingan', $historyBimbingan->resume_bimbingan) }}</textarea>
                    @error('resume_bimbingan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div> --}}


                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Jika Anda menggunakan editor WYSIWYG untuk textarea --}}
{{-- <script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script>
<script>
    CKEDITOR.replace('catatan_dosen');
    // CKEDITOR.replace('resume_bimbingan');
</script> --}}
@endpush
