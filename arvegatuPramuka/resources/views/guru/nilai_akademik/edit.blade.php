<head>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    {{-- Font Awesome for icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" xintegrity="sha512-1ycn6IcaQQ40JDrFNgychGUAUyVjQaqzLSYd0n1Nq3p4f+E20n2+03u+3h2cE8f9Xz+jF+7x+gY+2P+P+A+g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Basic table styling */
        .score-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .score-table th, .score-table td {
            border: 1px solid #ddd;
            padding: 4px; /* Reduced padding for more compact cells */
            text-align: left;
        }
        .score-table th {
            background-color: #f2f2f2;
            white-space: nowrap; /* Prevent header text from wrapping */
            font-size: 0.85em; /* Smaller font for headers */
        }
        .score-input {
            width: 60px; /* Further reduced width for input fields */
            padding: 3px; /* Reduced padding for input fields */
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 0.9em; /* Smaller font for input values */
        }
        /* Ensure input fields fit within table cells without overflowing */
        .score-table td input[type="number"] {
            box-sizing: border-box; /* Include padding and border in the element's total width and height */
        }
        /* Adjust specific column widths */
        .score-table th:nth-child(1), /* No */
        .score-table td:nth-child(1) {
            width: 30px;
            text-align: center;
        }
        .score-table th:nth-child(2), /* NISN */
        .score-table td:nth-child(2) {
            min-width: 90px; /* Adjusted min-width for NISN */
            font-size: 0.85em; /* Smaller font for NISN */
        }
        .score-table th:nth-child(3), /* Nama Siswa */
        .score-table td:nth-child(3) {
            min-width: 150px; /* Adjusted min-width for Nama Siswa */
            font-size: 0.85em; /* Smaller font for Nama Siswa */
        }
        .score-table th:nth-child(4), /* Nilai */
        .score-table td:nth-child(4) {
            min-width: 70px; /* Adjusted min-width for Nilai column */
        }

        .form-group {
            margin-bottom: 15px;
        }
        .category-display {
            font-size: 1.1em;
            font-weight: bold;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 8px;
            display: flex;
            align-items: center;
        }
        .category-display label {
            margin-right: 10px;
        }
        .category-display input {
            flex-grow: 1;
            background-color: #e9ecef; /* Match background */
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
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Edit Nilai Akademik</h4>
        </div>
        <div class="card-body">
            {{-- Form for updating the record. Note the PUT method and passing $nilaiAkademik->id --}}
            <form action="{{ route('nilai_akademik.update', $nilaiAkademik->id) }}" method="POST">
                @csrf
                @method('PUT') {{-- Laravel's way to simulate a PUT request for updates --}}

                {{-- Display selected category --}}
                <div class="form-group category-display">
                    <label for="displayMataPelajaran">Mata Pelajaran:</label>
                    <input type="text" id="displayMataPelajaran" class="form-control" value="{{ $selectedCategory }}" disabled>
                    {{-- No need for hidden mata_pelajaran input here, as it's part of the route or derived from $nilaiAkademik --}}
                </div>

                {{-- Moved buttons here, above the table --}}
                <div class="mb-3 d-flex justify-content-start" style="padding-top: 2%;">
                    <button type="submit" class="btn btn-primary me-2" title="Update Nilai">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    {{-- Make sure the 'Batal' button goes back to the correct category index page --}}
                    <a href="{{ route('nilai_akademik.index', ['mata_pelajaran' => $selectedCategory]) }}" class="btn btn-secondary" title="Batal">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="score-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NISN</th>
                                <th>Nama Siswa</th>
                                <th>Nilai</th> {{-- Ubah header menjadi "Nilai" --}}
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                            <td>{{ $siswa->nisn ?? 'N/A' }}</td>
                            <td>{{ $siswa->nama }}</td>
                            <td>
                                {{-- Input field for the single 'nilai' score. Pre-fill with existing 'nilai'. --}}
                                <input type="number" class="form-control score-input" name="nilai" value="{{ old('nilai', $nilaiAkademik->nilai) }}" min="0" max="100">
                                <input type="hidden" name="siswa_id" value="{{ $siswa->id }}">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </form>
    </div>
</div>
</div>
@endsection