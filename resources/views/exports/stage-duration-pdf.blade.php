<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            color: #1f2937;
        }
        .date-range {
            margin-bottom: 20px;
            color: #4b5563;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        .summary-section {
            margin-top: 30px;
            padding: 20px;
            background-color: #f9fafb;
            border-radius: 4px;
        }
        .bottleneck {
            color: #dc2626;
        }
        .efficient {
            color: #059669;
        }
        .footer {
            font-size: 0.8em;
            text-align: right;
            margin-top: 30px;
            color: #6b7280;
        }
    </style>
</head>
<body>
<div class="header">
    <h2>Análisis de Duración de Etapas del Proceso</h2>
</div>

<div class="date-range">
    <strong>Período:</strong> {{ $startDate }} - {{ $endDate }}
</div>

<table>
    <thead>
    <tr>
        <th>Etapa del Proceso</th>
        <th>Duración Promedio (min)</th>
        <th>Comparación</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $item)
        @php
            $comparisonToAvg = ($item['duration'] / $analysis['averageDuration']) * 100 - 100;
            $class = $comparisonToAvg > 20 ? 'bottleneck' : ($comparisonToAvg < -20 ? 'efficient' : '');
        @endphp
        <tr>
            <td>{{ $item['name'] }}</td>
            <td>{{ number_format($item['duration'], 2) }}</td>
            <td class="{{ $class }}">
                {{ $comparisonToAvg > 0 ? '+' : '' }}{{ number_format($comparisonToAvg, 1) }}%
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="summary-section">
    <h3>Resumen del Análisis</h3>
    <p><strong>Duración Total Promedio:</strong> {{ number_format($analysis['totalDuration'], 2) }} minutos</p>
    <p><strong>Duración Promedio por Etapa:</strong> {{ number_format($analysis['averageDuration'], 2) }} minutos</p>

    @if($analysis['bottlenecks']->count() > 0)
        <h4>Cuellos de Botella Potenciales:</h4>
        <ul>
            @foreach($analysis['bottlenecks'] as $stage)
                <li>
                    <strong>{{ $stage['name'] }}</strong>:
                    {{ number_format($stage['duration'], 2) }} min
                    ({{ number_format(($stage['duration'] / $analysis['averageDuration'] * 100) - 100, 1) }}% sobre el promedio)
                </li>
            @endforeach
        </ul>
    @endif
</div>

<div class="footer">
    Generado el: {{ $generatedAt }}
</div>
</body>
</html>
