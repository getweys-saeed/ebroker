<?php

namespace App\Http\Controllers;

use App\Models\AssignedOutdoorFacilities;
use App\Models\AssignParameters;
use App\Models\Customer;
use App\Models\Notifications;
use App\Models\ProjectCategory;
use App\Models\Projects;
use App\Models\Setting;
use App\Models\UserPurchasedPackage;
use App\Models\Usertokens;
use App\Models\Category;
use App\Models\OutdoorFacilities;
use App\Models\parameter;
use App\Models\ProjectDocuments;
use App\Models\ProjectPlans;
use App\Services\BootstrapTableService;
use App\Services\ResponseService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $category = ProjectCategory::all();
        return view('project.index', compact('category'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $parameters = parameter::all();
        $categorys = ProjectCategory::where('status', '1')->get();
        $facility = OutdoorFacilities::all();
        $currency_symbol = Setting::where('type', 'currency_symbol')->pluck('data')->first();
        return view("project.create", compact('parameters', 'facility', 'categorys', 'currency_symbol'));
    }




    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'propery_type' => 'required',
            'square_yd' => 'required',
            'description' => 'required',
            'image' => 'required|file|mimes:jpg,png,jpeg|max:2048',
            'category_id' => 'required',
            'city' => 'required',
            'state' => 'required',
            'country' => 'required',
            'price' => 'required',
            'documents.*' => 'required|file|mimes:jpg,png,jpeg,pdf|max:2048',
            'gallery_images.*' => 'required|file|mimes:jpg,png,jpeg|max:2048',
            'plans.*' => 'required|file|mimes:jpg,png,jpeg,pdf|max:2048',
            'rentduration' => 'required',
            'type' => 'required',
            // 'slug_id' => 'required',
        ]);

        // If validation fails, redirect back with errors
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();
            $currentUser = 0;
            // dd($currentUser);
            // dd($currentUser);
            $arr = [];


            // Check if the project exists and belongs to the current user

            $project = new Projects();
            $project->added_by = $currentUser;


            // Update project details
            $project->title = $request->title;
            $project->description = $request->description;
            $project->category_id = $request->category_id;
            $project->city = $request->city;
            $project->state = $request->state;
            $project->type = $request->type;
            $project->propery_type = $request->propery_type;
            $project->square_yd = $request->square_yd;
            $project->price = $request->price;
            $project->rentduration = $request->rentduration;
            $project->country = $request->country;
            $project->latitude = (isset($request->latitude)) ? $request->latitude : '';
            $project->location = (isset($request->location)) ? $request->location : '';
            $project->longitude = (isset($request->longitude)) ? $request->longitude : '';
            $project->added_by  = (isset($request->added_by)) ? $request->added_by  : $currentUser;
            $project->video_link = (isset($request->video_link)) ? $request->video_link : '';
            $project->total_click = (isset($request->total_click)) ? $request->total_click : '';
            $project->slug_id = generateUniqueSlug($project->title, 1);

            // Image handling
            if ($request->hasFile('image')) {
                $project->image = store_image($request->file('image'), 'PROJECT_TITLE_IMG_PATH');
            }

            if ($request->has('meta_title')) {
                $project->meta_title = $request->meta_title;
            }
            if ($request->has('meta_description')) {
                $project->meta_description = $request->meta_description;
            }
            if ($request->has('meta_keywords')) {
                $project->meta_keywords = $request->meta_keywords;
            }
            if ($request->has('latitude')) {
                $project->latitude = $request->latitude;
            }
            if ($request->has('longitude')) {
                $project->longitude = $request->longitude;
            }
            if ($request->has('video_link')) {
                $project->video_link = $request->video_link;
            }
            if ($request->has('type')) {
                $project->type = $request->type;
            }

            // Saving the project
            $project->save();
            $facility = OutdoorFacilities::all();

            foreach ($facility as $value) {

                // $distanceKey = 'facility_distance[' . $value->id . ']';
                // $distance = $request->input($distanceKey);

                // if ($distance && $distance != '') {

                //     $facilities = new AssignedOutdoorFacilities();
                //     $facilities->facility_id = $value->id;
                //     $facilities->property_id = null; // Set property ID if needed
                //     $facilities->project_id = $project->id; // Set the project ID separately
                //     $facilities->distance = $distanceKey; // The distance value from the input
                //     $facilities->save();
                // }


                foreach ($request->input('facility_distance', []) as $facility_id => $distance) {
                    if ($distance) {
                        // Check if the record already exists
                        $existingFacility = AssignedOutdoorFacilities::where('facility_id', $facility_id)
                            ->where('project_id', $project->id)
                            ->first();

                        if ($existingFacility) {
                            // If the record exists, update the distance
                            $existingFacility->distance = $distance;
                            $existingFacility->save();
                        } else {
                            // If the record doesn't exist, create a new one
                            $facilities = new AssignedOutdoorFacilities();
                            $facilities->facility_id = $facility_id;
                            $facilities->project_id = $project->id;
                            $facilities->distance = $distance;
                            $facilities->save();
                        }
                    }
                }
            }


            $parameters = Parameter::all();

            foreach ($parameters as $par) {
                $parameterKey = 'par_' . $par->id;

                if ($request->has($parameterKey)) {

                    $assign_parameter = new AssignParameters();
                    $assign_parameter->parameter_id = $par->id;
                    $assign_parameter->project_id = $project->id;

                    // Check if file has been uploaded for this parameter
                    if ($request->hasFile($parameterKey)) {
                        $destinationPath = public_path('images') . config('global.PARAMETER_IMG_PATH');
                        // Create directory if it doesn't exist
                        if (!is_dir($destinationPath)) {
                            mkdir($destinationPath, 0777, true);
                        }
                        // Generate a unique image name
                        $imageName = microtime(true) . "." . $request->file($parameterKey)->getClientOriginalExtension();
                        // Move the file to the destination path
                        $request->file($parameterKey)->move($destinationPath, $imageName);
                        $assign_parameter->value = $imageName; // Store the file name in the 'value' field
                    } else {
                        // For non-file inputs, store the value directly
                        $inputValue = $request->input($parameterKey);
                        $assign_parameter->value = is_array($inputValue)
                            ? json_encode($inputValue, JSON_FORCE_OBJECT)
                            : $inputValue;
                    }

                    // Associate the parameter with the project and save it
                    $assign_parameter->modal()->associate($project);

                    // Store the parameter's value for further processing if needed
                    $arr[$par->id] = $request->input($parameterKey);
                    try {
                        $assign_parameter->save();
                        log::info("Data saved successfully for parameter ID {$par->id}");
                    } catch (\Exception $e) {
                        Log::error("Error saving data: " . $e->getMessage());
                    }
                }
            }

            // Handle gallery images if provided
            if ($request->hasfile('gallery_images')) {
                foreach ($request->file('gallery_images') as $file) {
                    $gallery_image = new ProjectDocuments();
                    $gallery_image->name = store_image($file, 'PROPERTY_GALLERY_IMG_PATH');
                    $gallery_image->project_id = $project->id;
                    $gallery_image->type = 'image';
                    $gallery_image->save();
                }
            }

            // Handle project documents if provided
            if ($request->hasfile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $project_documents = new ProjectDocuments();
                    $project_documents->name = store_image($file, 'PROJECT_DOCUMENT_PATH');
                    $project_documents->project_id = $project->id;
                    $project_documents->type = 'doc';
                    $project_documents->save();
                }
            }

            if ($request->hasFile('plans')) { // Correct key is checked here
                foreach ($request->file('plans') as $file) { // Use 'plans' here instead of 'documents'
                    $project_plan = new ProjectPlans();
                    $project_plan->document = store_image($file, 'PROJECT_DOCUMENT_PATH');
                    $project_plan->title = $project->title;
                    $project_plan->project_id = $project->id;
                    $project_plan->save();
                }
            }

            // dd($request->all());


            // Commit the transaction
            DB::commit();
            return redirect()->back()->with('success', isset($request->id) ? 'Project Updated Successfully' : 'Project Posted Successfully');
        } catch (Exception $e) {
            dd($e->getMessage());
            DB::rollBack();
            return redirect()->back()->with('error', $e);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'sequence');
        $order = $request->input('order', 'ASC');

        // Initialize the query
        $sql = Projects::with('category')->with('gallary_images')->with('documents')->with('plans')->with('customer')
            ->orderBy($sort, $order);

        // Search filter
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql = $sql->where('id', 'LIKE', "%$search%")
                       ->orWhere('title', 'LIKE', "%$search%")
                       ->orWhere('location', 'LIKE', "%$search%")
                       ->orWhereHas('category', function ($query) use ($search) {
                           $query->where('category', 'LIKE', "%$search%");
                       });
        }

        // Status filter
        if (isset($_GET['status']) && $_GET['status'] != '') {
            $status = $_GET['status'];
            $sql = $sql->where('status', $status);
        }


        // Category filter
        if (isset($_GET['category']) && $_GET['category'] != '') {
            $category_id = $_GET['category'];
            $sql = $sql->where('category_id', $category_id);
        }

        // Get the total count
        $total = $sql->count();

        // Apply limit and offset
        if (isset($_GET['limit'])) {
            $sql->skip($offset)->take($limit);
        }

        // Get the results
        $res = $sql->get();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $count = 1;

        // Get currency symbol from settings
        $currency_symbol = Setting::where('type', 'currency_symbol')->pluck('data')->first();

        // Retrieve additional models
        $outdoor = AssignedOutdoorFacilities::all();
        $indoor = AssignParameters::all();

        foreach ($res as $row) {
            // Prepare the action button
            $action = BootstrapTableService::editButton('', true, null, null, $row->id, null, '', 'bi bi-eye edit_icon');

            $tempRow = $row->toArray();
            $tempRow['edit_status_url'] = 'updateProjectStatus';

            // Check if customer is admin or regular customer
            if ($row->added_by == 0) {  // Admin-added project
                $tempRow['customer_name'] = 'Admin';
                $tempRow['customer_mobile'] = '-';
            } else {  // Customer-added project
                $tempRow['customer_name'] = $row->customer->name ?? 'Unknown';
                $tempRow['customer_mobile'] = $row->customer->mobile ?? '-';
            }

            // Format the price and square yard data
            $tempRow['price'] = $currency_symbol . '' . $row->price . '/' . (!empty($row->rentduration) ? $row->rentduration : 'Month');
            $tempRow['square_yd'] = $row->square_yd;



            $tempRow['action'] = $action;
            $rows[] = $tempRow;
            $count++;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

    // Retrieve the project by ID
    $project = Projects::findOrFail($id); // Fetch the project by ID

    // Retrieve data from each model independently
    $categorys = ProjectCategory::all();        // Fetch all categories
    $images = ProjectDocuments::where('project_id', $id)->get(); // Fetch all gallery images
    $plans = ProjectPlans::where('project_id', $id)->get();                 // Fetch all plans
    $customers = Customer::all();
    $facilit   = AssignedOutdoorFacilities::all();
    $assignedParameters =AssignParameters::where('project_id', $id)->get();
    $parameters = Parameter::all();
    $outdoor = OutdoorFacilities::all();

    // Pass all the retrieved data to the view
    return view('project.edit', compact('outdoor','parameters','assignedParameters', 'project', 'categorys', 'images', 'plans', 'customers','facilit'));


        // If the project doesn't exist, redirect back with an error

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'propery_type' => 'required',
            'square_yd' => 'required',
            'description' => 'required',
            'image' => 'nullable|file|mimes:jpg,png,jpeg|max:2048',
            'category_id' => 'required',
            'city' => 'required',
            'state' => 'required',
            'country' => 'required',
            'price' => 'required',
            'documents.*' => 'required|file|mimes:jpg,png,jpeg,pdf|max:2048',
            'gallery_images.*' => 'required|file|mimes:jpg,png,jpeg|max:2048',
            'plans.*' => 'required|file|mimes:jpg,png,jpeg,pdf|max:2048',
            'rentduration' => 'required',
            'type' => 'required',
            // 'slug_id' => 'required',
        ]);

        // dd("ok");

        // If validation fails, redirect back with errors
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();
            $currentUser = 0;
            // dd($currentUser);
            // dd($currentUser);
            $arr = [];


            // Check if the project exists and belongs to the current user

            $project = Projects::findOrFail($id);
            $project->added_by = $currentUser;


            // Update project details
            $project->title = $request->title;
            $project->description = $request->description;
            $project->category_id = $request->category_id;
            $project->city = $request->city;
            $project->state = $request->state;
            $project->type = $request->type;
            $project->propery_type = $request->propery_type;
            $project->square_yd = $request->square_yd;
            $project->price = $request->price;
            $project->rentduration = $request->rentduration;
            $project->country = $request->country;
            $project->latitude = (isset($request->latitude)) ? $request->latitude : '';
            $project->location = (isset($request->location)) ? $request->location : '';
            $project->longitude = (isset($request->longitude)) ? $request->longitude : '';
            $project->added_by  = (isset($request->added_by)) ? $request->added_by  : $currentUser;
            $project->video_link = (isset($request->video_link)) ? $request->video_link : '';
            $project->total_click = (isset($request->total_click)) ? $request->total_click : '';
            $project->slug_id = generateUniqueSlug($project->title, 1);

            // Image handling
            if ($request->hasFile('image')) {
                $project->image = store_image($request->file('image'), 'PROJECT_TITLE_IMG_PATH');
            }

            if ($request->has('meta_title')) {
                $project->meta_title = $request->meta_title;
            }
            if ($request->has('meta_description')) {
                $project->meta_description = $request->meta_description;
            }
            if ($request->has('meta_keywords')) {
                $project->meta_keywords = $request->meta_keywords;
            }
            if ($request->has('latitude')) {
                $project->latitude = $request->latitude;
            }
            if ($request->has('longitude')) {
                $project->longitude = $request->longitude;
            }
            if ($request->has('video_link')) {
                $project->video_link = $request->video_link;
            }
            if ($request->has('type')) {
                $project->type = $request->type;
            }

            // Saving the project
            $project->save();
            $facility = OutdoorFacilities::all();

            foreach ($facility as $value) {

                // $distanceKey = 'facility_distance[' . $value->id . ']';
                // $distance = $request->input($distanceKey);

                // if ($distance && $distance != '') {

                //     $facilities = new AssignedOutdoorFacilities();
                //     $facilities->facility_id = $value->id;
                //     $facilities->property_id = null; // Set property ID if needed
                //     $facilities->project_id = $project->id; // Set the project ID separately
                //     $facilities->distance = $distanceKey; // The distance value from the input
                //     $facilities->save();
                // }


                foreach ($request->input('facility_distance', []) as $facility_id => $distance) {
                    if ($distance) {
                        // Check if the record already exists
                        $existingFacility = AssignedOutdoorFacilities::where('facility_id', $facility_id)
                            ->where('project_id', $project->id)
                            ->first();

                        if ($existingFacility) {
                            // If the record exists, update the distance
                            $existingFacility->distance = $distance;
                            $existingFacility->save();
                        } else {
                            // If the record doesn't exist, create a new one
                            $facilities = new AssignedOutdoorFacilities();
                            $facilities->facility_id = $facility_id;
                            $facilities->project_id = $project->id;
                            $facilities->distance = $distance;
                            $facilities->save();
                        }
                    }
                }
            }


            $parameters = Parameter::all();

            foreach ($parameters as $par) {
                $parameterKey = 'par_' . $par->id;

                if ($request->has($parameterKey)) {

                    $assign_parameter = new AssignParameters();
                    $assign_parameter->parameter_id = $par->id;
                    $assign_parameter->project_id = $project->id;

                    // Check if file has been uploaded for this parameter
                    if ($request->hasFile($parameterKey)) {
                        $destinationPath = public_path('images') . config('global.PARAMETER_IMG_PATH');
                        // Create directory if it doesn't exist
                        if (!is_dir($destinationPath)) {
                            mkdir($destinationPath, 0777, true);
                        }
                        // Generate a unique image name
                        $imageName = microtime(true) . "." . $request->file($parameterKey)->getClientOriginalExtension();
                        // Move the file to the destination path
                        $request->file($parameterKey)->move($destinationPath, $imageName);
                        $assign_parameter->value = $imageName; // Store the file name in the 'value' field
                    } else {
                        // For non-file inputs, store the value directly
                        $inputValue = $request->input($parameterKey);
                        $assign_parameter->value = is_array($inputValue)
                            ? json_encode($inputValue, JSON_FORCE_OBJECT)
                            : $inputValue;
                    }

                    // Associate the parameter with the project and save it
                    $assign_parameter->modal()->associate($project);

                    // Store the parameter's value for further processing if needed
                    $arr[$par->id] = $request->input($parameterKey);
                    try {
                        $assign_parameter->save();
                        log::info("Data saved successfully for parameter ID {$par->id}");
                    } catch (\Exception $e) {
                        Log::error("Error saving data: " . $e->getMessage());
                    }
                }
            }

            // Handle gallery images if provided
            if ($request->hasfile('gallery_images')) {
                foreach ($request->file('gallery_images') as $file) {
                    $gallery_image = new ProjectDocuments();
                    $gallery_image->name = store_image($file, 'PROPERTY_GALLERY_IMG_PATH');
                    $gallery_image->project_id = $project->id;
                    $gallery_image->type = 'image';
                    $gallery_image->save();
                }
            }

            // Handle project documents if provided
            if ($request->hasfile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $project_documents = new ProjectDocuments();
                    $project_documents->name = store_image($file, 'PROJECT_DOCUMENT_PATH');
                    $project_documents->project_id = $project->id;
                    $project_documents->type = 'doc';
                    $project_documents->save();
                }
            }

            if ($request->hasFile('plans')) { // Correct key is checked here
                foreach ($request->file('plans') as $file) { // Use 'plans' here instead of 'documents'
                    $project_plan = new ProjectPlans();
                    $project_plan->document = store_image($file, 'PROJECT_DOCUMENT_PATH');
                    $project_plan->title = $project->title;
                    $project_plan->project_id = $project->id;
                    $project_plan->save();
                }
            }

            // dd($request->all());


            // Commit the transaction
            // dd($request->all());
            DB::commit();
            return redirect()->back()->with('success', isset($request->id) ? 'Project Updated Successfully' : 'Project Posted Successfully');
        } catch (Exception $e) {
            dd($e->getMessage());
            DB::rollBack();
            return redirect()->back()->with('error', $e);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function updateStatus(Request $request)
    {
        if (!has_permissions('delete', 'projects')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        } else {
            Projects::where('id', $request->id)->update(['status' => $request->status]);




            $project = Projects::with('customer')->find($request->id);

            if ($project->customer) {

                if ($project->customer->fcm_id != '' && $project->customer->notification == 1) {

                    $fcm_ids = array();

                    $customer_id = Customer::where('id', $project->customer->id)->where('isActive', '1')->where('notification', 1)->get();
                    if (count($customer_id)) {
                        $user_token = Usertokens::where('customer_id', $project->customer->id)->select('id', 'fcm_id')->get()->pluck('fcm_id')->toArray();
                    }

                    $fcm_ids[] = $user_token;

                    $msg = "";
                    if (!empty($fcm_ids)) {
                        $msg = $project->status == 1 ? 'Activate now by Adminstrator ' : 'Deactive now by Adminstrator ';
                        $registrationIDs = $fcm_ids[0];

                        $fcmMsg = array(
                            'title' =>  $project->name . 'project Updated',
                            'message' => 'Your project Post ' . $msg,
                            'type' => 'project_inquiry',
                            'body' => 'Your project Post ' . $msg,
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            'sound' => 'default',
                            'id' => (int)$project->id,
                        );
                        send_push_notification($registrationIDs, $fcmMsg);
                    }
                    //END ::  Send Notification To Customer

                    Notifications::create([
                        'title' => $project->name . 'project Updated',
                        'message' => 'Your project Post ' . $msg,
                        'image' => '',
                        'type' => '1',
                        'send_type' => '0',
                        'customers_id' => $project->customer->id,
                        'projects_id' => $project->id
                    ]);
                }
            }
            $response['error'] = false;
            ResponseService::successResponse($request->status ? "project Activatd Successfully" : "project Deactivatd Successfully");





            ResponseService::successResponse($request->status ? "Project Activatd Successfully" : "Project Deactivatd Successfully");
        }
    }
}
