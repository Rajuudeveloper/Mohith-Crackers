<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgentController extends Controller
{
    // ✅ List Agents
    public function index()
    {
        $agents = DB::table('agents')->orderBy('id', 'DESC')->get();
        return view('agents.index', compact('agents'));
    }

    // ✅ Show Create Form
    public function create()
    {
        return view('agents.create');
    }

    // ✅ Store Agent
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'mobile_no' => 'required',
            'email' => 'nullable|email',
            'address' => 'nullable',
            'opening_balance' => 'required|numeric',
        ]);

        DB::table('agents')->insert([
            'name' => $request->name,
            'mobile_no' => $request->mobile_no,
            'email' => $request->email,
            'address' => $request->address,
            'opening_balance' => $request->opening_balance,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('agents.index')
            ->with('success', 'Agent created successfully');
    }

    // ✅ Show Edit Form
    public function edit($id)
    {
        $agent = DB::table('agents')->where('id', $id)->first();
        return view('agents.edit', compact('agent'));
    }

    // ✅ Update Agent
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'mobile_no' => 'required',
            'email' => 'nullable|email',
            'address' => 'nullable',
            'opening_balance' => 'required|numeric',
        ]);

        DB::table('agents')->where('id', $id)->update([
            'name' => $request->name,
            'mobile_no' => $request->mobile_no,
            'email' => $request->email,
            'address' => $request->address,
            'opening_balance' => $request->opening_balance,
            'updated_at' => now(),
        ]);

        return redirect()->route('agents.index')
            ->with('success', 'Agent updated successfully');
    }

    // ✅ Delete Agent
    public function destroy($id)
    {
        DB::table('agents')->where('id', $id)->delete();

        return redirect()->route('agents.index')
            ->with('success', 'Agent deleted successfully');
    }

    // ✅ View Single Agent (optional)
    public function show($id)
    {
        $agent = DB::table('agents')->where('id', $id)->first();
        return view('agents.show', compact('agent'));
    }
}
