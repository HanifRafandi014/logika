<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@extends('layouts.main')
@section('sidebar')
    @include('layouts.sidebar.admin')
@endsection
@section('content')
<div class="container">
    <div class="page-inner">
        <div class="row">
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div
                        class="icon-big text-center icon-primary bubble-shadow-small"
                      >
                        <i class="fas fa-users"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Data Siswa</p>
                        <h4 class="card-title">{{ number_format($jumlahSiswa) }}</h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div
                        class="icon-big text-center icon-info bubble-shadow-small"
                      >
                        <i class="fas fa-users"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Data Guru</p>
                        <h4 class="card-title">{{ number_format($jumlahGuru) }}</h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div
                        class="icon-big text-center icon-success bubble-shadow-small"
                      >
                        <i class="fas fa-users"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Data Pembina</p>
                        <h4 class="card-title">{{ number_format($jumlahPembina) }}</h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div
                        class="icon-big text-center icon-secondary bubble-shadow-small"
                      >
                        <i class="fas fa-users"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Data Alumni</p>
                        <h4 class="card-title">{{ number_format($jumlahAlumni) }}</h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
    </div>
</div>

<div class="container my-4">
    <h4 class="mb-3">Hasil Perkembangan Nilai Siswa</h4>
    <canvas id="comparisonRadarChart" width="50" height="50"></canvas>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('comparisonRadarChart').getContext('2d');

    new Chart(ctx, {
        type: 'radar',
        data: {
            labels: [
                'IPA',
                'Matematika',
                'IPS',
                'Bahasa Indonesia',
                'Bahasa Inggris'
            ],
            datasets: [
                {
                    label: 'Akademik',
                    data: [86.25, 89.08, 55.46, 93.44, 84.72],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2
                },
                {
                    label: 'Non Akademik',
                    data: [70, 70, 65, 70, 60],
                    backgroundColor: 'rgba(144, 238, 144, 0.2)',
                    borderColor: 'rgba(144, 238, 144, 1)',
                    pointBackgroundColor: 'rgba(144, 238, 144, 1)',
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: false
                }
            },
            scales: {
                r: {
                    angleLines: { display: true },
                    suggestedMin: 0,
                    suggestedMax: 100,
                    ticks: {
                        stepSize: 10,
                        backdropColor: 'transparent'
                    }
                }
            }
        }
    });
});
</script>
@endsection