<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Admin\CareerFieldController as AdminCareerFieldController;

class CareerFieldController extends AdminCareerFieldController
{
    protected string $routePrefix = 'guru.';
}
