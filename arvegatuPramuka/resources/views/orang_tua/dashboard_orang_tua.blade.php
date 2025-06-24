@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.orang_tua')
@endsection

@section('content')
<style>
    /* Custom styles for better visual appeal */
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f0f4f8; /* Warna latar belakang terang */
    }
    .filter-container {
        display: flex;
        justify-content: flex-end; /* Pindahkan filter ke kanan */
        padding: 1.5rem;
        margin-bottom: -1rem; /* Kurangi jarak ke charts-wrapper */
    }
    .filter-select {
        padding: 0.5rem 1rem;
        border: 1px solid #cbd5e1; /* border-gray-300 */
        border-radius: 0.75rem; /* rounded-xl */
        font-size: 1rem;
        color: #475569; /* text-slate-600 */
        background-color: #ffffff;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* shadow-md */
        outline: none;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
    }
    .filter-select:hover {
        border-color: #60a5fa; /* border-blue-400 */
    }
    .filter-select:focus {
        border-color: #3b82f6; /* ring-blue-500 */
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2); /* ring-blue-500/20 */
    }

    .charts-wrapper {
        display: flex;
        flex-wrap: wrap; /* Memungkinkan grafik untuk wrap ke baris berikutnya di layar kecil */
        justify-content: center;
        align-items: flex-start; /* Align items to the start of the cross axis */
        gap: 2rem; /* Jarak antar grafik */
        padding: 1.5rem;
        box-sizing: border-box;
    }
    .chart-container {
        background-color: #ffffff; /* Latar belakang putih */
        border-radius: 1.5rem; /* Sudut lebih membulat */
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); /* Bayangan lembut */
        padding: 1.5rem;
        flex: 1 1 45%; /* Fleksibel, tumbuh dan menyusut, basis 45% untuk 2 kolom */
        max-width: 500px; /* Lebar maksimum untuk tampilan elegan */
        min-width: 300px; /* Lebar minimum agar tidak terlalu kecil */
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    @media (max-width: 768px) {
        .chart-container {
            flex: 1 1 100%; /* Satu kolom di layar kecil */
            max-width: 100%;
        }
    }
    .chart-title {
        color: #334155; /* Warna teks judul yang lebih gelap */
        font-weight: 600; /* Semi-bold */
        margin-bottom: 0.5rem;
        text-align: center;
        font-size: 1.25rem;
    }
    .student-name {
        color: #475569; /* Warna teks nama siswa */
        font-weight: 500; /* Medium weight */
        margin-bottom: 1rem;
        text-align: center;
        font-size: 1rem;
    }
    .no-data-message {
        color: #64748b;
        text-align: center;
        margin-top: 20px;
        font-size: 1.1rem;
    }
    canvas {
        max-width: 100%; /* Memastikan kanvas responsif */
        height: auto; /* Mempertahankan rasio aspek */
    }
</style>

{{-- Filter Semester --}}
<div class="filter-container">
    <form action="{{ route('orang_tua.dashboard') }}" method="GET">
        <select name="semester" id="semesterFilter" class="filter-select" onchange="this.form.submit()">
            <option value="">Semua Semester</option>
            @php
                $semesters = ['semester 1', 'semester 2', 'semester 3', 'semester 4', 'semester 5', 'semester 6'];
            @endphp
            @foreach ($semesters as $semester)
                <option value="{{ $semester }}" {{ $selectedSemester == $semester ? 'selected' : '' }}>
                    {{ ucfirst($semester) }}
                </option>
            @endforeach
        </select>
    </form>
</div>

