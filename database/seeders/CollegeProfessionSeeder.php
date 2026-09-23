<?php

namespace Database\Seeders;

use App\Models\College;
use App\Models\Profession;
use Illuminate\Database\Seeder;

class CollegeProfessionSeeder extends Seeder
{
    /**
     * How many professions each college is linked to.
     */
    private const PROFESSIONS_PER_COLLEGE = 3;

    /**
     * Link existing colleges and professions through the college_profession pivot.
     *
     * Professions are assigned cyclically so every college and every profession ends up
     * with related data. syncWithoutDetaching() keeps it safe to re-run on a database
     * that already has links (no duplicate-key errors, existing links are preserved).
     */
    public function run(): void
    {
        $colleges = College::query()->orderBy('id')->get();
        $professionIds = Profession::query()->orderBy('id')->pluck('id')->values();

        if ($colleges->isEmpty() || $professionIds->isEmpty()) {
            $this->command?->warn('No colleges or professions found — skipping college_profession seeding.');

            return;
        }

        $perCollege = min(self::PROFESSIONS_PER_COLLEGE, $professionIds->count());

        $colleges->values()->each(function (College $college, int $index) use ($professionIds, $perCollege) {
            $ids = collect(range(0, $perCollege - 1))
                ->map(fn (int $offset) => $professionIds->get(($index + $offset) % $professionIds->count()));

            $college->professions()->syncWithoutDetaching($ids->all());
        });
    }
}
