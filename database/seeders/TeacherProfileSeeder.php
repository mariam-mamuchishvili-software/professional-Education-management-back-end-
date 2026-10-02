<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\TeacherEducation;
use App\Models\TeacherTraining;
use App\Models\TeacherWorkExperience;
use Illuminate\Database\Seeder;

class TeacherProfileSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = Teacher::query()
            ->orderBy('id')
            ->get();

        if ($teachers->isEmpty()) {
            $this->command?->warn(
                'No teachers found — skipping teacher profile seeding.'
            );

            return;
        }

        foreach ($teachers as $teacher) {
            TeacherWorkExperience::factory()
                ->count(2)
                ->for($teacher)
                ->create();

            TeacherEducation::factory()
                ->count(2)
                ->for($teacher)
                ->create();

            TeacherTraining::factory()
                ->count(2)
                ->for($teacher)
                ->create();
        }
    }
}