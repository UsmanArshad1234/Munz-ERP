<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profit & Loss Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #222; }
        .header { text-align: center; border-bottom: 2px solid #1a3a5c; padding-bottom: 10px; margin-bottom: 20px; }
        .header .company { font-size: 20px; font-weight: bold; color: #1a3a5c; }
        .header .title { font-size: 15px; font-weight: bold; margin-top: 4px; }
        .header .period { font-size: 11px; color: #666; margin-top: 3px; }
        .section { margin-bottom: 20px; }
        .section-title { background: #1a3a5c; color: #fff; padding: 7px 12px; font-size: 13px; font-weight: bold; margin-bottom: 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 7px 12px; border-bottom: 1px solid #eee; }
        td.amount { text-align: right; font-weight: 500; }
        .subtotal td { background: #f0f4f8; font-weight: bold; }
        .net-row { background: #1a3a5c; color: #fff; }
        .net-row td { color: #fff; font-size: 14px; font-weight: bold; border: none; }
        .green { color: #155724; }
        .red { color: #721c24; }
        .two-col { display: table; width: 100%; margin-bottom: 20px; }
        .col { display: table-cell; width: 50%; vertical-align: top; padding-right: 10px; }
        .col:last-child { padding-right: 0; padding-left: 10px; }
    </style>
</head>
<body>
<div class="header">
    <div class="company">MUZN Delivery Operations</div>
    <div class="title">Profit & Loss Report</div>
    <div class="period">
        @if($summary['period']['month'])
            {{ date('F', mktime(0,0,0,$summary['period']['month'],1)) }} {{ $summary['period']['year'] }}
        @elseif($summary['period']['from_date'])
            {{ $summary['period']['from_date'] }} to {{ $summary['period']['to_date'] }}
        @else
            All Time
        @endif
        &nbsp;|&nbsp; Generated: {{ now()->format('d M Y H:i') }}
    </div>
</div>

<div class="two-col">
    {{-- INCOME SECTION --}}
    <div class="col">
        <div class="section-title">INCOME</div>
        <table>
            <tr>
                <td>Platform Income</td>
                <td class="amount">AED {{ number_format($summary['income']['platform_income'], 2) }}</td>
            </tr>
            @foreach($summary['income']['by_platform'] as $platform => $amount)
            <tr style="background:#f8f9fa;">
                <td style="padding-left:24px; font-size:11px; color:#666;">{{ $platform }}</td>
                <td class="amount" style="font-size:11px; color:#666;">{{ number_format($amount, 2) }}</td>
            </tr>
            @endforeach
            <tr>
                <td>Rider Income</td>
                <td class="amount">AED {{ number_format($summary['income']['rider_income'], 2) }}</td>
            </tr>
            <tr class="subtotal">
                <td>Total Income</td>
                <td class="amount">AED {{ number_format($summary['income']['total_income'], 2) }}</td>
            </tr>
        </table>
    </div>

    {{-- EXPENSE SECTION --}}
    <div class="col">
        <div class="section-title">EXPENSES</div>
        <table>
            <tr>
                <td>Payroll (Net)</td>
                <td class="amount">AED {{ number_format($summary['expenses']['payroll'], 2) }}</td>
            </tr>
            <tr>
                <td>Operational Expenses</td>
                <td class="amount">AED {{ number_format($summary['expenses']['operational'], 2) }}</td>
            </tr>
            @foreach($summary['expenses']['by_category'] as $cat => $amount)
            <tr style="background:#f8f9fa;">
                <td style="padding-left:24px; font-size:11px; color:#666;">{{ ucfirst(str_replace('_',' ',$cat)) }}</td>
                <td class="amount" style="font-size:11px; color:#666;">{{ number_format($amount, 2) }}</td>
            </tr>
            @endforeach
            <tr>
                <td>Loans Disbursed</td>
                <td class="amount">AED {{ number_format($summary['expenses']['loans_disbursed'], 2) }}</td>
            </tr>
            <tr>
                <td>External Fines</td>
                <td class="amount">AED {{ number_format($summary['expenses']['external_fines'], 2) }}</td>
            </tr>
            <tr class="subtotal">
                <td>Total Expenses</td>
                <td class="amount">AED {{ number_format($summary['expenses']['total_expenses'], 2) }}</td>
            </tr>
        </table>
    </div>
</div>

{{-- NET SUMMARY --}}
<table style="margin-top:10px;">
    <tr class="net-row">
        <td>NET {{ $summary['summary']['is_profitable'] ? 'PROFIT' : 'LOSS' }}</td>
        <td class="amount">AED {{ number_format(abs($summary['summary']['net_profit']), 2) }}</td>
    </tr>
    <tr class="subtotal">
        <td>Profit Margin</td>
        <td class="amount" class="{{ $summary['summary']['is_profitable'] ? 'green' : 'red' }}">{{ $summary['summary']['profit_margin'] }}%</td>
    </tr>
</table>
</body>
</html>
