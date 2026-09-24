<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Colleges\CollegeResource;
use App\Filament\Resources\Colleges\Pages\EditCollege;
use App\Filament\Resources\Colleges\Pages\ListColleges;
use App\Filament\Resources\Colleges\RelationManagers\ProfessionsRelationManager;
use App\Filament\Resources\Groups\Pages\ListGroups;
use App\Filament\Resources\Modules\Pages\ListModules;
use App\Filament\Resources\Professions\ProfessionResource;
use App\Filament\Resources\Slides\Pages\ListSlides;
use App\Models\College;
use App\Models\Group;
use App\Models\Module;
use App\Models\Profession;
use App\Models\Slide;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class RelationColumnsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_group_profession_column_links_to_the_profession_edit_page(): void
    {
        $group = Group::factory()->create();

        Livewire::test(ListGroups::class)
            ->assertTableColumnExists(
                'profession.name',
                fn (TextColumn $column): bool => $column->getUrl() === ProfessionResource::getUrl('edit', ['record' => $group->profession_id]),
                $group,
            );
    }

    public function test_slide_college_column_links_to_the_college_edit_page(): void
    {
        $slide = Slide::factory()->create();

        Livewire::test(ListSlides::class)
            ->assertTableColumnExists(
                'college.name',
                fn (TextColumn $column): bool => $column->getUrl() === CollegeResource::getUrl('edit', ['record' => $slide->college_id]),
                $slide,
            );
    }

    public function test_group_students_count_tooltip_lists_student_names(): void
    {
        $group = Group::factory()->create();
        $group->students()->attach([
            Student::factory()->create(['first_name' => 'Ana', 'last_name' => 'Beridze'])->id,
            Student::factory()->create(['first_name' => 'Giorgi', 'last_name' => 'Kapanadze'])->id,
        ]);

        Livewire::test(ListGroups::class)
            ->assertTableColumnStateSet('students_count', 2, $group)
            ->assertTableColumnExists(
                'students_count',
                fn (TextColumn $column): bool => $column->getTooltip() === 'Ana Beridze, Giorgi Kapanadze',
                $group,
            );
    }

    public function test_groups_list_does_not_run_extra_queries_per_record(): void
    {
        $this->seedGroups(2);
        $queriesForTwoGroups = $this->countQueriesWhileRendering(ListGroups::class);

        $this->seedGroups(5);
        $queriesForSevenGroups = $this->countQueriesWhileRendering(ListGroups::class);

        $this->assertSame($queriesForTwoGroups, $queriesForSevenGroups);
    }

    public function test_colleges_list_does_not_run_extra_queries_per_record(): void
    {
        $this->seedColleges(2);
        $queriesForTwoColleges = $this->countQueriesWhileRendering(ListColleges::class);

        $this->seedColleges(5);
        $queriesForSevenColleges = $this->countQueriesWhileRendering(ListColleges::class);

        $this->assertSame($queriesForTwoColleges, $queriesForSevenColleges);
    }

    public function test_modules_list_does_not_run_extra_queries_per_record(): void
    {
        $this->seedModules(2);
        $queriesForTwoModules = $this->countQueriesWhileRendering(ListModules::class);

        $this->seedModules(5);
        $queriesForSevenModules = $this->countQueriesWhileRendering(ListModules::class);

        $this->assertSame($queriesForTwoModules, $queriesForSevenModules);
    }

    public function test_college_professions_relation_manager_can_attach_and_detach_professions(): void
    {
        $college = College::factory()->create();
        $profession = Profession::factory()->create();

        $relationManager = Livewire::test(ProfessionsRelationManager::class, [
            'ownerRecord' => $college,
            'pageClass' => EditCollege::class,
        ]);

        $relationManager->callTableAction(AttachAction::class, data: ['recordId' => $profession->id])
            ->assertHasNoTableActionErrors()
            ->assertCanSeeTableRecords([$profession]);

        $relationManager->callTableAction(DetachAction::class, $profession);

        $this->assertFalse($college->professions()->whereKey($profession->id)->exists());
    }

    private function seedGroups(int $count): void
    {
        Group::factory()->count($count)->create()
            ->each(fn (Group $group) => $group->students()->attach(Student::factory()->count(2)->create()));
    }

    private function seedColleges(int $count): void
    {
        College::factory()->count($count)->create()->each(function (College $college): void {
            $college->teachers()->attach(Teacher::factory()->count(2)->create());
            $college->students()->attach(Student::factory()->count(2)->create());
            $college->professions()->attach(Profession::factory()->count(3)->create());
        });
    }

    private function seedModules(int $count): void
    {
        Module::factory()->count($count)->create()->each(function (Module $module): void {
            $module->teachers()->attach(Teacher::factory()->count(2)->create());
            $module->students()->attach(Student::factory()->count(2)->create());
            $module->professions()->attach(Profession::factory()->count(3)->create());
        });
    }

    /**
     * @param  class-string  $page
     */
    private function countQueriesWhileRendering(string $page): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        Livewire::test($page);

        $count = count(DB::getQueryLog());

        DB::disableQueryLog();

        return $count;
    }
}
