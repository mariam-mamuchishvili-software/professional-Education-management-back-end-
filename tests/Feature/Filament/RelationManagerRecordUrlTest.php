<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Colleges\CollegeResource;
use App\Filament\Resources\Colleges\Pages\EditCollege;
use App\Filament\Resources\Colleges\RelationManagers as CollegeRelationManagers;
use App\Filament\Resources\Groups\GroupResource;
use App\Filament\Resources\Groups\Pages\EditGroup;
use App\Filament\Resources\Groups\RelationManagers as GroupRelationManagers;
use App\Filament\Resources\Modules\ModuleResource;
use App\Filament\Resources\Modules\Pages\EditModule;
use App\Filament\Resources\Modules\RelationManagers as ModuleRelationManagers;
use App\Filament\Resources\Professions\Pages\EditProfession;
use App\Filament\Resources\Professions\ProfessionResource;
use App\Filament\Resources\Professions\RelationManagers as ProfessionRelationManagers;
use App\Filament\Resources\Slides\SlideResource;
use App\Filament\Resources\Students\Pages\EditStudent;
use App\Filament\Resources\Students\RelationManagers as StudentRelationManagers;
use App\Filament\Resources\Students\StudentResource;
use App\Filament\Resources\Teachers\Pages\EditTeacher;
use App\Filament\Resources\Teachers\RelationManagers as TeacherRelationManagers;
use App\Filament\Resources\Teachers\TeacherResource;
use App\Models\College;
use App\Models\Group;
use App\Models\Module;
use App\Models\Profession;
use App\Models\Slide;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RelationManagerRecordUrlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    /**
     * @return array<string, array{class-string, class-string, class-string, class-string, class-string, string}>
     */
    public static function relationManagers(): array
    {
        return [
            'college professions' => [CollegeRelationManagers\ProfessionsRelationManager::class, EditCollege::class, College::class, Profession::class, ProfessionResource::class, 'name'],
            'college slides' => [CollegeRelationManagers\SlidesRelationManager::class, EditCollege::class, College::class, Slide::class, SlideResource::class, 'title'],
            'college students' => [CollegeRelationManagers\StudentsRelationManager::class, EditCollege::class, College::class, Student::class, StudentResource::class, 'first_name'],
            'college teachers' => [CollegeRelationManagers\TeachersRelationManager::class, EditCollege::class, College::class, Teacher::class, TeacherResource::class, 'first_name'],
            'group students' => [GroupRelationManagers\StudentsRelationManager::class, EditGroup::class, Group::class, Student::class, StudentResource::class, 'first_name'],
            'module professions' => [ModuleRelationManagers\ProfessionsRelationManager::class, EditModule::class, Module::class, Profession::class, ProfessionResource::class, 'name'],
            'module students' => [ModuleRelationManagers\StudentsRelationManager::class, EditModule::class, Module::class, Student::class, StudentResource::class, 'first_name'],
            'module teachers' => [ModuleRelationManagers\TeachersRelationManager::class, EditModule::class, Module::class, Teacher::class, TeacherResource::class, 'first_name'],
            'profession colleges' => [ProfessionRelationManagers\CollegesRelationManager::class, EditProfession::class, Profession::class, College::class, CollegeResource::class, 'name'],
            'profession groups' => [ProfessionRelationManagers\GroupsRelationManager::class, EditProfession::class, Profession::class, Group::class, GroupResource::class, 'name'],
            'profession modules' => [ProfessionRelationManagers\ModulesRelationManager::class, EditProfession::class, Profession::class, Module::class, ModuleResource::class, 'name'],
            'student colleges' => [StudentRelationManagers\CollegesRelationManager::class, EditStudent::class, Student::class, College::class, CollegeResource::class, 'name'],
            'student groups' => [StudentRelationManagers\GroupsRelationManager::class, EditStudent::class, Student::class, Group::class, GroupResource::class, 'name'],
            'student modules' => [StudentRelationManagers\ModulesRelationManager::class, EditStudent::class, Student::class, Module::class, ModuleResource::class, 'name'],
            'teacher colleges' => [TeacherRelationManagers\CollegesRelationManager::class, EditTeacher::class, Teacher::class, College::class, CollegeResource::class, 'name'],
            'teacher modules' => [TeacherRelationManagers\ModulesRelationManager::class, EditTeacher::class, Teacher::class, Module::class, ModuleResource::class, 'name'],
        ];
    }

    #[DataProvider('relationManagers')]
    public function test_related_records_link_to_their_resource_edit_page(
        string $relationManager,
        string $pageClass,
        string $ownerModel,
        string $relatedModel,
        string $relatedResource,
        string $titleColumn,
    ): void {
        $owner = $ownerModel::factory()->create();
        $related = $owner->{$relationManager::getRelationshipName()}()->save($relatedModel::factory()->make());

        $expectedUrl = $relatedResource::getUrl('edit', ['record' => $related]);

        $component = Livewire::test($relationManager, [
            'ownerRecord' => $owner,
            'pageClass' => $pageClass,
        ]);

        $this->assertSame($expectedUrl, $component->instance()->getTable()->getRecordUrl($related));

        $component->assertTableColumnExists(
            $titleColumn,
            fn (TextColumn $column): bool => $column->getUrl() === $expectedUrl,
            $related,
        );
    }
}
