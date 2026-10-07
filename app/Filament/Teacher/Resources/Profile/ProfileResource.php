<?php

namespace App\Filament\Teacher\Resources\Profile;

use App\Filament\Resources\Teachers\RelationManagers\EducationsRelationManager;
use App\Filament\Resources\Teachers\RelationManagers\TrainingsRelationManager;
use App\Filament\Resources\Teachers\RelationManagers\WorkExperiencesRelationManager;
use App\Filament\Teacher\Resources\Profile\Pages\EditProfile;
use App\Filament\Teacher\Resources\Profile\Schemas\ProfileForm;
use App\Models\Teacher;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

/**
 * The signed-in teacher's own profile, together with their work experience, education
 * and trainings. Every query is limited to the signed-in teacher, so no other teacher's
 * record can be reached; the admin-only TeacherPolicy is therefore not consulted.
 */
class ProfileResource extends Resource
{
    protected static ?string $model = Teacher::class;

    protected static ?string $slug = 'profile';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static ?string $navigationLabel = 'My profile';

    protected static ?string $modelLabel = 'profile';

    protected static ?int $navigationSort = -1;

    protected static bool $shouldSkipAuthorization = true;

    public static function form(Schema $schema): Schema
    {
        return ProfileForm::configure($schema);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereKey(Filament::auth()->id());
    }

    public static function getRelations(): array
    {
        return [
            WorkExperiencesRelationManager::class,
            EducationsRelationManager::class,
            TrainingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => EditProfile::route('/'),
        ];
    }
}
