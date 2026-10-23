<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations to create database views for class broadsheets and score sheets.
     */
    public function up(): void
    {
        // 1. Create SQL View for Subject Score Sheet
        DB::statement("
            CREATE OR REPLACE VIEW vw_subject_score_sheet AS
            SELECT 
                tss.id AS score_id,
                tss.school_id,
                tss.class_id,
                tss.subject_id,
                tss.session_id,
                tss.term_id,
                s.student_id,
                s.admission_no,
                s.full_name AS student_name,
                tss.first_test,
                tss.second_test,
                tss.third_test,
                tss.exam_score,
                tss.total_score,
                tss.grade,
                tss.remark,
                tss.subject_rank
            FROM terminal_score_sheet tss
            JOIN students s ON tss.student_id = s.student_id
            WHERE s.is_active = 1;
        ");

        // 2. Create SQL View for Class Terminal Averages & Totals
        DB::statement("
            CREATE OR REPLACE VIEW vw_student_term_averages AS
            SELECT 
                tss.school_id,
                tss.class_id,
                tss.session_id,
                tss.term_id,
                tss.student_id,
                s.admission_no,
                s.full_name AS student_name,
                COUNT(tss.subject_id) AS total_subjects,
                SUM(tss.total_score) AS grand_total_score,
                AVG(tss.total_score) AS average_score
            FROM terminal_score_sheet tss
            JOIN students s ON tss.student_id = s.student_id
            WHERE s.is_active = 1
            GROUP BY tss.school_id, tss.class_id, tss.session_id, tss.term_id, tss.student_id, s.admission_no, s.full_name;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS vw_student_term_averages;");
        DB::statement("DROP VIEW IF EXISTS vw_subject_score_sheet;");
    }
};
