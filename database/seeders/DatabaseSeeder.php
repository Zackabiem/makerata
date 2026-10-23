<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\SchoolProfile;
use App\Models\User;
use App\Models\Staff;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\GradingScale;
use App\Models\FeeCategory;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with default administrative, academic, and financial structures.
     */
    public function run(): void
    {
        // 1. Seed School Profile
        $school = SchoolProfile::updateOrCreate(
            ['school_code' => 'EDUPRO-001'],
            [
                'school_name'              => 'EduPro Model Academy',
                'plan'                     => 'Enterprise',
                'plan_locked'              => false,
                'onboarding_completed'     => true,
                'motto'                    => 'Excellence, Character and Innovation',
                'school_address'           => '12 Education Avenue, Victoria Island',
                'location'                 => 'Lagos, Nigeria',
                'email_address'            => 'info@eduproacademy.edu.ng',
                'phone_number'             => '+234 803 000 1122',
                'headteacher_name'         => 'Dr. Margaret Adeleke',
                'promotion_cutoff_average' => 50.00,
            ]
        );

        $schoolId = $school->id ?? 1;

        // 2. Seed Admin & Principal Users
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@edupro.com'],
            [
                'school_id' => $schoolId,
                'name'      => 'Super Administrator',
                'password'  => Hash::make('Password123!'),
                'role'      => 'admin',
                'phone'     => '+2348030001122',
                'is_active' => true,
            ]
        );

        Staff::updateOrCreate(
            ['email' => 'admin@edupro.com'],
            [
                'school_id'  => $schoolId,
                'user_id'    => $adminUser->user_id ?? 1,
                'surname'    => 'Administrator',
                'first_name' => 'Super',
                'phone'      => '+2348030001122',
                'address'    => 'School Administrative Block',
                'role'       => 'Administrator',
                'status'     => 'active',
            ]
        );

        $principalUser = User::updateOrCreate(
            ['email' => 'principal@edupro.com'],
            [
                'school_id' => $schoolId,
                'name'      => 'Dr. Margaret Adeleke',
                'password'  => Hash::make('Password123!'),
                'role'      => 'principal',
                'phone'     => '+2348021112233',
                'is_active' => true,
            ]
        );

        $principalStaff = Staff::updateOrCreate(
            ['email' => 'principal@edupro.com'],
            [
                'school_id'  => $schoolId,
                'user_id'    => $principalUser->user_id ?? 2,
                'surname'    => 'Adeleke',
                'first_name' => 'Margaret',
                'phone'      => '+2348021112233',
                'address'    => 'Principal Quarters, EduPro Campus',
                'role'       => 'Principal',
                'status'     => 'active',
            ]
        );

        // Seed Form Masters / Teachers
        $teacher1 = Staff::updateOrCreate(
            ['email' => 'o.okonkwo@edupro.com'],
            [
                'school_id'  => $schoolId,
                'surname'    => 'Okonkwo',
                'first_name' => 'Obinna',
                'phone'      => '+2348055554433',
                'address'    => 'Block 4 Staff Quarters',
                'role'       => 'Teacher',
                'status'     => 'active',
            ]
        );

        $teacher2 = Staff::updateOrCreate(
            ['email' => 'f.bello@edupro.com'],
            [
                'school_id'  => $schoolId,
                'surname'    => 'Bello',
                'first_name' => 'Fatima',
                'phone'      => '+2348066667788',
                'address'    => 'Block 2 Staff Quarters',
                'role'       => 'Teacher',
                'status'     => 'active',
            ]
        );

        // 3. Seed Class Arms
        $classes = [
            ['name' => 'JSS 1 Gold', 'level' => 'Junior Secondary 1', 'arm' => 'Gold', 'form_master_id' => $teacher1->staff_id],
            ['name' => 'JSS 1 Silver', 'level' => 'Junior Secondary 1', 'arm' => 'Silver', 'form_master_id' => $teacher2->staff_id],
            ['name' => 'JSS 2 Gold', 'level' => 'Junior Secondary 2', 'arm' => 'Gold', 'form_master_id' => $teacher1->staff_id],
            ['name' => 'JSS 3 Gold', 'level' => 'Junior Secondary 3', 'arm' => 'Gold', 'form_master_id' => $teacher2->staff_id],
            ['name' => 'SSS 1 Science', 'level' => 'Senior Secondary 1', 'arm' => 'Science', 'form_master_id' => $principalStaff->staff_id],
            ['name' => 'SSS 2 Arts', 'level' => 'Senior Secondary 2', 'arm' => 'Arts', 'form_master_id' => $teacher1->staff_id],
        ];

        foreach ($classes as $cls) {
            Classroom::updateOrCreate(
                ['school_id' => $schoolId, 'name' => $cls['name']],
                array_merge($cls, ['school_id' => $schoolId])
            );
        }

        // 4. Seed Core Subjects
        $subjects = [
            ['name' => 'Mathematics', 'code' => 'MTH', 'category' => 'Core General', 'description' => 'Algebra, Geometry, Arithmetic and Trigonometry'],
            ['name' => 'English Language', 'code' => 'ENG', 'category' => 'Core General', 'description' => 'Grammar, Comprehension, Essay and Oral Speech'],
            ['name' => 'Basic Science', 'code' => 'BSC', 'category' => 'Junior Science', 'description' => 'Foundational physical and biological sciences'],
            ['name' => 'Civic Education', 'code' => 'CVE', 'category' => 'Core General', 'description' => 'Citizenship, Governance and Rights'],
            ['name' => 'Physics', 'code' => 'PHY', 'category' => 'Senior Science', 'description' => 'Mechanics, Optics, Electricity and Modern Physics'],
            ['name' => 'Chemistry', 'code' => 'CHM', 'category' => 'Senior Science', 'description' => 'Organic, Inorganic and Physical Chemistry'],
            ['name' => 'Biology', 'code' => 'BIO', 'category' => 'Senior Science', 'description' => 'Living Organisms, Physiology and Ecology'],
            ['name' => 'Economics', 'code' => 'ECO', 'category' => 'Social Science', 'description' => 'Microeconomics, Macroeconomics and Trade'],
            ['name' => 'Computer Studies / ICT', 'code' => 'ICT', 'category' => 'Technology', 'description' => 'Computer Hardware, Software and Internet Literacy'],
            ['name' => 'Agricultural Science', 'code' => 'AGR', 'category' => 'Vocational', 'description' => 'Crop production, Animal husbandry and Economics'],
        ];

        foreach ($subjects as $sub) {
            Subject::updateOrCreate(
                ['school_id' => $schoolId, 'code' => $sub['code']],
                array_merge($sub, ['school_id' => $schoolId])
            );
        }

        // 5. Seed Standard Grading Scale Thresholds
        $scales = [
            ['grade' => 'A', 'min_score' => 75, 'max_score' => 100, 'remark' => 'Excellent'],
            ['grade' => 'B', 'min_score' => 65, 'max_score' => 74,  'remark' => 'Very Good'],
            ['grade' => 'C', 'min_score' => 55, 'max_score' => 64,  'remark' => 'Good'],
            ['grade' => 'D', 'min_score' => 45, 'max_score' => 54,  'remark' => 'Fair'],
            ['grade' => 'E', 'min_score' => 40, 'max_score' => 44,  'remark' => 'Pass'],
            ['grade' => 'F', 'min_score' => 0,  'max_score' => 39,  'remark' => 'Fail'],
        ];

        foreach ($scales as $sc) {
            GradingScale::updateOrCreate(
                ['school_id' => $schoolId, 'grade' => $sc['grade']],
                array_merge($sc, ['school_id' => $schoolId])
            );
        }

        // 6. Seed Fee Categories
        $feeCategories = [
            ['name' => 'Tuition Fee', 'description' => 'Main academic tuition fee per term', 'is_compulsory' => true],
            ['name' => 'Development Levy', 'description' => 'Infrastructure and facility maintenance', 'is_compulsory' => true],
            ['name' => 'PTA Levy', 'description' => 'Parent Teacher Association contribution', 'is_compulsory' => true],
            ['name' => 'ICT & Exam Fee', 'description' => 'Computer lab access and termly examination materials', 'is_compulsory' => true],
            ['name' => 'School Uniform Kit', 'description' => 'Standard uniform, sportswear and crest', 'is_compulsory' => false],
            ['name' => 'Bus Transportation', 'description' => 'Daily shuttle service for day students', 'is_compulsory' => false],
            ['name' => 'Boarding / Hostel Fee', 'description' => 'Hostel accommodation and feeding for boarders', 'is_compulsory' => false],
        ];

        foreach ($feeCategories as $fc) {
            FeeCategory::updateOrCreate(
                ['school_id' => $schoolId, 'name' => $fc['name']],
                array_merge($fc, ['school_id' => $schoolId])
            );
        }
    }
}
