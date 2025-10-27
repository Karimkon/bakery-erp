<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Production Report - {{ $selectedDate->format('d M Y') }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .summary-card { border: 1px solid #ddd; padding: 10px; margin: 5px 0; border-radius: 4px; }
        .table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .table th, .table td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        .table th { background-color: #f5f5f5; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-success { color: #28a745; }
        .text-danger { color: #dc3545; }
        .text-warning { color: #ffc107; }
        .mb-3 { margin-bottom: 15px; }
        .mt-3 { margin-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Daily Production & Sales Report</h1>
        <h3>{{ $selectedDate->format('d M Y') }}</h3>
        <p>Generated on: {{ now()->format('d M Y H:i') }}</p>
    </div>

    <!-- Summary Section -->
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 20px;">
    <div class="summary-card">
        <strong>Production Value</strong><br>
        UGX {{ number_format($totalProduction ?? 0) }}
    </div>
    <div class="summary-card">
        <strong>Total Sales</strong><br>
        UGX {{ number_format(($totalDispatch ?? 0) + ($totalSales ?? 0)) }}
    </div>
    <div class="summary-card">
        <strong>Bank Deposits</strong><br>
        UGX {{ number_format($totalBankDeposits ?? 0) }}
    </div>
    <div class="summary-card">
        <strong>Expenses</strong><br>
        UGX {{ number_format($totalExpenses ?? 0) }}
    </div>
    <div class="summary-card">
        <strong>Net Cash</strong><br>
        UGX {{ number_format($financialSummary['net_cash'] ?? 0) }}
    </div>
    <div class="summary-card">
        <strong>Cash {{ ($financialSummary['cash_shortage_excess'] ?? 0) >= 0 ? 'Excess' : 'Shortage' }}</strong><br>
        UGX {{ number_format(abs($financialSummary['cash_shortage_excess'] ?? 0)) }}
    </div>
</div>

    <!-- Product Breakdown -->
    <h3>Product Breakdown</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Product</th>
                <th class="text-center">Produced</th>
                <th class="text-center">Dispatched</th>
                <th class="text-center">Sold</th>
                <th class="text-center">Returned</th>
                <th class="text-center">Remaining</th>
                <th class="text-center">Sales Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productReport as $product => $data)
            @if($data['produced'] > 0 || $data['dispatched'] > 0 || $data['sold'] > 0)
            <tr>
                <td>{{ ucfirst(str_replace('_', ' ', $product)) }}</td>
                <td class="text-center">{{ number_format($data['produced']) }}</td>
                <td class="text-center">{{ number_format($data['dispatched']) }}</td>
                <td class="text-center">{{ number_format($data['sold']) }}</td>
                <td class="text-center">{{ number_format($data['returned']) }}</td>
                <td class="text-center">{{ number_format($data['remaining']) }}</td>
                <td class="text-center">UGX {{ number_format($data['total_value']) }}</td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>

    <!-- Driver Stock -->
    <h3>Driver Stock Remaining</h3>
    @foreach($driverStock as $driver => $products)
    @php $totalStock = array_sum($products); @endphp
    @if($totalStock > 0)
    <div style="margin-bottom: 10px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
        <strong>{{ $driver }}</strong> - {{ $totalStock }} items
        <br>
        <small>
            @foreach($products as $product => $qty)
            @if($qty > 0)
            {{ ucfirst(str_replace('_', ' ', $product)) }}: {{ $qty }}, 
            @endif
            @endforeach
        </small>
    </div>
    @endif
    @endforeach

    <!-- Financial Summary -->
    <h3>Financial Summary</h3>
    <table class="table">
        <tr>
            <td><strong>Total Production Value</strong></td>
            <td class="text-right">UGX {{ number_format($financialSummary['total_production_value']) }}</td>
        </tr>
        <tr>
            <td><strong>Total Sales Value</strong></td>
            <td class="text-right">UGX {{ number_format($financialSummary['total_sales_value']) }}</td>
        </tr>
        <tr>
            <td><strong>Total Bank Deposits</strong></td>
            <td class="text-right">UGX {{ number_format($financialSummary['total_bank_deposits']) }}</td>
        </tr>
        <tr>
            <td><strong>Total Expenses</strong></td>
            <td class="text-right">UGX {{ number_format($financialSummary['total_expenses']) }}</td>
        </tr>
        <tr>
            <td><strong>Net Cash (Sales - Expenses)</strong></td>
            <td class="text-right">UGX {{ number_format($financialSummary['net_cash']) }}</td>
        </tr>
        <tr>
            <td><strong>Cash {{ $financialSummary['cash_shortage_excess'] >= 0 ? 'Excess' : 'Shortage' }}</strong></td>
            <td class="text-right {{ $financialSummary['cash_shortage_excess'] >= 0 ? 'text-success' : 'text-danger' }}">
                UGX {{ number_format(abs($financialSummary['cash_shortage_excess'])) }}
            </td>
        </tr>
    </table>
</body>
</html>