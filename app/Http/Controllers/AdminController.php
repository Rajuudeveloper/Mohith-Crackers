<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }

     public function users(Request $request): View
    {
        $search = $request->get('search');
        
        $users = User::when($search, function($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search'));
    }

    public function settings()
    {
        return view('admin.settings');
    }
}