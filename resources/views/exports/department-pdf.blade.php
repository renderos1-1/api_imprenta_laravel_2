<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .date-range {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
        }
        .footer {
            font-size: 0.8em;
            text-align: right;
            margin-top: 30px;
        }
        .total-row {
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="header">
    <h2>Transacciones por Departamento</h2>
</div>

<div class="date-range">
    <strong>Período:</strong> {{ $startDate }} - {{ $endDate }}
</div>

<table>
    <thead>
    <tr>
        <th>Departamento</th>
        <th>Total Transacciones</th>
        <th>Porcentaje</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $item)
        <tr>
            <td>{{ $item['department'] }}</td>
            <td>{{ $item['count'] }}</td>
            <td>{{ $item['percentage'] }}%</td>
        </tr>
    @endforeach
    <tr class="total-row">
        <td>Total</td>
        <td>{{ $total }}</td>
        <td>100%</td>
    </tr>
    </tbody>
</table>

<div class="footer">
    Generado el: {{ $generatedAt }}
</div>
</body>
</html>
