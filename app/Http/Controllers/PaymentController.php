<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Student;
use App\Models\FeeStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * List payment transactions ledger.
     */
    public function index(Request $request)
    {
        $schoolId  = $request->query('school_id', 1);
        $studentId = $request->query('student_id');
        $sessionId = $request->query('session_id');
        $termId    = $request->query('term_id');
        $method    = $request->query('payment_method');
        $search    = $request->query('search');

        $query = Payment::with(['student.classroom', 'recorder'])
            ->where('school_id', $schoolId);

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        if ($sessionId) {
            $query->where('session_id', $sessionId);
        }

        if ($termId) {
            $query->where('term_id', $termId);
        }

        if ($method) {
            $query->where('payment_method', $method);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                  ->orWhere('receipt_no', 'like', "%{$search}%")
                  ->orWhereHas('student', function($sq) use ($search) {
                      $sq->where('surname', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('admission_no', 'like', "%{$search}%");
                  });
            });
        }

        $payments = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data'   => $payments,
        ]);
    }

    /**
     * Log a new payment transaction with transaction reference & receipt number generation.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id'            => 'required|integer',
            'student_id'           => 'required|integer|exists:students,student_id',
            'session_id'           => 'required|integer',
            'term_id'              => 'required|integer',
            'amount_paid'          => 'required|numeric|min:0.01',
            'payment_method'       => 'required|string|in:Cash,Bank Transfer,POS,Online,Cheque',
            'payment_date'         => 'required|date',
            'channel_details'      => 'nullable|string',
            'recorded_by_staff_id' => 'nullable|integer',
            'remarks'              => 'nullable|string',
        ]);

        // Unique Reference Number & Sequential Receipt Number
        $refNo = 'PAY-' . strtoupper(Str::random(10));

        $latestReceiptCount = Payment::where('school_id', $validated['school_id'])->count() + 1;
        $receiptNo = 'RCT-' . str_pad($latestReceiptCount, 5, '0', STR_PAD_LEFT);

        $validated['reference_no'] = $refNo;
        $validated['receipt_no']   = $receiptNo;
        $validated['status']       = 'completed';

        $payment = Payment::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Payment recorded successfully.',
            'data'    => $payment->load(['student', 'recorder']),
        ], 210);
    }

    /**
     * Show single payment details.
     */
    public function show($id)
    {
        $payment = Payment::with(['student.classroom', 'recorder', 'school'])->find($id);

        if (!$payment) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Payment transaction record not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $payment,
        ]);
    }

    /**
     * Update payment transaction (e.g. status or notes).
     */
    public function update(Request $request, $id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Payment transaction record not found.',
            ], 404);
        }

        $validated = $request->validate([
            'amount_paid'     => 'sometimes|required|numeric|min:0',
            'payment_method'  => 'sometimes|required|string',
            'payment_date'    => 'sometimes|required|date',
            'channel_details' => 'nullable|string',
            'status'          => 'sometimes|required|string|in:completed,pending,reversed',
            'remarks'         => 'nullable|string',
        ]);

        $payment->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Payment record updated successfully.',
            'data'    => $payment->fresh(['student', 'recorder']),
        ]);
    }

    /**
     * Reverse / void payment.
     */
    public function destroy($id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Payment transaction record not found.',
            ], 404);
        }

        $payment->status = 'reversed';
        $payment->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Payment transaction status changed to reversed.',
            'data'    => $payment,
        ]);
    }

    /**
     * Get comprehensive student payment ledger & history.
     */
    public function getStudentLedger(Request $request, $studentId)
    {
        $student = Student::with('classroom')->find($studentId);

        if (!$student) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Student record not found.',
            ], 404);
        }

        $schoolId  = $request->query('school_id', $student->school_id ?? 1);
        $sessionId = $request->query('session_id', 1);
        $termId    = $request->query('term_id', 1);

        $payments = Payment::where('student_id', $studentId)
            ->where('school_id', $schoolId)
            ->where('session_id', $sessionId)
            ->where('term_id', $termId)
            ->orderBy('payment_date', 'asc')
            ->get();

        $feeItems = FeeStructure::with('category')
            ->where('school_id', $schoolId)
            ->where('session_id', $sessionId)
            ->where('term_id', $termId)
            ->where(function ($q) use ($student) {
                $q->where('class_id', $student->class_id)->orWhereNull('class_id');
            })
            ->get();

        $totalBilled = $feeItems->sum('amount');
        $totalPaid   = $payments->where('status', 'completed')->sum('amount_paid');
        $balance     = max(0, $totalBilled - $totalPaid);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'student'      => $student,
                'session_id'   => $sessionId,
                'term_id'      => $termId,
                'fee_items'    => $feeItems,
                'payments'     => $payments,
                'total_billed' => round($totalBilled, 2),
                'total_paid'   => round($totalPaid, 2),
                'balance_due'  => round($balance, 2),
                'is_cleared'   => ($balance == 0 && $totalBilled > 0),
            ],
        ]);
    }
}
