<?php

namespace App\Http\Controllers;

use App\Models\FeeCategory;
use Illuminate\Http\Request;

class FeeCategoryController extends Controller
{
    /**
     * List all fee categories for a school.
     */
    public function index(Request $request)
    {
        $schoolId = $request->query('school_id', 1);

        $categories = FeeCategory::where('school_id', $schoolId)
            ->orderBy('name', 'asc')
            ->get();

        if ($categories->isEmpty()) {
            // Seed default fee categories if none exist
            $defaults = [
                ['name' => 'Tuition Fee', 'description' => 'Main academic tuition fee', 'is_compulsory' => true],
                ['name' => 'Development Levy', 'description' => 'School facility improvement levy', 'is_compulsory' => true],
                ['name' => 'PTA Levy', 'description' => 'Parent Teacher Association contribution', 'is_compulsory' => true],
                ['name' => 'ICT & Exam Fee', 'description' => 'Computer lab and exam materials fee', 'is_compulsory' => true],
                ['name' => 'School Uniform', 'description' => 'Standard clothing & sports wear kit', 'is_compulsory' => false],
                ['name' => 'Bus Transportation', 'description' => 'Daily school bus transport service', 'is_compulsory' => false],
            ];

            foreach ($defaults as $def) {
                FeeCategory::create(array_merge($def, ['school_id' => $schoolId]));
            }

            $categories = FeeCategory::where('school_id', $schoolId)
                ->orderBy('name', 'asc')
                ->get();
        }

        return response()->json([
            'status' => 'success',
            'data'   => $categories,
        ]);
    }

    /**
     * Store a new fee category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id'     => 'required|integer',
            'name'          => 'required|string|max:100',
            'description'   => 'nullable|string',
            'is_compulsory' => 'boolean',
        ]);

        $category = FeeCategory::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Fee category created successfully.',
            'data'    => $category,
        ], 210);
    }

    /**
     * Display a specific category.
     */
    public function show($id)
    {
        $category = FeeCategory::find($id);

        if (!$category) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Fee category not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $category,
        ]);
    }

    /**
     * Update category.
     */
    public function update(Request $request, $id)
    {
        $category = FeeCategory::find($id);

        if (!$category) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Fee category not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name'          => 'sometimes|required|string|max:100',
            'description'   => 'nullable|string',
            'is_compulsory' => 'boolean',
        ]);

        $category->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Fee category updated successfully.',
            'data'    => $category,
        ]);
    }

    /**
     * Delete category.
     */
    public function destroy($id)
    {
        $category = FeeCategory::find($id);

        if (!$category) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Fee category not found.',
            ], 404);
        }

        $category->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Fee category deleted successfully.',
        ]);
    }
}
