<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
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
            background-color: #f3f4f6;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
<div class="header">
    <h2>Person Type Distribution Report</h2>
    <p>Period: {{ $startDate }} to {{ $endDate }}</p>
</div>

<table>
    <thead>
    <tr>
        <th>Transaction Date</th>
        <th>Document Type</th>
        <th>Document Number</th>
        <th>Person Type</th>
        <th>Status</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $item)
        <tr>
            <td>{{ \Carbon\Carbon::parse($item->created_at)->format('Y-m-d H:i:s') }}</td>
            <td>{{ strtoupper($item->document_type) }}</td>
            <td>{{ $item->document_number }}</td>
            <td>{{ $item->person_type === 'persona_natural' ? 'Natural' : 'Jurídica' }}</td>
            <td>{{ ucfirst($item->status) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="footer">
    Generated at: {{ $generatedAt }}
</div>
</body>
</html>
