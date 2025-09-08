<head>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    {{-- Font Awesome for icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" xintegrity="sha512-1ycn6IcaQQ40JDrFNgychGUAUyVjQaqzLSYd0n1Nq3p4f+E20n2+03u+3h2cE8f9Xz+jF+7x+gY+2P+P+A+g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .import-section-card { /* Using a new class name to avoid conflict and be more specific */
            background-color: #f8f9fa;
            border: 1px solid #e2e6ea;
            border-radius: 8px;
            padding: 20px; /* Slightly more padding for the standalone page */
            margin-bottom: 20px;
        }
        .import-section-card h5 {
            margin-bottom: 15px; /* More space below heading */
            font-size: 1.2em; /* Slightly larger heading */
        }
        .import-section-card .form-control-file {
            display: block;
            width: 100%;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .form-group.category-display-import { /* New class name for clarity on import page */
            font-size: 1.1em;
            font-weight: bold;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 8px;
            display: flex;
            align-items: center;
        }
        .form-group.category-display-import label {
            margin-right: 10px;
        }
        .form-group.category-display-import input {
            flex-grow: 1;
            background-color: #e9ecef;
            border: none;
            font-weight: bold;
        }
    </style>
</head>

@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.guru')
@endsection

@section('content')
<div class="col-md-6 offset-md-3"> {{-- Center the card --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Impor Nilai Akademik</h4>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success mt-2">{!! session('success') !!}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger mt-2">{!! session('error') !!}</div>
            @endif

            @if (session('warning'))
                <div class="alert alert-warning mt-2">{!! session('warning') !!}</div>
            @endif

            {{-- Display selected category on import page --}}
            <div class="form-group category-display-import">
                <label for="displayMataPelajaranImport">Mata Pelajaran:</label>
                <input type="text" id="displayMataPelajaranImport" class="form-control" value="{{ $selectedCategory }}" disabled>
            </div>

            <div class="import-section-card">
                <h5>Upload File Excel</h5>
                <p>Pastikan file Excel Anda memiliki kolom dengan header: <strong>nisn</strong>, <strong>nama</strong>, dan <strong>nilai</strong>.</p>
                <form action="{{ route('nilai_akademik.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="file_impor" class="form-label">Pilih File Excel (.xlsx, .xls, .csv)</label>
                        <input type="file" class="form-control-file @error('file') is-invalid @enderror" id="file_impor" name="file" required>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        {{-- Pass the category as a hidden input --}}
                        <input type="hidden" name="mata_pelajaran_impor" value="{{ $selectedCategory }}">
                    </div>
                    <a href="{{ route('nilai_akademik.create', ['mata_pelajaran' => $selectedCategory]) }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-sm btn-success" title="Import Excel">
                        <i class="fas fa-upload"></i> 
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
