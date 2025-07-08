<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        // load both models
        $clients  = Client::orderBy('created_at','desc')->get();
        $vehicles = Vehicle::with('client')->orderBy('created_at','desc')->get();

        // pass them both into your view
        return view('cashier.vehicle', compact('clients','vehicles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'    => 'nullable|exists:clients,id',
            'plate_number' => 'required|string|unique:vehicles,plate_number',
            'model'        => 'nullable|string',
            'vin_chasis'   => 'nullable|string|unique:vehicles,vin_chasis',
            'manufacturer' => 'nullable|string',
            'year'         => 'nullable|string',
            'color'        => 'nullable|string',
            'odometer'     => 'nullable|string',
        ]);
        $vehicle = Vehicle::create($data);
        return response()->json($vehicle->load('client'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'client_id'    => 'nullable|exists:clients,id',
            'plate_number' => 'required|string|unique:vehicles,plate_number,' . $vehicle->id,
            'model'        => 'nullable|string',
            'vin_chasis'   => 'nullable|string|unique:vehicles,vin_chasis,' . $vehicle->id,
            'manufacturer' => 'nullable|string',
            'year'         => 'nullable|string',
            'color'        => 'nullable|string',
            'odometer'     => 'nullable|string',
        ]);
        $vehicle->update($data);
        return response()->json($vehicle->load('client'));
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return response()->json(['deleted' => true]);
    }
}
