<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Slip - {{ $payroll->payroll_id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 13px; color: #222; background: #fff; }
        .container { max-width: 700px; margin: 30px auto; padding: 30px; border: 1px solid #ddd; }

        .header { text-align: center; border-bottom: 2px solid #1a3a5c; padding-bottom: 15px; margin-bottom: 20px; }
        .header .company { font-size: 22px; font-weight: bold; color: #1a3a5c; }
        .header .subtitle { font-size: 12px; color: #666; margin-top: 4px; }
        .header .slip-title { font-size: 16px; font-weight: bold; color: #fff; background: #1a3a5c; padding: 6px 20px; border-radius: 4px; display: inline-block; margin-top: 10px; }

        .info-grid { display: table; width: 100%; margin-bottom: 20px; }
        .info-col { display: table-cell; width: 50%; vertical-align: top; }
        .info-row { margin-bottom: 6px; }
        .info-label { font-size: 11px; color: #888; text-transform: uppercase; }
        .info-value { font-size: 13px; font-weight: bold; color: #222; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th { background: #1a3a5c; color: #fff; padding: 8px 12px; text-align: left; font-size: 12px; }
        td { padding: 7px 12px; border-bottom: 1px solid #eee; font-size: 13px; }
        td.amount { text-align: right; font-weight: 500; }
        tr:last-child td { border-bottom: none; }
        .highlight { background: #f5f9ff; }

        .net-salary { background: #1a3a5c; color: #fff; padding: 14px 16px; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; margin-top: 10px; }
        .net-salary .label { font-size: 14px; font-weight: bold; }
        .net-salary .amount { font-size: 22px; font-weight: bold; }

        .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #ddd; display: table; width: 100%; }
        .footer-col { display: table-cell; width: 33%; text-align: center; }
        .footer-label { font-size: 11px; color: #888; margin-top: 20px; border-top: 1px solid #aaa; padding-top: 4px; }

        .badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; }
        .badge-approved { background: #d4edda; color: #155724; }
        .badge-draft { background: #fff3cd; color: #856404; }
        .badge-paid { background: #cce5ff; color: #004085; }
        .badge-unpaid { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="company">MUZN Delivery Operations</div>
        <div class="subtitle">UAE</div>
        <div class="slip-title">SALARY SLIP</div>
    </div>

    <div class="info-grid">
        <div class="info-col">
            <div class="info-row">
                <div class="info-label">Employee ID</div>
                <div class="info-value">{{ $payroll->employee->employee_id }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Employee Name</div>
                <div class="info-value">{{ $payroll->employee->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Phone</div>
                <div class="info-value">{{ $payroll->employee->mobile ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="info-col">
            <div class="info-row">
                <div class="info-label">Payroll ID</div>
                <div class="info-value">{{ $payroll->payroll_id }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Pay Period</div>
                <div class="info-value">{{ date('F', mktime(0,0,0,$payroll->month,1)) }} {{ $payroll->year }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Salary Type</div>
                <div class="info-value">{{ ucfirst($payroll->salary_type) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Status</div>
                <div class="info-value">
                    <span class="badge badge-{{ $payroll->payroll_status }}">{{ strtoupper($payroll->payroll_status) }}</span>
                    &nbsp;
                    <span class="badge badge-{{ $payroll->payment_status }}">{{ strtoupper($payroll->payment_status) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Attendance --}}
    @if($payroll->attendance_days !== null || $payroll->hours_compliance !== null)
    <table>
        <tr>
            <th colspan="2">Attendance & Compliance</th>
        </tr>
        @if($payroll->attendance_days !== null)
        <tr>
            <td>Attendance Days</td>
            <td class="amount">{{ $payroll->attendance_days }} days</td>
        </tr>
        @endif
        @if($payroll->hours_compliance !== null)
        <tr>
            <td>Hours Compliance</td>
            <td class="amount">{{ $payroll->hours_compliance }}%</td>
        </tr>
        @endif
    </table>
    @endif

    {{-- Earnings --}}
    <table>
        <tr>
            <th colspan="2">Earnings</th>
        </tr>
        <tr class="highlight">
            <td>Gross Salary</td>
            <td class="amount">AED {{ number_format($payroll->gross_salary, 2) }}</td>
        </tr>
    </table>

    {{-- Deductions --}}
    <table>
        <tr>
            <th colspan="2">Deductions</th>
        </tr>
        @if($payroll->loan_deduction > 0)
        <tr>
            <td>Loan Deduction</td>
            <td class="amount">- AED {{ number_format($payroll->loan_deduction, 2) }}</td>
        </tr>
        @endif
        @if($payroll->fine_deduction > 0)
        <tr>
            <td>Fine Deduction</td>
            <td class="amount">- AED {{ number_format($payroll->fine_deduction, 2) }}</td>
        </tr>
        @endif
        @if($payroll->salik_deduction > 0)
        <tr>
            <td>Salik Deduction</td>
            <td class="amount">- AED {{ number_format($payroll->salik_deduction, 2) }}</td>
        </tr>
        @endif
        @if($payroll->penalty_deduction > 0)
        <tr>
            <td>Company Penalty</td>
            <td class="amount">- AED {{ number_format($payroll->penalty_deduction, 2) }}</td>
        </tr>
        @endif
        @if($payroll->other_deduction > 0)
        <tr>
            <td>Other Deductions</td>
            <td class="amount">- AED {{ number_format($payroll->other_deduction, 2) }}</td>
        </tr>
        @endif
        <tr class="highlight">
            <td><strong>Total Deductions</strong></td>
            <td class="amount"><strong>- AED {{ number_format($payroll->total_deductions, 2) }}</strong></td>
        </tr>
    </table>

    {{-- Net Salary --}}
    <div class="net-salary">
        <div class="label">NET SALARY</div>
        <div class="amount">AED {{ number_format($payroll->net_salary, 2) }}</div>
    </div>

    @if($payroll->notes)
    <div style="margin-top: 16px; padding: 10px; background: #f8f9fa; border-radius: 4px; font-size: 12px;">
        <strong>Notes:</strong> {{ $payroll->notes }}
    </div>
    @endif

    <div class="footer">
        <div class="footer-col">
            <div class="footer-label">Employee Signature</div>
        </div>
        <div class="footer-col">
            @if($payroll->approver)
            <div style="font-size: 12px; color: #333;">Approved by: <strong>{{ $payroll->approver->name }}</strong></div>
            @if($payroll->approved_at)
            <div style="font-size: 11px; color: #888;">{{ $payroll->approved_at->format('d M Y') }}</div>
            @endif
            @else
            <div class="footer-label">Authorized Signature</div>
            @endif
        </div>
        <div class="footer-col">
            <div style="font-size: 11px; color: #aaa;">Generated: {{ now()->format('d M Y H:i') }}</div>
        </div>
    </div>
</div>
</body>
</html>
