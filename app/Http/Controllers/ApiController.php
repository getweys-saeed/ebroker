<?php

namespace App\Http\Controllers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use DateTime;
use Illuminate\Support\Facades\Hash;
// use Exception;
use Carbon\Carbon;
use App\Models\Type;
use App\Models\User;
use App\Models\Chats;
use App\Models\Slider;
use Illuminate\Support\Facades\Log;
use App\Models\Otp;
use App\Mail\SendOtpMail;
use Illuminate\Support\Facades\Mail;

use GuzzleHttp\Client;
use App\Models\Article;
use App\Models\Package;
use App\Models\Setting;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Language;

use App\Models\Payments;
use App\Models\Projects;
use App\Models\Property;
use App\Libraries\Paypal;
use App\Models\Favourite;
use App\Models\parameter;


use App\Models\Usertokens;
use App\Models\SeoSettings;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;
use App\Models\ProjectPlans;
use App\Models\user_reports;
use App\Models\UserInterest;
use Illuminate\Http\Request;
use App\Models\Advertisement;
use App\Models\Notifications;
use App\Models\InterestedUser;
use App\Models\PropertyImages;

// use GuzzleHttp\Client;
use App\Models\report_reasons;
use App\Models\Contactrequests;

use App\Models\AssignParameters;

use App\Models\ProjectDocuments;
use App\Models\OutdoorFacilities;
use Illuminate\Support\Facades\DB;
use App\Models\UserPurchasedPackage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

use App\Models\AssignedOutdoorFacilities;
use Illuminate\Support\Facades\Storage;
// use PayPal_Pro as GlobalPayPal_Pro;
use Illuminate\Support\Facades\Validator;

use Intervention\Image\ImageManagerStatic as Image;

class ApiController extends Controller
{





