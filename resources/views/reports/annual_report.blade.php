<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annual Academic Report Card - {{ $student->full_name ?? 'Student' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #0f172a;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #1e3a8a;
            font-size: 22px;
            text-transform: uppercase;
            font-weight: 800;
        }
        .header p {
            margin: 3px 0;
            color: #475569;
            font-size: 11px;
        }
        .report-badge {
            display: inline-block;
            background-color: #1e3a8a;
            color: #ffffff;
            font-size: 11px;
            font-weight: bold;
            padding: 4px 12px;
            border-radius: 4px;
            margin-top: 5px;
            text-transform: uppercase;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
        }
        .meta-table td {
            padding: 6px 10px;
            border: 1px solid #cbd5e1;
        }
        .meta-label {
            font-weight: bold;
            color: #334155;
            width: 15%;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }
        .main-table th, .main-table td {
            border: 1px solid #94a3b8;
            padding: 5px 6px;
            text-align: center;
        }
        .main-table th {
            background-color: #1e293b;
            color: #ffffff;
            text-transform: uppercase;
            font-size: 9px;
        }
        .main-table td.align-left {
            text-align: left;
            font-weight: 600;
        }
        .main-table tr:nth-child(even) {
            background-color: #f1f5f9;
        }
        .summary-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #eff6ff;
            border: 1px solid #93c5fd;
        }
        .summary-grid td {
            padding: 8px 12px;
            border: 1px solid #bfdbfe;
            font-size: 11px;
        }
        .remarks-box {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .remarks-box td {
            padding: 8px;
            border: 1px solid #cbd5e1;
        }
        @media print {
            .no-print { display: none; }
        }
        .print-btn {
            background-color: #2563eb;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <div class="no-print" style="text-align: right; margin-bottom: 10px;">
        <button onclick="window.print()" class="print-btn">🖨️ Print Annual Report Card</button>
    </div>

    <!-- Header -->
    <div class="header">
        <h1>EDUPRO LEDGER ACADEMY</h1>
        <p>Official Session Academic Performance Summary</p>
        <span class="report-badge">ANNUAL CUMULATIVE REPORT SHEET</span>
    </div>

    <!-- Student Metadata -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">Student Name:</td>
            <td><strong>{{ $student->full_name ?? 'N/A' }}</strong></td>
            <td class="meta-label">Admission No:</td>
            <td>{{ $student->admission_no ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Class:</td>
            <td>{{ $student->classroom->name ?? 'N/A' }}</td>
            <td class="meta-label">Academic Session:</td>
            <td>{{ $session ?? '2025/2026' }}</td>
        </tr>
    </table>

    <!-- Annual Scores Matrix Table -->
    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch; margin-bottom: 15px;">
    <table class="main-table" style="min-width: 650px;">
        <thead>
            <tr>
                <th rowspan="2" class="align-left" style="width: 22%;">Subject Name</th>
                <th colspan="4">Summary of Annual Scores</th>
                <th rowspan="2">Annual Avg (%)</th>
                <th rowspan="2">Highest</th>
                <th rowspan="2">Lowest</th>
                <th rowspan="2">Rank</th>
                <th rowspan="2">Grade</th>
                <th rowspan="2">Remark</th>
            </tr>
            <tr>
                <th style="width: 8%;">Term 1</th>
                <th style="width: 8%;">Term 2</th>
                <th style="width: 8%;">Term 3</th>
                <th style="width: 9%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($annual_subjects as $row)
            <tr>
                <td class="align-left">{{ $row['subject_name'] }}</td>
                <td>{{ $row['term1_total'] }}</td>
                <td>{{ $row['term2_total'] }}</td>
                <td>{{ $row['term3_total'] }}</td>
                <td style="font-weight: bold; background-color: #f8fafc;">{{ $row['yearly_total'] }}</td>
                <td style="font-weight: bold;">{{ $row['annual_average'] }}%</td>
                <td>{{ $row['highest_in_class'] }}</td>
                <td>{{ $row['lowest_in_class'] }}</td>
                <td>{{ $row['subject_position'] }}</td>
                <td style="font-weight: bold;">{{ $row['grade'] }}</td>
                <td>{{ $row['remark'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="11" style="padding: 15px; text-align: center; color: #64748b;">No annual score records found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <!-- Annual Overall Performance Summary -->
    <table class="summary-grid">
        <tr>
            <td><strong>Subjects Offered:</strong> {{ count($annual_subjects) }}</td>
            <td><strong>Grand Annual Marks:</strong> {{ $annual_grand_total }}</td>
            <td><strong>Final Annual Average:</strong> {{ $final_annual_average }}%</td>
            <td><strong>Annual Position:</strong> {{ $annual_class_position }} / {{ $total_students_in_class }}</td>
        </tr>
    </table>

    <!-- Remarks -->
    <table class="remarks-box">
        <tr>
            <td style="width: 25%; font-weight: bold; background-color: #f8fafc;">Form Master's Remark:</td>
            <td>{{ $remarks['form_master_remark'] ?? 'Good performance throughout the session.' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; background-color: #f8fafc;">Principal's Decision:</td>
            <td style="font-weight: bold; color: #1e3a8a;">{{ $remarks['principal_remark'] ?? 'Promoted to the next class.' }}</td>
        </tr>
    </table>

</body>
</html>
