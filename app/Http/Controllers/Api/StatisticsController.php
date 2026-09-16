<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\Group;
use App\Models\Module;
use App\Models\Profession;
use App\Models\Student;
use App\Models\Teacher;

class StatisticsController extends Controller
{
    /**
     * Return platform-wide entity counts for the statistics dashboard.
     */
    public function __invoke()
    {
        return response()->json([
            'data' => [
                'colleges' => College::count(),
                'professions' => Profession::count(),
                'modules' => Module::count(),
                'groups' => Group::count(),
                'teachers' => Teacher::count(),
                'students' => Student::count(),
            ],
        ]);
    }
}
