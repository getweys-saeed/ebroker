<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Usertokens;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DocumentVerificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('document_verification.document_verification');
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
                'document_status' => $customer->doc_verification_status == 1 ? 'Verified' : '<span class="bg-danger p-2 rounded-1 text-light fw-bold">Unproved</span>',
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
}
