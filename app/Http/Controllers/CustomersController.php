<?php

namespace App\Http\Controllers;

use App\Exports\CustomerExport;
use App\Models\Customer;
use App\Models\Usertokens;
use Illuminate\Http\Request;
use App\Models\InterestedUser;
use App\Services\ResponseService;
use Illuminate\Support\Facades\Auth;
use App\Exports\PropertyExport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class CustomersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        return view('customer.index');
    }

    public function verifiedUser()
    {

        return view('customer.verified_customer');
    }

    public function unverifiedUser()
    {

        return view('customer.unverified_customer');
    }

    public function trashUsers()
    {

        return view('customer.customer_trash');
    }




    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        if (!has_permissions('update', 'customer')) {
            return response()->json(['error' => true, 'message' => PERMISSION_ERROR_MSG]);
        }

        Customer::where('id', $request->id)->update(['isActive' => $request->status]);

        $fcm_ids = [];
        $customer_id = Customer::where(['id' => $request->id, 'notification' => 1])->count();
        if ($customer_id) {
            $user_token = Usertokens::where('customer_id', $request->id)->pluck('fcm_id')->toArray();
            $fcm_ids[] = $user_token;
        }

        if (!empty($fcm_ids)) {
            $msg = $request->status == 1 ? 'Activate now by Administrator ' : 'Deactivate now by Administrator ';
            $type = $request->status == 1 ? 'account_activated' : 'account_deactivated';
            $full_msg = $request->status == 1 ? 'Your Account ' . $msg : 'Please Contact Administrator';
            $registrationIDs = $fcm_ids[0];

            $fcmMsg = [
                'title' => 'Your Account ' . $msg,
                'message' => $full_msg,
                'type' => $type,
                'body' => 'Your Account ' . $msg,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'sound' => 'default',
            ];

            try {
                send_push_notification($registrationIDs, $fcmMsg);
            } catch (\Exception $e) {
                Log::error('Failed to send FCM notification: ' . $e->getMessage());
            }
        }

        $statusMessage = $request->status ? "Customer Activated Successfully" : "Customer Deactivated Successfully";
        return ResponseService::successResponse($statusMessage);
    }




    public function bulkDelete(Request $request)
    {
        // Validate that 'ids' is an array and contains valid values
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:customers,id' // Assuming your table name is 'customers'
        ]);

        try {
            // Retrieve all customers with the provided IDs
            $customers = Customer::whereIn('id', $request->ids)->get();

            foreach ($customers as $customer) {
                // Retrieve the old document image
                $oldImage = $customer->user_document;

                // Remove the old image if it exists
                if ($oldImage && file_exists(public_path('images') . config('global.USER_IMG_PATH') . $oldImage)) {
                    unlink(public_path('images') . config('global.USER_IMG_PATH') . $oldImage);
                }

                // Delete the customer record
                $customer->delete();
            }

            return response()->json(['success' => true, 'message' => 'Records deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete records.']);
        }
    }




    public function customerList(Request $request)
    {
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'sequence');
        $order = $request->input('order', 'ASC');

        $status = null;
        if (isset($_GET['property_id'])) {
            $interested_users =  InterestedUser::select('customer_id')->where('property_id', $_GET['property_id'])->pluck('customer_id');

            $sql = Customer::whereIn('id', $interested_users)->orderBy($sort, $order);
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $search = $_GET['search'];
                $sql->where(function ($query) use ($search) {
                    $query->where('id', 'LIKE', "%$search%")->orwhere('email', 'LIKE', "%$search%")->orwhere('name', 'LIKE', "%$search%")->orwhere('mobile', 'LIKE', "%$search%");
                });
            }
        } else {

            $sql = Customer::orderBy($sort, $order);
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $search = $_GET['search'];
                $sql->where('id', 'LIKE', "%$search%")->orwhere('email', 'LIKE', "%$search%")->orwhere('name', 'LIKE', "%$search%")->orwhere('mobile', 'LIKE', "%$search%");
            }
        }
        if (isset($_GET['status']) && $_GET['status'] !== '') {
            $status = $_GET['status'];
        }

        if ($status !== null) {
            $sql = $sql->where('otp_verified', $status);
        }


        $total = $sql->count();

        if (isset($_GET['limit'])) {
            $sql->skip($offset)->take($limit);
        }


        $res = $sql->where("trash", 0)->get();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $count = 1;


        $operate = '';
        foreach ($res as $row) {
            $tempRow = $row->toArray();

            // Mask Details in Demo Mode
            $tempRow['mobile'] = (env('DEMO_MODE') ? (env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ($row->mobile) : '****************************') : ($row->mobile));
            $tempRow['email'] = (env('DEMO_MODE') ? (env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ($row->email) : '****************************') : ($row->email));
            $tempRow['firebase_id'] = (env('DEMO_MODE') ? (env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ($row->firebase_id) : '****************************') : ($row->firebase_id));
            $tempRow['address'] = (env('DEMO_MODE') ? (env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ($row->address) : '****************************') : ($row->address));

            $tempRow['edit_status_url'] = 'customerstatus';
            $tempRow['total_properties'] =  '<a href="' . url('property') . '?customer=' . $row->id . '">' . $row->total_properties . '</a>';
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
            $count++;
        }

        $bulkData['rows'] = $rows;
        // dd($bulkData);
        return response()->json($bulkData);
    }

    public function customerListTrash(Request $request)
    {
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'sequence');
        $order = $request->input('order', 'ASC');

        $status = null;
        if (isset($_GET['property_id'])) {
            $interested_users =  InterestedUser::select('customer_id')->where('property_id', $_GET['property_id'])->pluck('customer_id');

            $sql = Customer::whereIn('id', $interested_users)->orderBy($sort, $order);
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $search = $_GET['search'];
                $sql->where(function ($query) use ($search) {
                    $query->where('id', 'LIKE', "%$search%")->orwhere('email', 'LIKE', "%$search%")->orwhere('name', 'LIKE', "%$search%")->orwhere('mobile', 'LIKE', "%$search%");
                });
            }
        } else {

            $sql = Customer::orderBy($sort, $order);
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $search = $_GET['search'];
                $sql->where('id', 'LIKE', "%$search%")->orwhere('email', 'LIKE', "%$search%")->orwhere('name', 'LIKE', "%$search%")->orwhere('mobile', 'LIKE', "%$search%");
            }
        }
        if (isset($_GET['status']) && $_GET['status'] !== '') {
            $status = $_GET['status'];
        }

        if ($status !== null) {
            $sql = $sql->where('otp_verified', $status);
        }


        $total = $sql->count();

        if (isset($_GET['limit'])) {
            $sql->skip($offset)->take($limit);
        }


        $res = $sql->where("trash", 1)->get();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $count = 1;


        $operate = '';
        foreach ($res as $row) {
            $tempRow = $row->toArray();

            // Mask Details in Demo Mode
            $tempRow['mobile'] = (env('DEMO_MODE') ? (env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ($row->mobile) : '****************************') : ($row->mobile));
            $tempRow['email'] = (env('DEMO_MODE') ? (env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ($row->email) : '****************************') : ($row->email));
            $tempRow['firebase_id'] = (env('DEMO_MODE') ? (env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ($row->firebase_id) : '****************************') : ($row->firebase_id));
            $tempRow['address'] = (env('DEMO_MODE') ? (env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ($row->address) : '****************************') : ($row->address));

            $tempRow['edit_status_url'] = 'customerstatus';
            $tempRow['total_properties'] =  '<a href="' . url('property') . '?customer=' . $row->id . '">' . $row->total_properties . '</a>';
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
            $count++;
        }

        $bulkData['rows'] = $rows;
        // dd($bulkData);
        return response()->json($bulkData);
    }


    public function customerListVerified(Request $request)
    {
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'sequence');
        $order = $request->input('order', 'ASC');


        if (isset($_GET['property_id'])) {
            $interested_users =  InterestedUser::select('customer_id')->where('property_id', $_GET['property_id'])->pluck('customer_id');

            $sql = Customer::whereIn('id', $interested_users)->orderBy($sort, $order);
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $search = $_GET['search'];
                $sql->where(function ($query) use ($search) {
                    $query->where('id', 'LIKE', "%$search%")->orwhere('email', 'LIKE', "%$search%")->orwhere('name', 'LIKE', "%$search%")->orwhere('mobile', 'LIKE', "%$search%");
                });
            }
        } else {

            $sql = Customer::orderBy($sort, $order);
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $search = $_GET['search'];
                $sql->where('id', 'LIKE', "%$search%")->orwhere('email', 'LIKE', "%$search%")->orwhere('name', 'LIKE', "%$search%")->orwhere('mobile', 'LIKE', "%$search%");
            }
        }



        $total = $sql->count();

        if (isset($_GET['limit'])) {
            $sql->skip($offset)->take($limit);
        }


        $res = $sql->where("isActive", 1)->get();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $count = 1;


        $operate = '';
        foreach ($res as $row) {
            $tempRow = $row->toArray();

            // Mask Details in Demo Mode
            $tempRow['mobile'] = (env('DEMO_MODE') ? (env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ($row->mobile) : '****************************') : ($row->mobile));
            $tempRow['email'] = (env('DEMO_MODE') ? (env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ($row->email) : '****************************') : ($row->email));
            $tempRow['firebase_id'] = (env('DEMO_MODE') ? (env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ($row->firebase_id) : '****************************') : ($row->firebase_id));
            $tempRow['address'] = (env('DEMO_MODE') ? (env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ($row->address) : '****************************') : ($row->address));

            $tempRow['edit_status_url'] = 'customerstatus';
            $tempRow['total_properties'] =  '<a href="' . url('property') . '?customer=' . $row->id . '">' . $row->total_properties . '</a>';
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
            $count++;
        }

        $bulkData['rows'] = $rows;
        // dd($bulkData);
        return response()->json($bulkData);
    }

    public function customerListUnverified(Request $request)
    {
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'sequence');
        $order = $request->input('order', 'ASC');


        if (isset($_GET['property_id'])) {
            $interested_users =  InterestedUser::select('customer_id')->where('property_id', $_GET['property_id'])->pluck('customer_id');

            $sql = Customer::whereIn('id', $interested_users)->orderBy($sort, $order);
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $search = $_GET['search'];
                $sql->where(function ($query) use ($search) {
                    $query->where('id', 'LIKE', "%$search%")->orwhere('email', 'LIKE', "%$search%")->orwhere('name', 'LIKE', "%$search%")->orwhere('mobile', 'LIKE', "%$search%");
                });
            }
        } else {

            $sql = Customer::orderBy($sort, $order);
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $search = $_GET['search'];
                $sql->where('id', 'LIKE', "%$search%")->orwhere('email', 'LIKE', "%$search%")->orwhere('name', 'LIKE', "%$search%")->orwhere('mobile', 'LIKE', "%$search%");
            }
        }



        $total = $sql->count();

        if (isset($_GET['limit'])) {
            $sql->skip($offset)->take($limit);
        }


        $res = $sql->where("isActive", 0)->get();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $count = 1;


        $operate = '';
        foreach ($res as $row) {
            $tempRow = $row->toArray();

            // Mask Details in Demo Mode
            $tempRow['mobile'] = (env('DEMO_MODE') ? (env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ($row->mobile) : '****************************') : ($row->mobile));
            $tempRow['email'] = (env('DEMO_MODE') ? (env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ($row->email) : '****************************') : ($row->email));
            $tempRow['firebase_id'] = (env('DEMO_MODE') ? (env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ($row->firebase_id) : '****************************') : ($row->firebase_id));
            $tempRow['address'] = (env('DEMO_MODE') ? (env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ($row->address) : '****************************') : ($row->address));

            $tempRow['edit_status_url'] = 'customerstatus';
            $tempRow['total_properties'] =  '<a href="' . url('property') . '?customer=' . $row->id . '">' . $row->total_properties . '</a>';
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
            $count++;
        }

        $bulkData['rows'] = $rows;
        // dd($bulkData);
        return response()->json($bulkData);
    }
    public function bulkUpdate(Request $request)
    {
        // Check if the user has permission to update customer records
        if (!has_permissions('update', 'customer')) {
            return response()->json([
                'success' => false,
                'message' => PERMISSION_ERROR_MSG,
            ]);
        }

        $action = $request->action;
        $ids = $request->ids;

        // Validate that $ids is an array
        if (!is_array($ids) || empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No customer IDs provided or invalid format.',
            ]);
        }

        // Handle bulk deletion
        if ($action == 'delete') {
            Customer::whereIn('id', $ids)->delete();
            return response()->json([
                'success' => true,
                'message' => 'Selected records have been deleted successfully.',
            ]);
        } elseif ($action == 'activate' || $action == 'deactivate') {
            // Set status based on the action
            $status = $action == 'activate' ? 1 : 0;

            // Update the isActive status for selected customers
            Customer::whereIn('id', $ids)->update(['isActive' => $status]);

            // Prepare for FCM notifications
            $fcm_ids = Usertokens::whereIn('customer_id', $ids)->pluck('fcm_id')->toArray();
            Log::debug("Fetched FCM IDs: ", $fcm_ids); // Debugging line

            // Ensure $fcm_ids is an array and not empty
            if (is_array($fcm_ids) && !empty($fcm_ids)) {
                $msg = $status == 1 ? 'Activated by Administrator' : 'Deactivated by Administrator';
                $type = $status == 1 ? 'account_activated' : 'account_deactivated';
                $full_msg = $status == 1 ? 'Your Account has been activated.' : 'Your Account has been deactivated. Please contact the Administrator.';

                $fcmMsg = [
                    'title' => 'Account Status Update',
                    'message' => $full_msg,
                    'type' => $type,
                    'body' => $msg,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'sound' => 'default',
                ];

                // Loop through each FCM ID and send notifications individually
                foreach ($fcm_ids as $registrationID) {
                    if (!empty($registrationID)) {
                        try {
                            // Send the push notification
                            send_push_notification([$registrationID], $fcmMsg);
                        } catch (\Exception $e) {
                            // Log the error for debugging
                            Log::error("Failed to send FCM notification for registration ID {$registrationID}: " . $e->getMessage());
                        }
                    } else {
                        // Log or handle the case where registrationID is empty
                        Log::warning("Empty FCM registration ID for customer ID: " . $registrationID);
                    }
                }
            } else {
                // Log that there are no valid FCM IDs to notify
                Log::info("No valid FCM IDs found for the given customer IDs.");
            }

            return response()->json([
                'success' => true,
                'message' => $request->status ? "Customer Activatd Successfully" : "Customer Deactivatd Successfully"
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid action selected.',
        ]);
    }

    public function updateTrashStatus(Request $request)
    {
        $customer = Customer::find($request->id);
        if ($customer) {
            $customer->trash = $request->trash;
            $customer->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 400);
    }


    public function export(Request $request)
    {
        // Validate the input data
        $validated = $request->validate([
            'start_month' => 'required|date',
            'end_month' => 'required|date|after_or_equal:start_month',
        ]);

        return Excel::download(new CustomerExport($validated['start_month'], $validated['end_month']), 'customer.csv');
    }
}
