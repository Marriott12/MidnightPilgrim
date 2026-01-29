<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AdjacencyEngine;

class AdjacencyController extends Controller
{
    public function index(AdjacencyEngine $engine)
    {
        $groups = $engine->run(2);
        return view('adjacency.index', ['groups' => $groups]);
    }
}
