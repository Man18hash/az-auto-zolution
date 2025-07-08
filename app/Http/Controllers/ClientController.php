<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::orderBy('created_at', 'desc')->get();
        return view('cashier.vehicle', compact('clients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string',
            'address' => 'nullable|string',
            'phone'   => 'nullable|string',
            'email'   => 'nullable|email|unique:clients,email',
        ]);
        $client = Client::create($data);
        return response()->json($client);
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'name'    => 'required|string',
            'address' => 'nullable|string',
            'phone'   => 'nullable|string',
            'email'   => 'nullable|email|unique:clients,email,' . $client->id,
        ]);
        $client->update($data);
        return response()->json($client);
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return response()->json(['deleted' => true]);
    }
}
