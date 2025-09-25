<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;

class ModuleController extends Controller
{
    public function index()
    {
        return response()->json(Module::all());
    }

    public function getRoles(Module $module)
    {
        return response()->json($module->roles);
    }

    public function getPermissions(Module $module)
    {
        return response()->json([]);
    }
}