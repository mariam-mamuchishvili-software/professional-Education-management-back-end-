<?php

namespace Database\Seeders;

use App\Models\College;
use App\Models\Group;
use App\Models\Module;
use App\Models\Profession;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        College::factory(5)->create();
        Teacher::factory(5)->create();
        Profession::factory(5)->create();
        Module::factory(5)->create();
        Group::factory(5)->create();
        Student::factory(5)->create();
    }
}