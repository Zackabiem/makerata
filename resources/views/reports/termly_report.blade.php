<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal Academic Report Card - {{ $student->full_name ?? 'Student' }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #1e293b; padding: 20px; max-width: 1200px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #0284c7; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #0f172a; font-size: 22px; text-transform: uppercase; }
        .header p { margin: 4px 0; color: #64748b; }
        .meta-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px; margin-bottom: 20px; background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0; }
        .table-container { overflow-x: auto; -webkit-overflow-scrolling: touch; margin-bottom: 20px; }
        table { width: 100%; min-width: 700px; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 8px 10px; text-align: center; white-space: nowrap; }
        th { background: #f1f5f9; font-weight: bold; color: #334155; text-transform: uppercase; font-size: 11px; }
        td.left { text-align: left; }
        .summary-box { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 10px; background: #eff6ff; padding: 12px 18px; border-radius: 6px; font-weight: bold; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>EDUPRO LEDGER ACADEMY</h1>
        <p>Terminal Academic Performance Report</p>
    </div>

    <div class="meta-grid">
        <div><strong>Student Name:</strong> {{ $student->full_name ?? 'N/A' }}</div>
        <div><strong>Admission No:</strong> {{ $student->admission_no ?? 'N/A' }}</div>
        <div><strong>Grand Total:</strong> {{ $grand_total }}</div>
        <div><strong>Overall Average:</strong> {{ $overall_average }}%</div>
        <div><strong>Position in Class:</strong> {{ $class_position }}</div>
        <div><strong>Class Population:</strong> {{ $total_students_in_class }} Students</div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Subject Name</th>
                    <th>1st Test (10)</th>
                    <th>2nd Test (10)</th>
                    <th>3rd Test (10)</th>
                    <th>Exam (70)</th>
                    <th>Total (100)</th>
                    <th>Grade</th>
                    <th>Remark</th>
                    <th>Class Avg</th>
                    <th>Highest</th>
                    <th>Lowest</th>
                    <th>Position</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subjects as $sub)
                <tr>
                    <td class="left"><strong>{{ $sub['subject_name'] }}</strong></td>
                    <td>{{ $sub['first_test'] ?? '-' }}</td>
                    <td>{{ $sub['second_test'] ?? '-' }}</td>
                    <td>{{ $sub['third_test'] ?? '-' }}</td>
                    <td>{{ $sub['exam_score'] ?? '-' }}</td>
                    <td><strong>{{ $sub['total_score'] }}</strong></td>
                    <td>{{ $sub['grade'] }}</td>
                    <td>{{ $sub['remark'] }}</td>
                    <td>{{ $sub['subject_average'] }}</td>
                    <td>{{ $sub['highest_score'] }}</td>
                    <td>{{ $sub['lowest_score'] }}</td>
                    <td>{{ $sub['subject_position'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="summary-box">
        <div>Overall Performance: {{ $overall_average >= 50 ? 'PASSED' : 'NEEDS IMPROVEMENT' }}</div>
        <div>Class Rank: {{ $class_position }} out of {{ $total_students_in_class }}</div>
    </div>

</body>
</html>
