<?php 

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\Vehicle;
use App\Models\Inventory; // your "parts"
use App\Models\Technician;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    // Show the appointment creation page
    public function index()
    {
        // Fetch the required data
        $clients = Client::all();  // Get all clients
        $vehicles = Vehicle::all();  // Get all vehicles
        $parts = Inventory::select('id', 'item_name', 'quantity', 'selling')->get(); // Get parts data
        $technicians = Technician::all();  // Get all technicians

        // Fetch history of invoices related to clients and vehicles
        $history = Invoice::with(['client', 'vehicle'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Pass data to the view
        return view('cashier.appointment', compact('clients', 'vehicles', 'parts', 'technicians', 'history'));
    }

    // Show the form to create a new appointment
    public function create()
    {
        $clients = Client::all();  // Get all clients
        $vehicles = Vehicle::all();  // Get all vehicles
        $parts = Inventory::all();  // Get all parts
        $technicians = Technician::all();  // Get all technicians
        $history = collect([]);  // Initialize an empty collection for history

        // Return view with data
        return view('cashier.appointment', compact('clients', 'vehicles', 'parts', 'technicians', 'history'));
    }

    // Store a new (appointment)
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'client_id'     => 'nullable|exists:clients,id',
            'vehicle_id'    => 'nullable|exists:vehicles,id',
            'customer_name' => 'nullable|string',
            'vehicle_name'  => 'nullable|string',
            'plate'         => 'nullable|string',
            'model'         => 'nullable|string',
            'year'          => 'nullable|string',
            'color'         => 'nullable|string',
            'odometer'      => 'nullable|string',
            'subtotal'      => 'required|numeric',
            'total_discount'=> 'required|numeric',
            'vat_amount'    => 'required|numeric',
            'grand_total'   => 'required|numeric',
            'payment_type'  => 'required|string',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|date_format:H:i',
        ]);

        // Handle the vehicle logic (create or update a vehicle)
        $vehicleId = $request->vehicle_id;
        if ($vehicleId) {
            $vehicle = Vehicle::find($vehicleId);
            if ($vehicle) {
                $vehicle->update([
                    'plate_number' => $request->plate,
                    'model'        => $request->model,
                    'year'         => $request->year,
                    'color'        => $request->color,
                    'odometer'     => $request->odometer,
                ]);
            }
        } else if ($request->plate || $request->model || $request->year || $request->color || $request->odometer) {
            // Create new vehicle if any field is provided
            $vehicle = Vehicle::create([
                'plate_number' => $request->plate,
                'model'        => $request->model,
                'year'         => $request->year,
                'color'        => $request->color,
                'odometer'     => $request->odometer,
                'client_id'    => $request->client_id, // Can be null
            ]);
            $vehicleId = $vehicle->id;
        } else {
            $vehicleId = null;
        }

        // Create the invoice ( appointment)
        $invoice = Invoice::create([
            'client_id'     => $request->client_id,
            'vehicle_id'    => $vehicleId,
            'customer_name' => $request->customer_name,
            'vehicle_name'  => $request->vehicle_name,
            'source_type'   => 'appointment', // Used to identify the source of the invoice
            'service_status'=> 'pending',
            'status'        => 'unpaid',
            'subtotal'      => $request->subtotal,
            'total_discount'=> $request->total_discount,
            'vat_amount'    => $request->vat_amount,
            'grand_total'   => $request->grand_total,
            'payment_type'  => $request->payment_type,
            'number'        => $request->number,
            'address'       => $request->address,
        ]);

        // Update items (delete old, add new)
