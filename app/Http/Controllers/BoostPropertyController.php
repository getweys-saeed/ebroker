<?php

namespace App\Http\Controllers;

use App\Models\PropertyBoost;
use App\Services\BootstrapTableService;
use App\Services\ResponseService;
use App\Models\BostProperty;
use App\Models\BoostPropertyInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;

class BoostPropertyController extends Controller
{
    public function index()
    {

        return view('boostProperty.index');
    }



    // public function storeAds(Request $request)
    // {
    //     // Validate incoming request
    //     $validated = $request->validate([
    //         'property_id' => 'required|exists:properties,id',
    //         'customer_id' => 'required|exists:customers,id',
    //         'start_date' => 'required|date',
    //         'end_date' => 'required|date|after_or_equal:start_date',
    //         'price' => 'required|numeric|min:0',
    //         'payment_getwey' => 'required|in:0,1,2',
    //         'order_id' => 'required|integer',
    //         'payment_screenshot' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    //         'payment_detail' => 'nullable|string',
    //     ]);

    //     // Handle the payment screenshot upload
    //     $paymentScreenshotPath = '';
    //     if ($request->hasFile('payment_screenshot')) {
    //         $paymentScreenshotPath = $request->file('payment_screenshot')->store('payment_screenshots', 'public');
    //     }

    //     // Prepare the data to be inserted into property_boosts
    //     $data = [
    //         'property_id' => $validated['property_id'],
    //         'customer_id' => $validated['customer_id'],
    //         'start_date' => $validated['start_date'],
    //         'end_date' => $validated['end_date'],
    //         'price' => $validated['price'],
    //         'payment_getwey' => $validated['payment_getwey'],
    //         'order_id' => $validated['order_id'],
    //         'payment_screenshot' => $paymentScreenshotPath,
    //         'payment_detail' => $validated['payment_detail'],
    //         'is_payed' => false, // You can change this logic if necessary
    //         'created_at' => now(),
    //         'updated_at' => now(),
    //     ];

    //     // Use Query Builder to insert the data
    //     $inserted = DB::table('property_boosts')->insert($data);

    //     // Check if the insertion was successful
    //     if ($inserted) {
    //         return response()->json([
    //             'error' => false,
    //             'message' => "Property Boosted successfully."
    //         ]);
    //     } else {
    //         Log::error("Failed to Boost Property");
    //         return response()->json([
    //             'error' => true,
    //             'message' => "Failed to update property feature status."
    //         ]);
    //     }
    // }



    public function listPropertiesAnalytics(Request $request)
    {
        // Pagination, sorting, and ordering parameters
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'start_date');
        $order = $request->input('order', 'DESC');
        $boost = null;
        $payment = null;
        // Initialize the query with relationships
        $query = PropertyBoost::with(['property', 'customer',])
            ->orderBy($sort, $order);

