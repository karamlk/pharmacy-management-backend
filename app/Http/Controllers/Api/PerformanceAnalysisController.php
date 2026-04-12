<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PerformanceAnalysis\PerformanceAnalysisService;

class PerformanceAnalysisController extends Controller
{
    protected $performaceAnalysisService;

    public function __construct(PerformanceAnalysisService $performaceAnalysisService)
    {
        $this->performaceAnalysisService = $performaceAnalysisService;
    }

    public function __invoke()
    {
        $data = $this->performaceAnalysisService->getFullAnalysis();
        return response()->json($data);
    }
}
