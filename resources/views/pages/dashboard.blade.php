@extends('layouts.app')

@section('title', ' Dashboard Admin')

@push('style')
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Dashboard </h1>
            </div>

            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-primary">
                            <i class="far fa-user"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Karyawan</h4>
                            </div>
                            <div class="card-body">{{ $totalKaryawan }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h4>Daftar Kehadiran Tanggal {{ $tanggal->format('d M Y') }}</h4>
                </div>
                <div class="card-body">
                    @if ($hadirHariIni->isEmpty())
                        <p class="text-muted">Tidak ada karyawan yang hadir pada tanggal ini.</p>
                    @else
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nama</th>
                                    <th>Jabatan</th>
                                    <th>Perusahaan</th>
                                    <th>Waktu Masuk</th>
                                    <th>Waktu Pulang</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($hadirHariIni as $absen)
                                    <tr>
                                        <td>{{ $absen->user->name }}</td>
                                        <td>{{ $absen->user->jabatan }}</td>
                                        <td>{{ $absen->user->company->name ?? 'Tidak Ada' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($absen->clock_in)->format('H:i:s') }}</td>
                                        <td>{{ $absen->clock_out ? \Carbon\Carbon::parse($absen->clock_out)->format('H:i:s') : 'Belum Pulang' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            {{-- <div class="card mb-4">
                <div class="card-header">
                    <h4>Pilih Karyawan</h4>
                </div>
                <div class="card-body">
                    <select id="userSelect" class="form-select">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div> --}}

            <div class="card mb-4">

                <div class="card-header">
                    <h4>Pilih Karyawan</h4>
                </div>
                <div class="card-body">
                    <select id="userSelect" class="form-control">
                        <option value="" selected disabled>-- Pilih Karyawan --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Container Tabel Data Izin Karyawan (Awalnya Disembunyikan) -->
            <div class="card" id="permissionTableContainer">
                <div class="card-header">
                    <div>
                        <h2>Data Izin Karyawan</h2>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>Tanggal Izin</th>
                                <th>Alasan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="permissionTable">
                            <tr>
                                <td colspan="3">Silakan pilih karyawan untuk melihat data izin.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>


            {{-- <div class="card">
                <div class="card-header">
                    <h4>Data Izin Karyawan</h4>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>Tanggal Izin</th>
                                <th>Alasan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="permissionTable">
                            <tr>
                                <td colspan="3" class="text-center">Silakan pilih karyawan untuk melihat data izin.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div> --}}

            <canvas id="attendanceChart"></canvas>
            <div id="chart-tooltip"
                style="position: absolute; background: rgba(0,0,0,0.8); color: white; padding: 10px; border-radius: 5px; font-size: 12px; display: none;">
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById("attendanceChart").getContext("2d");

            const labels = @json($attendanceLabels);
            const hadirData = @json($attendanceData);
            const absenData = @json($absentData);
            const presentDetails = @json($presentDetails);
            const absentDetails = @json($absentDetails);

            const attendanceChart = new Chart(ctx, {
                type: "bar",
                data: {
                    labels: labels,
                    datasets: [{
                            label: "Hadir",
                            data: hadirData,
                            backgroundColor: "rgba(75, 192, 192, 0.7)",
                            borderColor: "rgba(75, 192, 192, 1)",
                            borderWidth: 1,
                        },
                        {
                            label: "Absen",
                            data: absenData,
                            backgroundColor: "rgba(255, 99, 132, 0.7)",
                            borderColor: "rgba(255, 99, 132, 1)",
                            borderWidth: 1,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                        },
                        tooltip: {
                            enabled: false, // Matikan tooltip bawaan
                            external: function(context) {
                                let tooltipEl = document.getElementById("chart-tooltip");
                                if (!tooltipEl) {
                                    tooltipEl = document.createElement("div");
                                    tooltipEl.id = "chart-tooltip";
                                    tooltipEl.style.position = "absolute";
                                    tooltipEl.style.background = "rgba(0, 0, 0, 0.8)";
                                    tooltipEl.style.color = "#fff";
                                    tooltipEl.style.padding = "10px";
                                    tooltipEl.style.borderRadius = "5px";
                                    tooltipEl.style.fontSize = "12px";
                                    tooltipEl.style.pointerEvents = "none";
                                    document.body.appendChild(tooltipEl);
                                }

                                let tooltipModel = context.tooltip;
                                if (tooltipModel.opacity === 0) {
                                    tooltipEl.style.opacity = "0";
                                    tooltipEl.style.display = "none";
                                    return;
                                }

                                let date = labels[tooltipModel.dataPoints[0].dataIndex];
                                let hadirList = presentDetails[date] || [];
                                let absenList = absentDetails[date] || [];

                                tooltipEl.innerHTML = `
                                <b>${date}</b><br>
                                <b>Karyawan Hadir:</b><br> ${hadirList.join("<br>") || "Tidak ada"}<br><br>
                                <b>Karyawan Absen:</b><br> ${absenList.join("<br>") || "Tidak ada"}
                            `;

                                let position = context.chart.canvas.getBoundingClientRect();
                                tooltipEl.style.left = position.left + window.pageXOffset + tooltipModel
                                    .caretX + "px";
                                tooltipEl.style.top = position.top + window.pageYOffset + tooltipModel
                                    .caretY + "px";
                                tooltipEl.style.opacity = "1";
                                tooltipEl.style.display = "block";
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                        },
                    },
                },
            });
        });
    </script>


    <script>
        $(document).ready(function() {
            // Sembunyikan tabel izin saat pertama kali halaman dimuat
            $('#permissionTableContainer').hide();

            $('#userSelect').change(function() {
                var userId = $(this).val();

                if (userId) {
                    // Jika karyawan dipilih, tampilkan tabel izin
                    $('#permissionTableContainer').fadeIn();

                    // Ambil data izin dari server
                    $.ajax({
                        url: '/get-permissions/' + userId,
                        type: 'GET',
                        success: function(data) {
                            var rows = '';

                            if (data.length > 0) {
                                $.each(data, function(index, permission) {
                                    var statusClass = permission.is_approved ?
                                        'badge-success' : 'badge-danger';
                                    var statusText = permission.is_approved ?
                                        'Disetujui' : 'Pending';

                                    rows += '<tr>';
                                    rows += '<td>' + permission.date_permission +
                                        '</td>';
                                    rows += '<td>' + permission.reason + '</td>';
                                    rows += '<td><span class="badge ' + statusClass +
                                        '">' + statusText + '</span></td>';
                                    rows += '</tr>';
                                });
                            } else {
                                rows = '<tr><td colspan="3">Tidak ada data izin.</td></tr>';
                            }

                            $('#permissionTable').html(rows);
                        }
                    });
                } else {
                    // Jika tidak ada karyawan dipilih, sembunyikan tabel
                    $('#permissionTableContainer').fadeOut();
                }
            });
        });
    </script>
@endpush