$invoice->items()->delete();
if ($request->has('items')) {
    foreach ($request->items as $item) {
        $invoice->items()->create([
            'part_id'                  => $item['part_id'] ?? null,
            'manual_part_name'         => $item['manual_part_name'] ?? null,
            'manual_serial_number'     => $item['manual_serial_number'] ?? null,
            'manual_acquisition_price' => $item['manual_acquisition_price'] ?? null,
            'manual_selling_price'     => $item['manual_selling_price'] ?? null,
            'quantity'                 => $item['quantity'],
            'original_price'           => $item['original_price']  ?? ($item['manual_selling_price'] ?? 0),
            'discounted_price'         => $item['discounted_price']?? ($item['manual_selling_price'] ?? 0),
            'discount_value'           => (
                                            ($item['original_price'] ?? ($item['manual_selling_price'] ?? 0))
                                            - ($item['discounted_price'] ?? ($item['manual_selling_price'] ?? 0))
                                          ),
            'line_total'               => $item['quantity']
                                          * ($item['discounted_price'] ?? ($item['manual_selling_price'] ?? 0)),
        ]);
    }
}

        // Save jobs for the invoice
        if ($request->has('jobs')) {
            foreach ($request->jobs as $job) {
                $invoice->jobs()->create([
                    'job_description' => $job['job_description'] ?? '',
                    'technician_id'   => $job['technician_id'] ?? null,
                    'total'           => $job['total'] ?? 0,
                ]);
            }
        }

        // Redirect with success message
        return redirect()->route('cashier.appointment.index')->with('success', 'Appointment created!');
    }

    // Show the form for editing an existing quotation (appointment)
    public function edit($id)
    {
        // Fetch the invoice along with its items and jobs
        $invoice = Invoice::with(['items', 'jobs'])->findOrFail($id);
        $clients = Client::all();
        $vehicles = Vehicle::all();
        $parts = Inventory::all();
        $technicians = Technician::all();

        // Fetch invoice history
        $history = Invoice::with(['client', 'vehicle'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Return the edit view with the data
        return view('cashier.appointment', compact('invoice', 'clients', 'vehicles', 'parts', 'technicians', 'history'));
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

        // Full update logic for the invoice
        $request->validate([
            'client_id'     => 'nullable|exists:clients,id',
            'vehicle_id'    => 'nullable|exists:vehicles,id',
            'customer_name' => 'nullable|string',
            'vehicle_name'  => 'nullable|string',
            'plate'         => 'nullable|string',
            'model'         => 'nullable|string',
            'year'          => 'nullable|string',
            'color'         => 'nullable|string',
            'odometer'      => 'nullable|string',
            'subtotal'      => 'required|numeric',
            'total_discount'=> 'required|numeric',
            'vat_amount'    => 'required|numeric',
            'grand_total'   => 'required|numeric',
            'payment_type'  => 'required|string',
            'number'        => 'nullable|string',
            'address'       => 'nullable|string',
        ]);

        // Handle vehicle update logic
        $vehicleId = $request->vehicle_id;
        if ($vehicleId) {
            $vehicle = Vehicle::find($vehicleId);
            if ($vehicle) {
                $vehicle->update([
                    'plate_number' => $request->plate,
                    'model'        => $request->model,
                    'year'         => $request->year,
                    'color'        => $request->color,
                    'odometer'     => $request->odometer,
                ]);
            }
        } else if ($request->plate || $request->model || $request->year || $request->color || $request->odometer) {
            // Create new vehicle if any field is provided
            $vehicle = Vehicle::create([
                'plate_number' => $request->plate,
                'model'        => $request->model,
                'year'         => $request->year,
                'color'        => $request->color,
                'odometer'     => $request->odometer,
                'client_id'    => $request->client_id,
            ]);
            $vehicleId = $vehicle->id;
        } else {
            $vehicleId = null;
        }

        // Update the invoice
        $invoice->update([
            'client_id'     => $request->client_id,
            'vehicle_id'    => $vehicleId,
            'customer_name' => $request->customer_name,
            'vehicle_name'  => $request->vehicle_name,
            'source_type'   => 'appointment',
            'service_status'=> 'pending',
            'status'        => 'unpaid',
            'subtotal'      => $request->subtotal,
            'total_discount'=> $request->total_discount,
            'vat_amount'    => $request->vat_amount,
            'grand_total'   => $request->grand_total,
            'payment_type'  => $request->payment_type,
        ]);

        // Update items (delete old, add new)
$invoice->items()->delete();
if ($request->has('items')) {
    foreach ($request->items as $item) {
        $invoice->items()->create([
            'part_id'                  => $item['part_id'] ?? null,
            'manual_part_name'         => $item['manual_part_name'] ?? null,
            'manual_serial_number'     => $item['manual_serial_number'] ?? null,
            'manual_acquisition_price' => $item['manual_acquisition_price'] ?? null,
            'manual_selling_price'     => $item['manual_selling_price'] ?? null,
            'quantity'                 => $item['quantity'],
            'original_price'           => $item['original_price']  ?? ($item['manual_selling_price'] ?? 0),
            'discounted_price'         => $item['discounted_price']?? ($item['manual_selling_price'] ?? 0),
            'discount_value'           => (
                                            ($item['original_price'] ?? ($item['manual_selling_price'] ?? 0))
                                            - ($item['discounted_price'] ?? ($item['manual_selling_price'] ?? 0))
                                          ),
            'line_total'               => $item['quantity']
                                          * ($item['discounted_price'] ?? ($item['manual_selling_price'] ?? 0)),
        ]);
    }
}

        // Update jobs (delete old, add new)
        $invoice->jobs()->delete();
        if ($request->has('jobs')) {
            foreach ($request->jobs as $job) {
                $invoice->jobs()->create([
                    'job_description' => $job['job_description'] ?? '',
                    'technician_id'   => $job['technician_id'] ?? null,
                    'total'           => $job['total'] ?? 0,
                ]);
            }
        }

        return redirect()->route('cashier.appointment.index')->with('success', 'Appointment updated!');
    }

    // Delete an appointment
    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->items()->delete();
        $invoice->jobs()->delete();
        $invoice->delete();

        return redirect()->route('cashier.appointment.index')->with('success', 'Appointment deleted!');
    }

    // View an appointment (appointment)
    public function view($id)
    {
        $invoice = Invoice::with([
            'client',
            'vehicle',
            'items.part',
            'jobs.technician'
        ])->findOrFail($id);

        return view('cashier.appointment-view', compact('invoice'));
    }
}
