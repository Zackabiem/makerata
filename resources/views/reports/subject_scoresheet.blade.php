<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Score Sheet - {{ $subject->name ?? 'Subject' }} ({{ $classroom->name ?? 'Class' }})</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #111827;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .school-title {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            color: #1e3a8a;
            margin: 0 0 4px 0;
        }
        .school-sub {
            font-size: 11px;
            color: #4b5563;
            margin: 0;
        }
        .sheet-badge {
            display: inline-block;
            background-color: #1e3a8a;
            color: #ffffff;
            font-size: 12px;
            font-weight: bold;
            padding: 6px 12px;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        .info-grid td {
            padding: 8px 12px;
            font-size: 11px;
            border: 1px solid #e5e7eb;
        }
        .info-label {
            font-weight: bold;
            color: #374151;
            width: 15%;
        }
        .info-value {
            color: #111827;
            width: 35%;
        }
        .scores-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .scores-table th, .scores-table td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            text-align: center;
        }
        .scores-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .scores-table td.align-left {
            text-align: left;
        }
        .scores-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .font-bold {
            font-weight: bold;
        }
        .signature-section {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        .signature-section td {
            width: 50%;
            vertical-align: bottom;
            padding-top: 40px;
        }
        .sig-line {
            border-top: 1px dashed #4b5563;
            width: 80%;
            margin-bottom: 5px;
        }
        .sig-title {
            font-size: 11px;
            font-weight: bold;
            color: #374151;
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
            background-color: #2563eb;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

    <div class="no-print" style="text-align: right; margin-bottom: 10px;">
        <button onclick="window.print()" class="print-btn">🖨️ Print Subject Score Sheet</button>
    </div>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td>
                <h1 class="school-title">{{ $school->school_name ?? 'EduPro Model Academy' }}</h1>
                <p class="school-sub">{{ $school->school_address ?? '12 Education Avenue, Victoria Island' }} | {{ $school->phone_number ?? '' }}</p>
            </td>
            <td style="text-align: right;">
                <span class="sheet-badge">OFFICIAL SUBJECT SCORE SHEET</span>
            </td>
        </tr>
    </table>

    <!-- Meta Information -->
    <table class="info-grid">
        <tr>
            <td class="info-label">Class:</td>
            <td class="info-value">{{ $classroom->name ?? 'N/A' }}</td>
            <td class="info-label">Subject:</td>
            <td class="info-value">{{ $subject->name ?? 'N/A' }} ({{ $subject->code ?? '' }})</td>
        </tr>
        <tr>
            <td class="info-label">Academic Session:</td>
            <td class="info-value">{{ $session ?? '2025/2026' }}</td>
            <td class="info-label">Term:</td>
            <td class="info-value">{{ $term ?? 'First Term' }}</td>
        </tr>
        <tr>
            <td class="info-label">Subject Teacher:</td>
            <td class="info-value">{{ $teacher_name ?? 'N/A' }}</td>
            <td class="info-label">Total Enrolled:</td>
            <td class="info-value">{{ count($scoresList) }} Students</td>
        </tr>
    </table>

    <!-- Scores Table -->
    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch; margin-bottom: 25px;">
    <table class="scores-table" style="min-width: 650px;">
        <thead>
            <tr>
                <th style="width: 40px;">S/N</th>
                <th style="width: 110px;">Admission No</th>
                <th class="align-left">Student Full Name</th>
                <th style="width: 50px;">CA 1</th>
                <th style="width: 50px;">CA 2</th>
                <th style="width: 50px;">CA 3</th>
                <th style="width: 60px;">Exam</th>
                <th style="width: 60px;">Total</th>
                <th style="width: 50px;">Grade</th>
                <th style="width: 50px;">Rank</th>
                <th style="width: 90px;">Remark</th>
            </tr>
        </thead>
        <tbody>
            @forelse($scoresList as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $row['admission_no'] }}</td>
                <td class="align-left font-bold">{{ $row['full_name'] }}</td>
                <td>{{ $row['first_test'] ?? '-' }}</td>
                <td>{{ $row['second_test'] ?? '-' }}</td>
                <td>{{ $row['third_test'] ?? '-' }}</td>
                <td>{{ $row['exam_score'] ?? '-' }}</td>
                <td class="font-bold" style="background-color: #f1f5f9;">{{ $row['total_score'] ?? 0 }}</td>
                <td class="font-bold">{{ $row['grade'] ?? '-' }}</td>
                <td>{{ $row['subject_rank'] ? $row['subject_rank'] . 'th' : '-' }}</td>
                <td>{{ $row['remark'] ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="11" style="padding: 20px; text-align: center; color: #6b7280;">No score records found for this class and subject.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <!-- Signatures -->
    <table class="signature-section">
        <tr>
            <td>
                <div class="sig-line"></div>
                <div class="sig-title">Subject Teacher Signature & Date</div>
            </td>
            <td style="text-align: right;">
                <div class="sig-line" style="margin-left: auto;"></div>
                <div class="sig-title">Headteacher / Principal Signature & Stamp</div>
            </td>
        </tr>
    </table>

</body>
</html>
