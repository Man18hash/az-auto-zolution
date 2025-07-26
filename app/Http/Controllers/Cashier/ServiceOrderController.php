<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\Vehicle;
use App\Models\Inventory;
use App\Models\Technician;
use Illuminate\Support\Facades\DB;

class ServiceOrderController extends Controller
{
    public function index()
    {
        $clients = Client::all();
        $vehicles = Vehicle::all();
        $parts = Inventory::all();
        $technicians = Technician::all();

        $history = Invoice::with(['client', 'vehicle'])
            ->where('source_type', 'service_order')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('cashier.service-order', compact('clients', 'vehicles', 'parts', 'technicians', 'history'));
    }

    public function create()
    {
        $clients = Client::all();
        $vehicles = Vehicle::all();
        $parts = Inventory::select('id', 'item_name', 'quantity', 'selling')->get(); // Select quantity as the remaining stock
        $technicians = Technician::all();
        $history = collect([]);

        return view('cashier.service-order', compact('clients', 'vehicles', 'parts', 'technicians', 'history'));
    }

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
            'payment_type' => 'required|string'
        ]);


        if ($request->customer_name) {
            // Save manual customer as new client
            $client = Client::firstOrCreate(
                ['name' => $request->customer_name],
                ['phone' => $request->number, 'address' => $request->address]
            );

            $clientId = $client->id;
        } else {
            $clientId = $request->client_id;
        }

        // If vehicle_name is manually entered but not vehicle_id
        if ($request->vehicle_name && !$request->vehicle_id) {
            $vehicleId = null;
        } else {
            $vehicleId = $request->vehicle_id;
        }


        // Update client if missing phone or address
        if ($clientId) {
            $client = Client::find($clientId);
            $updated = false;

            if (!$client->phone && $request->number) {
                $client->phone = $request->number;
                $updated = true;
            }

            if (!$client->address && $request->address) {
                $client->address = $request->address;
                $updated = true;
            }

            if ($updated) {
                $client->save();
            }
        }


        // Vehicle logic

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

        $invoice = Invoice::create([
            'client_id' => $clientId,
            'vehicle_id' => $vehicleId,
            'customer_name' => $request->customer_name,
            'vehicle_name' => $request->vehicle_name,
            'source_type' => 'service_order',
            'service_status' => 'pending',
            'status' => 'unpaid',
            'subtotal' => 0,
            'total_discount' => 0,
            'vat_amount' => 0,
            'grand_total' => 0,
            'payment_type' => $request->payment_type,
            'number' => $request->number,
            'address' => $request->address,
        ]);





        // Save items (inventory OR manual)
        if ($request->has('items')) {
            foreach ($request->items as $item) {
                $invoice->items()->create([
                    'part_id' => $item['part_id'] ?? null,
                    'manual_part_name' => $item['manual_part_name'] ?? null,
                    'manual_serial_number' => $item['manual_serial_number'] ?? null,
                    'manual_acquisition_price' => $item['manual_acquisition_price'] ?? null,
                    'manual_selling_price' => $item['manual_selling_price'] ?? null,
                    'quantity' => $item['quantity'],
                    'original_price' => $item['original_price'] ?? ($item['manual_selling_price'] ?? 0),
                    'line_total' => $item['quantity'] * ($item['original_price'] ?? ($item['manual_selling_price'] ?? 0)),
                ]);

            }
        }

        // Save jobs
        if ($request->has('jobs')) {
            foreach ($request->jobs as $job) {
                $invoice->jobs()->create([
                    'job_description' => $job['job_description'] ?? '',
                    'technician_id' => $job['technician_id'] ?? null,
                    'total' => $job['total'] ?? 0,
                ]);
            }
        }

        return redirect()->route('cashier.serviceorder.index')->with('success', 'Service Order created!');
    }

    public function edit($id)
    {
        $invoice = Invoice::with(['items', 'jobs'])->findOrFail($id);
        $clients = Client::all();
        $vehicles = Vehicle::all();
        $parts = Inventory::all();
        $technicians = Technician::all();

        $history = Invoice::with(['client', 'vehicle'])
            ->where('source_type', 'service_order')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('cashier.service-order', compact('invoice', 'clients', 'vehicles', 'parts', 'technicians', 'history'));
    }

    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        // Fast update for just the source_type
        if ($request->has('quick_update') && $request->has('source_type')) {
            $invoice->update([
                'source_type' => $request->source_type
            ]);
            return redirect()->route('cashier.serviceorder.index')->with('success', 'Status updated!');
        }

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
            'subtotal' => 'required|numeric',
            'total_discount' => 'required|numeric',
            'vat_amount' => 'required|numeric',
            'grand_total' => 'required|numeric',
            'payment_type' => 'required|string',
            'number' => 'nullable|string',
            'address' => 'nullable|string',

        ]);

        if ($request->customer_name || $request->vehicle_name) {
            $clientId = null;
            $vehicleId = null;
        } else {
            $clientId = $request->client_id;
            $vehicleId = $request->vehicle_id;
        }

        // Update client if missing phone or address
        if ($clientId) {
            $client = Client::find($clientId);
            $updated = false;

            if (!$client->phone && $request->number) {
                $client->phone = $request->number;
                $updated = true;
            }

            if (!$client->address && $request->address) {
                $client->address = $request->address;
                $updated = true;
            }

            if ($updated) {
                $client->save();
            }
        }



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

        $invoice->update([
            'client_id' => $clientId,
            'vehicle_id' => $vehicleId,
            'customer_name' => $request->customer_name,
            'vehicle_name' => $request->vehicle_name,
            'source_type' => 'service_order',
            'service_status' => 'pending',
            'status' => 'unpaid',
            'subtotal' => $request->subtotal,
            'total_discount' => $request->total_discount,
            'vat_amount' => $request->vat_amount,
            'grand_total' => $request->grand_total,
            'payment_type' => $request->payment_type,
            'number' => $request->number,
            'address' => $request->address,
        ]);



        // Update items (delete old, add new: inventory OR manual)
        $invoice->items()->delete();
        if ($request->has('items')) {
            foreach ($request->items as $item) {
                $invoice->items()->create([
                    'part_id' => $item['part_id'] ?? null,
                    'manual_part_name' => $item['manual_part_name'] ?? null,
                    'manual_serial_number' => $item['manual_serial_number'] ?? null,
                    'manual_acquisition_price' => $item['manual_acquisition_price'] ?? null,
                    'manual_selling_price' => $item['manual_selling_price'] ?? null,
                    'quantity' => $item['quantity'],
                    'original_price' => $item['original_price'] ?? ($item['manual_selling_price'] ?? 0),
                    'line_total' => $item['quantity'] * ($item['original_price'] ?? ($item['manual_selling_price'] ?? 0)),
                ]);

            }
        }


        // Update jobs (delete old, add new)
        $invoice->jobs()->delete();
        if ($request->has('jobs')) {
            foreach ($request->jobs as $job) {
                $invoice->jobs()->create([
                    'job_description' => $job['job_description'] ?? '',
                    'technician_id' => $job['technician_id'] ?? null,
                    'total' => $job['total'] ?? 0,
                ]);
            }
        }

        return redirect()->route('cashier.serviceorder.index')->with('success', 'Service Order updated!');
    }

    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->items()->delete();
        $invoice->jobs()->delete();
        $invoice->delete();

        return redirect()->route('cashier.serviceorder.index')->with('success', 'Service Order deleted!');
    }

    public function view($id)
    {
        $invoice = Invoice::with([
            'client',
            'vehicle',
            'items.part',
            'jobs.technician'
        ])->findOrFail($id);

        return view('cashier.service-order-view', compact('invoice'));
    }
    public function show($id)
    {
        $invoice = Invoice::with([
            'client',
            'vehicle',
            'items.part',
            'jobs.technician'
        ])->findOrFail($id);

        // This assumes you want to reuse the view for showing details
        return view('cashier.service-order-view', compact('invoice'));
    }

    public function ajaxClients(Request $request)
    {
        $search = $request->input('q');
        $page = $request->input('page', 1);
        $perPage = 10;

        $query = \App\Models\Client::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $results = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'results' => $results->map(function ($client) {
                return [
                    'id' => $client->id,
                    'text' => $client->name,
                    'address' => $client->address,
                    'number' => $client->phone, // change from $client->number to $client->phone
                ];
            }),
            'pagination' => [
                'more' => $results->hasMorePages()
            ]
        ]);


    }


}
