<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Usertokens;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Exports\DocumentExport;
use Maatwebsite\Excel\Facades\Excel;

class DocumentVerificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('document_verification.document_verification');
    }

    public function activeDocument()
    {
        return view('document_verification.document_verified');
    }

    public function unactiveDocument()
    {
        return view('document_verification.document_unverified');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        try {
            Log::info('Update called with:', $request->all()); // Log the incoming request data

            // Validate incoming request
            $request->validate([
                'id' => 'required|integer|exists:customers,id',
                'status' => 'required|boolean',
            ]);

            // Check permissions
            if (!has_permissions('update', 'customer')) {
                return ResponseService::errorResponse(PERMISSION_ERROR_MSG);
            }


            // Update the customer document status
            $customer = Customer::find($request->id);
            $customer->doc_verification_status = $request->status; // This should be 0 for deactivation
            $customer->save();

            // Prepare for notification
            $fcm_ids = $this->getFcmIds($customer->id);

            // Send push notification if applicable
            if (!empty($fcm_ids)) {
                $msg = $this->getNotificationMessage($request->status);
                $fcmMsg = $this->prepareFcmMessage($msg, $request->status);
                send_push_notification($fcm_ids, $fcmMsg);
            }

            // Return success response
            return ResponseService::successResponse($request->status ? 'Customer Activated Successfully' : 'Customer Deactivated Successfully');
        } catch (\Throwable $th) {
            return ResponseService::successResponse($th ? 'error' : 'Customer Deactivated Successfully');
        }
    }



    /**
     * Fetch customer documents for verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */

    public function customerdocument(Request $request)
    {
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'ASC');
        $status = null;

        // Fetch customers with non-null documents
        $query = Customer::whereNotNull('user_document')->orderBy($sort, $order);

        // Apply search filter if any
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $search = $request->input('search');
                $q->where('id', 'LIKE', "%$search%")
                    ->orWhere('email', 'LIKE', "%$search%")
                    ->orWhere('name', 'LIKE', "%$search%")
                    ->orWhere('mobile', 'LIKE', "%$search%");
            });
        }
        if (isset($_GET['status']) && $_GET['status'] !== '') {
            $status = $_GET['status'];
        }

        if ($status !== null) {
            $query = $query->where('doc_verification_status', $status);
        }


        // Pagination
        $total = $query->count();
        $customers = $query->skip($offset)->take($limit)->get();

        // Format response data
        $rows = $this->formatCustomerData($customers);

        // Prepare the data response for AJAX
        $bulkData = [
            'total' => $total,
            'rows' => $rows,
        ];

        return $request->wantsJson() ? response()->json($bulkData) : view('document_verification.document_verification', ['customers' => $customers]);
    }
    public function verifiedDocument(Request $request)
    {
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'ASC');

        // Fetch customers with non-null documents
        $query = Customer::whereNotNull('user_document')->where("doc_verification_status",1)->orderBy($sort, $order);

        // Apply search filter if any
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $search = $request->input('search');
                $q->where('id', 'LIKE', "%$search%")
                    ->orWhere('email', 'LIKE', "%$search%")
                    ->orWhere('name', 'LIKE', "%$search%")
                    ->orWhere('mobile', 'LIKE', "%$search%");
            });
        }

        // Pagination
        $total = $query->count();
        $customers = $query->skip($offset)->take($limit)->get();

        // Format response data
        $rows = $this->formatCustomerData($customers);

        // Prepare the data response for AJAX
        $bulkData = [
            'total' => $total,
            'rows' => $rows,
        ];

        return $request->wantsJson() ? response()->json($bulkData) : view('document_verification.document_verification', ['customers' => $customers]);
    }

    public function unverifiedDocument(Request $request)
    {
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'ASC');

        // Fetch customers with non-null documents
        $query = Customer::whereNotNull('user_document')->where("doc_verification_status",0)->orderBy($sort, $order);

        // Apply search filter if any
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $search = $request->input('search');
                $q->where('id', 'LIKE', "%$search%")
                    ->orWhere('email', 'LIKE', "%$search%")
                    ->orWhere('name', 'LIKE', "%$search%")
                    ->orWhere('mobile', 'LIKE', "%$search%");
            });
        }

        // Pagination
        $total = $query->count();
        $customers = $query->skip($offset)->take($limit)->get();

        // Format response data
        $rows = $this->formatCustomerData($customers);

        // Prepare the data response for AJAX
        $bulkData = [
            'total' => $total,
            'rows' => $rows,
        ];

        return $request->wantsJson() ? response()->json($bulkData) : view('document_verification.document_verification', ['customers' => $customers]);
    }
    /**
     * Get FCM IDs for notifications.
     *
     * @param int $customerId
     * @return array
     */
    private function getFcmIds($customerId)
    {
        return Usertokens::where('customer_id', $customerId)->pluck('fcm_id')->toArray();
    }

    /**
     * Get notification message based on status.
     *
     * @param int $status
     * @return string
     */
    private function getNotificationMessage($status)
    {
        return $status === 1
            ? 'Your account has been activated by the Administrator.'
            : 'Your account has been deactivated by the Administrator. Please contact for further details.';
    }

    /**
     * Prepare FCM message payload.
     *
     * @param string $msg
     * @param int $status
     * @return array
     */
    private function prepareFcmMessage($msg, $status)
    {
        $type = $status === 1 ? 'account_activated' : 'account_deactivated';
        return [
            'title' => 'Account Status Update',
            'message' => $msg,
            'type' => $type,
            'body' => $msg,
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'sound' => 'default',
        ];
    }

    /**
     * Format customer data for response.
     *
     * @param $customers
     * @return array
     */

    private function formatCustomerData($customers)
    {

        return $customers->map(function ($customer) {


            return [
                'id' => $customer->id,
                'user_document' => url('/images/user_document/' . $customer->user_document), // Adjusted path to the public folder
                'name' => $customer->name,
                'mobile' => $customer->mobile,
                'address' => $customer->address,
                'doc_verification_status' => $customer->doc_verification_status,
                'document_status' => $customer->doc_verification_status == 1 ? '<span class="btn btn-success btn-sm p-2 rounded-1 text-light fw-bold">Verified</span>' : '<span class="btn  btn-sm p-2 rounded-1 text-light fw-bold" style="background-color:#f75454">Unproved</span>',
            ];
        });
    }



    /**
     * Mask sensitive data in demo mode.
     *
     * @param string $data
     * @return string
     */
    private function maskSensitiveData($data)
    {
        return env('DEMO_MODE') && Auth::user()->email != 'superadmin@gmail.com' ? '****************************' : $data;
    }


    public function bulkUpdate(Request $request)
    {
        // Check permissions for updating customers
        if (!has_permissions('update', 'customer')) {
            return response()->json([
                'success' => false,
                'message' => PERMISSION_ERROR_MSG,
            ]);
        }

        // Extract action and IDs from the request
        $action = $request->action;
        $ids = $request->ids;

        // Validate action
        if ($action == 'activate' || $action == 'deactivate') {
            $status = $action == 'activate' ? 1 : 0;

            // Update the document verification status in bulk
            Customer::whereIn('id', $ids)->update(['doc_verification_status' => $status]);

            // Prepare for FCM notification if needed
            foreach ($ids as $id) {
                $fcm_ids = [];
                // Check if the customer has notifications enabled
                $customer = Customer::where('id', $id)->where('notification', 1)->first();
                if ($customer) {
                    // Retrieve user tokens for the customer
                    $user_tokens = Usertokens::where('customer_id', $id)->pluck('fcm_id')->toArray();
                    $fcm_ids = array_merge($fcm_ids, $user_tokens);
                }

                // Send notification if tokens are found
                if (!empty($fcm_ids)) {
                    $msg = $status == 1 ? 'Activated by Administrator' : 'Deactivated by Administrator';
                    $type = $status == 1 ? 'account_activated' : 'account_deactivated';
                    $full_msg = $status == 1 ? 'Your Account has been activated' : 'Your Account has been deactivated. Please contact the Administrator.';

                    // Send the push notification
                    $fcmMsg = [
                        'title' => 'Account Status Update',
                        'message' => $full_msg,
                        'type' => $type,
                        'body' => $msg,
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'sound' => 'default',
                    ];

                    // Ensure registration IDs are not empty
                    if (!empty($fcm_ids)) {
                        try {
                            send_push_notification($fcm_ids, $fcmMsg);
                        } catch (\Exception $e) {
                            Log::error("Failed to send FCM notification for customer ID {$id}: " . $e->getMessage());
                        }
                    }
                }
            }

            // Return success response
            return response()->json([
                'success' => true,
                'message' => $status ? 'Selected Documents have been activated.' : 'Selected Documents have been deactivated.',
            ]);
        }

        // Return error for invalid action
        return response()->json([
            'success' => false,
            'message' => 'Invalid action selected.',
        ]);
    }


    public function export(Request $request)
    {
        $validated = $request->validate([
            'start_month' => 'required|date',
            'end_month' => 'required|date|after_or_equal:start_month',
        ]);

        return Excel::download(new DocumentExport($validated['start_month'], $validated['end_month']), 'user_document.csv');
    }


}
