<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;

class CompanyController extends Controller
{
    /**
     * Get companies by location
     */
public function getByLocation(string $location)
    {
        $companies = Company::where('location', $location)
            ->select('id', 'name', 'location')
            ->orderBy('name')
            ->get();

        return response()->json($companies);
    }

    /**
     * Get all companies
     */
    public function index()
    {
        $companies = Company::select('id', 'name', 'location')
            ->orderBy('name')
            ->get();

        return response()->json($companies);
    }
}