<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payroll Report - {{ date('F', mktime(0,0,0,$month,1)) }} {{ $year }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #222; }
        .header { text-align: center; border-bottom: 2px solid #1a3a5c; padding-bottom: 10px; margin-bottom: 15px; }
        .header .company { font-size: 18px; font-weight: bold; color: #1a3a5c; }
        .header .title { font-size: 14px; font-weight: bold; margin-top: 4px; }
        .header .period { font-size: 12px; color: #666; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background: #1a3a5c; color: #fff; padding: 6px 8px; text-align: left; font-size: 10px; }
        td { padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 10px; }
        tr:nth-child(even) { background: #f8f9fa; }
        .amount { text-align: right; }
        .footer-row { background: #1a3a5c !important; color: #fff; font-weight: bold; }
        .footer-row td { color: #fff; border: none; }
        .badge { display: inline-block; padding: 1px 6px; border-radius: 8px; font-size: 9px; font-weight: bold; }
        .badge-approved { background: #d4edda; color: #155724; }
        .badge-draft { background: #fff3cd; color: #856404; }
        .badge-paid { background: #cce5ff; color: #004085; }
        .badge-unpaid { background: #f8d7da; color: #721c24; }
        .summary-box { display: table; width: 100%; margin-bottom: 15px; }
        .summary-item { display: table-cell; text-align: center; padding: 10px; border: 1px solid #ddd; }
        .summary-item .val { font-size: 16px; font-weight: bold; color: #1a3a5c; }
        .summary-item .lbl { font-size: 10px; color: #888; margin-top: 2px; }
    </style>
</head>
<body>
<div class="header">
    <div class="company">MUZN Delivery Operations</div>
    <div class="title">Payroll Report</div>
    <div class="period">{{ date('F', mktime(0,0,0,$month,1)) }} {{ $year }} &nbsp;|&nbsp; Generated: {{ now()->format('d M Y H:i') }}</div>
</div>

<div class="summary-box">
    <div class="summary-item">
        <div class="val">{{ $payrolls->count() }}</div>
        <div class="lbl">Total Records</div>
    </div>
    <div class="summary-item">
        <div class="val">AED {{ number_format($payrolls->sum('gross_salary'), 2) }}</div>
        <div class="lbl">Total Gross</div>
    </div>
    <div class="summary-item">
        <div class="val">AED {{ number_format($payrolls->sum('total_deductions'), 2) }}</div>
        <div class="lbl">Total Deductions</div>
    </div>
    <div class="summary-item">
        <div class="val">AED {{ number_format($payrolls->sum('net_salary'), 2) }}</div>
        <div class="lbl">Total Net Salary</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Payroll ID</th>
            <th>Employee</th>
            <th>Gross Salary</th>
            <th>Loan Ded.</th>
            <th>Fine Ded.</th>
            <th>Salik Ded.</th>
            <th>Other Ded.</th>
            <th>Total Ded.</th>
            <th>Net Salary</th>
            <th>Status</th>
            <th>Payment</th>
        </tr>
    </thead>
    <tbody>
        @foreach($payrolls as $payroll)
        <tr>
            <td>{{ $payroll->payroll_id }}</td>
            <td>{{ $payroll->employee->employee_id ?? '' }} - {{ $payroll->employee->name ?? '' }}</td>
            <td class="amount">{{ number_format($payroll->gross_salary, 2) }}</td>
            <td class="amount">{{ number_format($payroll->loan_deduction, 2) }}</td>
            <td class="amount">{{ number_format($payroll->fine_deduction, 2) }}</td>
            <td class="amount">{{ number_format($payroll->salik_deduction, 2) }}</td>
            <td class="amount">{{ number_format($payroll->other_deduction + $payroll->penalty_deduction, 2) }}</td>
            <td class="amount">{{ number_format($payroll->total_deductions, 2) }}</td>
            <td class="amount"><strong>{{ number_format($payroll->net_salary, 2) }}</strong></td>
            <td><span class="badge badge-{{ $payroll->payroll_status }}">{{ strtoupper($payroll->payroll_status) }}</span></td>
            <td><span class="badge badge-{{ $payroll->payment_status }}">{{ strtoupper($payroll->payment_status) }}</span></td>
        </tr>
        @endforeach
        <tr class="footer-row">
            <td colspan="2"><strong>TOTAL</strong></td>
            <td class="amount">{{ number_format($payrolls->sum('gross_salary'), 2) }}</td>
            <td class="amount">{{ number_format($payrolls->sum('loan_deduction'), 2) }}</td>
            <td class="amount">{{ number_format($payrolls->sum('fine_deduction'), 2) }}</td>
            <td class="amount">{{ number_format($payrolls->sum('salik_deduction'), 2) }}</td>
            <td class="amount">{{ number_format($payrolls->sum('other_deduction') + $payrolls->sum('penalty_deduction'), 2) }}</td>
            <td class="amount">{{ number_format($payrolls->sum('total_deductions'), 2) }}</td>
            <td class="amount">{{ number_format($payrolls->sum('net_salary'), 2) }}</td>
            <td colspan="2"></td>
        </tr>
    </tbody>
</table>
</body>
</html>
