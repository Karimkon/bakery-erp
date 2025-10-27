<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Production Report</title>
</head>
<body>
    <h2>Daily Production & Sales Report</h2>
    <p><strong>Date:</strong> {{ $date->format('d M Y') }}</p>
    <p><strong>Generated:</strong> {{ now()->format('d M Y H:i') }}</p>
    
    <h3>Quick Summary</h3>
    <ul>
        <li><strong>Production Value:</strong> UGX {{ number_format($reportData['totalProduction']) }}</li>
        <li><strong>Total Sales:</strong> UGX {{ number_format($reportData['totalDispatch'] + $reportData['totalSales']) }}</li>
        <li><strong>Bank Deposits:</strong> UGX {{ number_format($reportData['totalBankDeposits']) }}</li>
        <li><strong>Expenses:</strong> UGX {{ number_format($reportData['totalExpenses']) }}</li>
        <li><strong>Net Cash:</strong> UGX {{ number_format($reportData['financialSummary']['net_cash']) }}</li>
    </ul>
    
    <p>A detailed PDF report is attached to this email.</p>
    
    <hr>
    <p><small>This is an automated report from Bakery ERP System.</small></p>
</body>
</html>