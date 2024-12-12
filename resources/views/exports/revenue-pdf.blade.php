<!DOCTYPE html>
<html>
<head>
    <title>Revenue Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .header {
            margin-bottom: 20px;
        }
        .date-range {
            margin-bottom: 15px;
            color: #666;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Revenue Report</h1>
    <div class="date-range">
        From: {{ $startDate->format('Y-m-d') }} To: {{ $endDate->format('Y-m-d') }}
    </div>
</div>

<table>
    <thead>
    <tr>
        <th>Date</th>
        <th>Revenue</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $item)
        <tr>
            <td>{{ $item->date }}</td>
            <td>${{ number_format($item->total, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
