<?php

namespace Database\Seeders;

use App\Models\College;
use App\Models\Group;
use App\Models\Module;
use App\Models\Profession;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $colleges = College::factory(5)->create();
        $teachers = Teacher::factory(5)->create();
        $professions = Profession::factory(5)->create();
        $modules = Module::factory(5)->create();
        $students = Student::factory(5)->create();

        $groups = $professions->map(
            fn (Profession $profession) => Group::factory()->create(['profession_id' => $profession->id])
        );

        $this->attachCyclically($teachers, $colleges, 'colleges');
        $this->attachCyclically($teachers, $modules, 'modules');
        $this->attachCyclically($modules, $professions, 'professions');
        $this->attachCyclically($students, $groups, 'groups');
        $this->attachCyclically($students, $modules, 'modules');
    }

    /**
     * Attach each model in $from to two models in $to, cyclically, so that every
     * record on both sides of the many-to-many relation ends up with related data
     * instead of leaving some records unlinked by chance.
     *
     * @param  Collection<int, Model>  $from
     * @param  Collection<int, Model>  $to
     */
    private function attachCyclically(Collection $from, Collection $to, string $relation): void
    {
        $to = $to->values();
        $count = $to->count();

        $from->each(function (Model $model, int $index) use ($to, $count, $relation) {
            $first = $to->get($index % $count);
            $second = $to->get(($index + 1) % $count);

            $model->{$relation}()->attach(collect([$first, $second])->pluck('id')->unique());
        });
    }
}
