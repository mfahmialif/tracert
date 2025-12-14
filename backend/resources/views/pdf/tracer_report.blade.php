<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Tracer Study</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
            color: #1a365d;
        }
        .header h2 {
            font-size: 14px;
            margin: 5px 0;
            font-weight: normal;
        }
        .header p {
            margin: 5px 0;
            font-size: 11px;
            color: #666;
        }
        .summary-box {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .summary-box h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #2d3748;
        }
        .summary-grid {
            display: table;
            width: 100%;
        }
        .summary-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 10px;
        }
        .summary-item .number {
            font-size: 24px;
            font-weight: bold;
            color: #3182ce;
        }
        .summary-item .label {
            font-size: 11px;
            color: #718096;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #e2e8f0;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #edf2f7;
            font-weight: bold;
            font-size: 11px;
        }
        td {
            font-size: 11px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #2d3748;
            margin: 20px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #e2e8f0;
        }
        .stat-chart {
            margin: 15px 0;
        }
        .stat-bar {
            background: #e2e8f0;
            height: 20px;
            border-radius: 4px;
            overflow: hidden;
            margin: 5px 0;
        }
        .stat-bar-fill {
            background: linear-gradient(90deg, #3182ce, #63b3ed);
            height: 100%;
        }
        .stat-label {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #718096;
            padding: 10px;
            border-top: 1px solid #e2e8f0;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN TRACER STUDY</h1>
        <h2>{{ $questionnaire->title }}</h2>
        <p>Periode: {{ $questionnaire->periode_tahun }}</p>
        <p>Digenerate pada: {{ $generated_at }}</p>
    </div>

    <div class="summary-box">
        <h3>Ringkasan Data</h3>
        <table>
            <tr>
                <td><strong>Jenis Kuesioner:</strong></td>
                <td>{{ $questionnaire->type->name }}</td>
                <td><strong>Total Responden:</strong></td>
                <td>{{ count($responses) }}</td>
            </tr>
            <tr>
                <td><strong>Periode Pengisian:</strong></td>
                <td>{{ $questionnaire->start_date->format('d/m/Y') }} - {{ $questionnaire->end_date->format('d/m/Y') }}</td>
                <td><strong>Status:</strong></td>
                <td>{{ $questionnaire->isOpen() ? 'Aktif' : 'Selesai' }}</td>
            </tr>
        </table>
    </div>

    @if(count($stats) > 0)
    <div class="section-title">Statistik Jawaban</div>
    
    @foreach($stats as $questionId => $stat)
    <div class="stat-chart">
        <p><strong>{{ $stat['question'] }}</strong></p>
        @php
            $total = array_sum($stat['counts']);
        @endphp
        @foreach($stat['counts'] as $option => $count)
        <div class="stat-label">
            <span>{{ $option }}</span>
            <span>{{ $count }} ({{ $total > 0 ? round(($count / $total) * 100, 1) : 0 }}%)</span>
        </div>
        <div class="stat-bar">
            <div class="stat-bar-fill" style="width: {{ $total > 0 ? ($count / $total) * 100 : 0 }}%"></div>
        </div>
        @endforeach
    </div>
    @endforeach
    @endif

    <div class="page-break"></div>

    <div class="section-title">Daftar Responden</div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NIM</th>
                <th>Nama</th>
                <th>Prodi</th>
                <th>Tahun Lulus</th>
                <th>Tanggal Isi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($responses as $index => $response)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $response->alumni->nim }}</td>
                <td>{{ $response->alumni->nama }}</td>
                <td>{{ $response->alumni->prodi->nama_prodi ?? '-' }}</td>
                <td>{{ $response->alumni->tahun_lulus }}</td>
                <td>{{ $response->submitted_at?->format('d/m/Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Laporan Tracer Study - {{ $questionnaire->title }} | Halaman <span class="pagenum"></span>
    </div>
</body>
</html>
