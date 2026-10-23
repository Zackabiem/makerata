<?php

namespace App\Http\Controllers;

use App\Models\FeeStructure;
use App\Models\FeeCategory;
use App\Models\Student;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeeStructureController extends Controller
{
    /**
     * Get fee structure schedule for a class/session/term.
     */
    public function index(Request $request)
    {
        $schoolId  = $request->query('school_id', 1);
        $classId   = $request->query('class_id');
        $sessionId = $request->query('session_id', 1);
        $termId    = $request->query('term_id', 1);

        $query = FeeStructure::with(['category', 'classroom'])
            ->where('school_id', $schoolId)
            ->where('session_id', $sessionId)
            ->where('term_id', $termId);

        if ($classId) {
            $query->where(function ($q) use ($classId) {
                $q->where('class_id', $classId)->orWhereNull('class_id');
            });
        }

        $structures = $query->get();

        return response()->json([
            'status' => 'success',
            'data'   => $structures,
        ]);
    }

    /**
     * Store single fee schedule entry.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id'       => 'required|integer',
            'class_id'        => 'nullable|integer|exists:classrooms,class_id',
            'session_id'      => 'required|integer',
            'term_id'         => 'required|integer',
            'fee_category_id' => 'required|integer|exists:fee_categories,fee_category_id',
            'amount'          => 'required|numeric|min:0',
            'description'     => 'nullable|string',
        ]);

        $structure = FeeStructure::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Fee structure item created.',
            'data'    => $structure->load('category'),
        ], 210);
    }

    /**
     * Batch save/configure fee schedule items for a session & term.
     */
    public function batchSave(Request $request)
    {
        $validated = $request->validate([
            'school_id'   => 'required|integer',
            'class_id'    => 'nullable|integer',
            'session_id'  => 'required|integer',
            'term_id'     => 'required|integer',
            'items'       => 'required|array',
            'items.*.fee_category_id' => 'required|integer|exists:fee_categories,fee_category_id',
            'items.*.amount'          => 'required|numeric|min:0',
            'items.*.description'     => 'nullable|string',
        ]);

        $schoolId  = $validated['school_id'];
        $classId   = $validated['class_id'] ?? null;
        $sessionId = $validated['session_id'];
        $termId    = $validated['term_id'];

        DB::beginTransaction();
        try {
            // Remove existing for this class/session/term combo
            $query = FeeStructure::where('school_id', $schoolId)
                ->where('session_id', $sessionId)
                ->where('term_id', $termId);

            if ($classId) {
                $query->where('class_id', $classId);
            } else {
                $query->whereNull('class_id');
            }
            $query->delete();

            $saved = [];
            foreach ($validated['items'] as $item) {
                $saved[] = FeeStructure::create([
                    'school_id'       => $schoolId,
                    'class_id'        => $classId,
                    'session_id'      => $sessionId,
                    'term_id'         => $termId,
                    'fee_category_id' => $item['fee_category_id'],
                    'amount'          => $item['amount'],
                    'description'     => $item['description'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Fee schedule updated successfully.',
                'data'    => $saved,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to save fee structure: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete fee structure item.
     */
    public function destroy($id)
    {
        $structure = FeeStructure::find($id);

        if (!$structure) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Fee structure item not found.',
            ], 404);
        }

        $structure->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Fee structure item deleted.',
        ]);
    }

    /**
     * Generate student bill statement & fee ledger calculation.
     */
    public function getStudentBill(Request $request, $studentId)
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
        $classId   = $student->class_id;

        // Fetch fee structures applicable to student's class or global
        $feeItems = FeeStructure::with('category')
            ->where('school_id', $schoolId)
            ->where('session_id', $sessionId)
            ->where('term_id', $termId)
            ->where(function ($q) use ($classId) {
                $q->where('class_id', $classId)->orWhereNull('class_id');
            })
            ->get();

        $totalBilled = $feeItems->sum('amount');

        // Total payments logged for this term
        $totalPaid = Payment::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->where('term_id', $termId)
            ->where('status', 'completed')
            ->sum('amount_paid');

        $balanceDue = max(0, $totalBilled - $totalPaid);
        $isCleared = ($balanceDue == 0 && $totalBilled > 0);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'student'       => $student,
                'session_id'    => $sessionId,
                'term_id'       => $termId,
                'fee_items'     => $feeItems,
                'total_billed'  => round($totalBilled, 2),
                'total_paid'    => round($totalPaid, 2),
                'balance_due'   => round($balanceDue, 2),
                'is_cleared'    => $isCleared,
            ],
        ]);
    }
}
