<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IncidentType;

class IncidentTypeController extends Controller
{
    public function index()
    {
        $types = IncidentType::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data'    => $types,
        ]);
    }
}
