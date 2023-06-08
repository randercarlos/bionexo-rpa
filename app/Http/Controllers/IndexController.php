<?php

namespace App\Http\Controllers;

use App\Services\RpaService;
use Illuminate\Http\Response;

class IndexController extends Controller
{
    public function __construct(private RpaService $rpaService) {
    }

    public function __invoke(): Response {
        return $this->rpaService->executeTasks();
    }
}