        // Optional search filter by customer name or property title
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            })->orWhereHas('property', function ($q) use ($search) {
                $q->where('title', 'LIKE', "%$search%");
            });
        }
        if (isset($_GET['boost']) && $_GET['boost'] !== '') {
            $boost = $_GET['boost'];
        }

        if ($boost !== null) {
            $query = $query->where('is_active', $boost);
        }

        if (isset($_GET['payment']) && $_GET['payment'] !== '') {
            $payment = $_GET['payment'];
        }

        if ($payment !== null) {
            $query = $query->where('payment_getwey', $payment);
        }
        // Total count before pagination
        $total = $query->count();
        $query->where("is_payed", 1)->where("is_active", 1);
        // Apply pagination
        $boostedProperties = $query->offset($offset)->limit($limit)->get();

        // Prepare data for response
        $bulkData = [];
        $bulkData['total'] = $total;
        $rows = [];

        foreach ($boostedProperties as $boostedProperty) {
            // Format each boosted property row
            $tempRow = $boostedProperty->toArray();

            // Mask sensitive data if in demo mode
            if (env('DEMO_MODE') && Auth::user()->email !== 'superadmin@gmail.com') {
                $tempRow['customer']['mobile'] = '****************************';
                $tempRow['customer']['email'] = '****************************';
            }
            $tempRow['edit_status_url'] = 'updateBoostPropertyStatus';
            // Format payment gateway field for display (e.g., Easy-Paisa, Jazz-Cash, etc.)
            $tempRow['payment_getwey'] = $boostedProperty->payment_getwey;

            // Format price with commas
            $tempRow['price'] = number_format($boostedProperty->price);

            // Convert string date to Carbon instance and format
            $tempRow['start_date'] = \Carbon\Carbon::parse($boostedProperty->start_date)->format('Y-m-d');
            $tempRow['end_date'] = \Carbon\Carbon::parse($boostedProperty->end_date)->format('Y-m-d');

            // Add other fields
            $tempRow['is_enable'] = $boostedProperty->is_active;
            $tempRow['title'] = $boostedProperty->property;
            $tempRow['views'] = $boostedProperty->views;
            $tempRow['impressions'] = $boostedProperty->impressions;
            $tempRow['status'] = $boostedProperty->status;
            $tempRow['name'] = $boostedProperty->customer ? $boostedProperty->customer->name : 'N/A'; // Assuming customer has a name attribute

            // Action buttons can be added here
            $tempRow['operate'] = ''; // You can add buttons like Edit/Delete here

            // Append to rows array
            $rows[] = $tempRow;
        }

        // Final response structure
        $bulkData['rows'] = $rows;

        return response()->json($bulkData);
    }

    public function listBoostedProperties(Request $request)
    {
        // Pagination, sorting, and ordering parameters
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'start_date');
        $order = $request->input('order', 'DESC');
        $boost = null;
        $payment = null;
        // Initialize the query with relationships
        $query = PropertyBoost::with(['property', 'customer',])
            ->orderBy($sort, $order);

        // Optional search filter by customer name or property title
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            })->orWhereHas('property', function ($q) use ($search) {
                $q->where('title', 'LIKE', "%$search%");
            });
        }
        if (isset($_GET['boost']) && $_GET['boost'] !== '') {
            $boost = $_GET['boost'];
        }

        if ($boost !== null) {
            $query = $query->where('is_active', $boost);
        }

        if (isset($_GET['payment']) && $_GET['payment'] !== '') {
            $payment = $_GET['payment'];
        }

        if ($payment !== null) {
            $query = $query->where('payment_getwey', $payment);
        }
        // Total count before pagination
        $total = $query->count();

        // Apply pagination
        $boostedProperties = $query->offset($offset)->limit($limit)->get();

        // Prepare data for response
        $bulkData = [];
        $bulkData['total'] = $total;
        $rows = [];

        foreach ($boostedProperties as $boostedProperty) {
            // Format each boosted property row
            $tempRow = $boostedProperty->toArray();

            // Mask sensitive data if in demo mode
            if (env('DEMO_MODE') && Auth::user()->email !== 'superadmin@gmail.com') {
                $tempRow['customer']['mobile'] = '****************************';
                $tempRow['customer']['email'] = '****************************';
            }
            $tempRow['edit_status_url'] = 'updateBoostPropertyStatus';
            // Format payment gateway field for display (e.g., Easy-Paisa, Jazz-Cash, etc.)
            $tempRow['payment_getwey'] = $boostedProperty->payment_getwey;

            // Format price with commas
            $tempRow['price'] = number_format($boostedProperty->price);

            // Convert string date to Carbon instance and format
            $tempRow['start_date'] = \Carbon\Carbon::parse($boostedProperty->start_date)->format('Y-m-d');
            $tempRow['end_date'] = \Carbon\Carbon::parse($boostedProperty->end_date)->format('Y-m-d');

            // Add other fields
            $tempRow['is_enable'] = $boostedProperty->is_active;
            $tempRow['title'] = $boostedProperty->property;
            $tempRow['status'] = $boostedProperty->status;
            $tempRow['name'] = $boostedProperty->customer ? $boostedProperty->customer->name : 'N/A'; // Assuming customer has a name attribute

            // Action buttons can be added here
            $tempRow['operate'] = ''; // You can add buttons like Edit/Delete here

            // Append to rows array
            $rows[] = $tempRow;
        }

        // Final response structure
        $bulkData['rows'] = $rows;

        return response()->json($bulkData);
    }



    // 2. Method to view a single boosted property with related details


    // 3. Method to update the status of a boosted property
    public function updateBoostPropertyStatus(Request $request)
    {
        // Permission check: Ensure the user has the right to update boost properties
        if (!has_permissions('update', 'boost_properties')) {
            return ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }

        // Validate the status input
        $validator = Validator($request->all(), [
            'status' => 'required|in:0,1,2', // Status can be 0, 1, or 2
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()
            ]);
        }

        // Find the boosted property by ID
        $boostedProperty = PropertyBoost::find($request->id);

        if (!$boostedProperty) {
            return response()->json([
                'error' => true,
                'message' => 'Boosted property not found'
            ], 404);
        }

        // Update the status of the boosted property
        $boostedProperty->is_active = $request->status;
        $boostedProperty->save();

        // Return a success response with an appropriate message based on the status
        return ResponseService::successResponse(
            $request->status == 1 ? "Boosted property activated successfully" : "Boosted property deactivated successfully"
        );
    }


    // 4. Method to view a list of invoices related to boosted properties
    public function listBoostPropertyInvoices(Request $request)
    {
        // Pagination, sorting, and ordering parameters
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'start_date');
        $order = $request->input('order', 'DESC');



        // Initialize the query with relationships
        $query = PropertyBoost::with(['customer', 'property'])
            ->orderBy($sort, $order);

        // Optional search filter by customer name or property title
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            })->orWhereHas('property', function ($q) use ($search) {
                $q->where('title', 'LIKE', "%$search%");
            });
        }




        // Total count before pagination
        $total = $query->count();

        // Apply pagination
        $boostedProperties = $query->offset($offset)->limit($limit)->get();

        // Prepare data for response
        $bulkData = [];
        $bulkData['total'] = $total;
        $rows = [];

        foreach ($boostedProperties as $boostedProperty) {
            // Format each boosted property row
            $tempRow = $boostedProperty->toArray();

            // Mask sensitive data if in demo mode
            if (env('DEMO_MODE') && Auth::user()->email !== 'superadmin@gmail.com') {
                $tempRow['customer']['mobile'] = '****************************';
                $tempRow['customer']['email'] = '****************************';
            }
            $tempRow['edit_status_url'] = 'updateBoostPropertyInvoiceStatus';

            // Check if boostproperty is not null before accessing its properties

            // Format payment gateway field for display (e.g., Easy-Paisa, Jazz-Cash, etc.)
            $tempRow['order_id'] = $boostedProperty->order_id;

            // Format price with commas
            $tempRow['ispayed'] = $boostedProperty->is_payed;
            $tempRow['payment_screenshot'] = 'images/invoice/' . $boostedProperty->payment_screenshot;
            // Convert string date to Carbon instance and format

            // Add other fields

            $tempRow['title'] = $boostedProperty->boostProperty; // Assuming there's a title attribute



            // Add customer name (or 'N/A' if not found)
            $tempRow['name'] = $boostedProperty->customer ? $boostedProperty->customer->name : 'N/A';

            // Action buttons can be added here
            $tempRow['operate'] = ''; // You can add buttons like Edit/Delete here

            // Append to rows array
            $rows[] = $tempRow;
        }

        // Final response structure
        $bulkData['rows'] = $rows;

        return response()->json($bulkData);
    }
    public function updateBoostPropertyInvoiceStatus(Request $request)
    {
        // Permission check: Ensure the user has the right to update boost properties
        if (!has_permissions('update', 'boost_properties')) {
            return ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }

        // Validate the status input
        $validator = Validator($request->all(), [
            'status' => 'required|in:0,1,2', // Status can be 0, 1, or 2
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()
            ]);
        }

        // Find the boosted property by ID
        $boostedProperty = PropertyBoost::find($request->id);

        if (!$boostedProperty) {
            return response()->json([
                'error' => true,
                'message' => 'Boosted property not found'
            ], 404);
        }

        // Update the status of the boosted property
        $boostedProperty->is_payed = $request->status;
        // $boostedProperty->is_active = 1;
        $boostedProperty->save();

        // Return a success response with an appropriate message based on the status
        return ResponseService::successResponse(
            $request->status == 1 ? "Boosted property activated successfully" : "Boosted property deactivated successfully"
        );
    }

    // 5. Method to view a single invoice with details on payment and related boosted property
    public function viewInvoice()
    {

        return view("boostInvoice.index");
    }

    public function viewSuccessInvoice()
    {
        return view("boostInvoice.successInvoice");
    }
    public function listBoostPropertyInvoicesSuccess(Request $request)
    {
        // Pagination, sorting, and ordering parameters
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'start_date');
        $order = $request->input('order', 'DESC');

        // Initialize the query with relationships
        $query = PropertyBoost::with([ 'customer', 'property'])
            ->orderBy($sort, $order);

        // Optional search filter by customer name or property title
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            })->orWhereHas('property', function ($q) use ($search) {
                $q->where('title', 'LIKE', "%$search%");
            });
        }

        // Total count before pagination
        $total = $query->count();

        // Apply pagination
        $query->where("is_payed", 1);
        $boostedProperties = $query->offset($offset)->limit($limit)->get();

        // Prepare data for response
        $bulkData = [];
        $bulkData['total'] = $total;
        $rows = [];

        foreach ($boostedProperties as $boostedProperty) {
            // Format each boosted property row
            $tempRow = $boostedProperty->toArray();

            // Mask sensitive data if in demo mode
            if (env('DEMO_MODE') && Auth::user()->email !== 'superadmin@gmail.com') {
                $tempRow['customer']['mobile'] = '****************************';
                $tempRow['customer']['email'] = '****************************';
            }
            $tempRow['edit_status_url'] = 'updateBoostPropertyInvoiceStatus';

            // Check if boostproperty is not null before accessing its properties

            // Format payment gateway field for display (e.g., Easy-Paisa, Jazz-Cash, etc.)
            $tempRow['order_id'] = $boostedProperty->order_id;

            // Format price with commas
            $tempRow['ispayed'] = $boostedProperty->is_payed;
            $tempRow['payment_screenshot'] = 'images/invoice/' . $boostedProperty->payment_screenshot;
            // Convert string date to Carbon instance and format

            // Add other fields
            $tempRow['title'] = $boostedProperty->boostProperty; // Assuming there's a title attribute



            // Add customer name (or 'N/A' if not found)
            $tempRow['name'] = $boostedProperty->customer ? $boostedProperty->customer->name : 'N/A';

            // Action buttons can be added here
            $tempRow['operate'] = ''; // You can add buttons like Edit/Delete here

            // Append to rows array
            $rows[] = $tempRow;
        }

        // Final response structure
        $bulkData['rows'] = $rows;

        return response()->json($bulkData);
    }

    public function viewPendingInvoice()
    {
        return view("boostInvoice.pendingInvoice");
    }
    public function listBoostPropertyInvoicesPending(Request $request)
    {
        // Pagination, sorting, and ordering parameters
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'start_date');
        $order = $request->input('order', 'DESC');

        // Initialize the query with relationships
        $query = PropertyBoost::with([ 'customer', 'property'])
            ->orderBy($sort, $order);

        // Optional search filter by customer name or property title
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            })->orWhereHas('property', function ($q) use ($search) {
                $q->where('title', 'LIKE', "%$search%");
            });
        }

        // Total count before pagination
        $total = $query->count();

        // Apply pagination
        $query->where("is_payed", 0);
        $boostedProperties = $query->offset($offset)->limit($limit)->get();

        // Prepare data for response
        $bulkData = [];
        $bulkData['total'] = $total;
        $rows = [];

        foreach ($boostedProperties as $boostedProperty) {
            // Format each boosted property row
            $tempRow = $boostedProperty->toArray();

            // Mask sensitive data if in demo mode
            if (env('DEMO_MODE') && Auth::user()->email !== 'superadmin@gmail.com') {
                $tempRow['customer']['mobile'] = '****************************';
                $tempRow['customer']['email'] = '****************************';
            }
            $tempRow['edit_status_url'] = 'updateBoostPropertyInvoiceStatus';

            // Check if boostproperty is not null before accessing its properties

            // Format payment gateway field for display (e.g., Easy-Paisa, Jazz-Cash, etc.)
            $tempRow['order_id'] = $boostedProperty->order_id;

            // Format price with commas
            $tempRow['ispayed'] = $boostedProperty->is_payed;
            $tempRow['payment_screenshot'] = 'images/invoice/' . $boostedProperty->payment_screenshot;
            // Convert string date to Carbon instance and format

            // Add other fields

            $tempRow['title'] = $boostedProperty->boostProperty; // Assuming there's a title attribute



            // Add customer name (or 'N/A' if not found)
            $tempRow['name'] = $boostedProperty->customer ? $boostedProperty->customer->name : 'N/A';

            // Action buttons can be added here
            $tempRow['operate'] = ''; // You can add buttons like Edit/Delete here

            // Append to rows array
            $rows[] = $tempRow;
        }

        // Final response structure
        $bulkData['rows'] = $rows;

        return response()->json($bulkData);
    }


    public function viewSuccessBoost()
    {
        return view("boostProperty.successProperty");
    }

    public function listBoostedPropertiesSuccess(Request $request)
    {
        // Pagination, sorting, and ordering parameters
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'start_date');
        $order = $request->input('order', 'DESC');

        // Initialize the query with relationships
        $query = PropertyBoost::with(['property', 'customer'])
            ->orderBy($sort, $order);

        // Optional search filter by customer name or property title
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            })->orWhereHas('property', function ($q) use ($search) {
                $q->where('title', 'LIKE', "%$search%");
            });
        }

        // Total count before pagination
        $total = $query->count();

        $query->where("is_active", 1);
        // Apply pagination
        $boostedProperties = $query->offset($offset)->limit($limit)->get();


        // Prepare data for response
        $bulkData = [];
        $bulkData['total'] = $total;
        $rows = [];

        foreach ($boostedProperties as $boostedProperty) {
            // Format each boosted property row
            $tempRow = $boostedProperty->toArray();

            // Mask sensitive data if in demo mode
            if (env('DEMO_MODE') && Auth::user()->email !== 'superadmin@gmail.com') {
                $tempRow['customer']['mobile'] = '****************************';
                $tempRow['customer']['email'] = '****************************';
            }
            $tempRow['edit_status_url'] = 'updateBoostPropertyStatus';
            // Format payment gateway field for display (e.g., Easy-Paisa, Jazz-Cash, etc.)
            $tempRow['payment_getwey'] = $boostedProperty->payment_getwey;

            // Format price with commas
            $tempRow['price'] = number_format($boostedProperty->price);

            // Convert string date to Carbon instance and format
            $tempRow['start_date'] = \Carbon\Carbon::parse($boostedProperty->start_date)->format('Y-m-d');
            $tempRow['end_date'] = \Carbon\Carbon::parse($boostedProperty->end_date)->format('Y-m-d');

            // Add other fields
            $tempRow['is_enable'] = $boostedProperty->is_active;
            $tempRow['title'] = $boostedProperty->property;
            // $tempRow['status'] = $boostedProperty->boostpropertyinvoices;
            $tempRow['name'] = $boostedProperty->customer ? $boostedProperty->customer->name : 'N/A'; // Assuming customer has a name attribute

            // Action buttons can be added here
            $tempRow['operate'] = ''; // You can add buttons like Edit/Delete here

            // Append to rows array
            $rows[] = $tempRow;
        }

        // Final response structure
        $bulkData['rows'] = $rows;

        return response()->json($bulkData);
    }

    public function viewPendingBoost()
    {
        return view("boostProperty.pendingProperty");
    }
    public function listBoostedPropertiesPending(Request $request)
    {
        // Pagination, sorting, and ordering parameters
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'start_date');
        $order = $request->input('order', 'DESC');

        // Initialize the query with relationships
        $query = PropertyBoost::with(['property', 'customer'])
            ->orderBy($sort, $order);

        // Optional search filter by customer name or property title
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            })->orWhereHas('property', function ($q) use ($search) {
                $q->where('title', 'LIKE', "%$search%");
            });
        }

        // Total count before pagination
        $total = $query->count();

        $query->where("is_active", 0);
        // Apply pagination
        $boostedProperties = $query->offset($offset)->limit($limit)->get();


        // Prepare data for response
        $bulkData = [];
        $bulkData['total'] = $total;
        $rows = [];

        foreach ($boostedProperties as $boostedProperty) {
            // Format each boosted property row
            $tempRow = $boostedProperty->toArray();

            // Mask sensitive data if in demo mode
            if (env('DEMO_MODE') && Auth::user()->email !== 'superadmin@gmail.com') {
                $tempRow['customer']['mobile'] = '****************************';
                $tempRow['customer']['email'] = '****************************';
            }
            $tempRow['edit_status_url'] = 'updateBoostPropertyStatus';
            // Format payment gateway field for display (e.g., Easy-Paisa, Jazz-Cash, etc.)
            $tempRow['payment_getwey'] = $boostedProperty->payment_getwey;

            // Format price with commas
            $tempRow['price'] = number_format($boostedProperty->price);

            // Convert string date to Carbon instance and format
            $tempRow['start_date'] = \Carbon\Carbon::parse($boostedProperty->start_date)->format('Y-m-d');
            $tempRow['end_date'] = \Carbon\Carbon::parse($boostedProperty->end_date)->format('Y-m-d');

            // Add other fields
            $tempRow['is_enable'] = $boostedProperty->is_active;
            $tempRow['title'] = $boostedProperty->property;
            // $tempRow['status'] = $boostedProperty->;
            $tempRow['name'] = $boostedProperty->customer ? $boostedProperty->customer->name : 'N/A'; // Assuming customer has a name attribute

            // Action buttons can be added here
            $tempRow['operate'] = ''; // You can add buttons like Edit/Delete here

            // Append to rows array
            $rows[] = $tempRow;
        }

        // Final response structure
        $bulkData['rows'] = $rows;

        return response()->json($bulkData);
    }
    public function viewBoostAnalytics()
    {
        return view("propertyAnalytics.index");
    }
}