{{-- Wrapper untuk menengahkan grafik --}}
<div class="charts-wrapper">
    {{-- Kontainer Grafik Akademik --}}
    <div class="chart-container">
        <h4 class="chart-title">Perkembangan Nilai Akademik Siswa</h4>
        <p class="student-name">Nama Siswa: {{ $studentName }}</p>

        @if (empty($academicData))
            <p class="no-data-message">Tidak ada data nilai akademik untuk siswa ini.</p>
        @else
            <canvas id="academicRadarChart"></canvas>
        @endif
    </div>

    {{-- Kontainer Grafik Non Akademik --}}
    <div class="chart-container">
        <h4 class="chart-title">Perkembangan Nilai Non Akademik Siswa</h4>
        <p class="student-name">Nama Siswa: {{ $studentName }}</p>

        @if (empty($nonAcademicData))
            <p class="no-data-message">Tidak ada data nilai non akademik untuk siswa ini.</p>
        @else
            <canvas id="nonAcademicRadarChart"></canvas>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // --- Grafik Akademik ---
    const academicDataExists = {{ json_encode(!empty($academicData)) }};
    if (academicDataExists) {
        const ctxAcademic = document.getElementById('academicRadarChart').getContext('2d');
        const academicLabels = @json($academicLabels);
        const academicData = @json($academicData);

        new Chart(ctxAcademic, {
            type: 'radar',
            data: {
                labels: academicLabels,
                datasets: [
                    {
                        label: 'Akademik',
                        data: academicData,
                        backgroundColor: 'rgba(79, 170, 240, 0.3)', /* Bright blue color */
                        borderColor: 'rgba(79, 170, 240, 1)',
                        pointBackgroundColor: 'rgba(79, 170, 240, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(79, 170, 240, 1)',
                        borderWidth: 2,
                        fill: true,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 1,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 12, family: 'Inter' },
                            color: '#475569',
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        titleFont: { size: 14, family: 'Inter' },
                        bodyFont: { size: 12, family: 'Inter' },
                        padding: 10,
                        displayColors: true,
                        cornerRadius: 8
                    }
                },
                scales: {
                    r: {
                        angleLines: { display: true, color: 'rgba(203, 213, 225, 0.6)' },
                        grid: { color: 'rgba(203, 213, 225, 0.6)' },
                        pointLabels: {
                            font: { size: 11, family: 'Inter', weight: 'bold' },
                            color: '#334155'
                        },
                        suggestedMin: 0,
                        suggestedMax: 100,
                        ticks: {
                            stepSize: 20,
                            backdropColor: 'transparent',
                            font: { size: 10, family: 'Inter' },
                            color: '#64748b'
                        }
                    }
                },
                elements: {
                    line: { tension: 0.1 },
                    point: { radius: 4, hoverRadius: 6 }
                }
            }
        });
    }

    // --- Grafik Non Akademik ---
    const nonAcademicDataExists = {{ json_encode(!empty($nonAcademicData)) }};
    if (nonAcademicDataExists) {
        const ctxNonAcademic = document.getElementById('nonAcademicRadarChart').getContext('2d');
        const nonAcademicLabels = @json($nonAcademicLabels);
        const nonAcademicData = @json($nonAcademicData);

        new Chart(ctxNonAcademic, {
            type: 'radar',
            data: {
                labels: nonAcademicLabels,
                datasets: [
                    {
                        label: 'Non Akademik',
                        data: nonAcademicData,
                        backgroundColor: 'rgba(255, 159, 64, 0.3)', /* Different orange color */
                        borderColor: 'rgba(255, 159, 64, 1)',
                        pointBackgroundColor: 'rgba(255, 159, 64, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(255, 159, 64, 1)',
                        borderWidth: 2,
                        fill: true,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 1,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 12, family: 'Inter' },
                            color: '#475569',
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        titleFont: { size: 14, family: 'Inter' },
                        bodyFont: { size: 12, family: 'Inter' },
                        padding: 10,
                        displayColors: true,
                        cornerRadius: 8
                    }
                },
                scales: {
                    r: {
                        angleLines: { display: true, color: 'rgba(203, 213, 225, 0.6)' },
                        grid: { color: 'rgba(203, 213, 225, 0.6)' },
                        pointLabels: {
                            font: { size: 11, family: 'Inter', weight: 'bold' },
                            color: '#334155'
                        },
                        suggestedMin: 0,
                        suggestedMax: 100,
                        ticks: {
                            stepSize: 20,
                            backdropColor: 'transparent',
                            font: { size: 10, family: 'Inter' },
                            color: '#64748b'
                        }
                    }
                },
                elements: {
                    line: { tension: 0.1 },
                    point: { radius: 4, hoverRadius: 6 }
                }
            }
        });
    }
});
</script>
@endsection
