<?php

namespace Database\Seeders;

use App\Models\College;
use App\Models\Student;
use Illuminate\Database\Seeder;

class CollegeStudentSeeder extends Seeder
{
    /**
     * How many colleges each student is linked to.
     */
    private const COLLEGES_PER_STUDENT = 1;

    /**
     * Link existing students and colleges through the college_student pivot.
     *
     * Colleges are assigned cyclically so every student and every college ends up
     * with related data. syncWithoutDetaching() keeps it safe to re-run on a database
     * that already has links (no duplicate-key errors, existing links are preserved).
     */
    public function run(): void
    {
        $students = Student::query()->orderBy('id')->get();
        $collegeIds = College::query()->orderBy('id')->pluck('id')->values();

        if ($students->isEmpty() || $collegeIds->isEmpty()) {
            $this->command?->warn('No students or colleges found — skipping college_student seeding.');

            return;
        }

        $perStudent = min(self::COLLEGES_PER_STUDENT, $collegeIds->count());

        $students->values()->each(function (Student $student, int $index) use ($collegeIds, $perStudent) {
            $ids = collect(range(0, $perStudent - 1))
                ->map(fn (int $offset) => $collegeIds->get(($index + $offset) % $collegeIds->count()));

            $student->colleges()->syncWithoutDetaching($ids->all());
        });
    }
}
