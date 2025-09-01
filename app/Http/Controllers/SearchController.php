<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->input('q'));

        if (!$q) {
            return view('search.results', [
                'q' => $q,
                'users' => collect(),
                'products' => collect(),
            ]);
        }

        return view('search.results', compact('q'));
    }
}
