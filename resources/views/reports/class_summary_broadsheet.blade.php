<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Broadsheet - {{ $classroom->name ?? 'Class' }} Summary</title>
    <style>
        @page {
            size: A3 landscape;
            margin: 10mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #0f172a;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }
        .header-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .school-name {
            font-size: 22px;
            font-weight: 800;
            color: #1e3a8a;
            text-transform: uppercase;
            margin: 0;
        }
        .school-sub {
            font-size: 11px;
            color: #475569;
            margin: 2px 0 0 0;
        }
        .sheet-title {
            font-size: 16px;
            font-weight: 800;
            background-color: #1e3a8a;
            color: #ffffff;
            padding: 6px 16px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .meta-bar {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
        }
        .meta-bar td {
            padding: 6px 12px;
            font-size: 11px;
            border-right: 1px solid #cbd5e1;
        }
        .meta-label {
            font-weight: bold;
            color: #334155;
        }
        .broadsheet-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-bottom: 15px;
        }
        .broadsheet-table th, .broadsheet-table td {
            border: 1px solid #94a3b8;
            padding: 4px 5px;
            text-align: center;
            white-space: nowrap;
        }
        .broadsheet-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
        }
        .broadsheet-table td.align-left {
            text-align: left;
            font-weight: 600;
        }
        .sub-header {
            background-color: #334155 !important;
            color: #f8fafc !important;
            font-size: 8px;
        }
        .broadsheet-table tr:nth-child(even) {
            background-color: #f1f5f9;
        }
        .highlight-col {
            background-color: #e2e8f0;
            font-weight: 700;
        }
        .position-col {
            background-color: #fef08a;
            font-weight: 800;
            color: #854d0e;
        }
        .signature-grid {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .signature-grid td {
            width: 33.33%;
            vertical-align: bottom;
            padding-top: 30px;
        }
        .sig-line {
            border-top: 1px dashed #475569;
            width: 85%;
            margin-bottom: 4px;
        }
        .sig-title {
            font-size: 10px;
            font-weight: bold;
            color: #334155;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
            }
        }
        .print-btn {
            background-color: #16a34a;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <div class="no-print" style="text-align: right; margin-bottom: 10px;">
        <button onclick="window.print()" class="print-btn">🖨️ Print Class Summary Broadsheet</button>
    </div>

    <!-- Header -->
    <div class="header-box">
        <div>
            <h1 class="school-name">{{ $school->school_name ?? 'EduPro Model Academy' }}</h1>
            <p class="school-sub">{{ $school->school_address ?? '12 Education Avenue, Victoria Island' }} | {{ $school->phone_number ?? '' }}</p>
        </div>
        <div>
            <span class="sheet-title">CLASS SUMMARY BROADSHEET</span>
        </div>
    </div>

    <!-- Meta Details -->
    <table class="meta-bar">
        <tr>
            <td><span class="meta-label">Class:</span> {{ $classroom->name ?? 'N/A' }}</td>
            <td><span class="meta-label">Session:</span> {{ $session ?? '2025/2026' }}</td>
            <td><span class="meta-label">Term:</span> {{ $term ?? 'First Term' }}</td>
            <td><span class="meta-label">Form Master:</span> {{ $form_master ?? 'N/A' }}</td>
            <td><span class="meta-label">Total Class Roster:</span> {{ count($students) }} Students</td>
        </tr>
    </table>

    <!-- Matrix Table -->
    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch; margin-bottom: 15px;">
    <table class="broadsheet-table" style="min-width: 800px;">
        <thead>
            <tr>
                <th rowspan="2" style="width: 30px;">S/N</th>
                <th rowspan="2" style="width: 90px;">Admission No</th>
                <th rowspan="2" class="align-left" style="min-width: 140px;">Student Name</th>
                @foreach($subjects as $sub)
                    <th colspan="2" class="sub-header">{{ $sub->code ?? $sub->name }}</th>
                @endforeach
                <th rowspan="2" class="highlight-col">Grand Total</th>
                <th rowspan="2" class="highlight-col">Average (%)</th>
                <th rowspan="2" class="position-col">Class Position</th>
                <th rowspan="2" style="min-width: 120px;">Form Master Remark</th>
            </tr>
            <tr>
                @foreach($subjects as $sub)
                    <th style="font-size: 7px; width: 28px;">Score</th>
                    <th style="font-size: 7px; width: 20px;">Grd</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($students as $index => $stu)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $stu['admission_no'] }}</td>
                <td class="align-left">{{ $stu['full_name'] }}</td>
                @foreach($subjects as $sub)
                    @php
                        $scoreItem = $stu['scores'][$sub->subject_id] ?? null;
                    @endphp
                    <td>{{ $scoreItem ? ($scoreItem['total_score'] ?? '-') : '-' }}</td>
                    <td style="font-weight: bold;">{{ $scoreItem ? ($scoreItem['grade'] ?? '-') : '-' }}</td>
                @endforeach
                <td class="highlight-col">{{ $stu['grand_total'] ?? 0 }}</td>
                <td class="highlight-col">{{ number_format($stu['average'] ?? 0, 2) }}%</td>
                <td class="position-col">{{ $stu['class_position'] ? $stu['class_position'] . 'th' : '-' }}</td>
                <td style="text-align: left; font-size: 8px;">{{ $stu['form_master_remarks'] ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ 6 + (count($subjects) * 2) }}" style="padding: 20px; text-align: center;">No student broadsheet records found for this class.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <!-- Signatures Section -->
    <table class="signature-grid">
        <tr>
            <td>
                <div class="sig-line"></div>
                <div class="sig-title">Form Master Signature & Date</div>
            </td>
            <td>
                <div class="sig-line"></div>
                <div class="sig-title">Guidance Counselor Signature</div>
            </td>
            <td style="text-align: right;">
                <div class="sig-line" style="margin-left: auto;"></div>
                <div class="sig-title">Headteacher / Principal Signature & Stamp</div>
            </td>
        </tr>
    </table>

</body>
</html>
