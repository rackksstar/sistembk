<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Admin\InterestCategoryController as AdminInterestCategoryController;

class InterestCategoryController extends AdminInterestCategoryController
{
    protected string $routePrefix = 'guru.';
}