    //* START :: get_system_settings   *//
    public function get_system_settings(Request $request)
    {
        $result =  Setting::select('type', 'data')->get();

        foreach ($result as $row) {


            if ($row->type == "place_api_key" || $row->type == "stripe_secret_key") {

                $publicKey = file_get_contents(base_path('public_key.pem')); // Load the public key
                $encryptedData = '';
                if (openssl_public_encrypt($row->data, $encryptedData, $publicKey)) {

                    $tempRow[$row->type] = base64_encode($encryptedData);
                }
            } else if ($row->type == 'company_logo') {

                $tempRow[$row->type] = url('/assets/images/logo/logo.png');
            } else if ($row->type == 'web_logo' || $row->type == 'web_placeholder_logo' || $row->type == 'app_home_screen' || $row->type == 'web_footer_logo') {


                $tempRow[$row->type] = url('/assets/images/logo/') . '/' . $row->data;
            } else {
                $tempRow[$row->type] = $row->data;
            }
        }

        if (collect(Auth::guard('sanctum')->user())->isNotEmpty()) {
            $loggedInUserId = Auth::guard('sanctum')->user()->id;
            update_subscription($loggedInUserId);

            $customer_data = Customer::find($loggedInUserId);
            if ($customer_data->isActive == 0) {

                $tempRow['is_active'] = false;
            } else {
                $tempRow['is_active'] = true;
            }
            if ($row->type == "seo_settings") {

                $tempRow[$row->type] = $row->data == 1 ? true : false;
            }

            $customer = Customer::select('id', 'subscription', 'is_premium')
                ->where(function ($query) {
                    $query->where('subscription', 1)
                        ->orWhere('is_premium', 1);
                })
                ->find($loggedInUserId);



            if (($customer)) {
                $tempRow['is_premium'] = $customer->is_premium == 1 ? true : ($customer->subscription == 1 ? true : false);

                $tempRow['subscription'] = $customer->subscription == 1 ? true : false;
            } else {

                $tempRow['is_premium'] = false;
                $tempRow['subscription'] = false;
            }
        }
        $language = Language::select('code', 'name')->get();
        $user_data = User::find(1);
        $tempRow['admin_name'] = $user_data->name;
        $tempRow['admin_image'] = url('/assets/images/faces/2.jpg');
        $tempRow['demo_mode'] = env('DEMO_MODE');
        $tempRow['languages'] = $language;
        $tempRow['img_placeholder'] = url('/assets/images/placeholder.svg');


        $tempRow['min_price'] = DB::table('propertys')
            ->selectRaw('MIN(price) as min_price')
            ->value('min_price');


        $tempRow['max_price'] = DB::table('propertys')
            ->selectRaw('MAX(price) as max_price')
            ->value('max_price');

        if (!empty($result)) {
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['data'] = $tempRow;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }
    //* END :: Get System Setting   *//


    //* START :: user_signup   *//
    public function user_signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'firebase_id' => 'required',


        ]);

        if (!$validator->fails()) {
            $type = $request->type;
            $firebase_id = $request->firebase_id;

            $user = Customer::where('firebase_id', $firebase_id)->where('logintype', $type)->get();
            if ($user->isEmpty()) {
                $saveCustomer = new Customer();
                $saveCustomer->name = isset($request->name) ? $request->name : '';


                if (!$request->type == 0) {
                    $saveCustomer->email = isset($request->email) ? $request->email : '';
                } else {
                    $saveCustomer->email = '';
                }
                $saveCustomer->mobile = isset($request->mobile) ? $request->mobile : '';
                $saveCustomer->slug_id = generateUniqueSlug($request->name, 5);
                $saveCustomer->profile = isset($request->profile) ? $request->profile : '';
                $saveCustomer->user_document = isset($request->user_document) ? $request->user_document : '';



                $saveCustomer->fcm_id = isset($request->fcm_id) ? $request->fcm_id : '';
                $saveCustomer->logintype = isset($request->type) ? $request->type : '';
                $saveCustomer->address = isset($request->address) ? $request->address : '';
                $saveCustomer->firebase_id = isset($request->firebase_id) ? $request->firebase_id : '';
                //login Type
                //Login Type Email == 0
                //Login Type Gmail == 1
                //Login Type Apple == 2

                if ($request->type == 0) {
                    $saveCustomer->otp_verified = isset($request->otp_verified) ? $request->otp_verified : 0; // Hash the password for email login
                }

                $saveCustomer->about_me = isset($request->about_me) ? $request->about_me : '';
                $saveCustomer->facebook_id = isset($request->facebook_id) ? $request->facebook_id : '';
                $saveCustomer->twiiter_id = isset($request->twiiter_id) ? $request->twiiter_id : '';
                $saveCustomer->instagram_id = isset($request->instagram_id) ? $request->instagram_id : '';
                $saveCustomer->youtube_id = isset($request->youtube_id) ? $request->youtube_id : '';


                $saveCustomer->latitude = isset($request->latitude) ? $request->latitude : '';
                $saveCustomer->longitude = isset($request->longitude) ? $request->longitude : '';
                $saveCustomer->notification = 1;


                $saveCustomer->about_me = isset($request->about_me) ? $request->about_me : '';
                $saveCustomer->facebook_id = isset($request->facebook_id) ? $request->facebook_id : '';
                $saveCustomer->twiiter_id = isset($request->twiiter_id) ? $request->twiiter_id : '';
                $saveCustomer->instagram_id = isset($request->instagram_id) ? $request->instagram_id : '';
                $saveCustomer->isActive = 1;
                $saveCustomer->doc_verification_status = 0; //0:not verified
                //false





                $destinationPath = public_path('images') . config('global.USER_DOCUMENT');
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                // image upload

                if ($request->hasFile('profile')) {
                    $profile = $request->file('profile');
                    $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                    $profile->move($destinationPath, $imageName);
                    $saveCustomer->profile = $imageName;
                } else {
                    $saveCustomer->profile = $request->profile;
                }



                // Document Verification  Upload

                if ($request->hasFile('user_document')) {
                    $user_document = $request->file('user_document');
                    $imageName = microtime(true) . "." . $user_document->getClientOriginalExtension();
                    $user_document->move($destinationPath, $imageName);
                    $saveCustomer->user_document = $imageName;
                } else {
                    $saveCustomer->user_document = $request->user_document;
                }

                $saveCustomer->save();
                // Create a new personal access token for the user
                $token = $saveCustomer->createToken('token-name');

                $start_date =  Carbon::now();
                $package = Package::find(1);

                if ($package && $package->status == 1) {
                    $user_package = new UserPurchasedPackage();
                    $user_package->modal()->associate($saveCustomer);
                    $user_package->package_id = 1;
                    $user_package->start_date = $start_date;
                    $user_package->end_date =  Carbon::now()->addDays($package->duration);
                    $user_package->save();

                    $saveCustomer->subscription = 1;
                    $saveCustomer->update();
                }


                $response['error'] = false;
                $response['message'] = 'User Register Successfully';

                $credentials = Customer::find($saveCustomer->id);
                $credentials = Customer::where('firebase_id', $firebase_id)->where('logintype', $type)->first();

                $response['token'] = $token->plainTextToken;
                $response['data'] = $credentials;
            } else {
                $credentials = Customer::where('firebase_id', $firebase_id)->where('logintype', $type)->first();
                if ($credentials->isActive == 0) {
                    $response['error'] = true;
                    $response['message'] = 'Account Deactivated by Administrative please connect to them';
                    $response['is_active'] = false;
                    return response()->json($response);
                }
                $credentials->update();
                $token = $credentials->createToken('token-name');


                // Update or add FCM ID in UserToken for Current User
                if ($request->has('fcm_id') && !empty($request->fcm_id)) {
                    Usertokens::updateOrCreate(
                        ['fcm_id' => $request->fcm_id],
                        ['customer_id' => $credentials->id,]
                    );
                }
                $response['error'] = false;
                $response['message'] = 'Login Successfully';
                $response['token'] = $token->plainTextToken;
                $response['data'] = $credentials;
            }
        } else {
            $response['error'] = true;
            $response['message'] = 'Please fill all data and Submit';
        }
        return response()->json($response);
    }
    // check_otp_verified
    public function check_otp_verified(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'phone_number' => 'nullable', // Removed unique validation
            'firebase_id' => 'nullable',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'error' => false,
                'message' => $validator->errors(),
            ], 200); // 400 Bad Request
        }

        // Get the request data
        $firebase_id = $request->firebase_id;
        $phone_number = $request->phone_number;

        // Check if the user exists with the given firebase_id and phone number
        $user = Customer::orWhere('firebase_id', $firebase_id)
            ->orWhere('mobile', $phone_number)
            ->first();

        // If user exists, return the user details
        if ($user) {
            return response()->json([
                'error' => false,
                'message' => 'User found.',
                'data' => [$user], // You can customize the returned fields if needed
            ], 200); // 200 OK
        }

        // If no user found, return an error message
        return response()->json([
            'error' => false,
            'message' => 'User not found.',
            'data' => []
        ], 200); // 404 Not Found
    }




    //* START :: get_slider   *//
    public function get_slider(Request $request)
    {
        $tempRow = array();
        $slider = Slider::select('id', 'image', 'sequence', 'category_id', 'propertys_id')->orderBy('sequence', 'ASC')->get();
        if (!$slider->isEmpty()) {
            foreach ($slider as $row) {
                $property = Property::with('parameters')->find($row->propertys_id);

                if (collect($property)->isNotEmpty()) {
                    $tempRow['id'] = $row->id;
                    $tempRow['sequence'] = $row->sequence;
                    $tempRow['category_id'] = $row->category_id;
                    $tempRow['propertys_id'] = $row->propertys_id;
                    $tempRow['image'] = $row->image;

                    $tempRow['parameters'] = [];
                    $tempRow['video_link'] = $property->video_link;
                    $tempRow['slug_id'] = $property->slug_id;
                    $tempRow['property_title'] = $property->title;
                    $tempRow['property_title_image'] = $property->title_image;
                    $tempRow['property_price'] = $property->price;
                    $tempRow['property_type'] = $property->propery_type;

                    if (collect($property->parameters)->isNotEmpty()) {
                        foreach ($property->parameters as $res) {
                            $res = (object)$res;
                            if (is_string($res->value) && is_array(json_decode($res->value, true))) {
                                $value = json_decode($res->value, true);
                            } else {
                                if ($res->type_of_parameter == "file") {
                                    if ($res->value == "null") {
                                        $value = "";
                                    } else {
                                        $value = url('') . config('global.IMG_PATH') . config('global.PARAMETER_IMG_PATH') . '/' .  $res->value;
                                    }
                                } else {
                                    if ($res->value == "null") {
                                        $value = "";
                                    } else {
                                        $value = $res->value;
                                    }
                                }
                            }

                            $parameter = [
                                'id' => $res->id,
                                'name' => $res->name,
                                'value' => $value,
                            ];
                            array_push($tempRow['parameters'], $parameter);
                        }
                    }

                    $advertisement = Advertisement::where(['property_id' => $row->propertys_id, 'type' => 'Slider'])->first();
                    if ($advertisement) {
                        if ($advertisement->status == 0 && $advertisement->is_enable == 1) {
                            $tempRow['promoted'] = true;
                        } else {
                            $tempRow['promoted'] = false;
                        }
                    } else {
                        $tempRow['promoted'] = false;
                    }
                }
                $rows[] = $tempRow;
            }


            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['data'] = $rows;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }

    //* END :: get_slider   *//


    //* START :: get_categories   *//
    public function get_categories(Request $request)
    {
        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;

        $categories = Category::select('id', 'category', 'image', 'parameter_types', 'meta_title', 'meta_description', 'meta_keywords', 'slug_id')->where('status', '1')->withCount(['properties' => function ($q) {
            $q->where('status', 1);
        }]);

        if (isset($request->search) && !empty($request->search)) {
            $search = $request->search;
            $categories->where('category', 'LIKE', "%$search%");
        }

        if (isset($request->id) && !empty($request->id)) {
            $id = $request->id;
            $categories->where('id', $id);
        }
        if (isset($request->slug_id) && !empty($request->slug_id)) {
            $id = $request->slug_id;
            $categories->where('slug_id', $request->slug_id);
        }

        $total = $categories->get()->count();
        $result = $categories->orderBy('sequence', 'ASC')->skip($offset)->take($limit)->get();

        $result->map(function ($result) {
            $result['meta_image'] = $result->image;
        });
        // $categoriesWithCount = Category::withCount('properties')->get();


        if (!$result->isEmpty()) {
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            foreach ($result as $row) {
                $parameterData = parameterTypesByCategory($row->id);
                if (collect($parameterData)->isNotEmpty()) {
                    $parameterData = $parameterData->map(function ($item) {
                        unset($item->assigned_parameter);
                        return $item;
                    });
                }
                $row->parameter_types = $parameterData;
            }

            $response['total'] = $total;
            $response['data'] = $result;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }
    //* END :: get_slider   *//







    //* START :: about_meofile   *//
    public function update_profile(Request $request)
    {
        try {
            DB::beginTransaction();
            $currentUser = Auth::user();
            $customer =  Customer::find($currentUser->id);

            if (!empty($customer)) {

                // update the Data passed in payload
                $fieldsToUpdate = $request->only([
                    'name',
                    'email',
                    'mobile',
                    'fcm_id',
                    'address',
                    'notification',
                    'about_me',
                    'facebook_id',
                    'twiiter_id',
                    'instagram_id',
                    'youtube_id',
                    'latitude',
                    'longitude',
                    'city',
                    'state',
                    'country',
                    'user_document',
                    'doc_verification_status',
                    'otp_verified'
                ]);


                //    $    intval($request->otp_verified)


                $customer->update($fieldsToUpdate);

                if ($request->has('fcm_id') && !empty($request->fcm_id)) {
                    Usertokens::updateOrCreate(
                        ['fcm_id' => $request->fcm_id],
                        ['customer_id' => $customer->id,]
                    );
                }

                // Update Profile
                if ($request->hasFile('profile')) {
                    $destinationPath = public_path('images') . config('global.USER_IMG_PATH');
                    if (!is_dir($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }

                    $old_image = $customer->profile;
                    $profile = $request->file('profile');
                    $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();

                    if ($profile->move($destinationPath, $imageName)) {
                        $customer->profile = $imageName;
                        if ($old_image != '') {
                            if (file_exists(public_path('images') . config('global.USER_IMG_PATH') . $old_image)) {
                                unlink(public_path('images') . config('global.USER_IMG_PATH') . $old_image);
                            }
                        }
                        $customer->update();
                    }
                }

                // Update User Documnet
                if ($request->hasFile('user_document')) {
                    $destinationPath = public_path('images') . config('global.USER_DOCUMENT');
                    if (!is_dir($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }

                    $old_image = $customer->user_document;
                    $document = $request->file('user_document');
                    $imageName = microtime(true) . "." . $document->getClientOriginalExtension();

                    if ($document->move($destinationPath, $imageName)) {
                        $customer->user_document = $imageName;
                        if ($old_image != '') {
                            if (file_exists(public_path('images') . config('global.USER_IMG_PATH') . $old_image)) {
                                unlink(public_path('images') . config('global.USER_IMG_PATH') . $old_image);
                            }
                        }
                        $customer->update();
                    }
                }

                // intval($fieldsToUpdate->otp_verified);


                DB::commit();
                // return response()->json(['error' => false, 'data' => $request->otp_verified]);
                return response()->json(['error' => false, 'data' => $customer]);
            } else {
                return response()->json(['error' => false, 'message' => "No data found!", 'data' => []]);
            }
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['error' => true, 'message' => 'Something Went Wrong'], 500);
        }
    }

    //* END :: update_profile   *//


    //* START :: document_verification_request   *//

    public function document_verification_request(Request $request)
    {
        try {
            // Get the current authenticated user
            $currentUser = Auth::user();

            $userVerificationDoc = Customer::find($currentUser->id);

            if (!$userVerificationDoc) {
                return response()->json(['error' => 'Customer not found.'], 404);
            }

            // Validate the incoming request fields and file
            $request->validate(rules: [
                'user_document' => 'required|file|mimes:jpg,jpeg,png,pdf,svg|max:5000',
            ]);

            // Check if a file was uploaded
            if ($request->hasFile('user_document')) {
                $verificationImage = $request->file('user_document');
                $imageName = microtime(true) . "." . $verificationImage->getClientOriginalExtension();


                $path = $verificationImage->storeAs(config('global.USER_IMG_PATH'), $imageName);

                $userVerificationDoc->user_document = $imageName;
                if (!empty($userVerificationDoc->getOriginal('user_document'))) {
                    Storage::delete(config('global.USER_IMG_PATH') . '/' . $userVerificationDoc->getOriginal('user_document'));
                }


                $userVerificationDoc->save();

                return response()->json(['message' => 'Document uploaded and updated successfully.']);
            } else {
                return response()->json(['error' => 'No document file uploaded.'], 400);
            }
        } catch (\Exception $e) {

            return response()->json(['error' => 'Document verification failed: ' . $e->getMessage()], 500);
        }
    }

    //* END :: document_verification_request   *//



    //* START :: get_user_by_id   *//
    public function getUserData()
    {
        try {
            // Get LoggedIn User Data from Toke
            $userData = Auth::user();
            // Check the User Data is not Empty
            if (collect($userData)->isNotEmpty()) {
                $response['error'] = false;
                $response['data'] = $userData;
            } else {
                $response['error'] = false;
                $response['message'] = "No data found!";
                $response['data'] = [];
            }
            return response()->json($response);
        } catch (Exception $e) {
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }
    //* END :: get_user_by_id   *//


    //* START :: get_property   *//
    public function get_property(Request $request)
    {
        // Set default offset and limit for pagination
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);

        // Get the current user ID if authenticated
        $current_user = Auth::guard('sanctum')->user() ? Auth::guard('sanctum')->user()->id : null;

        // Start building the property query with related models
        $property = Property::with('customer', 'user', 'category:id,category,image,slug_id', 'assignfacilities.outdoorfacilities', 'parameters', 'favourite', 'interested_users')
            ->where('status', 1);

        // Handle price filtering
        $min_price = (float)($request->input('min_price', 0));
        $max_price = (float)($request->input('max_price', Property::max('price')));

        // Debugging - Check price values
        Log::info("Min Price: $min_price, Max Price: $max_price");

        if ($min_price > $max_price) {
            return response()->json(['error' => true, 'message' => 'Min price cannot be greater than Max price.'], 400);
        }

        // Apply the price filter
        $property = $property->whereBetween('price', [$min_price, $max_price]);

        // Other filtering conditions
        if ($request->has('parameter_id') && !empty($request->parameter_id)) {
            $property = $property->whereHas('parameters', function ($q) use ($request) {
                $q->where('parameter_id', $request->parameter_id);
            });
        }

        if ($request->has('property_type') && !empty($request->property_type)) {
            $property = $property->where('propery_type', $request->property_type);
        }

        // Date filters
        if ($request->has('posted_since') && !empty($request->posted_since)) {
            $posted_since = $request->posted_since;

            if ($posted_since == 0) {
                $startDateOfWeek = Carbon::now()->subWeek()->startOfWeek();
                $endDateOfWeek = Carbon::now()->subWeek()->endOfWeek();
                $property = $property->whereBetween('created_at', [$startDateOfWeek, $endDateOfWeek]);
            } elseif ($posted_since == 1) {
                $yesterdayDate = Carbon::yesterday();
                $property = $property->whereDate('created_at', $yesterdayDate);
            }
        }

        // Additional filters
        if ($request->has('category_id') && !empty($request->category_id)) {
            $property = $property->where('category_id', $request->category_id);
        }

        if ($request->has('id') && !empty($request->id)) {
            $property = $property->where('id', $request->id);
        }

        if ($request->has('country') && !empty($request->country)) {
            $property = $property->where('country', $request->country);
        }

        if ($request->has('state') && !empty($request->state)) {
            $property = $property->where('state', $request->state);
        }

        if ($request->has('city') && !empty($request->city)) {
            $property = $property->where('city', $request->city);
        }

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $property = $property->where(function ($query) use ($search) {
                $query->where('title', 'LIKE', "%$search%")
                    ->orWhere('address', 'LIKE', "%$search%")
                    ->orWhereHas('category', function ($query1) use ($search) {
                        $query1->where('category', 'LIKE', "%$search%");
                    });
            });
        }

        try {
            // Order and pagination
            $total = $property->count();
            $result = $property->orderBy('id', 'DESC')->skip($offset)->take($limit)->get();

            // Prepare the response
            if (!$result->isEmpty()) {
                $property_details = get_property_details($result, $current_user);

                $response = [
                    'error' => false,
                    'message' => "Data fetched successfully",
                    'total' => $total,
                    'data' => $property_details,
                ];
            } else {
                $response = [
                    'error' => false,
                    'message' => "No data found!",
                    'data' => [],
                ];
            }
        } catch (\Exception $e) {
            // Log the error and return a response
            Log::error("Error fetching properties: " . $e->getMessage());
            return response()->json(['error' => true, 'message' => 'An error occurred while fetching properties. Please try again later.'], 500);
        }

        return response()->json($response);
    }




    //* START :: post_property   *//
    public function post_property(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'category_id' => 'required',
            'price' => ['required', function ($attribute, $value, $fail) {
                if ($value > 1000000000000) {
                    $fail("The $attribute must not exceed one trillion that is 1000000000000.");
                }
            }],
            'property_type' => 'required',
            'address' => 'required',
            'title_image' => 'required|file|max:3000|mimes:jpeg,png,jpg',
            'document' => 'required|image|max:3000|mimes:jpeg,png,jpg',
            'whatsapp_number' => 'required|max:15|min:10',
            'square_yd' => 'required',


        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }

        try {
            DB::beginTransaction();
            $loggedInUserId = Auth::user()->id;

            // Get Current Package with checking the property limit
            $currentPackage = $this->getCurrentPackage($loggedInUserId, 1);

            if (!($currentPackage)) {
                $response['error'] = false;
                $response['message'] = 'Package not found';
                return response()->json($response);
            } else {
                // // Check the prop_status column if there is zero than property limit is over
                // if ($currentPackage->prop_status == 0) {
                //     $response['error'] = false;
                //     $response['message'] = 'Package not found for add property';
                //     return response()->json($response);
                // }

                $saveProperty = new Property();
                $saveProperty->category_id = $request->category_id;
                $saveProperty->slug_id = generateUniqueSlug($request->title, 1);
                $saveProperty->title = $request->title;
                $saveProperty->description = $request->description;
                $saveProperty->address = $request->address;
                $saveProperty->client_address = (isset($request->client_address)) ? $request->client_address : '';
                $saveProperty->propery_type = $request->property_type;
                $saveProperty->price = $request->price;
                $saveProperty->country = (isset($request->country)) ? $request->country : '';
                $saveProperty->state = (isset($request->state)) ? $request->state : '';
                $saveProperty->city = (isset($request->city)) ? $request->city : '';
                $saveProperty->latitude = (isset($request->latitude)) ? $request->latitude : '';
                $saveProperty->longitude = (isset($request->longitude)) ? $request->longitude : '';
                $saveProperty->rentduration = (isset($request->rentduration)) ? $request->rentduration : '';
                $saveProperty->meta_title = (isset($request->meta_title)) ? $request->meta_title : '';
                $saveProperty->meta_description = (isset($request->meta_description)) ? $request->meta_description : '';
                $saveProperty->meta_keywords = (isset($request->meta_keywords)) ? $request->meta_keywords : '';
                $saveProperty->added_by = $loggedInUserId;
                $saveProperty->status = (isset($request->status)) ? $request->status : 0;
                $saveProperty->video_link = (isset($request->video_link)) ? $request->video_link : "";
                $saveProperty->square_yd = (isset($request->square_yd)) ? $request->square_yd : "";
                $saveProperty->whatsapp_number = (isset($request->whatsapp_number)) ? $request->whatsapp_number : "";
                $saveProperty->package_id = $request->package_id;
                $saveProperty->post_type = 1;

                //Title Image
                if ($request->hasFile('title_image')) {
                    $destinationPath = public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH');
                    if (!is_dir($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }
                    $file = $request->file('title_image');
                    $imageName = microtime(true) . "." . $file->getClientOriginalExtension();
                    $titleImageName = handleFileUpload($request, 'title_image', $destinationPath, $imageName);
                    $saveProperty->title_image = $titleImageName;
                } else {
                    $saveProperty->title_image  = '';
                }

                // document Image
                if ($request->hasFile('document')) {
                    $destinationPath = public_path('images') . config('global.PROJECT_Documnet_PATH');
                    if (!is_dir($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }
                    $file = $request->file('document');
                    $imageName = microtime(true) . "." . $file->getClientOriginalExtension();
                    $documentImageName = handleFileUpload($request, 'document', $destinationPath, $imageName);
                    $saveProperty->document = $documentImageName;
                } else {
                    $saveProperty->document  = '';
                }


                // three_d_image
                if ($request->hasFile('three_d_image')) {
                    $destinationPath = public_path('images') . config('global.3D_IMG_PATH');
                    if (!is_dir($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }
                    $file = $request->file('three_d_image');
                    $imageName = microtime(true) . "." . $file->getClientOriginalExtension();
                    $three_dImage = handleFileUpload($request, 'three_d_image', $destinationPath, $imageName);
                    $saveProperty->three_d_image = $three_dImage;
                } else {
                    $saveProperty->three_d_image  = '';
                }


                $saveProperty->is_premium = isset($request->is_premium) ? ($request->is_premium == "true" ? 1 : 0) : 0;
                $saveProperty->save();


                $newPropertyLimitCount = 0;
                // Increment the property limit count
                $newPropertyLimitCount = $currentPackage->used_limit_for_property + 1;
                if ($newPropertyLimitCount >= $currentPackage->package->property_limit) {
                    $addPropertyStatus = 0;
                } else if ($currentPackage->package->property_limit == null) {
                    $addPropertyStatus = 1;
                } else {
                    $addPropertyStatus = 1;
                }
                // Update the Limit and status
                UserPurchasedPackage::where('id', $currentPackage->id)->update(['used_limit_for_property' => $newPropertyLimitCount, 'prop_status' => $addPropertyStatus]);






                $destinationPathForParam = public_path('images') . config('global.PARAMETER_IMAGE_PATH');
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                if ($request->facilities) {
                    foreach ($request->facilities as $key => $value) {

                        $facilities = new AssignedOutdoorFacilities();
                        $facilities->facility_id = $value['facility_id'];
                        $facilities->property_id = $saveProperty->id;
                        $facilities->distance = $value['distance'];
                        $facilities->save();
                    }
                }
                if ($request->parameters) {
                    foreach ($request->parameters as $key => $parameter) {

                        $AssignParameters = new AssignParameters();

                        $AssignParameters->modal()->associate($saveProperty);

                        $AssignParameters->parameter_id = $parameter['parameter_id'];
                        if ($request->hasFile('parameters.' . $key . '.value')) {

                            $profile = $request->file('parameters.' . $key . '.value');
                            $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                            $profile->move($destinationPathForParam, $imageName);
                            $AssignParameters->value = $imageName;
                        } else if (filter_var($parameter['value'], FILTER_VALIDATE_URL)) {


                            $ch = curl_init($parameter['value']);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $fileContents = curl_exec($ch);
                            curl_close($ch);

                            $filename
                                = microtime(true) . basename($parameter['value']);

                            file_put_contents($destinationPathForParam . '/' . $filename, $fileContents);
                            $AssignParameters->value = $filename;
                        } else {
                            $AssignParameters->value = $parameter['value'];
                        }

                        $AssignParameters->save();
                    }
                }

                /// START :: UPLOAD GALLERY IMAGE
                $FolderPath = public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH');
                if (!is_dir($FolderPath)) {
                    mkdir($FolderPath, 0777, true);
                }


                $destinationPath = public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . "/" . $saveProperty->id;
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                if ($request->hasfile('gallery_images')) {


                    foreach ($request->file('gallery_images') as $file) {


                        $name = time() . rand(1, 100) . '.' . $file->extension();
                        $file->move($destinationPath, $name);

                        $gallary_image = new PropertyImages();
                        $gallary_image->image = $name;
                        $gallary_image->propertys_id = $saveProperty->id;

                        $gallary_image->save();
                    }
                }

                /// END :: UPLOAD GALLERY IMAGE

                $result = Property::with('customer')->with('category:id,category,image')->with('assignfacilities.outdoorfacilities')->with('favourite')->with('parameters')->with('interested_users')->where('id', $saveProperty->id)->get();
                $property_details = get_property_details($result);

                DB::commit();

                $response['error'] = false;
                $response['message'] = 'Property Post Successfully';
                $response['data'] = $property_details;
            }
        } catch (Exception $e) {
            DB::rollback();
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
        return response()->json($response);
    }
    //* END :: post_property   *//


    //otp send //
    public function send_otp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);
        do {
            $otp = rand(100000, 999999);
            $exists = Otp::where('otp_code', $otp)->exists();
        } while ($exists);

        $message = 'Your OTP code is: ' . $otp;
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'saeedmoto3@gmail.com';
            $mail->Password = 'ulng jmaf kyoe wkki';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('saeedmoto3@gmail.com', 'Your Name');
            $mail->addAddress($request->email);

            $mail->isHTML(true);
            $mail->Subject = 'Your OTP Code';
            $mail->Body    = $message;

            $mail->send();
            $otpRecord = Otp::where('otp_email', $request->email)->first();

            if ($otpRecord) {
                $otpRecord->otp_code = $otp;
                $otpRecord->updated_at = now();
                $otpRecord->save();
            } else {
                $otpRecord = new Otp();
                $otpRecord->otp_email = $request->email;
                $otpRecord->otp_code = $otp;
                $otpRecord->created_at = now();
                $otpRecord->updated_at = now();
                $otpRecord->save();
            }
            return response()->json(['error' => false, 'message' => 'OTP sent to email and saved in the OTP record.']);
        } catch (Exception $e) {
            return response()->json(['error' => true, 'message' => 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo], 500);
        }
    }



    // Verify Otp Start //

    public function verifyOtp(Request $request)
    {
        // Validate input
        $request->validate([

            'otp' => 'required|digits:6',
        ]);

        // Find the OTP record for the email
        $otpRecord = Otp::where('otp_code', $request->otp)
            ->first();

        if ($otpRecord) {
            // OTP matched, delete it from the database
            $otpRecord->delete();

            return response()->json(['message' => 'OTP verified successfully.']);
        } else {
            // OTP did not match
            return response()->json(['error' => false, 'message' => 'Invalid OTP.'], 200);
        }
    }


    // Verify Otp End //

    //* START :: update_post_property   *//
    public function update_post_property(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'id' => 'required',
            'action_type' => 'required',
            'price' => ['nullable', function ($attribute, $value, $fail) {
                if ($value > 1000000000000) {
                    $fail("The $attribute must not exceed one trillion that is 1000000000000.");
                }
            }],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }
        try {
            DB::beginTransaction();
            $current_user = Auth::user()->id;
            $id = $request->id;
            $action_type = $request->action_type;
            if ($request->slug_id) {

                $property = Property::where('added_by', $current_user)->where('slug_id', $request->slug_id)->first();
                if (!$property) {
                    $property = Property::where('added_by', $current_user)->find($id);
                }
            } else {
                $property = Property::where('added_by', $current_user)->find($id);
            }

            // $property = Property::where('added_by', $current_user)->find($id);
            if (($property)) {
                // 0: Update 1: Delete
                if ($action_type == 0) {

                    $destinationPath = public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH');
                    if (!is_dir($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }

                    if (isset($request->category_id)) {
                        $property->category_id = $request->category_id;
                    }

                    if (isset($request->title)) {
                        $property->title = $request->title;
                        $property->slug_id = generateUniqueSlug($request->title, 1);
                    }

                    if (isset($request->description)) {
                        $property->description = $request->description;
                    }

                    if (isset($request->whatsapp_number)) {
                        $property->whatsapp_number = $request->whatsapp_number;
                    }
                    if (isset($request->square_yd)) {
                        $property->square_yd = $request->square_yd;
                    }

                    if (isset($request->address)) {
                        $property->address = $request->address;
                    }

                    if (isset($request->client_address)) {
                        $property->client_address = $request->client_address;
                    }

                    if (isset($request->property_type)) {
                        $property->propery_type = $request->property_type;
                    }

                    if (isset($request->price)) {
                        $property->price = $request->price;
                    }
                    if (isset($request->country)) {
                        $property->country = $request->country;
                    }
                    if (isset($request->state)) {
                        $property->state = $request->state;
                    }
                    if (isset($request->city)) {
                        $property->city = $request->city;
                    }
                    if (isset($request->status)) {
                        $property->status = $request->status;
                    }
                    if (isset($request->latitude)) {
                        $property->latitude = $request->latitude;
                    }
                    if (isset($request->longitude)) {
                        $property->longitude = $request->longitude;
                    }
                    if (isset($request->rentduration)) {
                        $property->rentduration = $request->rentduration;
                    }
                    $property->meta_title = $request->meta_title;
                    $property->meta_description = $request->meta_description;
                    $property->meta_keywords = $request->meta_keywords;
                    // if (isset($request->meta_title)) {
                    //     $property->meta_title = $request->meta_title;
                    // }
                    // if (isset($request->meta_description)) {
                    //     $property->meta_description = $request->meta_description;
                    // }
                    // if (isset($request->meta_keywords)) {
                    //     $property->meta_keywords = $request->meta_keywords;
                    // }

                    if (isset($request->is_premium)) {
                        $property->is_premium = $request->is_premium == "true" ? 1 : 0;
                    }


                    if ($request->hasFile('title_image')) {
                        $profile = $request->file('title_image');
                        $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                        $profile->move($destinationPath, $imageName);


                        if ($property->title_image != '') {
                            if (file_exists(public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH') .  $property->title_image)) {
                                unlink(public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH') . $property->title_image);
                            }
                        }
                        $property->title_image = $imageName;
                    }

                    if ($request->hasFile('document')) {
                        $profile = $request->file('document');
                        $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                        $profile->move($destinationPath, $imageName);


                        if ($property->document != '') {
                            if (file_exists(public_path('images') . config('global.PROJECT_Documnet_PATH') .  $property->document)) {
                                unlink(public_path('images') . config('global.PROJECT_Documnet_PATH') . $property->document);
                            }
                        }
                        $property->document = $imageName;
                    }



                    if ($request->hasFile('meta_image')) {
                        if (!empty($property->meta_image)) {

                            $url = $property->meta_image;

                            $relativePath = parse_url($url, PHP_URL_PATH);

                            if (file_exists(public_path()  . $relativePath)) {
                                unlink(public_path()  . $relativePath);
                            }
                        }

                        $destinationPath = public_path('images') . config('global.PROPERTY_SEO_IMG_PATH');
                        if (!is_dir($destinationPath)) {
                            mkdir($destinationPath, 0777, true);
                        }





                        $profile = $request->file('meta_image');
                        $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                        $profile->move($destinationPath, $imageName);



                        $property->meta_image = $imageName;
                    } else {
                        if (!empty($property->meta_image)) {

                            $url = $property->meta_image;

                            $relativePath = parse_url($url, PHP_URL_PATH);

                            if (file_exists(public_path()  . $relativePath)) {
                                unlink(public_path()  . $relativePath);
                            }
                        }
                        $property->meta_image = null;
                    }


                    if ($request->hasFile('three_d_image')) {
                        $destinationPath1 = public_path('images') . config('global.3D_IMG_PATH');
                        if (!is_dir($destinationPath1)) {
                            mkdir($destinationPath1, 0777, true);
                        }
                        $profile = $request->file('three_d_image');
                        $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                        $profile->move($destinationPath1, $imageName);


                        if ($property->title_image != '') {
                            if (file_exists(public_path('images') . config('global.3D_IMG_PATH') .  $property->title_image)) {
                                unlink(public_path('images') . config('global.3D_IMG_PATH') . $property->title_image);
                            }
                        }
                        $property->three_d_image = $imageName;
                    }
                    if ($request->parameters) {
                        $destinationPathforparam = public_path('images') . config('global.PARAMETER_IMAGE_PATH');
                        if (!is_dir($destinationPath)) {
                            mkdir($destinationPath, 0777, true);
                        }

                        foreach ($request->parameters as $key => $parameter) {
                            $AssignParameters = AssignParameters::where('modal_id', $property->id)->where('parameter_id', $parameter['parameter_id'])->pluck('id');
                            if (count($AssignParameters)) {
                                $update_data = AssignParameters::find($AssignParameters[0]);
                                if ($request->hasFile('parameters.' . $key . '.value')) {
                                    $profile = $request->file('parameters.' . $key . '.value');
                                    $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                                    $profile->move($destinationPathforparam, $imageName);
                                    $update_data->value = $imageName;
                                } else if (filter_var($parameter['value'], FILTER_VALIDATE_URL)) {
                                    $ch = curl_init($parameter['value']);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    $fileContents = curl_exec($ch);
                                    curl_close($ch);
                                    $filename = microtime(true) . basename($parameter['value']);
                                    file_put_contents($destinationPathforparam . '/' . $filename, $fileContents);
                                    $update_data->value = $filename;
                                } else {
                                    $update_data->value = $parameter['value'];
                                }
                                $update_data->save();
                            } else {

                                $AssignParameters = new AssignParameters();

                                $AssignParameters->modal()->associate($property);

                                $AssignParameters->parameter_id = $parameter['parameter_id'];
                                if ($request->hasFile('parameters.' . $key . '.value')) {

                                    $profile = $request->file('parameters.' . $key . '.value');
                                    $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                                    $profile->move($destinationPathforparam, $imageName);
                                    $AssignParameters->value = $imageName;
                                } else if (filter_var($parameter['value'], FILTER_VALIDATE_URL)) {


                                    $ch = curl_init($parameter['value']);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    $fileContents = curl_exec($ch);
                                    curl_close($ch);

                                    $filename
                                        = microtime(true) . basename($parameter['value']);

                                    file_put_contents($destinationPathforparam . '/' . $filename, $fileContents);
                                    $AssignParameters->value = $filename;
                                } else {
                                    $AssignParameters->value = $parameter['value'];
                                }

                                $AssignParameters->save();
                            }
                        }

                        // $AssignParameters->save();
                    }

                    if ($request->slug_id) {

                        $prop = Property::where('slug_id', $request->slug_id)->first();
                        $prop_id = $prop->id;
                        AssignedOutdoorFacilities::where('property_id', $prop->id)->delete();
                    } else {
                        $prop_id = $request->id;
                        AssignedOutdoorFacilities::where('property_id', $request->id)->delete();
                    }
                    // AssignedOutdoorFacilities::where('property_id', $request->id)->delete();
                    if ($request->facilities) {
                        foreach ($request->facilities as $key => $value) {



                            $facilities = new AssignedOutdoorFacilities();
                            $facilities->facility_id = $value['facility_id'];
                            $facilities->property_id = $prop_id;
                            $facilities->distance = $value['distance'];
                            $facilities->save();
                        }
                    }



                    $property->update();
                    $update_property = Property::with('customer')->with('category:id,category,image')->with('assignfacilities.outdoorfacilities')->with('favourite')->with('parameters')->with('interested_users')->where('id', $request->id)->get();


                    /// START :: UPLOAD GALLERY IMAGE

                    $FolderPath = public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH');
                    if (!is_dir($FolderPath)) {
                        mkdir($FolderPath, 0777, true);
                    }

                    $destinationPath = public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . "/" . $property->id;
                    if (!is_dir($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }
                    if ($request->remove_gallery_images) {



                        foreach ($request->remove_gallery_images as $key => $value) {

                            $gallary_images = PropertyImages::find($value);


                            if (file_exists(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $gallary_images->propertys_id . '/' . $gallary_images->image)) {

                                unlink(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $gallary_images->propertys_id . '/' . $gallary_images->image);
                            }

                            $gallary_images->delete();
                        }
                    }
                    if ($request->hasfile('gallery_images')) {


                        foreach ($request->file('gallery_images') as $file) {
                            $name = time() . rand(1, 100) . '.' . $file->extension();
                            $file->move($destinationPath, $name);

                            PropertyImages::create([
                                'image' => $name,
                                'propertys_id' => $property->id,


                            ]);
                        }
                    }

                    /// END :: UPLOAD GALLERY IMAGE
                    $current_user = Auth::user()->id;

                    $property_details = get_property_details($update_property, $current_user);
                    $response['error'] = false;
                    $response['message'] = 'Property Update Succssfully';
                    $response['data'] = $property_details;
                } elseif ($action_type == 1) {
                    if ($property->delete()) {

                        $chat = Chats::where('property_id', $property->id);
                        if ($chat) {
                            $chat->delete();
                        }


                        $slider = Slider::where('propertys_id', $property->id);
                        if ($slider) {
                            $slider->delete();
                        }


                        $notifications = Notifications::where('propertys_id', $property->id);
                        if ($notifications) {
                            $notifications->delete();
                        }

                        if ($property->title_image != '') {
                            if (file_exists(public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH') . $property->title_image)) {
                                unlink(public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH') . $property->title_image);
                            }
                        }
                        foreach ($property->gallery as $row) {
                            if (PropertyImages::where('id', $row->id)->delete()) {
                                if ($row->image_url != '') {
                                    if (file_exists(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $property->id . "/" . $row->image)) {
                                        unlink(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $property->id . "/" . $row->image);
                                    }
                                }
                            }
                        }
                        rmdir(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $property->id);

                        Notifications::where('propertys_id', $id)->delete();


                        $slider = Slider::where('propertys_id', $id)->get();

                        foreach ($slider as $row) {
                            $image = $row->image;

                            if (Slider::where('id', $row->id)->delete()) {
                                if (file_exists(public_path('images') . config('global.SLIDER_IMG_PATH') . $image)) {
                                    unlink(public_path('images') . config('global.SLIDER_IMG_PATH') . $image);
                                }
                            }
                        }

                        $response['error'] = false;
                        $response['message'] =  'Delete Successfully';
                    } else {
                        $response['error'] = true;
                        $response['message'] = 'something wrong';
                    }
                }
            } else {
                $response['error'] = true;
                $response['message'] = 'No Data Found';
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }

        return response()->json($response);
    }
    //* END :: update_post_property   *//


    //* START :: remove_post_images   *//
    public function remove_post_images(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);

        if (!$validator->fails()) {
            $id = $request->id;
            $getImage = PropertyImages::where('id', $id)->first();
            $image = $getImage->image;
            $propertys_id =  $getImage->propertys_id;

            if (PropertyImages::where('id', $id)->delete()) {
                if (file_exists(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $propertys_id . "/" . $image)) {
                    unlink(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $propertys_id . "/" . $image);
                }
                $response['error'] = false;
            } else {
                $response['error'] = true;
            }

            $countImage = PropertyImages::where('propertys_id', $propertys_id)->get();
            if ($countImage->count() == 0) {
                rmdir(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $propertys_id);
            }

            $response['error'] = false;
            $response['message'] = 'Property Post Succssfully';
        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }

        return response()->json($response);
    }
    //* END :: remove_post_images   *//

    //* START :: set_property_inquiry   *//




    //* START :: get_notification_list   *//
    public function get_notification_list(Request $request)
    {
        $loggedInUserId = Auth::user()->id;
        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;

        $notificationQuery = Notifications::where("customers_id", $loggedInUserId)
            ->orWhere('send_type', '1')
            ->with('property:id,title_image')
            ->select('id', 'title', 'message', 'image', 'type', 'send_type', 'customers_id', 'propertys_id', 'created_at')
            ->orderBy('id', 'DESC');

        $result = $notificationQuery->clone()
            ->skip($offset)
            ->take($limit)
            ->get();

        $total = $notificationQuery->count();

        if (!$result->isEmpty()) {
            $result = $result->map(function ($notification) {
                $notification->created = $notification->created_at->diffForHumans();
                $notification->notification_image = !empty($notification->image) ? $notification->image : (!empty($notification->propertys_id) && !empty($notification->property) ? $notification->property->title_image : "");
                unset($notification->image);
                return $notification;
            });

            $response = [
                'error' => false,
                'total' => $total,
                'data' => $result->toArray(),
            ];
        } else {
            $response = [
                'error' => false,
                'message' => 'No data found!',
                'data' => [],
            ];
        }

        return response()->json($response);
    }
    //* END :: get_notification_list   *//




    //* START :: set_property_total_click   *//
    public function set_property_total_click(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required_without_all:project_id,project_slug_id,property_slug_id',
            'project_id' => 'required_without_all:property_id,project_slug_id,property_slug_id',
            'project_slug_id' => 'required_without_all:property_id,project_id,property_slug_id',
            'property_slug_id' => 'required_without_all:property_id,project_id,project_slug_id',
        ]);

        if (!$validator->fails()) {
            if (isset($request->project_id)) {
                // When project id is there
                $project = Projects::find($request->project_id);
                $project->increment('total_click');
            } else if ($request->property_id) {
                // When property id is there
                $Property = Property::find($request->property_id);
                $Property->increment('total_click');
            } else if (isset($request->project_slug_id)) {
                // When project slug is there
                $project = Projects::where('slug_id', $request->project_slug_id);
                $project->increment('total_click');
            } else if (isset($request->property_slug_id)) {
                // When property slug is there
                $Property = Property::where('slug_id', $request->property_slug_id);
                $Property->increment('total_click');
            }


            $response['error'] = false;
            $response['message'] = 'Update Succssfully';
        } else {
            $response['error'] = true;
            $response['message'] = $validator->errors()->first();
        }

        return response()->json($response);
    }
    //* END :: set_property_total_click   *//


    //* START :: delete_user   *//
    public function delete_user(Request $request)
    {
        try {
            DB::beginTransaction();
            $loggedInUserId = Auth::user()->id;

            Customer::find($loggedInUserId)->delete();
            Property::where('added_by', $loggedInUserId)->delete();

            Chats::where('sender_id', $loggedInUserId)->orWhere('receiver_id', $loggedInUserId)->delete();
            Notifications::where('customers_id', $loggedInUserId)->delete();
            Advertisement::where('customer_id', $loggedInUserId)->delete();
            UserPurchasedPackage::where('model_id', $loggedInUserId)->delete();

            DB::commit();
            $response['error'] = false;
            $response['message'] = 'Delete Successfully';
            return response()->json($response);
        } catch (Exception $e) {
            DB::rollBack();
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }
    //* END :: delete_user   *//
    public function bearerToken($request)
    {
        $header = $request->header('Authorization', '');
        if (Str::startsWith($header, 'Bearer ')) {
            return Str::substr($header, 7);
        }
    }
    //*START :: add favoutite *//
    public function add_favourite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'property_id' => 'required',


        ]);

        if (!$validator->fails()) {
            //add favourite
            $current_user = Auth::user()->id;
            if ($request->type == 1) {


                $fav_prop = Favourite::where('user_id', $current_user)->where('property_id', $request->property_id)->get();

                if (count($fav_prop) > 0) {
                    $response['error'] = false;
                    $response['message'] = "Property already add to favourite";
                    return response()->json($response);
                }
                $favourite = new Favourite();
                $favourite->user_id = $current_user;
                $favourite->property_id = $request->property_id;
                $favourite->save();
                $response['error'] = false;
                $response['message'] = "Property add to Favourite add successfully";
            }
            //delete favourite
            if ($request->type == 0) {
                Favourite::where('property_id', $request->property_id)->where('user_id', $current_user)->delete();

                $response['error'] = false;
                $response['message'] = "Property remove from Favourite  successfully";
            }
        } else {
            $response['error'] = true;
            $response['message'] = $validator->errors()->first();
        }


        return response()->json($response);
    }

    public function get_articles(Request $request)
    {
        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;
        $article = Article::with('category:id,category,slug_id')->select('id', 'slug_id', 'image', 'title', 'description', 'meta_title', 'meta_description', 'meta_keywords', 'category_id', 'created_at');

        if (isset($request->category_id)) {
            $category_id = $request->category_id;
            if ($category_id == 0) {
                $article = $article->clone()->where('category_id', '');
            } else {

                $article = $article->clone()->where('category_id', $category_id);
            }
        }

        if (isset($request->id)) {
            $similarArticles = $article->clone()->where('id', '!=', $request->id)->get();
            $article = $article->clone()->where('id', $request->id);
        } else if (isset($request->slug_id)) {
            $category = Category::where('slug_id', $request->slug_id)->first();
            if ($category) {
                $article = $article->clone()->where('category_id', $category->id);
            } else {
                $similarArticles = $article->clone()->where('slug_id', '!=', $request->slug_id)->get();
                $article = $article->clone()->where('slug_id', $request->slug_id);
            }
        }


        $total = $article->clone()->get()->count();
        $result = $article->clone()->orderBy('id', 'ASC')->skip($offset)->take($limit)->get();
        if (!$result->isEmpty()) {
            $result = $result->toArray();

            foreach ($result as &$item) {
                $item['meta_image'] = $item['image'];
                $item['created_at'] = Carbon::parse($item['created_at'])->diffForHumans();
            }

            $response['data'] = $result;
            $response['similar_articles'] = $similarArticles ?? array();
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['total'] = $total;
            $response['data'] = $result;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['total'] = $total;
            $response['data'] = [];
        }
        return response()->json($response);
    }



    public function store_advertisement(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'property_id' => 'required',

        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }

        try {
            DB::beginTransaction();
            $current_user = Auth::user()->id;

            $currentPackage = $this->getCurrentPackage($current_user, 2);

            if (!($currentPackage)) {
                $response['error'] = false;
                $response['message'] = 'Package not found';
                return response()->json($response);
            } else {
                // // Check the prop_status column if there is zero than property limit is over
                // if ($currentPackage->adv_status == 0) {
                //     $response['error'] = false;
                //     $response['message'] = 'Package not found for add property';
                //     return response()->json($response);
                // }

                $advertisementData = new Advertisement();

                $advertisementData->start_date = Carbon::now();
                if (isset($request->end_date)) {
                    $advertisementData->end_date = $request->end_date;
                } else {
                    $advertisementData->end_date = Carbon::now()->addDays($currentPackage->package->duration);
                }
                $advertisementData->package_id = $currentPackage->package_id;
                $advertisementData->type = $request->type;
                $advertisementData->property_id = $request->property_id;
                $advertisementData->customer_id = $current_user;
                $advertisementData->is_enable = false;
                $advertisementData->status = 1;

                $destinationPath = public_path('images') . config('global.ADVERTISEMENT_IMAGE_PATH');
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                // If Type is Slider then add new slider entry
                if ($request->type == 'Slider') {
                    $destinationPath_slider = public_path('images') . config('global.SLIDER_IMG_PATH');
                    if (!is_dir($destinationPath_slider)) {
                        mkdir($destinationPath_slider, 0777, true);
                    }
                    $slider = new Slider();
                    if ($request->hasFile('image')) {
                        $file = $request->file('image');
                        $name = time() . '.' . $file->extension();
                        $file->move($destinationPath_slider, $name);
                        $sliderImageName = $name;
                        $slider->image = $sliderImageName;
                    } else {
                        $slider->image = '';
                    }
                    $categoryId = Property::where('id', $request->property_id)->pluck('category_id')->first();
                    $slider->category_id = isset($request->category_id) ? $request->category_id : $categoryId;
                    $slider->propertys_id = $request->property_id;
                    $slider->save();
                }

                $advertisementData->image = "";
                // save
                $advertisementData->save();

                $result = Property::with('customer')->with('category:id,category,image')->with('favourite')->with('parameters')->with('interested_users')->where('id', $request->property_id)->get();
                $propertyDetails = get_property_details($result);

                $newAdvertisementLimitCount = 0;
                // Increment the property limit count
                $newAdvertisementLimitCount = $currentPackage->used_limit_for_advertisement + 1;
                if ($currentPackage->package->advertisement_limit == null) {
                    $addAdvertisementStatus = 1;
                } else if ($newAdvertisementLimitCount >= $currentPackage->package->advertisement_limit) {
                    $addAdvertisementStatus = 0;
                } else {
                    $addAdvertisementStatus = 1;
                }
                // Update the Limit and status
                UserPurchasedPackage::where('id', $currentPackage->id)->update(['used_limit_for_advertisement' => $newAdvertisementLimitCount, 'adv_status' => $addAdvertisementStatus]);

                DB::commit();
                $response['error'] = false;
                $response['message'] = "Advertisement add successfully";
                $response['data'] = $propertyDetails;
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            DB::rollback();
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }

    public function get_advertisement(Request $request)
    {

        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;

        $article = Article::select('id', 'image', 'title', 'description');
        $date = date('Y-m-d');

        $adv = Advertisement::select('id', 'image', 'category_id', 'property_id', 'type', 'customer_id', 'is_enable', 'status')->with('customer:id,name')->where('end_date', '>', $date);
        if (isset($request->customer_id)) {
            $adv->where('customer_id', $request->customer_id);
        }
        $total = $adv->get()->count();
        $result = $adv->orderBy('id', 'ASC')->skip($offset)->take($limit)->get();
        if (!$result->isEmpty()) {
            foreach ($adv as $row) {
                if (filter_var($row->image, FILTER_VALIDATE_URL) === false) {
                    $row->image = ($row->image != '') ? url('') . config('global.IMG_PATH') . config('global.ADVERTISEMENT_IMAGE_PATH') . $row->image : '';
                } else {
                    $row->image = $row->image;
                }
            }
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['data'] = $result;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }


        return response()->json($response);
    }
    public function get_package(Request $request)
    {
        if ($request->platform == "ios") {
            $packages = Package::where('status', 1)
                ->where('ios_product_id', '!=', '')
                ->orderBy('price', 'ASC')
                ->get();
        } else {
            $packages = Package::where('status', 1)
                ->orderBy('price', 'ASC')
                ->get();
        }

        $packages->transform(function ($item) use ($request) {
            if (collect(Auth::guard('sanctum')->user())->isNotEmpty()) {
                $currentDate = Carbon::now()->format("Y-m-d");

                $loggedInUserId = Auth::guard('sanctum')->user()->id;
                $user_package = UserPurchasedPackage::where('modal_id', $loggedInUserId)->where(function ($query) use ($currentDate) {
                    $query->whereDate('start_date', '<=', $currentDate)
                        ->whereDate('end_date', '>=', $currentDate);
                });

                if ($request->type == 'property') {
                    $user_package->where('prop_status', 1);
                } else if ($request->type == 'advertisement') {
                    $user_package->where('adv_status', 1);
                }

                $user_package = $user_package->where('package_id', $item->id)->first();


                if (!empty($user_package)) {
                    $startDate = new DateTime(Carbon::now());
                    $endDate = new DateTime($user_package->end_date);

                    // Calculate the difference between two dates
                    $interval = $startDate->diff($endDate);

                    // Get the difference in days
                    $diffInDays = $interval->days;

                    $item['is_active'] = 1;
                    $item['type'] = $item->type === "premium_user" ? "premium_user" : "product_listing";

                    if (!($item->type === "premium_user")) {
                        $item['used_limit_for_property'] = $user_package->used_limit_for_property;
                        $item['used_limit_for_advertisement'] = $user_package->used_limit_for_advertisement;
                        $item['property_status'] = $user_package->prop_status;
                        $item['advertisement_status'] = $user_package->adv_status;
                    }

                    $item['start_date'] = $user_package->start_date;
                    $item['end_date'] = $user_package->end_date;
                    $item['remaining_days'] = $diffInDays;
                } else {
                    $item['is_active'] = 0;
                }
            }

            if (!($item->type === "premium_user")) {
                $item['advertisement_limit'] = $item->advertisement_limit == '' ? "unlimited" : ($item->advertisement_limit == 0 ? "not_available" : $item->advertisement_limit);
                $item['property_limit'] = $item->property_limit == '' ? "unlimited" : ($item->property_limit == 0 ? "not_available" : $item->property_limit);
            } else {
                unset($item['property_limit']);
                unset($item['advertisement_limit']);
            }


            return $item;
        });

        // Sort the packages based on is_active flag (active packages first)
        $packages = $packages->sortByDesc('is_active');

        $response = [
            'error' => false,
            'message' => 'Data Fetch Successfully',
            'data' => $packages->values()->all(), // Reset the keys after sorting
        ];

        return response()->json($response);
    }

    //     public function get_package(Request $request)
    //     {

    //  if($request->platform=="ios"){
    //     //  dd("in");
    //               $packages = Package::where('status', 1)->where('ios_product_id','!=','')
    //             ->orderBy('id', 'ASC')

    //             ->get();

    //             }else{
    //                  $packages = Package::where('status', 1)
    //             ->orderBy('id', 'ASC')

    //             ->get();
    //             }

    //         // $packages = Package::where('status', 1)
    //         //     ->orderBy('id', 'ASC')

    //         //     ->get();

    //         $packages->map(function ($item) use ($request) {




    //             // If the user has purchased a package, set "is_active" for that specific package
    //             if ($request->filled('current_user')) {
    //                 $user_package = UserPurchasedPackage::where('modal_id', $request->current_user)->first();
    //                 $is_active = $user_package ? 1 : 0;

    //                 if ($is_active && $item->id == $user_package->package_id) {
    //                     $item['is_active'] = 1;
    //                 } else {
    //                     $item['is_active'] = 0;
    //                 }
    //             }
    //             $item['advertisement_limit'] = $item->advertisement_limit == '' ? "unlimited" : ($item->advertisement_limit == 0 ? "not_available" : $item->advertisement_limit);
    //             $item['property_limit'] = $item->property_limit == '' ? "unlimited" : ($item->property_limit == 0 ? "not_available" : $item->property_limit);

    //             return $item;
    //         });

    // // dd($packages->toArray());

    //         $response = [
    //             'error' => false,
    //             'message' => 'Data Fetch Successfully',
    //             'data' => $packages,
    //         ];

    //         return response()->json($response);
    //     }
    public function user_purchase_package(Request $request)
    {

        $start_date =  Carbon::now();
        $validator = Validator::make($request->all(), [
            'package_id' => 'required',
        ]);

        if (!$validator->fails()) {
            $loggedInUserId = Auth::user()->id;
            if (isset($request->flag)) {
                $user_exists = UserPurchasedPackage::where('modal_id', $loggedInUserId)->get();
                if ($user_exists) {
                    UserPurchasedPackage::where('modal_id', $loggedInUserId)->delete();
                }
            }

            $package = Package::find($request->package_id);
            $user = Customer::find($loggedInUserId);
            $data_exists = UserPurchasedPackage::where('modal_id', $loggedInUserId)->get();
            if (count($data_exists) == 0 && $package) {
                $user_package = new UserPurchasedPackage();
                $user_package->modal()->associate($user);
                $user_package->package_id = $request->package_id;
                $user_package->start_date = $start_date;
                $user_package->end_date = $package->duratio != 0 ? Carbon::now()->addDays($package->duration) : NULL;
                $user_package->save();

                $user->subscription = 1;
                $user->update();

                $response['error'] = false;
                $response['message'] = "purchased package  add successfully";
            } else {
                $response['error'] = false;
                $response['message'] = "data already exists or package not found or add flag for add new package";
            }
        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }
        return response()->json($response);
    }
    public function get_favourite_property(Request $request)
    {

        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 25;

        $current_user = Auth::user()->id;

        $favourite = Favourite::where('user_id', $current_user)->select('property_id')->get();
        $arr = array();
        foreach ($favourite as $p) {
            $arr[] =  $p->property_id;
        }

        $property_details = Property::whereIn('id', $arr)->with('category:id,category,image')->with('assignfacilities.outdoorfacilities')->with('parameters');
        $result = $property_details->orderBy('id', 'ASC')->skip($offset)->take($limit)->get();

        $total = $property_details->count();

        if (!$result->isEmpty()) {

            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['data'] =  get_property_details($result, $current_user);
            $response['total'] = $total;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }
    public function delete_advertisement(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',

        ]);

        if (!$validator->fails()) {
            $adv = Advertisement::find($request->id);
            if (!$adv) {
                $response['error'] = false;
                $response['message'] = "Data not found";
            } else {

                $adv->delete();
                $response['error'] = false;
                $response['message'] = "Advertisement Deleted successfully";
            }
        } else {
            $response['error'] = true;
            $response['message'] = $validator->errors()->first();
        }
        return response()->json($response);
    }
    public function interested_users(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required',
            'type' => 'required'


        ]);
        if (!$validator->fails()) {
            $current_user = Auth::user()->id;

            $interested_user = InterestedUser::where('customer_id', $current_user)->where('property_id', $request->property_id);

            if ($request->type == 1) {

                if (count($interested_user->get()) > 0) {
                    $response['error'] = false;
                    $response['message'] = "already added to interested users ";
                } else {
                    $interested_user = new InterestedUser();
                    $interested_user->property_id = $request->property_id;
                    $interested_user->customer_id = $current_user;
                    $interested_user->save();
                    $response['error'] = false;
                    $response['message'] = "Interested Users added successfully";
                }
            }
            if ($request->type == 0) {

                if (count($interested_user->get()) == 0) {
                    $response['error'] = false;
                    $response['message'] = "No data found to delete";
                } else {
                    $interested_user->delete();

                    $response['error'] = false;
                    $response['message'] = "Interested Users removed  successfully";
                }
            }
        } else {
            $response['error'] = true;
            $response['message'] = $validator->errors()->first();
        }
        return response()->json($response);
    }

    public function user_interested_property(Request $request)
    {

        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 25;

        $current_user = Auth::user()->id;


        $favourite = InterestedUser::where('customer_id', $current_user)->select('property_id')->get();
        $arr = array();
        foreach ($favourite as $p) {
            $arr[] =  $p->property_id;
        }
        $property_details = Property::whereIn('id', $arr)->with('category:id,category')->with('parameters');
        $result = $property_details->orderBy('id', 'ASC')->skip($offset)->take($limit)->get();


        $total = $result->count();

        if (!$result->isEmpty()) {
            foreach ($property_details as $row) {
                if (filter_var($row->image, FILTER_VALIDATE_URL) === false) {
                    $row->image = ($row->image != '') ? url('') . config('global.IMG_PATH') . config('global.PROPERTY_TITLE_IMG_PATH') . $row->image : '';
                } else {
                    $row->image = $row->image;
                }
            }
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['data'] = $result;
            $response['total'] = $total;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }
    // public function get_limits(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'id' => 'required',

    //     ]);
    //     if (!$validator->fails()) {
    //         $payload = JWTAuth::getPayload($this->bearerToken($request));
    //         $current_user = ($payload['customer_id']);
    //         $package = UserPurchasedPackage::where('modal_id', $current_user)->where('package_id', $request->id)->with(['package' => function ($q) {
    //             $q->select('id', 'property_limit', 'advertisement_limit');
    //         }])->first();
    //         if (!$package) {
    //             $response['error'] = true;
    //             $response['message'] = "package not found";
    //             return response()->json($response);
    //         }
    //         $arr = 0;
    //         $adv_count = 0;
    //         $prop_count = 0;
    //         // foreach ($package as $p) {

    //         ($adv_count = $package->package->advertisement_limit == 0 ? "Unlimited" : $package->package->advertisement_limit);
    //         ($prop_count = $package->package->property_limit == 0 ? "Unlimited" : $package->package->property_limit);

    //         ($arr = $package->id);
    //         // }

    //         $advertisement_limit = Advertisement::where('customer_id', $current_user)->where('package_id', $request->id)->get();
    //         // DB::enableQueryLog();

    //         $propeerty_limit = Property::where('added_by', $current_user)->where('package_id', $request->id)->get();


    //         $response['total_limit_of_advertisement'] = ($adv_count);
    //         $response['total_limit_of_property'] = ($prop_count);


    //         $response['used_limit_of_advertisement'] = $package->used_limit_for_advertisement;
    //         $response['used_limit_of_property'] = $package->used_limit_for_property;
    //     } else {
    //         $response['error'] = true;
    //         $response['message'] = $validator->errors()->first();
    //     }
    //     return response()->json($response);
    // }
    public function get_limits(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_type' => 'required',

        ]);
        if (!$validator->fails()) {
            if ($request->package_type == "property") {
                $package_type = "property_limit";
                $status = "prop_status";
                $message = "Post Property";
            } else {
                $package_type = "advertisement_limit";
                $message = "Advertisement";
                $status = "adv_status";
            }

            $current_user = Auth::user()->id;
            $current_package = UserPurchasedPackage::where('modal_id', $current_user)
                ->with(['package' => function ($q) use ($package_type) {
                    $q->select('id', $package_type)->where($package_type, '>', 0)->orWhere($package_type, null);
                }])
                ->whereHas('package', function ($q) use ($package_type) {
                    $q->where($package_type, '>', 0)->orWhere($package_type, null);
                })->where($status, 1)
                ->first();

            if (!($current_package)) {
                $response['error'] = false;
                $response['message'] = 'Please Subscribe for ' . $message;
                $response['package'] = false;
            } else {
                $response['error'] = false;
                $response['message'] = "User able to upload";
                $response['package'] = true;
            }

            $customer = Customer::select('id', 'subscription', 'is_premium')
                ->where(function ($query) {
                    $query->where('subscription', 1)
                        ->orWhere('is_premium', 1);
                })
                ->find($current_user);



            if (($customer)) {


                $response['is_premium'] = $customer->is_premium == 1 ? true : ($customer->subscription == 1 ? true : false);

                $response['subscription'] = $customer->subscription == 1 ? true : false;
            } else {

                $response['is_premium'] = false;
                $response['subscription'] = false;
            }
        } else {
            $response['error'] = true;
            $response['message'] = $validator->errors()->first();
        }
        return response()->json($response);
    }
    public function get_languages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language_code' => 'required',
        ]);

        if (!$validator->fails()) {
            $language = Language::where('code', $request->language_code)->first();

            if ($language) {
                if ($request->web_language_file) {
                    $json_file_path = public_path('web_languages/' . $request->language_code . '.json');
                } else {
                    $json_file_path = public_path('languages/' . $request->language_code . '.json');
                }

                if (file_exists($json_file_path)) {
                    $json_string = file_get_contents($json_file_path);
                    $json_data = json_decode($json_string);

                    if ($json_data !== null) {
                        $language->file_name = $json_data;
                        $response['error'] = false;
                        $response['message'] = "Data Fetch Successfully";
                        $response['data'] = $language;
                    } else {
                        $response['error'] = true;
                        $response['message'] = "Invalid JSON format in the language file";
                    }
                } else {
                    $response['error'] = true;
                    $response['message'] = "Language file not found";
                }
            } else {
                $response['error'] = true;
                $response['message'] = "Language not found";
            }
        } else {
            $response['error'] = true;
            $response['message'] = $validator->errors()->first();
        }

        return response()->json($response);
    }
    public function get_payment_details(Request $request)
    {
        $current_user = Auth::user()->id;



        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;
        // $id = $request->userid;

        //  $result=  Notifications::where("customers_id",$id)->orwhere('send_type', '1')->orderBy('id', 'DESC');
        // $total = $result->get()->count();
        // $Notifications = $result->skip($offset)->take($limit)->get();


        $payment = Payments::where('customer_id', $current_user);
        $total = $payment->get()->count();

        $result = $payment->skip($offset)->take($limit)->get();

        if (count($result)) {
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";

            $response['total'] = $total;

            $response['data'] = $result;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }



    public function paypal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_id' => 'required',
            'amount' => 'required'

        ]);
        if (!$validator->fails()) {
            $current_user = Auth::user()->id;
            $paypal = new Paypal();
            // url('') . config('global.IMG_PATH')
            $returnURL = url('api/app_payment_status');
            $cancelURL = url('api/app_payment_status');
            $notifyURL = url('webhook/paypal');
            // $package_id = $request->package_id;
            $package_id = $request->package_id;
            // Get product data from the database

            // Get current user ID from the session
            $paypal->add_field('return', $returnURL);
            $paypal->add_field('cancel_return', $cancelURL);
            $paypal->add_field('notify_url', $notifyURL);
            $custom_data = $package_id . ',' . $current_user;

            // // Add fields to paypal form


            $paypal->add_field('item_name', "package");
            $paypal->add_field('custom_id', json_encode($custom_data));

            $paypal->add_field('custom', ($custom_data));

            $paypal->add_field('amount', $request->amount);

            // Render paypal form
            $paypal->paypal_auto_form();
        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }
    }
    public function app_payment_status(Request $request)
    {

        $paypalInfo = $request->all();

        if (!empty($paypalInfo) && isset($_GET['st']) && strtolower($_GET['st']) == "completed") {

            $response['error'] = false;
            $response['message'] = "Your Purchase Package Activate Within 10 Minutes ";
            $response['data'] = $paypalInfo['txn_id'];
        } elseif (!empty($paypalInfo) && isset($_GET['st']) && strtolower($_GET['st']) == "authorized") {

            $response['error'] = false;
            $response['message'] = "Your payment has been Authorized successfully. We will capture your transaction within 30 minutes, once we process your order. After successful capture Ads wil be credited automatically.";
            $response['data'] = $paypalInfo;
        } else {
            $response['error'] = true;
            $response['message'] = "Payment Cancelled / Declined ";
            $response['data'] = (isset($_GET)) ? $paypalInfo : "";
        }
        return (response()->json($response));
    }
    public function get_payment_settings(Request $request)
    {
        $payment_settings = Setting::select('type', 'data')->whereIn('type', ['paypal_business_id', 'sandbox_mode', 'paypal_gateway', 'razor_key', 'razor_secret', 'razorpay_gateway', 'paystack_public_key', 'paystack_secret_key', 'paystack_currency', 'paystack_gateway', 'stripe_publishable_key', 'stripe_currency', 'stripe_gateway', 'stripe_secret_key'])->get();
        foreach ($payment_settings as $setting) {
            if ($setting->type === 'stripe_secret_key') {
                $publicKey = file_get_contents(base_path('public_key.pem')); // Load the public key
                $encryptedData = '';
                if (openssl_public_encrypt($setting->data, $encryptedData, $publicKey)) {
                    $setting->data = base64_encode($encryptedData);
                }
            }
        }

        if (count($payment_settings)) {
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['data'] = $payment_settings;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return (response()->json($response));
    }
    public function send_message(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'sender_id' => 'required',
            'receiver_id' => 'required',

            'property_id' => 'required',
        ]);
        $fcm_id = array();
        if (!$validator->fails()) {

            $chat = new Chats();
            $chat->sender_id = $request->sender_id;
            $chat->receiver_id = $request->receiver_id;
            $chat->property_id = $request->property_id;
            $chat->message = $request->message;
            $destinationPath = public_path('images') . config('global.CHAT_FILE');
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            // image upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = microtime(true) . "." . $file->getClientOriginalExtension();
                $file->move($destinationPath, $fileName);
                $chat->file = $fileName;
            } else {
                $chat->file = '';
            }

            $audiodestinationPath = public_path('images') . config('global.CHAT_AUDIO');
            if (!is_dir($audiodestinationPath)) {
                mkdir($audiodestinationPath, 0777, true);
            }
            if ($request->hasFile('audio')) {
                $file = $request->file('audio');
                $fileName = microtime(true) . "." . $file->getClientOriginalExtension();
                $file->move($audiodestinationPath, $fileName);
                $chat->audio = $fileName;
            } else {
                $chat->audio = '';
            }
            $chat->save();
            $customer = Customer::select('id', 'name', 'profile')->with(['usertokens' => function ($q) {
                $q->select('fcm_id', 'id', 'customer_id');
            }])->find($request->receiver_id);
            if ($customer) {
                foreach ($customer->usertokens as $usertokens) {
                    array_push($fcm_id, $usertokens->fcm_id);
                }
                $username = $customer->name;
            } else {

                $user_data = User::select('fcm_id', 'name')->get();
                $username = "Admin";
                foreach ($user_data as $user) {
                    array_push($fcm_id, $user->fcm_id);
                }
            }
            $senderUser = Customer::select('fcm_id', 'name', 'profile')->find($request->sender_id);
            if ($senderUser) {
                $profile = $senderUser->profile;
            } else {
                $profile = "";
            }

            $Property = Property::find($request->property_id);






            $chat_message_type = "";

            if (!empty($request->file('audio'))) {
                $chat_message_type = "audio";
            } else if (!empty($request->file('file')) && $request->message == "") {
                $chat_message_type = "file";
            } else if (!empty($request->file('file')) && $request->message != "") {
                $chat_message_type = "file_and_text";
            } else if (empty($request->file('file')) && $request->message != "" && empty($request->file('audio'))) {
                $chat_message_type = "text";
            } else {
                $chat_message_type = "text";
            }


            $fcmMsg = array(
                'title' => 'Message',
                'message' => $request->message,
                'type' => 'chat',
                'body' => $request->message,
                'sender_id' => $request->sender_id,
                'receiver_id' => $request->receiver_id,
                'file' => $chat->file,
                'username' => $username,
                'user_profile' => $profile,
                'audio' => $chat->audio,
                'date' => $chat->created_at->diffForHumans(now(), CarbonInterface::DIFF_RELATIVE_AUTO, true),
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'sound' => 'default',
                'time_ago' => $chat->created_at->diffForHumans(now(), CarbonInterface::DIFF_RELATIVE_AUTO, true),
                'property_id' => (string)$Property->id,
                'property_title_image' => $Property->title_image,
                'title' => $Property->title,
                'chat_message_type' => $chat_message_type,
            );

            $send = send_push_notification($fcm_id, $fcmMsg);
            $response['error'] = false;
            $response['message'] = "Data Store Successfully";
            $response['id'] = $chat->id;
            // $response['data'] = $send;
        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }
        return (response()->json($response));
    }
    public function get_messages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required'

        ]);
        if (!$validator->fails()) {
            $currentUser = Auth::user();

            $perPage = $request->per_page ? $request->per_page : 15; // Number of results to display per page
            $page = $request->page ?? 1; // Get the current page from the query string, or default to 1
            $chat = Chats::where('property_id', $request->property_id)
                ->where(function ($query) use ($currentUser) {
                    $query->where('sender_id', $currentUser->id)
                        ->orWhere('receiver_id', $currentUser->id);
                })
                ->orderBy('created_at', 'DESC')
                ->paginate($perPage, ['*'], 'page', $page);

            // You can then pass the $chat object to your view to display the paginated results.




            $chat_message_type = "";
            if ($chat) {


                $chat->map(function ($chat) use ($chat_message_type, $currentUser) {
                    if (!empty($chat->audio)) {
                        $chat_message_type = "audio";
                    } else if (!empty($chat->file) && $chat->message == "") {
                        $chat_message_type = "file";
                    } else if (!empty($chat->file) && $chat->message != "") {
                        $chat_message_type = "file_and_text";
                    } else if (empty($chat->file) && !empty($chat->message) && empty($chat->audio)) {
                        $chat_message_type = "text";
                    } else {
                        $chat_message_type = "text";
                    }
                    $chat['chat_message_type'] = $chat_message_type;
                    $chat['user_profile'] = $currentUser->profile;
                    $chat['time_ago'] = $chat->created_at->diffForHumans(now(), CarbonInterface::DIFF_RELATIVE_AUTO, true);
                });

                $response['error'] = false;
                $response['message'] = "Data Fetch Successfully";
                $response['total_page'] = $chat->lastPage();
                $response['data'] = $chat;
            } else {
                $response['error'] = false;
                $response['message'] = "No data found!";
                $response['data'] = [];
            }
        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }
        return response()->json($response);
    }

    public function get_chats(Request $request)
    {
        $current_user = Auth::user()->id;
        $perPage = $request->per_page ? $request->per_page : 15; // Number of results to display per page
        $page = $request->page ?? 1;

        $chat = Chats::with(['sender', 'receiver'])->with('property')
            ->select('id', 'sender_id', 'receiver_id', 'property_id', 'created_at')
            ->where('sender_id', $current_user)
            ->orWhere('receiver_id', $current_user)
            ->orderBy('id', 'desc')
            ->groupBy('property_id')
            ->paginate($perPage, ['*'], 'page', $page);

        if (!$chat->isEmpty()) {

            $rows = array();

            $count = 1;

            $response['total_page'] = $chat->lastPage();

            foreach ($chat as $key => $row) {
                $tempRow = array();
                $tempRow['property_id'] = $row->property_id;
                $tempRow['title'] = $row->property->title;
                $tempRow['title_image'] = $row->property->title_image;
                $tempRow['date'] = $row->created_at;
                $tempRow['property_id'] = $row->property_id;
                if (!$row->receiver || !$row->sender) {
                    $user =
                        user::where('id', $row->sender_id)->orWhere('id', $row->receiver_id)->select('id')->first();

                    $tempRow['user_id'] = 0;
                    $tempRow['name'] = "Admin";
                    $tempRow['profile'] = url('assets/images/faces/2.jpg');

                    // $tempRow['fcm_id'] = $row->receiver->fcm_id;
                } else {
                    if ($row->sender->id == $current_user) {

                        $tempRow['user_id'] = $row->receiver->id;
                        $tempRow['name'] = $row->receiver->name;
                        $tempRow['profile'] = $row->receiver->profile;
                        $tempRow['firebase_id'] = $row->receiver->firebase_id;
                        $tempRow['fcm_id'] = $row->receiver->fcm_id;
                    }
                    if ($row->receiver->id == $current_user) {
                        $tempRow['user_id'] = $row->sender->id;
                        $tempRow['name'] = $row->sender->name;

                        $tempRow['profile'] = $row->sender->profile;
                        $tempRow['firebase_id'] = $row->sender->firebase_id;
                        $tempRow['fcm_id'] = $row->sender->fcm_id;
                    }
                }
                $rows[] = $tempRow;
                // $parameters[] = $arr;
                $count++;
            }


            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['data'] = $rows;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }
    public function get_nearby_properties(Request $request)
    {

        if (isset($request->city) || isset($request->state)) {
            if (isset($request->type)) {
                DB::enableQueryLog();


                $result = Property::with('category')
                    ->where('status', 1)
                    ->where('propery_type', $request->type)
                    ->where(function ($query) use ($request) {
                        $query->where('state', 'LIKE', "%$request->state%")
                            ->orWhere('city', 'LIKE', "%$request->city%");
                    })
                    ->get();
            } else {
                $result = Property::with('category')->where('city', 'LIKE', "%$request->city%")->where('state', 'LIKE', "%$request->state%")->where('status', 1)->get();
            }
        } else {
            $result = Property::with('category')->where('status', 1)->get();
        }


        $rows = array();
        $tempRow = array();
        $count = 1;

        if (!$result->isEmpty()) {

            foreach ($result as $key => $row) {
                $tempRow['id'] = $row->id;
                $tempRow['slug_id'] = $row->slug_id;
                $tempRow['title'] = $row->title;
                $tempRow['title_image'] = $row->title_image;
                $tempRow['price'] = $row->price;
                $tempRow['latitude'] = $row->latitude;
                $tempRow['longitude'] = $row->longitude;
                $tempRow['city'] = $row->city;
                $tempRow['state'] = $row->state;
                $tempRow['country'] = $row->country;
                $tempRow['category'] = $row->category;
                $tempRow['property_type'] = $row->propery_type;
                $rows[] = $tempRow;

                $count++;
            }


            $response['error'] = false;
            $response['data'] = $rows;
        } else {
            $response['error'] = false;
            $response['data'] = [];
        }
        return response()->json($response);
    }
    public function update_property_status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required',
            'property_id' => 'required'

        ]);
        if (!$validator->fails()) {
            $property = Property::find($request->property_id);
            $property->propery_type = $request->status;
            $property->save();
            $response['error'] = false;
            $response['message'] = "Data updated Successfully";
        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }
        return response()->json($response);
    }
    public function getCitiesData(Request $request)
    {
        // Get Offset and Limit from payload request
        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;
        $city_arr = array();
        $propertyQuery = Property::groupBy('city')->where('status', 1);
        $propertiesByCity = $propertyQuery->clone()->select('city', DB::raw('count(*) as count'))->orderBy('count', 'DESC')->skip($offset)->take($limit)->get();
        $propertiesTotalCount = $propertyQuery->clone()->count();

        // $webSettingPlaceholderData = system_setting('web_placeholder_logo');
        // $webSettingPlaceholder = url('/assets/images/logo/') . '/' . $webSettingPlaceholderData;
        foreach ($propertiesByCity as $key => $city) {
            if ($city->city != '') {
                $apiKey = env('UNSPLASH_API_KEY');
                $query = $city->city;
                $apiUrl = "https://api.unsplash.com/search/photos/?query=$query";
                $ch = curl_init($apiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Client-ID ' . $apiKey,
                ]);
                $unsplashResponse = curl_exec($ch);

                curl_close($ch);

                $unsplashData = json_decode($unsplashResponse, true);
                // Check if the response contains data
                if (isset($unsplashData['results'])) {
                    $results = $unsplashData['results'];

                    // Initialize the image URL
                    $imageUrl = '';

                    // Loop through the results and get the first image URL
                    foreach ($results as $result) {
                        $imageUrl = $result['urls']['regular'];
                        break; // Stop after getting the first image URL
                    }
                    if ($imageUrl != "") {

                        array_push($city_arr, ['City' => $city->city, 'Count' => $city->count, 'image' => $imageUrl]);
                    }
                } else {
                    array_push($city_arr, ['City' => $city->city, 'Count' => $city->count, 'image' => ""]);
                }
            }
        }
        $response['error'] = false;
        $response['data'] = $city_arr;
        $response['total'] = $propertiesTotalCount;
        $response['message'] = "Data Fetched Successfully";

        return response()->json($response);
    }

    public function get_agents_details(Request $request)
    {
        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;
        $agent_arr = array();
        $propertiesByAgent = Property::with(['customer' => function ($q) {
            $q->where('role', 1);
        }])
            ->groupBy('added_by')
            ->select('added_by', DB::raw('count(*) as count'))->skip($offset)->take($limit)
            ->get();
        foreach ($propertiesByAgent as $agent) {
            if (count($agent->customer)) {
                array_push($agent_arr, ['agent' => $agent->added_by, 'Count' => $agent->count, 'customer' => $agent->customer]);
            }
        }
        if (count($agent_arr)) {
            $response['error'] = false;
            $response['message'] = "Data Fetch  Successfully";
            $response['agent_data'] = $agent_arr;
        } else {
            $response['error'] = false;
            $response['message'] = "No Data Found";
        }
        return response()->json($response);
    }
    public function get_facilities(Request $request)
    {
        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;

        $facilities = OutdoorFacilities::query();

        // if (isset($request->search) && !empty($request->search)) {
        //     $search = $request->search;
        //     $facilities->where('category', 'LIKE', "%$search%");
        // }

        if (isset($request->id) && !empty($request->id)) {
            $id = $request->id;
            $facilities->where('id', '=', $id);
        }
        $total = $facilities->clone()->count();
        $result = $facilities->clone()->skip($offset)->take($limit)->get();


        if (!$result->isEmpty()) {
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";

            $response['total'] = $total;
            $response['data'] = $result;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }
    public function get_report_reasons(Request $request)
    {
        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;

        $report_reason = report_reasons::all();

        if (isset($request->id) && !empty($request->id)) {
            $id = $request->id;
            $report_reason->where('id', '=', $id);
        }
        $result = $report_reason->skip($offset)->take($limit);

        $total = $report_reason->count();

        if (!$result->isEmpty()) {
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";

            $response['total'] = $total;
            $response['data'] = $result;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }
    public function add_reports(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reason_id' => 'required',
            'property_id' => 'required',



        ]);
        $current_user = Auth::user()->id;
        if (!$validator->fails()) {
            $report_count = user_reports::where('property_id', $request->property_id)->where('customer_id', $current_user)->get();
            if (!count($report_count)) {
                $report_reason = new user_reports();
                $report_reason->reason_id = $request->reason_id ? $request->reason_id : 0;
                $report_reason->property_id = $request->property_id;
                $report_reason->customer_id = $current_user;
                $report_reason->other_message = $request->other_message ? $request->other_message : '';



                $report_reason->save();


                $response['error'] = false;
                $response['message'] = "Report Submited Successfully";
            } else {
                $response['error'] = false;
                $response['message'] = "Already Reported";
            }
        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }
        return response()->json($response);
    }
    public function delete_chat_message(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'receiver_id' => 'required',


        ]);
        if (!$validator->fails()) {
            // Get Customer IDs

            // Get FCM IDs
            $fcmId = Usertokens::select('fcm_id')->where('customer_id', $request->receiver_id)->pluck('fcm_id')->toArray();

            if (isset($request->message_id)) {
                $chat = Chats::find($request->message_id);
                if ($chat) {
                    if (!empty($fcmId)) {
                        $registrationIDs = array_filter($fcmId);
                        $fcmMsg = array(
                            'title' => "Delete Chat Message",
                            'message' => "Message Deleted Successfully",
                            "image" => '',
                            'type' => 'delete_message',
                            'message_id' => $request->message_id,
                            'body' => 'Message Deleted Successfully',
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            'sound' => 'default',

                        );
                        send_push_notification($registrationIDs, $fcmMsg, 1);
                    }
                    $chat->delete();

                    $response['error'] = false;
                    $response['message'] = "Message Deleted Successfully";
                }
            }
            if (isset($request->sender_id) && isset($request->receiver_id) && isset($request->property_id)) {

                $user_chat = Chats::where('property_id', $request->property_id)
                    ->where(function ($query) use ($request) {
                        $query->where('sender_id', $request->sender_id)
                            ->orWhere('receiver_id', $request->receiver_id);
                    })
                    ->orWhere(function ($query) use ($request) {
                        $query->where('sender_id', $request->receiver_id)
                            ->orWhere('receiver_id', $request->sender_id);
                    });
                if (count($user_chat->get())) {

                    $user_chat->delete();
                    $response['error'] = false;
                    $response['message'] = "chat deleted successfully";
                } else {
                    $response['error'] = false;
                    $response['message'] = "No Data Found";
                }
            } else {
                $response['error'] = false;
                $response['message'] = "No Data Found";
            }
        }
        return response()->json($response);
    }
    public function get_user_recommendation(Request $request)
    {
        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;
        $current_user = Auth::user()->id;


        $user_interest = UserInterest::where('user_id', $current_user)->first();
        if (collect($user_interest)->isNotEmpty()) {

            $property = Property::with('customer')->with('user')->with('category:id,category,image')->with('assignfacilities.outdoorfacilities')->with('favourite')->with('parameters')->with('interested_users')->where('status', 1);


            $property_type = $request->property_type;
            if ($user_interest->category_ids != '') {

                $category_ids = explode(',', $user_interest->category_ids);

                $property = $property->whereIn('category_id', $category_ids);
            }

            if ($user_interest->price_range != '') {

                $max_price = explode(',', $user_interest->price_range)[1];

                $min_price = explode(',', $user_interest->price_range)[0];

                if (isset($max_price) && isset($min_price)) {
                    $min_price = floatval($min_price);
                    $max_price = floatval($max_price);

                    $property = $property->where(function ($query) use ($min_price, $max_price) {
                        $query->whereRaw("CAST(price AS DECIMAL(10, 2)) >= ?", [$min_price])
                            ->whereRaw("CAST(price AS DECIMAL(10, 2)) <= ?", [$max_price]);
                    });
                }
            }


            if ($user_interest->city != '') {
                $city = $user_interest->city;
                $property = $property->where('city', $city);
            }
            if ($user_interest->property_type != '') {
                $property_type = explode(',',  $user_interest->property_type);
            }
            if ($user_interest->outdoor_facilitiy_ids != '') {


                $outdoor_facilitiy_ids = explode(',', $user_interest->outdoor_facilitiy_ids);
                $property = $property->whereHas('assignfacilities.outdoorfacilities', function ($q) use ($outdoor_facilitiy_ids) {
                    $q->whereIn('id', $outdoor_facilitiy_ids);
                });
            }



            if (isset($property_type)) {
                if (count($property_type) == 2) {
                    $property_type = $property->where(function ($query) use ($property_type) {
                        $query->where('propery_type', $property_type[0])->orWhere('propery_type', $property_type[1]);
                    });
                } else {
                    if (isset($property_type[0])  &&  $property_type[0] == 0) {

                        $property = $property->where('propery_type', $property_type[0]);
                    }
                    if (isset($property_type[0])  &&  $property_type[0] == 1) {

                        $property = $property->where('propery_type', $property_type[0]);
                    }
                }
            }



            $total = $property->get()->count();

            $result = $property->skip($offset)->take($limit)->get();
            $property_details = get_property_details($result, $current_user);

            if (!empty($result)) {
                $response['error'] = false;
                $response['message'] = "Data Fetch Successfully";
                $response['total'] = $total;
                $response['data'] = $property_details;
            } else {

                $response['error'] = false;
                $response['message'] = "No data found!";
                $response['data'] = [];
            }
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return ($response);
    }
    public function contct_us(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required',
            'subject' => 'required',
            'message' => 'required',




        ]);

        if (!$validator->fails()) {

            $contactrequest = new Contactrequests();
            $contactrequest->first_name = $request->first_name;
            $contactrequest->last_name = $request->last_name;
            $contactrequest->email = $request->email;
            $contactrequest->subject = $request->subject;
            $contactrequest->message = $request->message;
            $contactrequest->save();
            $response['error'] = false;
            $response['message'] = "Conatct Request Send successfully";
        } else {


            $response['error'] = true;
            $response['message'] =  $validator->errors()->first();
        }
        return response()->json($response);
    }
    public function createPaymentIntent(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'package_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }
        try {
            $limit = isset($request->limit) ? $request->limit : 10;
            $current_user = Auth::user()->id;

            $secret_key = system_setting('stripe_secret_key');

            $stripe_currency = system_setting('stripe_currency');
            $package = Package::find($request->package_id);

            $data = [
                'amount' => ((int)($package['price'])) * 100,
                'currency' => $stripe_currency,
                'description' => $request->description,


                'payment_method_types[]' => $request->payment_method,
                'metadata' => [
                    'userId' => $current_user,
                    'packageId' => $request->package_id,
                ],
                'shipping' => [
                    'name' => $request['shipping']['name'], // Replace with the actual name
                    'address' => [
                        'line1' => !empty($request['shipping']['address']['line1']) ? $request['shipping']['address']['line1'] : '',
                        'line2' => !empty($request['shipping']['address']['line2']) ? $request['shipping']['address']['line2'] : '',
                        'postal_code' => !empty($request['shipping']['address']['postal_code']) ? $request['shipping']['address']['postal_code'] : '',
                        'city' => !empty($request['shipping']['address']['city']) ? $request['shipping']['address']['city'] : '',
                        'state' => !empty($request['shipping']['address']['state']) ? $request['shipping']['address']['state'] : '',
                        'country' => !empty($request['shipping']['address']['country']) ? $request['shipping']['address']['country'] : '',
                    ],
                ],
            ];
            $headers = [
                'Authorization' => 'Bearer ' . $secret_key,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ];
            $response = Http::withHeaders($headers)->asForm()->post('https://api.stripe.com/v1/payment_intents', $data);
            $responseData = $response->json();
            return response()->json([
                'data' => $responseData,
                'message' => 'Intent created.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while processing the payment.',
            ], 500);
        }
    }
    public function confirmPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'paymentIntentId' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }
        try {

            $secret_key = system_setting('stripe_secret_key');
            $headers = [
                'Authorization' => 'Bearer ' . $secret_key,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ];
            $response = Http::withHeaders($headers)
                ->get("https://api.stripe.com/v1/payment_intents/{$request->paymentIntentId}");
            $responseData = $response->json();
            $statusOfTransaction = $responseData['status'];
            if ($statusOfTransaction == 'succeeded') {
                return response()->json([
                    'message' => 'Transaction successful',
                    'success' => true,
                    'status' => $statusOfTransaction,
                ]);
            } elseif ($statusOfTransaction == 'pending' || $statusOfTransaction == 'captured') {
                return response()->json([
                    'message' => 'Transaction pending',
                    'success' => true,
                    'status' => $statusOfTransaction,
                ]);
            } else {
                return response()->json([
                    'message' => 'Transaction failed',
                    'success' => false,
                    'status' => $statusOfTransaction,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while processing the payment.',
            ], 500);
        }
    }
    public function delete_property(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }
        DB::beginTransaction();
        $property = Property::find($request->id);
        if ($property) {
            if ($property->delete()) {

                $chat = Chats::where('property_id', $property->id);
                if ($chat) {
                    $chat->delete();
                }



                $slider = Slider::where('propertys_id', $property->id);
                if ($slider) {
                    $slider->delete();
                }


                $notifications = Notifications::where('propertys_id', $property->id);
                if ($notifications) {
                    $notifications->delete();
                }

                if ($property->getRawOriginal('title_image') != '') {
                    if (file_exists(public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH') . $property->getRawOriginal('title_image'))) {
                        unlink(public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH') . $property->getRawOriginal('title_image'));
                    }
                }
                foreach ($property->gallery as $row) {
                    if (PropertyImages::where('id', $row->id)->delete()) {
                        if ($row->image_url != '') {
                            if (file_exists(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $property->id . "/" . $row->image)) {
                                unlink(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $property->id . "/" . $row->image);
                            }
                        }
                    }
                }




                $slider = Slider::where('propertys_id', $property->id)->get();

                foreach ($slider as $row) {
                    $image = $row->image;

                    if (Slider::where('id', $row->id)->delete()) {
                        if (file_exists(public_path('images') . config('global.SLIDER_IMG_PATH') . $image)) {
                            unlink(public_path('images') . config('global.SLIDER_IMG_PATH') . $image);
                        }
                    }
                }
                DB::commit();
                $response['error'] = false;
                $response['message'] =  'Delete Successfully';
            } else {
                $response['error'] = true;
                $response['message'] = 'something wrong';
            }
        } else {
            $response['error'] = true;
            $response['message'] = 'Data not found';
        }
        return response()->json($response);
    }
    public function assign_package(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'package_id' => 'required',
            'product_id' => 'required_if:in_app,true',


        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }
        $current_user = Auth::user()->id;

        $user = Customer::find($current_user);

        $start_date =  Carbon::now();

        if ($request->in_app === true) {

            $package = Package::where('ios_product_id', $request->product_id)->find($request->package_id);
        } else {


            $package = Package::where('price', 0)->find($request->package_id);
        }
        $data_exists = UserPurchasedPackage::where('modal_id', $current_user)->get();

        if ($package) {

            if ($package->type == "premium_user") {
                UserPurchasedPackage::where('modal_id', $current_user)->where('package_id', $package->id)->delete();
            }
            $user_package = new UserPurchasedPackage();

            $user_package->modal()->associate($user);
            $user_package->package_id = $request->package_id;
            $user_package->start_date = $start_date;
            $user_package->end_date = $package->duration != 0 ? Carbon::now()->addDays($package->duration) : NULL;
            $user_package->save();

            if ($package->type == "premium_user") {
                $user->is_premium = 1;
            } else {

                $user->subscription = 1;
            }
            $user->update();
            $response['error'] = false;
            $response['message'] =  'Package Purchased Successfully';
        } else {
            $response['error'] = false;
            $response['message'] =  'Package Not Found';
        }
        return response()->json($response);
    }
    // public function assign_package(Request $request)
    // {

    //     $validator = Validator::make($request->all(), [
    //         'package_id' => 'required',
    //         'product_id' => 'required_if:in_app,true',


    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'error' => true,
    //             'message' => $validator->errors()->first(),
    //         ]);
    //     }
    //     $payload = JWTAuth::getPayload($this->bearerToken($request));
    //     $current_user = ($payload['customer_id']);
    //     $user = Customer::find($current_user);
    //     $start_date =  Carbon::now();
    //     if ($request->in_app) {
    //         $package = Package::where('ios_product_id', $request->product_id)->find($request->package_id);
    //     } else {

    //         $package = Package::where('price', 0)->find($request->package_id);
    //     }
    //     $data_exists = UserPurchasedPackage::where('modal_id', $current_user)->get();

    //     if ($package) {

    //         $user_package = new UserPurchasedPackage();

    //         $user_package->modal()->associate($user);
    //         $user_package->package_id = $request->package_id;
    //         $user_package->start_date = $start_date;
    //         $user_package->end_date = $package->duration != 0 ? Carbon::now()->addDays($package->duration) : NULL;
    //         $user_package->save();
    //         if ($data_exists) {
    //             UserPurchasedPackage::where('modal_id', $current_user)->where('id', '!=', $user_package->id)->delete();
    //         }
    //         $user->subscription = 1;
    //         $user->update();
    //         $response['error'] = false;
    //         $response['message'] =  'Package Purchased Successfully';
    //     } else {
    //         $response['error'] = false;
    //         $response['message'] =  'Package Not Found';
    //     }
    //     return response()->json($response);
    // }
    public function get_app_settings(Request $request)
    {
        $result =  Setting::select('type', 'data')->whereIn('type', ['splash_logo', 'app_home_screen', 'placeholder_logo', 'light_tertiary', 'light_secondary', 'light_primary', 'dark_tertiary', 'dark_secondary', 'dark_primary'])->get();


        $tempRow = [];

        if (($request->user_id) != "") {
            update_subscription($request->user_id);

            $customer_data = Customer::find($request->user_id);
            if ($customer_data) {
                if ($customer_data->isActive == 0) {

                    $tempRow['is_active'] = false;
                } else {
                    $tempRow['is_active'] = true;
                }
            }
        }



        foreach ($result as $row) {
            $tempRow[$row->type] = $row->data;

            if ($row->type == 'splash_logo' || $row->type == 'app_home_screen' || $row->type = "placeholder_logo") {

                $tempRow[$row->type] = url('/assets/images/logo/') . '/' . $row->data;
            }
        }

        $response['error'] = false;
        $response['data'] = $tempRow;
        return response()->json($response);
    }
    public function get_seo_settings(Request $request)
    {
        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;

        $seo_settings = SeoSettings::select('id', 'page', 'image', 'title', 'description', 'keywords');


        if (isset($request->page) && !empty($request->page)) {

            $seo_settings->where('page', 'LIKE', "%$request->page%");
        } else {
            $seo_settings->where('page', 'LIKE', "%homepage%");
        }

        $total = $seo_settings->count();
        $result = $seo_settings->skip($offset)->take($limit)->get();


        // $seo_settingsWithCount = Category::withCount('properties')->get();
        $rows = array();
        $count = 0;
        if (!$result->isEmpty()) {

            foreach ($result as $key => $row) {
                $tempRow['id'] = $row->id;
                $tempRow['page'] = $row->page;
                $tempRow['meta_image'] = $row->image;

                if ($row->page == "properties-city") {
                    $tempRow['meta_title'] = "[Your City]'s Finest:" . $row->title;
                    $tempRow['meta_description'] = "Discover the charm of living near [Your City]." . $row->description;
                } else {

                    $tempRow['meta_title'] = $row->title;
                    $tempRow['meta_description'] = $row->description;
                }
                $tempRow['meta_keywords'] = $row->keywords;

                $rows[] = $tempRow;

                $count++;
            }
        }


        if (!$result->isEmpty()) {
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";


            $response['total'] = $total;
            $response['data'] = $rows;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }
    public function get_interested_users(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'property_id' => 'required_without:slug_id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validator->errors()->first(),
                ]);
            }
            $offset = isset($request->offset) ? $request->offset : 0;
            $limit = isset($request->limit) ? $request->limit : 10;
            if (isset($request->slug_id)) {
                $property = Property::where('slug_id', $request->slug_id)->first();
                $property_id = $property->id;
            } else {
                $property_id = $request->property_id;
            }
            $interested_users = InterestedUser::with('customer:id,name,profile,email,mobile')
                ->where('property_id', $property_id)
                ->take($limit)
                ->skip($offset)
                ->get();


            $rows = $interested_users->pluck('customer')->filter()->flatten();



            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['data'] = $rows;
            $response['total'] = count($rows);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->__toString(),
            ]);
        }

        return response()->json($response);
    }
    public function post_project(Request $request)
    {
        if ($request->has('id')) {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'description' => 'required',
                'image' => 'required|file|max:3000|mimes:jpeg,png,jpg',
                'category_id' => 'required',
                'city' => 'required',
                'state' => 'required',
                'country' => 'required',
            ]);
        }
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }
        try {
            DB::beginTransaction();
            $currentUser = Auth::user()->id;

            if (!(isset($request->id))) {
                $currentPackage = $this->getCurrentPackage($currentUser, 1);
                if (!($currentPackage)) {
                    $response['error'] = false;
                    $response['message'] = 'Package not found';
                    return response()->json($response);
                }
                $project = new Projects();
            } else {
                $project = Projects::where('added_by', $currentUser)->find($request->id);
                if (!$project) {
                    $response['error'] = false;
                    $response['message'] = 'Project Not Found ';
                }
            }

            if ($request->category_id) {
                $project->category_id = $request->category_id;
            }
            if ($request->description) {
                $project->description = $request->description;
            }
            if ($request->location) {
                $project->location = $request->location;
            }
            if ($request->meta_title) {
                $project->meta_title = $request->meta_title;
            }
            if ($request->meta_description) {
                $project->meta_description = $request->meta_description;
            }
            if ($request->meta_keywords) {
                $project->meta_keywords = $request->meta_keywords;
            }
            $project->added_by = $currentUser;
            if ($request->country) {
                $project->country = $request->country;
            }
            if ($request->state) {
                $project->state = $request->state;
            }
            if ($request->city) {
                $project->city = $request->city;
            }
            if ($request->latitude) {
                $project->latitude = $request->latitude;
            }
            if ($request->longitude) {
                $project->longitude = $request->longitude;
            }
            if ($request->video_link) {
                $project->video_link = $request->video_link;
            }
            if ($request->type) {
                $project->type = $request->type;
            }
            if ($request->id) {
                if ($project->title !== $request->title) {
                    $title = !empty($request->title) ? $request->title : $project->title;
                    $project->title = $title;
                    $project->slug_id = generateUniqueSlug($title, 4);
                } else {
                    $title = $request->title;
                    $project->title = $title;
                }
                if ($request->hasFile('image')) {
                    $project->image = store_image($request->file('image'), 'PROJECT_TITLE_IMG_PATH');
                }
                if ($request->hasFile('meta_image')) {
                    $project->meta_image = store_image($request->file('meta_image'), 'PROJECT_SEO_IMG_PATH');
                } else {
                    if ($project->meta_image) {
                        unlink_image($project->meta_image);
                    }
                    $project->meta_image = "";
                }
            } else {
                $project->title = $request->title;
                $project->image = $request->hasFile('image') ? store_image($request->file('image'), 'PROJECT_TITLE_IMG_PATH') : '';
                $project->meta_image = $request->hasFile('meta_image') ? store_image($request->file('meta_image'), 'PROJECT_SEO_IMG_PATH') : '';
                $title = $request->title;
                $project->slug_id = generateUniqueSlug($title, 4);
            }

            $project->save();

            if ($request->remove_gallery_images) {
                $remove_gallery_images = explode(',', $request->remove_gallery_images);
                foreach ($remove_gallery_images as $key => $value) {
                    $gallary_images = ProjectDocuments::find($value);
                    unlink_image($gallary_images->name);
                    $gallary_images->delete();
                }
            }

            if ($request->remove_documents) {
                $remove_documents = explode(',', $request->remove_documents);
                foreach ($remove_documents as $key => $value) {
                    $gallary_images = ProjectDocuments::find($value);
                    unlink_image($gallary_images->name);
                    $gallary_images->delete();
                }
            }

            if ($request->hasfile('gallery_images')) {
                foreach ($request->file('gallery_images') as $file) {
                    $gallary_image = new ProjectDocuments();
                    $gallary_image->name = store_image($file, 'PROJECT_DOCUMENT_PATH');
                    $gallary_image->project_id = $project->id;
                    $gallary_image->type = 'image';
                    $gallary_image->save();
                }
            }

            if ($request->hasfile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $project_documents = new ProjectDocuments();
                    $project_documents->name = store_image($file, 'PROJECT_DOCUMENT_PATH');
                    $project_documents->project_id = $project->id;
                    $project_documents->type = 'doc';
                    $project_documents->save();
                }
            }

            if ($request->plans) {
                foreach ($request->plans as $key => $plan) {
                    if (isset($plan['id']) && $plan['id'] != '') {
                        $project_plans =  ProjectPlans::find($plan['id']);
                    } else {
                        $project_plans = new ProjectPlans();
                    }
                    if (isset($plan['document'])) {
                        $project_plans->document = store_image($plan['document'], 'PROJECT_DOCUMENT_PATH');
                    }
                    $project_plans->title = $plan['title'];
                    $project_plans->project_id = $project->id;
                    $project_plans->save();
                }
            }


            if ($request->remove_plans) {
                $remove_plans = explode(',', $request->remove_plans);
                foreach ($remove_plans as $key => $value) {
                    $project_plans = ProjectPlans::find($value);
                    unlink_image($project_plans->document);
                    $project_plans->delete();
                }
            }
            if (!(isset($request->id))) {
                $newPropertyLimitCount = 0;
                // Increment the property limit count
                $newPropertyLimitCount = $currentPackage->used_limit_for_property + 1;
                if ($currentPackage->package->property_limit == null) {
                    $addPropertyStatus = 1;
                } else if ($newPropertyLimitCount >= $currentPackage->package->property_limit) {
                    $addPropertyStatus = 0;
                } else {
                    $addPropertyStatus = 1;
                }
                // Update the Limit and status
                UserPurchasedPackage::where('id', $currentPackage->id)->update(['used_limit_for_property' => $newPropertyLimitCount, 'prop_status' => $addPropertyStatus]);
            }
            $result = Projects::with('customer')->with('gallary_images')->with('documents')->with('plans')->with('category:id,category,image')->where('id', $project->id)->get();

            DB::commit();
            $response['error'] = false;
            $response['message'] = isset($request->id) ? 'Project Updated Successfully' : 'Project Post Succssfully';
            $response['data'] = $result;
            return response()->json($response);
        } catch (Exception $e) {
            DB::rollback();
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }
    public function delete_project(Request $request)
    {
        $current_user = Auth::user()->id;

        $validator = Validator::make($request->all(), [

            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }
        $project = Projects::where('added_by', $current_user)->with('gallary_images')->with('documents')->with('plans')->find($request->id);

        if ($project) {
            foreach ($project->gallary_images as $row) {
                if ($project->title_image != '') {
                    unlink_image($row->title_image);
                }
                $gallary_image = ProjectDocuments::find($row->id);
                if ($gallary_image) {
                    if ($row->name != '') {

                        unlink_image($row->name);
                    }
                }
            }

            foreach ($project->documents as $row) {

                $project_documents = ProjectDocuments::find($row->id);
                if ($project_documents) {
                    if ($row->name != '') {

                        unlink_image($row->name);
                    }
                    $project_documents->delete();
                }
            }
            foreach ($project->plans as $row) {

                $project_plans = ProjectPlans::find($row->id);
                if ($project_plans) {
                    if ($row->name != '') {

                        unlink_image($row->document);
                    }
                    $project_plans->delete();
                }
            }
            $project->delete();
            $response['error'] = false;
            $response['message'] =  'Project Delete Successfully';
        } else {
            $response['error'] = true;
            $response['message'] = 'Data not found';
        }
        return response()->json($response);
    }
    public function get_projects(Request $request)
    {
        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;


        $project = Projects::select('*')->with('customer:id,name,profile,email,mobile,address')->with('gallary_images')->with('documents')->with('plans')->with('category:id,category,image');

        $userid = $request->userid;
        $posted_since = $request->posted_since;
        $category_id = $request->category_id;
        $id = $request->id;
        $country = $request->country;
        $state = $request->state;
        $city = $request->city;

        if (isset($userid)) {
            $project = $project->where('added_by', $userid);
        } else {
            $project = $project->where('status', 1);
        }




        if (isset($posted_since)) {
            // 0: last_week   1: yesterday
            if ($posted_since == 0) {
                $project = $project->whereBetween(
                    'created_at',
                    [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]
                );
            }
            if ($posted_since == 1) {
                $project =  $project->whereDate('created_at', Carbon::yesterday());
            }
        }

        if (isset($category_id)) {
            $project = $project->where('category_id', $category_id);
        }
        if (isset($id)) {
            if (isset($request->get_simiilar)) {
                $project = $project->where('id', '!=', $id);
            } else {

                $project = $project->where('id', $id);
            }
        }


        if (isset($request->slug_id)) {


            $category = Category::where('slug_id', $request->slug_id)->first();

            if ($category) {

                $project = $project->where('category_id', $category->id);
            } else {


                if (isset($request->get_similar)) {

                    $project = $project->where('slug_id', '!=', $request->slug_id);
                } else {
                    DB::enableQueryLog();
                    $project = $project->where('slug_id', $request->slug_id);
                }
            }
        }

        if (isset($country)) {
            $project = $project->where('country', $country);
        }
        if (isset($state)) {
            $project = $project->where('state', $state);
        }
        if (isset($city) && $city != '') {
            $project = $project->where('city', $city);
        }



        if (isset($request->search) && !empty($request->search)) {
            $search = $request->search;

            $project = $project->where(function ($query) use ($search) {
                $query->where('title', 'LIKE', "%$search%")->orwhere('address', 'LIKE', "%$search%")->orwhereHas('category', function ($query1) use ($search) {
                    $query1->where('category', 'LIKE', "%$search%");
                });
            });
        }



        $total = $project->get()->count();

        $result = $project->skip($offset)->take($limit)->get();


        if (!$result->isEmpty()) {



            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";

            $response['total'] = $total;
            $response['data'] = $result;
        } else {

            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }

    public function getUserPersonalisedInterest(Request $request)
    {
        try {
            // Get Current User's ID From Token
            $loggedInUserId = Auth::user()->id;

            // Get User Interest Data on the basis of current User
            $userInterest = UserInterest::where('user_id', $loggedInUserId)->first();

            // Get Datas
            $categoriesIds = !empty($userInterest->category_ids) ? explode(',', $userInterest->category_ids) : '';
            $priceRange = $userInterest->property_type != null ? explode(',', $userInterest->price_range) : '';
            $propertyType = $userInterest->property_type == 0 || $userInterest->property_type == 1 ? explode(',', $userInterest->property_type) : '';
            $outdoorFacilitiesIds = !empty($userInterest->outdoor_facilitiy_ids) ? explode(',', $userInterest->outdoor_facilitiy_ids) : '';
            $city = !empty($userInterest->city) ?  $userInterest->city : '';

            // Custom Data Array
            $data = array(
                'user_id'               => $loggedInUserId,
                'category_ids'          => $categoriesIds,
                'price_range'           => $priceRange,
                'property_type'         => $propertyType,
                'outdoor_facilitiy_ids' => $outdoorFacilitiesIds,
                'city'                  => $city,
            );

            $response = array(
                'error' => false,
                'data' => $data,
                'message' => 'Data fetched Successfully'
            );
            return response()->json($response);
        } catch (Exception $e) {
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }

    public function storeUserPersonalisedInterest(Request $request)
    {
        try {
            DB::beginTransaction();
            // Get Current User's ID From Token
            $loggedInUserId = Auth::user()->id;

            // Get User Interest
            $userInterest = UserInterest::where('user_id', $loggedInUserId)->first();

            // If data Exists then update or else insert new data
            if (collect($userInterest)->isNotEmpty()) {
                $response['error'] = false;
                $response['message'] = "Data updated Successfully";
            } else {
                $userInterest = new UserInterest();
                $response['error'] = false;
                $response['message'] = "Data Store Successfully";
            }

            // Change the values
            $userInterest->user_id = $loggedInUserId;
            $userInterest->category_ids = (isset($request->category_ids) && !empty($request->category_ids)) ? $request->category_ids : "";
            $userInterest->outdoor_facilitiy_ids = (isset($request->outdoor_facilitiy_ids) && !empty($request->outdoor_facilitiy_ids)) ? $request->outdoor_facilitiy_ids : null;
            $userInterest->price_range = (isset($request->price_range) && !empty($request->price_range)) ? $request->price_range : "";
            $userInterest->city = (isset($request->city) && !empty($request->city)) ? $request->city : "";
            $userInterest->property_type = isset($request->property_type) && ($request->property_type == 0 || $request->property_type == 1) ? $request->property_type : "0,1";
            $userInterest->save();

            DB::commit();

            // Get Datas
            $categoriesIds = !empty($userInterest->category_ids) ? explode(',', $userInterest->category_ids) : '';
            $priceRange = !empty($userInterest->price_range) ? explode(',', $userInterest->price_range) : '';
            $propertyType = explode(',', $userInterest->property_type);
            $outdoorFacilitiesIds = !empty($userInterest->outdoor_facilitiy_ids) ? explode(',', $userInterest->outdoor_facilitiy_ids) : '';
            $city = !empty($userInterest->city) ?  $userInterest->city : '';

            // Custom Data Array
            $data = array(
                'user_id'               => $userInterest->user_id,
                'category_ids'          => $categoriesIds,
                'price_range'           => $priceRange,
                'property_type'         => $propertyType,
                'outdoor_facilitiy_ids' => $outdoorFacilitiesIds,
                'city'                  => $city,
            );
            $response['data'] = $data;

            return response()->json($response);
        } catch (Exception $e) {
            DB::rollback();
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }

    public function deleteUserPersonalisedInterest(Request $request)
    {
        try {
            DB::beginTransaction();
            // Get Current User From Token
            $loggedInUserId = Auth::user()->id;

            // Get User Interest
            UserInterest::where('user_id', $loggedInUserId)->delete();
            DB::commit();
            $response = array(
                'error' => false,
                'message' => 'Data Deleted Successfully'
            );
            return response()->json($response);
        } catch (Exception $e) {
            DB::rollback();
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }

    public function removeAllPackages(Request $request)
    {
        try {
            DB::beginTransaction();

            $loggedInUserId = Auth::user()->id;

            // Delete All Packages
            UserPurchasedPackage::where('modal_id', $loggedInUserId)->delete();

            // Make subscription and is premium status 0 in customer table
            $customerData = Customer::find($loggedInUserId);
            $customerData->subscription = 0;
            $customerData->is_premium = 0;
            $customerData->save();

            DB::commit();
            $response = array(
                'error' => false,
                'message' => 'Data Deleted Successfully'
            );
            return response()->json($response);
        } catch (Exception $e) {
            DB::rollback();
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }


    public function getAddedProperties(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_type' => 'nullable|in:0,1',
            'is_promoted' => 'nullable|in:1'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }
        try {
            // Get Offset and Limit from payload request
            $offset = isset($request->offset) ? $request->offset : 0;
            $limit = isset($request->limit) ? $request->limit : 10;

            // Get Logged In User data
            $loggedInUserData = Auth::user();
            // Get Current Logged In User ID
            $loggedInUserID = $loggedInUserData->id;

            // Check the property's post is done by customer and added by logged in user
            $propertyQuery = Property::where(['post_type' => 1, 'added_by' => $loggedInUserID])
                // When property type is passed in payload show data according property type that is sell or rent
                ->when($request->filled('property_type'), function ($query) use ($request) {
                    return $query->where('propery_type', $request->property_type);
                })
                ->when($request->filled('id'), function ($query) use ($request) {
                    return $query->where('id', $request->id);
                })
                ->when($request->filled('slug_id'), function ($query) use ($request) {
                    return $query->where('slug_id', $request->slug_id);
                })
                // when is_promoted is passed then show only property who has been featured (advertised)
                ->when($request->is_promoted == 1, function ($query) {
                    return $query->has('advertisement');
                })
                // Pass the Property Data with Category and Advertisement Relation Data
                ->with('category', 'advertisement');

            // Get Total Views by Sum of total click of each property
            $totalViews = $propertyQuery->sum('total_click');

            // Get total properties
            $totalProperties = $propertyQuery->count();

            // Get the property data with extra data and changes :- is_premium, post_created and promoted
            $propertyData = $propertyQuery->skip($offset)->take($limit)->orderBy('id', 'DESC')->get()->map(function ($property) use ($loggedInUserData) {
                $property->is_premium = $property->is_premium == 1 ? true : false;
                $property->property_type = $property->propery_type;
                $property->post_created = $property->created_at->diffForHumans();
                $property->promoted = $property->advertisement->isNotEmpty();
                $property->parameters = $property->parameters;
                $property->assign_facilities = $property->assign_facilities;

                // Add User's Details
                $property->customer_name = $loggedInUserData->name;
                $property->email = $loggedInUserData->email;
                $property->mobile = $loggedInUserData->mobile;
                $property->profile = $loggedInUserData->profile;
                return $property;
            });

            $response = array(
                'error' => false,
                'data' => $propertyData,
                'total' => $totalProperties,
                'total_views' => $totalViews,
                'message' => 'Data fetched Successfully'
            );
            return response()->json($response);
        } catch (Exception $e) {
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }


    /**
     * Homepage Data API
     * Params :- None
     */
    public function homepageData()
    {
        try {
            $projectsData = Projects::select('id', 'slug_id', 'city', 'state', 'country', 'title', 'type', 'image', 'location', 'category_id')->where('status', 1)->with('category:id,slug_id,image,category', 'gallary_images')->orderBy('id', 'desc')->limit(12)->get();
            $slidersData = Slider::select('id', 'image', 'propertys_id')->with('property:id,slug_id,title,title_image,price,propery_type,video_link')->orderBy('sequence', 'ASC')->get()->map(function ($slider) {
                $slider->parameters = $slider->property->parameters;
                return $slider;
            });
            $categoriesData = Category::select('id', 'category', 'image', 'slug_id')->withCount('properties')->limit(12)->get();
            $articlesData = Article::select('id', 'slug_id', 'category_id', 'title', 'description', 'image', 'created_at')->with('category:id,slug_id,image,category')->limit(5)->get();
            $agentsData = Customer::where(function ($query) {
                $query->where('isActive', 1);
            })->where(function ($query) {
                $query->whereHas('projects', function ($query) {
                    $query->where('status', 1);
                })->orWhereHas('property', function ($query) {
                    $query->where('status', 1);
                });
            })->select('id', 'name', 'slug_id', 'profile', 'email', 'mobile')
                ->withCount([
                    'projects' => function ($query) {
                        $query->where('status', 1);
                    },
                    'property' => function ($query) {
                        $query->where('status', 1);
                    }
                ])->orderBy('id', 'desc')->limit(12)->get();

            // Product Data Query
            $propertyDataQuery = Property::select('id', 'slug_id', 'category_id', 'city', 'state', 'country', 'price', 'propery_type', 'title', 'title_image', 'is_premium', 'address', 'rentduration')->with('category:id,slug_id,image,category')->where('status', 1);
            $featuredSection = $propertyDataQuery->clone()->has('advertisement')->orderBy('id', 'DESC')->limit(12)->get()->map(function ($propertyData) {
                $propertyData->property_type = $propertyData->propery_type;
                $propertyData->parameters = $propertyData->parameters;
                return $propertyData;
            });
            $mostLikedProperties = $propertyDataQuery->clone()->withCount('favourite')->orderBy('favourite_count', 'DESC')->limit(12)->get()->map(function ($propertyData) {
                $propertyData->property_type = $propertyData->propery_type;
                $propertyData->parameters = $propertyData->parameters;
                return $propertyData;
            });;
            $mostViewedProperties = $propertyDataQuery->clone()->orderBy('total_click', 'DESC')->limit(12)->get()->map(function ($propertyData) {
                $propertyData->property_type = $propertyData->propery_type;
                $propertyData->parameters = $propertyData->parameters;
                return $propertyData;
            });;

            // Add Data in Homepage Array Data
            $homepageData = array(
                'featured_section' => $featuredSection,
                'most_liked_properties' => $mostLikedProperties,
                'most_viewed_properties' => $mostViewedProperties,
                'project_section' => $projectsData,
                'slider_section' => $slidersData,
                'categories_section' => $categoriesData,
                'article_section' => $articlesData,
                'agents_list' => $agentsData
            );

            // Get The data only on the Auth Data exists
            if (Auth::guard('sanctum')->check()) {
                $loggedInUserId = Auth::guard('sanctum')->user()->id;
                $cityOfUser = Auth::guard('sanctum')->user()->city;
                if (collect($cityOfUser)->isNotEmpty()) {
                    $nearByProperties = $propertyDataQuery->clone()->where('city', $cityOfUser)->orderBy('id', 'DESC')->limit(12)->get()->map(function ($propertyData) {
                        $propertyData->property_type = $propertyData->propery_type;
                        $propertyData->parameters = $propertyData->parameters;
                        return $propertyData;
                    });
                    $homepageData['nearby_properties'] = $nearByProperties;

                    // User Recommendation Query
                    $userRecommendationQuery = $propertyDataQuery->clone();
                    // Get User Recommendation Data
                    $userInterestData = UserInterest::where('user_id', $loggedInUserId)->first();

                    // Check the User's Interested Category Ids
                    if (!empty($userInterestData->category_ids)) {
                        $categoryIds = explode(',', $userInterestData->category_ids);
                        $userRecommendationQuery = $userRecommendationQuery->whereIn('category_id', $categoryIds);
                    }

                    // Check User's Interested Price Range
                    if (!empty($userInterestData->price_range)) {
                        $minPrice = explode(',', $userInterestData->price_range)[0]; // Get User's Minimum Price
                        $maxPrice = explode(',', $userInterestData->price_range)[1]; // Get User's Maximum Price

                        if (isset($maxPrice) && isset($minPrice)) {
                            $minPrice = floatval($minPrice);
                            $maxPrice = floatval($maxPrice);
                            $userRecommendationQuery = $userRecommendationQuery->where(function ($query) use ($minPrice, $maxPrice) {
                                $query->whereRaw("CAST(price AS DECIMAL(10, 2)) >= ?", [$minPrice])
                                    ->whereRaw("CAST(price AS DECIMAL(10, 2)) <= ?", [$maxPrice]);
                            });
                        }
                    }

                    // Check User's Interested City
                    if (!empty($userInterestData->city)) {
                        $city = $userInterestData->city;
                        $userRecommendationQuery = $userRecommendationQuery->where('city', $city);
                    }

                    // Check User's Interested Property Types
                    if (!empty($userInterestData->property_type)) {
                        $propertyType = explode(',',  $userInterestData->property_type);
                        $userRecommendationQuery = $userRecommendationQuery->whereIn('propery_type', $propertyType);
                    }

                    // Check User's Interested Outdoor Facilities
                    if (!empty($userInterestData->outdoor_facilitiy_ids)) {
                        $outdoorFacilityIds = explode(',', $userInterestData->outdoor_facilitiy_ids);
                        $userRecommendationQuery = $userRecommendationQuery->whereHas('assignfacilities.outdoorfacilities', function ($q) use ($outdoorFacilityIds) {
                            $q->whereIn('id', $outdoorFacilityIds);
                        });
                    }
                    $userRecommendationData = $userRecommendationQuery->orderBy('id', 'DESC')->limit(12)->get()->map(function ($propertyData) {
                        $propertyData->property_type = $propertyData->propery_type;
                        $propertyData->parameters = $propertyData->parameters;
                        return $propertyData;
                    });
                    $homepageData['user_recommendation'] = $userRecommendationData;
                }
            }


            $response = array(
                'error' => false,
                'data' => $homepageData,
                'message' => 'Data fetched Successfully'
            );
            return response()->json($response);
        } catch (Exception $e) {
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }

    /**
     * Agent List API
     * Params :- limit and offset
     */
    public function getAgentList(Request $request)
    {
        try {
            // Get Offset and Limit from payload request
            $offset = isset($request->offset) ? $request->offset : 0;
            $limit = isset($request->limit) ? $request->limit : 10;

            $agentsListQuery = Customer::where(function ($query) {
                $query->where('isActive', 1);
            })->where(function ($query) {
                $query->whereHas('projects', function ($query) {
                    $query->where('status', 1);
                })->orWhereHas('property', function ($query) {
                    $query->where('status', 1);
                });
            })->select('id', 'name', 'slug_id', 'profile', 'email', 'mobile')
                ->withCount(['projects' => function ($query) {
                    $query->where('status', 1);
                }, 'property' => function ($query) {
                    $query->where('status', 1);
                }]);
            $agentListCount = $agentsListQuery->clone()->count();
            $agentListData = $agentsListQuery->clone()->skip($offset)->take($limit)->orderBy('id', 'DESC')->get();

            $response = array(
                'error' => false,
                'total' => $agentListCount,
                'data' => $agentListData,
                'message' => 'Data fetched Successfully'
            );
            return response()->json($response);
        } catch (Exception $e) {
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }

    /**
     * Agent Properties API
     * Params :- id or slug_id, limit, offset and is_project
     */
    public function getAgentProperties(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug_id' => 'required_without:id',
            'is_projects' => 'nullable|in:1'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }
        try {
            // Get Offset and Limit from payload request
            $offset = isset($request->offset) ? $request->offset : 0;
            $limit = isset($request->limit) ? $request->limit : 10;

            // Customer Query
            $customerQuery = Customer::select('id', 'slug_id', 'name', 'profile', 'mobile', 'email', 'address', 'city', 'country', 'state', 'facebook_id', 'twiiter_id as twitter_id', 'youtube_id', 'instagram_id', 'about_me')->where(function ($query) {
                $query->where('isActive', 1);
            })->withCount(['projects' => function ($query) {
                $query->where('status', 1);
            }, 'property' => function ($query) {
                $query->where('status', 1);
            }]);
            // Check if id exists or slug id on the basis of get agent id
            if ($request->has('id') && !empty($request->id)) {
                $addedBy = $request->id;
                // Get Customer Data
                $customerData = $customerQuery->clone()->where('id', $request->id)->first();
                $addedBy = !empty($customerData) ? $customerData->id : "";
            } else if ($request->has('slug_id')) {
                // Get Customer Data
                $customerData = $customerQuery->clone()->where('slug_id', $request->slug_id)->first();
                $addedBy = !empty($customerData) ? $customerData->id : "";
            }

            // if there is agent id then only get properties of it
            if (!empty($addedBy)) {

                if ($request->has('is_projects') && !empty($request->is_projects) && $request->is_projects == 1) {
                    $projectQuery = Projects::select('id', 'slug_id', 'city', 'state', 'country', 'title', 'type', 'image', 'location', 'category_id')->where(['status' => 1, 'added_by' => $addedBy])->with('gallary_images', 'category:id,slug_id,image,category');
                    $totalProjects = $projectQuery->clone()->count();
                    $projectData = $projectQuery->clone()->skip($offset)->take($limit)->get();
                    $totalData = $totalProjects;
                } else {
                    $propertiesQuery = Property::select('id', 'slug_id', 'city', 'state', 'category_id', 'country', 'price', 'propery_type', 'title', 'title_image', 'is_premium', 'address', 'added_by')->where(['status' => 1, 'added_by' => $addedBy])->with('category:id,slug_id,image,category');
                    $totalProperties = $propertiesQuery->clone()->count();
                    $propertiesData = $propertiesQuery->clone()->orderBy('id', 'DESC')->skip($offset)->take($limit)->get()->map(function ($property) {
                        $property->property_type = $property->propery_type;
                        $property->parameters = $property->parameters;
                        unset($property->propery_type);
                        return $property;
                    });
                    $totalData = $totalProperties;
                }
            }

            $response = array(
                'error' => false,
                'total' => $totalData ?? 0,
                'data' => array(
                    'customer_data' => $customerData ?? array(),
                    'properties_data' => $propertiesData ?? array(),
                    'projects_data' => $projectData ?? array(),
                ),
                'message' => 'Data fetched Successfully'
            );
            return response()->json($response);
        } catch (Exception $e) {
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }


    public function getWebSettings(Request $request)
    {
        try {
            // Types for web requirement only
            $types = array('company_name', 'currency_symbol', 'default_language', 'number_with_suffix', 'web_maintenance_mode', 'privacy_policy', 'Terms_conditions', 'company_tel', 'company_tel2', 'system_version', 'web_logo', 'web_footer_logo', 'web_placeholder_logo', 'company_email', 'latitude', 'longitude', 'company_address', 'system_color', 'svg_clr', 'iframe_link', 'facebook_id', 'instagram_id', 'twitter_id', 'youtube_id', 'playstore_id', 'sell_background', 'appstore_id', 'category_background', 'web_maintenance_mod', 'seo_settings', 'company_tel1', 'place_api_key', 'stripe_publishable_key', 'paystack_public_key', 'sell_web_color', 'sell_web_background_color', 'rent_web_color', 'rent_web_background_color', 'about_us', 'terms_conditions', 'privacy_policy');

            // Query the Types to Settings Table to get its data
            $result =  Setting::select('type', 'data')->whereIn('type', $types)->get();

            // Check the result data is not empty
            if (collect($result)->isNotEmpty()) {
                $settingsData = array();

                // Loop on the result data
                foreach ($result as $row) {
                    // Change data according to conditions
                    if ($row->type == 'company_logo') {
                        // Add logo image with its url
                        $settingsData[$row->type] = url('/assets/images/logo/logo.png');
                    } else if ($row->type == 'seo_settings') {
                        // Change Value to Bool
                        $settingsData[$row->type] = $row->data == 1 ? true : false;
                    } else if ($row->type == 'web_logo' || $row->type == 'web_placeholder_logo' || $row->type == 'web_footer_logo') {
                        // Add Full URL to the specified type
                        $settingsData[$row->type] = url('/assets/images/logo/') . '/' . $row->data;
                    } else if ($row->type == 'place_api_key') {
                        // Add Full URL to the specified type
                        $publicKey = file_get_contents(base_path('public_key.pem')); // Load the public key
                        $encryptedData = '';
                        if (openssl_public_encrypt($row->data, $encryptedData, $publicKey)) {
                            $settingsData[$row->type] = base64_encode($encryptedData);
                        } else {
                            $settingsData[$row->type] = "";
                        }
                    } else {
                        // add the data as it is in array
                        $settingsData[$row->type] = $row->data;
                    }
                }

                $user_data = User::find(1);
                $settingsData['admin_name'] = $user_data->name;
                $settingsData['admin_image'] = url('/assets/images/faces/2.jpg');
                $settingsData['demo_mode'] = env('DEMO_MODE');
                $settingsData['img_placeholder'] = url('/assets/images/placeholder.svg');

                // if Token is passed of current user.
                if (collect(Auth::guard('sanctum')->user())->isNotEmpty()) {
                    $loggedInUserId = Auth::guard('sanctum')->user()->id;
                    update_subscription($loggedInUserId);

                    $customerDataQuery = Customer::select('id', 'subscription', 'is_premium', 'isActive');
                    $customerData = $customerDataQuery->clone()->find($loggedInUserId);

                    // Check Active of current User
                    if (collect($customerData)->isNotEmpty()) {
                        $settingsData['is_active'] = $customerData->isActive == 1 ? true : false;
                    } else {
                        $settingsData['is_active'] = false;
                    }

                    // Check the subscription
                    if (collect($customerData)->isNotEmpty()) {
                        $settingsData['is_premium'] = $customerData->is_premium == 1 ? true : ($customerData->subscription == 1 ? true : false);
                        $settingsData['subscription'] = $customerData->subscription == 1 ? true : false;
                    } else {
                        $settingsData['is_premium'] = false;
                        $settingsData['subscription'] = false;
                    }
                }


                // Check the min_price and max_price
                $settingsData['min_price'] = DB::table('propertys')->selectRaw('MIN(price) as min_price')->value('min_price');
                $settingsData['max_price'] = DB::table('propertys')->selectRaw('MAX(price) as max_price')->value('max_price');

                // Get Languages Data
                $language = Language::select('id', 'code', 'name')->get();
                $settingsData['languages'] = $language;

                $response['error'] = false;
                $response['message'] = "Data Fetch Successfully";
                $response['data'] = $settingsData;
            } else {
                $response['error'] = false;
                $response['message'] = "No data found!";
                $response['data'] = [];
            }
            return response()->json($response);
        } catch (Exception $e) {
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }


    public function getAppSettings(Request $request)
    {
        try {
            $types = array("company_name", "currency_symbol", "ios_version", "default_language", "force_update", "android_version", "number_with_suffix", "maintenance_mode", "company_tel1", "company_tel2", "system_version", "company_email", "latitude", "longitude", "company_address", "place_api_key", "svg_clr", "playstore_id", "sell_background", "appstore_id", "seo_settings", "show_admob_ads", "android_banner_ad_id", "ios_banner_ad_id", "android_interstitial_ad_id", "ios_interstitial_ad_id", "android_native_ad_id", "ios_native_ad_id", "demo_mode", "min_price", "max_price", "privacy_policy", "terms_conditions", "about_us");

            // Query the Types to Settings Table to get its data
            $result =  Setting::select('type', 'data')->whereIn('type', $types)->get();

            // Check the result data is not empty
            if (collect($result)->isNotEmpty()) {
                $settingsData = array();

                // Loop on the result data
                foreach ($result as $row) {
                    if ($row->type == "place_api_key") {
                        $publicKey = file_get_contents(base_path('public_key.pem')); // Load the public key
                        $encryptedData = '';
                        if (openssl_public_encrypt($row->data, $encryptedData, $publicKey)) {
                            $settingsData[$row->type] = base64_encode($encryptedData);
                        }
                    } else {
                        // add the data as it is in array
                        $settingsData[$row->type] = $row->data;
                    }
                }

                $settingsData['demo_mode'] = env('DEMO_MODE');

                // Check the min_price and max_price
                $settingsData['min_price'] = DB::table('propertys')->selectRaw('MIN(price) as min_price')->value('min_price');
                $settingsData['max_price'] = DB::table('propertys')->selectRaw('MAX(price) as max_price')->value('max_price');

                // Get Languages Data
                $language = Language::select('id', 'code', 'name')->get();
                $settingsData['languages'] = $language;

                $response['error'] = false;
                $response['message'] = "Data Fetch Successfully";
                $response['data'] = $settingsData;
            } else {
                $response['error'] = false;
                $response['message'] = "No data found!";
                $response['data'] = [];
            }
            return response()->json($response);
        } catch (Exception $e) {
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }

    public function getLanguagesData()
    {
        try {
            $languageData = Language::select('id', 'code', 'name')->get();
            if (collect($languageData)->isNotEmpty()) {
                $response['error'] = false;
                $response['message'] = "Data Fetch Successfully";
                $response['data'] = $languageData;
            } else {
                $response['error'] = false;
                $response['message'] = "No data found!";
                $response['data'] = [];
            }
            return response()->json($response);
        } catch (Exception $e) {
            $response = array(
                'error' => true,
                'message' => 'Something Went Wrong'
            );
            return response()->json($response, 500);
        }
    }

    /**
     * Get Current Package
     * Params :- user id, package type 1 - property and 2 - advertisement
     */
    function getCurrentPackage($userId, $packageType)
    {
        if ($packageType == 1) {
            $currentPackage = UserPurchasedPackage::where(['modal_id' => $userId, 'prop_status' => 1])->whereHas('package', function ($q) {
                $q->where('property_limit', '>', 0)->orWhere('property_limit', null);
            })->with('package:id,property_limit')->first();
        } else {
            $currentPackage = UserPurchasedPackage::where(['modal_id' => $userId, 'adv_status' => 1])->whereHas('package', function ($q) {
                $q->where('advertisement_limit', '>', 0)->orWhere('advertisement_limit', null);
            })->with('package:id,advertisement_limit,duration')->first();
        }
        return $currentPackage;
    }
}
