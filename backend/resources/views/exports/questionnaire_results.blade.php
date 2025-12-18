<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: sans-serif;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .question-card {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 10px;
            page-break-inside: avoid;
        }

        .question-title {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .stats-table {
            width: 100%;
            border-collapse: collapse;
        }

        .stats-table th,
        .stats-table td {
            border: 1px solid #eee;
            padding: 5px;
            text-align: left;
        }

        .answer-list {
            list-style-type: none;
            padding: 0;
        }

        .answer-list li {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }

        .bar-container {
            background-color: #f0f0f0;
            height: 20px;
            width: 100%;
            border-radius: 4px;
            overflow: hidden;
        }

        .bar-fill {
            background-color: #4f46e5;
            height: 100%;
        }

        .count-badge {
            float: right;
            font-size: 0.8em;
            color: #666;
        }

    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Hasil Kuesioner</h2>
        <h3>{{ $title }}</h3>
        <p>Tanggal Cetak: {{ date('d-m-Y') }}</p>
    </div>

    @foreach($results as $q)
    <div class="question-card">
        <div class="question-title">
            {{ $q['text'] }}
            <span class="count-badge">({{ $q['count'] }} Respon)</span>
        </div>

        @if(in_array($q['type'], ['radio', 'checkbox', 'select', 'scale']))
        <table class="stats-table">
            <thead>
                <tr>
                    <th>Opsi</th>
                    <th width="10%">Jumlah</th>
                    <th width="15%">Persentase</th>
                </tr>
            </thead>
            <tbody>
                @foreach($q['stats'] as $option => $count)
                @php
                $percent = $q['count'] > 0 ? round(($count / $q['count']) * 100, 1) : 0;
                @endphp
                <tr>
                    <td>
                        {{ $option }}
                        <div class="bar-container">
                            <div class="bar-fill" style="width: {{ $percent }}%"></div>
                        </div>
                    </td>
                    <td>{{ $count }}</td>
                    <td>{{ $percent }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <ul class="answer-list">
            @forelse($q['answers'] as $ans)
            <li>{{ $ans }}</li>
            @empty
            <li>Belum ada jawaban</li>
            @endforelse
        </ul>
        @endif
    </div>
    @endforeach
</body>
</html>
