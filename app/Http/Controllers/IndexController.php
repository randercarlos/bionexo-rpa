<?php

namespace App\Http\Controllers;

use App\Services\RpaService;

class IndexController extends Controller
{
    public function __construct(private RpaService $rpaService) {}

    public function __invoke() {
        $this->rpaService->readRpaData();
    }
}
