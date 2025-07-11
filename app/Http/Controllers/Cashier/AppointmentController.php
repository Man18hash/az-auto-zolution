<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\Vehicle;


class AppointmentController extends Controller
{
    // Show the appointment creation page
    public function index()
    {
        // Fetch the required data
        $clients = Client::all();  // Get all clients
        $vehicles = Vehicle::all();  // Get all vehicles

        // Fetch history of invoices related to clients and vehicles
        $history = Invoice::with(['client', 'vehicle'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Pass data to the view
        return view('cashier.appointment', compact('clients', 'vehicles', 'history'));

    }

    // Show the form to create a new appointment
    public function create()
    {
        $clients = Client::all();  // Get all clients
        $vehicles = Vehicle::all();  // Get all vehicles

        $history = collect([]);  // Initialize an empty collection for history

        // Return view with data
        return view('cashier.appointment', compact('clients', 'vehicles', 'history'));

    }

    // Store a new (appointment)
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'customer_name' => 'nullable|string',
            'vehicle_name' => 'nullable|string',
            'plate' => 'nullable|string',
            'model' => 'nullable|string',
            'year' => 'nullable|string',
            'color' => 'nullable|string',
            'odometer' => 'nullable|string',
            
            'appointment_date' => 'nullable|date',
            'note' => 'nullable|string',
        ]);

        // Handle manual client creation
        $clientId = $request->client_id;
        if (!$clientId && $request->customer_name) {
            $client = Client::create([
                'name' => $request->customer_name,
            ]);
            $clientId = $client->id;
        }

        // Handle vehicle logic
        $vehicleId = $request->vehicle_id;
        if ($vehicleId) {
            $vehicle = Vehicle::find($vehicleId);
            if ($vehicle) {
                $vehicle->update([
                    'plate_number' => $request->plate,
                    'model' => $request->model,
                    'year' => $request->year,
                    'color' => $request->color,
                    'odometer' => $request->odometer,
                ]);
            }
        } else if ($request->plate || $request->model || $request->year || $request->color || $request->odometer) {
            // Always use resolved $clientId here
            $vehicle = Vehicle::create([
                'plate_number' => $request->plate,
                'model' => $request->model,
                'year' => $request->year,
                'color' => $request->color,
                'odometer' => $request->odometer,
                'client_id' => $clientId,
            ]);
            $vehicleId = $vehicle->id;
        } else {
            $vehicleId = null;
        }

        // Create appointment invoice
        $invoice = Invoice::create([
            'client_id' => $clientId,
            'vehicle_id' => $vehicleId,
            'customer_name' => $request->customer_name,
            'vehicle_name' => $request->vehicle_name,
            'source_type' => 'appointment',
            'service_status' => 'pending',
            'status' => 'unpaid',
            
            'appointment_date' => $request->appointment_date,
            'note' => $request->note,
        ]);

        return redirect()->route('cashier.appointment.index')->with('success', 'Appointment created!');
    }


    // Show the form for editing an existing quotation (appointment)
    public function edit($id)
    {
        // Fetch the invoice along with its items and jobs
        $invoice = Invoice::findOrFail($id);

        $clients = Client::all();
        $vehicles = Vehicle::all();


        // Fetch invoice history
        $history = Invoice::with(['client', 'vehicle'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Return the edit view with the data
        return view('cashier.appointment', compact('invoice', 'clients', 'vehicles', 'history'));

    }

    // Update an existing appointment
    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        // Fast update for just the source_type
        if ($request->has('quick_update') && $request->has('source_type')) {
            $invoice->update([
                'source_type' => $request->source_type
            ]);
            return redirect()->route('cashier.appointment.index')->with('success', 'Status updated!');
        }

        // Validate
        $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'customer_name' => 'nullable|string',
            'vehicle_name' => 'nullable|string',
            'plate' => 'nullable|string',
            'model' => 'nullable|string',
            'year' => 'nullable|string',
            'color' => 'nullable|string',
            'odometer' => 'nullable|string',

            'appointment_date' => 'nullable|date',
            'note' => 'nullable|string',
        ]);

        // Handle manual client creation on update
        $clientId = $request->client_id;
        if (!$clientId && $request->customer_name) {
            $client = Client::create([
                'name' => $request->customer_name,
            ]);
            $clientId = $client->id;
        }

        // Handle vehicle update logic
        $vehicleId = $request->vehicle_id;
        if ($vehicleId) {
            $vehicle = Vehicle::find($vehicleId);
            if ($vehicle) {
                $vehicle->update([
                    'plate_number' => $request->plate,
                    'model' => $request->model,
                    'year' => $request->year,
                    'color' => $request->color,
                    'odometer' => $request->odometer,
                ]);
            }
        } else if ($request->plate || $request->model || $request->year || $request->color || $request->odometer) {
            $vehicle = Vehicle::create([
                'plate_number' => $request->plate,
                'model' => $request->model,
                'year' => $request->year,
                'color' => $request->color,
                'odometer' => $request->odometer,
                'client_id' => $clientId,
            ]);
            $vehicleId = $vehicle->id;
        } else {
            $vehicleId = null;
        }

        // Update the invoice
        $invoice->update([
            'client_id' => $clientId,
            'vehicle_id' => $vehicleId,
            'customer_name' => $request->customer_name,
            'vehicle_name' => $request->vehicle_name,
            'source_type' => 'appointment',
            'service_status' => 'pending',
            'status' => 'unpaid',

            'appointment_date' => $request->appointment_date,
            'note' => $request->note,
        ]);

        return redirect()->route('cashier.appointment.index')->with('success', 'Appointment updated!');
    }


    // Delete an appointment
    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);

        $invoice->delete();

        return redirect()->route('cashier.appointment.index')->with('success', 'Appointment deleted!');
    }

    // View an appointment (appointment)
    public function view($id)
    {
        $invoice = Invoice::with(['client', 'vehicle'])->findOrFail($id);


        return view('cashier.appointment-view', compact('invoice'));
    }
}
