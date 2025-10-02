<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; }
        .header { text-align: center; margin-bottom: 20px; }
        .company { font-size: 18px; font-weight: bold; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #333; padding: 8px; text-align: left; }
        .footer { text-align: center; font-size: 12px; margin-top: 30px; }
    </style>
</head>
<body>

    <div class="header">
        <div class="company">🍞 Bakery ERP System</div>
        <div>Payslip for {{ $payroll->pay_month->format('F Y') }}</div>
    </div>

    <table class="table">
        <tr>
            <th>Employee Name</th>
            <td>{{ $payroll->user->name }}</td>
        </tr>
        <tr>
            <th>Pay Month</th>
            <td>{{ $payroll->pay_month->format('F Y') }}</td>
        </tr>
        <tr>
            <th>Base Salary</th>
            <td>UGX {{ number_format($payroll->base_salary, 0) }}</td>
        </tr>
        <tr>
            <th>Commission</th>
            <td>UGX {{ number_format($payroll->commission, 0) }}</td>
        </tr>
        <tr>
            <th><strong>Total Salary</strong></th>
            <td><strong>UGX {{ number_format($payroll->total_salary, 0) }}</strong></td>
        </tr>
        <tr>
            <th>Status</th>
            <td>{{ ucfirst($payroll->status) }}</td>
        </tr>
    </table>

    <div class="footer">
        <p>Generated on {{ now()->format('d M Y, h:i A') }}</p>
        <p>Signature: ____________________________</p>
    </div>

</body>
</html>
