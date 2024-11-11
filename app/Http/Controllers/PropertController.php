<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Models\AssignedOutdoorFacilities;
use App\Models\AssignParameters;
use App\Models\Category;
use App\Models\Chats;
use App\Models\Customer;
use App\Exports\PropertyExport;
use Maatwebsite\Excel\Facades\Excel;


use App\Models\Notifications;
use App\Models\OutdoorFacilities;
use App\Models\parameter;
use App\Models\Property;
use App\Models\PropertyImages;
use App\Models\PropertysInquiry;
use App\Models\Setting;
use App\Models\Slider;
use App\Models\PropertyBoost;
use App\Models\Usertokens;
use App\Services\BootstrapTableService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PropertController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!has_permissions('read', 'property')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $category = Category::all();
            return view('property.index', compact('category'));
        }
    }


    public function show() {}
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!has_permissions('create', 'property')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $category = Category::where('status', '1')->get();



            $parameters = parameter::all();
            $currency_symbol = Setting::where('type', 'currency_symbol')->pluck('data')->first();
            $facility = OutdoorFacilities::all();
            return view('property.create', compact('category', 'parameters', 'currency_symbol', 'facility'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $arr = [];
        $count = 1;
        if (!has_permissions('read', 'property')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $request->validate([
                'gallery_images.*' => 'required|image|mimes:jpg,png,jpeg|max:2048',
                'title_image' => 'required|image|mimes:jpg,png,jpeg|max:2048',
            ]);


            $Saveproperty = new Property();
            $Saveproperty->category_id = $request->category;
            $Saveproperty->title = $request->title;
            $title = $request->title;
            $Saveproperty->slug_id = generateUniqueSlug($title, 1);


            $Saveproperty->description = $request->description;
            $Saveproperty->address = $request->address;
            $Saveproperty->client_address = $request->client_address;
            $Saveproperty->propery_type = $request->property_type;
            $Saveproperty->price = $request->price;
            $Saveproperty->package_id = 0;
            $Saveproperty->city = (isset($request->city)) ? $request->city : '';
            $Saveproperty->country = (isset($request->country)) ? $request->country : '';
            $Saveproperty->state = (isset($request->state)) ? $request->state : '';
            $Saveproperty->latitude = (isset($request->latitude)) ? $request->latitude : '';
            $Saveproperty->longitude = (isset($request->longitude)) ? $request->longitude : '';
            $Saveproperty->video_link = (isset($request->video_link)) ? $request->video_link : '';
            $Saveproperty->post_type = 0;
            $Saveproperty->added_by = 0;
            $Saveproperty->meta_title = isset($request->meta_title) ? $request->meta_title : $request->title;
            $Saveproperty->meta_description = $request->meta_description;
            $Saveproperty->meta_keywords = $request->keywords;

            $Saveproperty->rentduration = $request->price_duration;
            $Saveproperty->is_premium = $request->is_premium;

            if ($request->hasFile('title_image')) {

                $Saveproperty->title_image = store_image($request->file('title_image'), 'PROPERTY_TITLE_IMG_PATH');
                // $Saveproperty->title_image_hash = $image_hash;
            } else {
                $Saveproperty->title_image  = '';
            }
            if ($request->hasFile('3d_image')) {


                $Saveproperty->three_d_image = store_image($request->file('3d_image'), '3D_IMG_PATH');
            } else {
                $Saveproperty->three_d_image  = '';
            }

            if ($request->hasFile('meta_image')) {


                $Saveproperty->meta_image = store_image($request->file('meta_image'), 'PROPERTY_SEO_IMG_PATH');
            }
            if ($request->hasFile('document')) {
                $Saveproperty->document = store_image($request->file('document'), 'PROJECT_Documnet_PATH');
            } else {
                $Saveproperty->document  = '';
            }



            $Saveproperty->save();

            $facility = OutdoorFacilities::all();
            foreach ($facility as $key => $value) {
                if ($request->has('facility' . $value->id) && $request->input('facility' . $value->id) != '') {
                    $facilities = new AssignedOutdoorFacilities();
                    $facilities->facility_id = $value->id;
                    $facilities->property_id = $Saveproperty->id;
                    $facilities->distance = $request->input('facility' . $value->id);
                    $facilities->save();
                }
            }
            $parameters = parameter::all();
            foreach ($parameters as $par) {

                if ($request->has('par_' . $par->id)) {

                    $assign_parameter = new AssignParameters();
                    $assign_parameter->parameter_id = $par->id;
                    if (($request->hasFile('par_' . $par->id))) {
                        $destinationPath = public_path('images') . config('global.PARAMETER_IMG_PATH');
                        if (!is_dir($destinationPath)) {
                            mkdir($destinationPath, 0777, true);
                        }
                        $imageName = microtime(true) . "." . ($request->file('par_' . $par->id))->getClientOriginalExtension();
                        ($request->file('par_' . $par->id))->move($destinationPath, $imageName);
                        $assign_parameter->value = $imageName;
                    } else {

                        $assign_parameter->value = is_array($request->input('par_' . $par->id)) ? json_encode($request->input('par_' . $par->id), JSON_FORCE_OBJECT) : ($request->input('par_' . $par->id));
                    }
                    $assign_parameter->modal()->associate($Saveproperty);
                    $assign_parameter->save();
                    $arr = $arr + [$par->id => $request->input('par_' . $par->id)];
                }
            }
            /// START :: UPLOAD GALLERY IMAGE


            $destinationPath = public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . "/" . $Saveproperty->id;
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            if ($request->hasfile('gallery_images')) {
                // dd("in");
                foreach ($request->file('gallery_images') as $file) {
                    $name = time() . rand(1, 100) . '.' . $file->extension();
                    $file->move($destinationPath, $name);
                    PropertyImages::create([
                        'image' => $name,
                        'propertys_id' => $Saveproperty->id
                    ]);
                }
            }
            /// END :: UPLOAD GALLERY IMAGE
            ResponseService::successRedirectResponse('Property Added Successfully');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!has_permissions('update', 'property')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            // $prop  = Property::find($id);
            $category = Category::all()->where('status', '1')->mapWithKeys(function ($item, $key) {
                return [$item['id'] => $item['category']];
            });
            $category = Category::where('status', '1')->get();
            $list = Property::with('assignParameter.parameter')->where('id', $id)->get()->first();

            $categoryData = Category::find($list->category_id);

            $categoryParameterTypeIds = explode(',', $categoryData['parameter_types']);

            $edit_parameters = parameter::with(['assigned_parameter' => function ($q) use ($id) {
                $q->where('modal_id', $id);
            }])->whereIn('id', $categoryParameterTypeIds)->get();

            // Sort the collection by the order of IDs in $categoryParameterTypeIds.
            $edit_parameters = $edit_parameters->sortBy(function ($parameter) use ($categoryParameterTypeIds) {
                return array_search($parameter->id, $categoryParameterTypeIds);
            });

            // Reset the keys on the sorted collection.
            $edit_parameters = $edit_parameters->values();




            $facility = OutdoorFacilities::with(['assign_facilities' => function ($q) use ($id) {
                $q->where('property_id', $id);
            }])->get();

            $assignFacility = AssignedOutdoorFacilities::where('property_id', $id)->get();

            $arr = json_decode($list->carpet_area);
            $par_arr = [];
            $par_id = [];
            $type_arr = [];
            foreach ($list->assignParameter as  $par) {
                $par_arr = $par_arr + [$par->parameter->name => $par->value];
                $par_id = $par_id + [$par->parameter->name => $par->value];
            }
            $currency_symbol = Setting::where('type', 'currency_symbol')->pluck('data')->first();
            $parameters = parameter::all();
            return view('property.edit', compact('category', 'facility', 'assignFacility', 'edit_parameters', 'list', 'id', 'par_arr', 'parameters', 'par_id', 'currency_symbol'));
        }
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

        if (!has_permissions('update', 'property')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            DB::beginTransaction();

            $UpdateProperty = Property::with('assignparameter.parameter')->find($id);

            $destinationPath = public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH');
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            $UpdateProperty->category_id = $request->category;
            $UpdateProperty->title = $request->title;
            $UpdateProperty->description = $request->description;
            $UpdateProperty->address = $request->address;
            $UpdateProperty->client_address = $request->client_address;
            $UpdateProperty->propery_type = $request->property_type;
            $UpdateProperty->price = $request->price;
            $UpdateProperty->propery_type = $request->property_type;
            $UpdateProperty->price = $request->price;
            $UpdateProperty->state = (isset($request->state)) ? $request->state : '';
            $UpdateProperty->country = (isset($request->country)) ? $request->country : '';
            $UpdateProperty->city = (isset($request->city)) ? $request->city : '';
            $UpdateProperty->latitude = (isset($request->latitude)) ? $request->latitude : '';
            $UpdateProperty->longitude = (isset($request->longitude)) ? $request->longitude : '';
            $UpdateProperty->video_link = (isset($request->video_link)) ? $request->video_link : '';
            $UpdateProperty->is_premium = $request->is_premium;
            $UpdateProperty->meta_title = (isset($request->edit_meta_title)) ? $request->edit_meta_title : '';
            $UpdateProperty->meta_description = (isset($request->edit_meta_description)) ? $request->edit_meta_description : '';
            $UpdateProperty->meta_keywords = (isset($request->Keywords)) ? $request->Keywords : '';
            $UpdateProperty->featured_property = (isset($request->featured_property)) ? $request->featured_property : '';

            $UpdateProperty->rentduration = $request->price_duration;
            if ($request->hasFile('title_image')) {

                \unlink_image($UpdateProperty->title_image);

                $UpdateProperty->title_image = \store_image($request->file('title_image'), 'PROPERTY_TITLE_IMG_PATH');
            }

            if ($request->hasFile('3d_image')) {

                \unlink_image($UpdateProperty->three_d_image);

                $UpdateProperty->three_d_image = \store_image($request->file('3d_image'), '3D_IMG_PATH');
            }

            if ($request->hasFile('document')) {
                \unlink_image($UpdateProperty->document);

                $UpdateProperty->document = \store_image($request->file('document'), 'PROJECT_Documnet_PATH');
            }


            if ($request->hasFile('meta_image')) {




                \unlink_image($UpdateProperty->meta_image);

                $UpdateProperty->meta_image = \store_image($request->file('meta_image'), 'PROPERTY_SEO_IMG_PATH');
            }


            $UpdateProperty->update();
            AssignedOutdoorFacilities::where('property_id', $UpdateProperty->id)->delete();
            $facility = OutdoorFacilities::all();
            foreach ($facility as $key => $value) {

                if ($request->has('facility' . $value->id) && $request->input('facility' . $value->id) != '') {

                    $facilities = new AssignedOutdoorFacilities();
                    $facilities->facility_id = $value->id;
                    $facilities->property_id = $UpdateProperty->id;
                    $facilities->distance = $request->input('facility' . $value->id);
                    $facilities->save();
                }
                # code...
            }
            $parameters = parameter::all();

            AssignParameters::where('modal_id', $id)->delete();

            foreach ($parameters as $par) {

                if ($request->has('par_' . $par->id)) {
                    $update_parameter = new AssignParameters();
                    $update_parameter->parameter_id = $par->id;


                    if (($request->hasFile('par_' . $par->id))) {
                        $update_parameter->value = \store_image($request->file('par_' . $par->id), 'PARAMETER_IMG_PATH');
                    } else {
                        $update_parameter->value = is_array($request->input('par_' . $par->id)) || $request->input('par_' . $par->id) == null ? json_encode($request->input('par_' . $par->id), JSON_FORCE_OBJECT) : ($request->input('par_' . $par->id));
                    }

                    $update_parameter->modal()->associate($UpdateProperty);
                    $update_parameter->save();
                }
            }

            /// START :: UPLOAD GALLERY IMAGE

            $FolderPath = public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH');
            if (!is_dir($FolderPath)) {
                mkdir($FolderPath, 0777, true);
            }


            $destinationPath = public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . "/" . $UpdateProperty->id;
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            if ($request->hasfile('gallery_images')) {
                foreach ($request->file('gallery_images') as $file) {
                    $name = time() . rand(1, 100) . '.' . $file->extension();
                    $file->move($destinationPath, $name);

                    PropertyImages::create([
                        'image' => $name,
                        'propertys_id' => $UpdateProperty->id
                    ]);
                }
            }
            DB::commit();
            ResponseService::successRedirectResponse('Property Updated Successfully');
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
        if (env('DEMO_MODE') && Auth::user()->email != "superadmin@gmail.com") {
            return redirect()->back()->with('error', 'This is not allowed in the Demo Version');
        }
        if (!has_permissions('delete', 'property')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            DB::beginTransaction();
            $property = Property::find($id);

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

                $advertisements = Advertisement::where('property_id', $property->id);
                if ($advertisements) {
                    $advertisements->delete();
                }

                if ($property->document != '') {
                    $url = $property->document;
                    $relativePath = parse_url($url, PHP_URL_PATH);
                    if (file_exists(public_path()  . $relativePath)) {
                        unlink(public_path()  . $relativePath);
                    }
                }

                if ($property->title_image != '') {
                    $url = $property->title_image;
                    $relativePath = parse_url($url, PHP_URL_PATH);
                    if (file_exists(public_path()  . $relativePath)) {
                        unlink(public_path()  . $relativePath);
                    }
                }
                foreach ($property->gallery as $row) {
                    if (PropertyImages::where('id', $row->id)->delete()) {
                        if ($row->image != '') {
                            $url =
                                $row->image;
                            $relativePath = parse_url($url, PHP_URL_PATH);

                            if (file_exists(public_path()  . $relativePath)) {
                                unlink(public_path()  . $relativePath);
                            }
                        }
                    }
                }

                Notifications::where('propertys_id', $id)->delete();
                DB::commit();
                ResponseService::successRedirectResponse('Property Deleted Successfully');
            } else {
                ResponseService::errorRedirectResponse('Something Wrong');
            }
        }
    }

    public function getPropertyListActive(Request $request)
    {
        // Pagination and sorting
        $offset = (int) $request->input('offset', 0); // Ensure integer for pagination
        $limit = (int) $request->input('limit', 10);   // Ensure integer for pagination
        $sort = $request->input('sort', 'sequence');
        $order = $request->input('order', 'ASC');

        // Base query
        $sql = Property::with('category')
            ->with('customer:id,name')
            ->with('assignParameter.parameter')
            ->with('interested_users')
            ->with('advertisement')
            ->orderBy($sort, $order);

        // Filter inputs
        $searchQuery = null;
        $propertyType = null;
        $status = null;
        $categoryId = null;

        // Extract and validate filters
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $searchQuery = trim($_GET['search']);  // Trim whitespace
        }

        if (isset($_GET['property_type']) && $_GET['property_type'] !== "") {
            $propertyType = $_GET['property_type'];
        }

        if (isset($_GET['status']) && $_GET['status'] !== '') {
            $status = $_GET['status'];
        }

        if (isset($_GET['category']) && $_GET['category'] !== '') {
            $categoryId = (int) $_GET['category']; // Ensure integer for category ID
        }

        // Apply search filters
        if ($searchQuery !== null) {
            $sql = $sql->where(function ($query) use ($searchQuery) {
                $query->where('id', 'LIKE', "%$searchQuery%")
                    ->orWhere('title', 'LIKE', "%$searchQuery%")
                    ->orWhere('address', 'LIKE', "%$searchQuery%")
                    ->orWhereHas('category', function ($query) use ($searchQuery) {
                        $query->where('category', 'LIKE', "%$searchQuery%");
                    })
                    ->orWhereHas('customer', function ($query) use ($searchQuery) {
                        $query->where('name', 'LIKE', "%$searchQuery%")
                            ->orWhere('email', 'LIKE', "%$searchQuery%");
                    });
            });
        }

        if ($propertyType !== null) {
            $sql = $sql->where('propery_type', $propertyType);
        }

        if ($status !== null) {
            $sql = $sql->where('status', $status);
        }

        if ($categoryId !== null) {
            $sql = $sql->where('category_id', $categoryId);
        }

        // Fetch total count before applying pagination
        $total = $sql->count();

        // Apply pagination
        if (isset($limit)) {
            $sql = $sql->skip($offset)->take($limit);
        }

        $sql->where('status', '=', 1);
        // Fetch the data
        $res = $sql->get();



        // Prepare response
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $currency_symbol = Setting::where('type', 'currency_symbol')->pluck('data')->first();

        foreach ($res as $row) {
            $tempRow = $row->toArray();
            $tempRow['property_type_raw'] = $row->getRawOriginal('propery_type');

            // Operation buttons based on permissions
            if ($row->added_by == 0) {
                $operate = '';
                if (has_permissions('update', 'property')) {
                    $operate = BootstrapTableService::editButton(route('property.edit', $row->id), false);
                }
                if (has_permissions('delete', 'property')) {
                    $operate .= BootstrapTableService::deleteButton(route('property.destroy', $row->id));
                }
            } else {
                $operate = BootstrapTableService::deleteButton(route('property.destroy', $row->id));
            }

            // Handling interested users
            $interested_users = array();
            foreach ($row->interested_users as $interested_user) {
                if ($interested_user->property_id == $row->id) {
                    array_push($interested_users, $interested_user->customer_id);
                }
            }

            // Price handling based on property type
            $price = null;
            if (!empty($row->propery_type) && $row->getRawOriginal('propery_type') == 1) {
                $price = !empty($row->rentduration) ? $currency_symbol . $row->price . '/' . $row->rentduration : $currency_symbol . $row->price;
            } else {
                $price = $currency_symbol . $row->price;
            }

            // Interested users count and display
            $count = "  " . count($interested_users);
            $operate1 = BootstrapTableService::editButton('', true, null, 'text-secondary', $row->id, null, '', 'bi bi-eye-fill edit_icon', $count);

            // Fill data for the table
            $tempRow['total_interested_users'] = count($interested_users);
            $tempRow['edit_status_url'] = 'updatepropertystatus';
            $tempRow['price'] = $price;

            $featured = count($row->advertisement) ? '<div class="featured_tag"><div class="featured_label">Featured</div></div>' : '';
            $tempRow['Property_name'] = '<div class="property_name d-flex"><img class="property_image" alt="" src="' . $row->title_image . '"><div class="property_detail"><div class="property_title">' . $row->title . '</div>' . $featured . '</div></div></div>';
            $tempRow['interested_users'] = $operate1;

            // Add customer details
            if ($row->added_by != 0) {
                $tempRow['added_by'] = $row->customer->name;
                $tempRow['mobile'] = env('DEMO_MODE') ? (Auth::user()->email == 'superadmin@gmail.com' ? $row->customer->mobile : '****************************') : $row->customer->mobile;
            } else {
                $mobile = Setting::where('type', 'company_tel1')->pluck('data');
                $tempRow['added_by'] = trans('Admin');
                $tempRow['mobile'] = $mobile[0];
            }

            // Interested user details
            $tempRow['customer_ids'] = $interested_users;
            foreach ($row->interested_users as $interested_user) {
                if ($interested_user->property_id == $row->id) {
                    $tempRow['interested_users_details'] = Customer::where('id', $interested_user->customer_id)->get()->toArray();
                }
            }

            // Operation buttons
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;

        // Return the data as JSON response
        return response()->json($bulkData);
    }

    public function getPropertyListInactive(Request $request)
    {
        // Pagination and sorting
        $offset = (int) $request->input('offset', 0); // Ensure integer for pagination
        $limit = (int) $request->input('limit', 10);   // Ensure integer for pagination
        $sort = $request->input('sort', 'sequence');
        $order = $request->input('order', 'ASC');

        // Base query
        $sql = Property::with('category')
            ->with('customer:id,name')
            ->with('assignParameter.parameter')
            ->with('interested_users')
            ->with('advertisement')
            ->orderBy($sort, $order);

        // Filter inputs
        $searchQuery = null;
        $propertyType = null;
        $status = null;
        $categoryId = null;

        // Extract and validate filters
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $searchQuery = trim($_GET['search']);  // Trim whitespace
        }

        if (isset($_GET['property_type']) && $_GET['property_type'] !== "") {
            $propertyType = $_GET['property_type'];
        }

        if (isset($_GET['status']) && $_GET['status'] !== '') {
            $status = $_GET['status'];
        }

        if (isset($_GET['category']) && $_GET['category'] !== '') {
            $categoryId = (int) $_GET['category']; // Ensure integer for category ID
        }

        // Apply search filters
        if ($searchQuery !== null) {
            $sql = $sql->where(function ($query) use ($searchQuery) {
                $query->where('id', 'LIKE', "%$searchQuery%")
                    ->orWhere('title', 'LIKE', "%$searchQuery%")
                    ->orWhere('address', 'LIKE', "%$searchQuery%")
                    ->orWhereHas('category', function ($query) use ($searchQuery) {
                        $query->where('category', 'LIKE', "%$searchQuery%");
                    })
                    ->orWhereHas('customer', function ($query) use ($searchQuery) {
                        $query->where('name', 'LIKE', "%$searchQuery%")
                            ->orWhere('email', 'LIKE', "%$searchQuery%");
                    });
            });
        }

        if ($propertyType !== null) {
            $sql = $sql->where('propery_type', $propertyType);
        }

        if ($status !== null) {
            $sql = $sql->where('status', $status);
        }

        if ($categoryId !== null) {
            $sql = $sql->where('category_id', $categoryId);
        }

        // Fetch total count before applying pagination
        $total = $sql->count();

        // Apply pagination
        if (isset($limit)) {
            $sql = $sql->skip($offset)->take($limit);
        }

        $sql->where('status', '=', 0);
        // Fetch the data
        $res = $sql->get();



        // Prepare response
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $currency_symbol = Setting::where('type', 'currency_symbol')->pluck('data')->first();

        foreach ($res as $row) {
            $tempRow = $row->toArray();
            $tempRow['property_type_raw'] = $row->getRawOriginal('propery_type');

            // Operation buttons based on permissions
            if ($row->added_by == 0) {
                $operate = '';
                if (has_permissions('update', 'property')) {
                    $operate = BootstrapTableService::editButton(route('property.edit', $row->id), false);
                }
                if (has_permissions('delete', 'property')) {
                    $operate .= BootstrapTableService::deleteButton(route('property.destroy', $row->id));
                }
            } else {
                $operate = BootstrapTableService::deleteButton(route('property.destroy', $row->id));
            }

            // Handling interested users
            $interested_users = array();
            foreach ($row->interested_users as $interested_user) {
                if ($interested_user->property_id == $row->id) {
                    array_push($interested_users, $interested_user->customer_id);
                }
            }

            // Price handling based on property type
            $price = null;
            if (!empty($row->propery_type) && $row->getRawOriginal('propery_type') == 1) {
                $price = !empty($row->rentduration) ? $currency_symbol . $row->price . '/' . $row->rentduration : $currency_symbol . $row->price;
            } else {
                $price = $currency_symbol . $row->price;
            }

            // Interested users count and display
            $count = "  " . count($interested_users);
            $operate1 = BootstrapTableService::editButton('', true, null, 'text-secondary', $row->id, null, '', 'bi bi-eye-fill edit_icon', $count);

            // Fill data for the table
            $tempRow['total_interested_users'] = count($interested_users);
            $tempRow['edit_status_url'] = 'updatepropertystatus';
            $tempRow['price'] = $price;

            $featured = count($row->advertisement) ? '<div class="featured_tag"><div class="featured_label">Featured</div></div>' : '';
            $tempRow['Property_name'] = '<div class="property_name d-flex"><img class="property_image" alt="" src="' . $row->title_image . '"><div class="property_detail"><div class="property_title">' . $row->title . '</div>' . $featured . '</div></div></div>';
            $tempRow['interested_users'] = $operate1;

            // Add customer details
            if ($row->added_by != 0) {
                $tempRow['added_by'] = $row->customer->name;
                $tempRow['mobile'] = env('DEMO_MODE') ? (Auth::user()->email == 'superadmin@gmail.com' ? $row->customer->mobile : '****************************') : $row->customer->mobile;
            } else {
                $mobile = Setting::where('type', 'company_tel1')->pluck('data');
                $tempRow['added_by'] = trans('Admin');
                $tempRow['mobile'] = $mobile[0];
            }

            // Interested user details
            $tempRow['customer_ids'] = $interested_users;
            foreach ($row->interested_users as $interested_user) {
                if ($interested_user->property_id == $row->id) {
                    $tempRow['interested_users_details'] = Customer::where('id', $interested_user->customer_id)->get()->toArray();
                }
            }

            // Operation buttons
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;

        // Return the data as JSON response
        return response()->json($bulkData);
    }

    public function getPropertyList(Request $request)
    {
        // Pagination and sorting
        $offset = (int) $request->input('offset', 0); // Ensure integer for pagination
        $limit = (int) $request->input('limit', 10);   // Ensure integer for pagination
        $sort = $request->input('sort', 'sequence');
        $order = $request->input('order', 'ASC');

        // Base query
        $sql = Property::with('category')
            ->with('customer:id,name')
            ->with('assignParameter.parameter')
            ->with('interested_users')
            ->with('advertisement')
            ->with('PropertyBoost')
            ->orderBy($sort, $order);

        // Filter inputs
        $searchQuery = null;
        $propertyType = null;
        $status = null;
        $categoryId = null;

        // Extract and validate filters
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $searchQuery = trim($_GET['search']);  // Trim whitespace
        }

        if (isset($_GET['property_type']) && $_GET['property_type'] !== "") {
            $propertyType = $_GET['property_type'];
        }

        if (isset($_GET['status']) && $_GET['status'] !== '') {
            $status = $_GET['status'];
        }

        if (isset($_GET['category']) && $_GET['category'] !== '') {
            $categoryId = (int) $_GET['category']; // Ensure integer for category ID
        }

        // Apply search filters
        if ($searchQuery !== null) {
            $sql = $sql->where(function ($query) use ($searchQuery) {
                $query->where('id', 'LIKE', "%$searchQuery%")
                    ->orWhere('title', 'LIKE', "%$searchQuery%")
                    ->orWhere('address', 'LIKE', "%$searchQuery%")
                    ->orWhereHas('category', function ($query) use ($searchQuery) {
                        $query->where('category', 'LIKE', "%$searchQuery%");
                    })
                    ->orWhereHas('customer', function ($query) use ($searchQuery) {
                        $query->where('name', 'LIKE', "%$searchQuery%")
                            ->orWhere('email', 'LIKE', "%$searchQuery%");
                    });
            });
        }

        if ($propertyType !== null) {
            $sql = $sql->where('propery_type', $propertyType);
        }

        if ($status !== null) {
            $sql = $sql->where('status', $status);
        }

        if ($categoryId !== null) {
            $sql = $sql->where('category_id', $categoryId);
        }

        // Fetch total count before applying pagination
        $total = $sql->count();

        // Apply pagination
        if (isset($limit)) {
            $sql = $sql->skip($offset)->take($limit);
        }

        // Fetch the data
        $res = $sql->get();

        // Prepare response
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $currency_symbol = Setting::where('type', 'currency_symbol')->pluck('data')->first();

        foreach ($res as $row) {
            $tempRow = $row->toArray();
            $tempRow['property_type_raw'] = $row->getRawOriginal('propery_type');

            // Operation buttons based on permissions
            if ($row->added_by == 0) {
                $operate = '';
                if (has_permissions('update', 'property')) {
                    $operate = BootstrapTableService::editButton(route('property.edit', $row->id), false);
                }
                if (has_permissions('delete', 'property')) {
                    $operate .= BootstrapTableService::deleteButton(route('property.destroy', $row->id));
                }
            } else {
                $operate = BootstrapTableService::deleteButton(route('property.destroy', $row->id));
            }

            // Handling interested users
            $interested_users = array();
            foreach ($row->interested_users as $interested_user) {
                if ($interested_user->property_id == $row->id) {
                    array_push($interested_users, $interested_user->customer_id);
                }
            }

            // Price handling based on property type
            $price = null;
            if (!empty($row->propery_type) && $row->getRawOriginal('propery_type') == 1) {
                $price = !empty($row->rentduration) ? $currency_symbol . $row->price . '/' . $row->rentduration : $currency_symbol . $row->price;
            } else {
                $price = $currency_symbol . $row->price;
            }

            // Interested users count and display
            $count = "  " . count($interested_users);
            $operate1 = BootstrapTableService::editButton('', true, null, 'text-secondary', $row->id, null, '', 'bi bi-eye-fill edit_icon', $count);

            // Fill data for the table
            $tempRow['total_interested_users'] = count($interested_users);
            $tempRow['edit_status_url'] = 'updatepropertystatus';
            $tempRow['price'] = $price;
            $tempRow['details'] = $row;
            $featured = count($row->advertisement) ? '<div class="featured_tag"><div class="featured_label">Featured</div></div>' : '';
            $tempRow['Property_name'] = '<div class="property_name d-flex"><img class="property_image" alt="" src="' . $row->title_image . '"><div class="property_detail"><div class="property_title">' . $row->title . '</div>' . $featured . '</div></div></div>';
            $tempRow['interested_users'] = $operate1;

            // Add customer details
            if ($row->added_by != 0) {
                $tempRow['added_by'] = $row->customer->name;
                $tempRow['mobile'] = env('DEMO_MODE') ? (Auth::user()->email == 'superadmin@gmail.com' ? $row->customer->mobile : '****************************') : $row->customer->mobile;
            } else {
                $mobile = Setting::where('type', 'company_tel1')->pluck('data');
                $tempRow['added_by'] = trans('Admin');
                $tempRow['mobile'] = $mobile[0];
            }

            // Interested user details

            $tempRow['customer_ids'] = $interested_users;
            foreach ($row->interested_users as $interested_user) {
                if ($interested_user->property_id == $row->id) {
                    $tempRow['interested_users_details'] = Customer::where('id', $interested_user->customer_id)->get()->toArray();
                }
            }

            // Operation buttons
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;

        // Return the data as JSON response
        return response()->json($bulkData);
    }
    public function storeAds(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'property_id' => 'required|exists:propertys,id',
            'customer_id' => 'required|exists:customers,id',
            'days' => 'required|integer|min:1', // Number of days for the advertisement
            'price' => 'required|numeric|min:0',
            'payment_getweys' => 'required',
            'order_id' => 'required|integer',
            'payment_screenshot' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'payment_detail' => 'nullable|string',
        ]);
    
        if ($validated->fails()) {
            return response()->json([
                "error" => true,
                "message" => $validated->errors()->first(),
            ]);
        }
        
        // Get current date and time
        $startDate = now(); 
        // Calculate the end date by adding the number of days to the start date
        $endDate = now()->addDays($request->days); 
        
        // Path for payment screenshot (if provided)
        $paymentScreenshotPath = 'images/invoice/';
        $destinationPath = public_path($paymentScreenshotPath);
        
        // Default image name
        $defaultImage = 'noImg.png';
        $imageName = $defaultImage;
        
        // Check if a file was uploaded
        if ($request->hasFile('payment_screenshot')) {
            // Create the directory if it doesn’t exist
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            
            // Retrieve the file and create a unique name
            $file = $request->file('payment_screenshot');
            $imageName = microtime(true) . '.' . $file->getClientOriginalExtension();
            
            // Move the uploaded file to the destination path
            $file->move($destinationPath, $imageName);
        }
        
        // Create the property boost entry
        $ads = PropertyBoost::create([
            'property_id' => $request->property_id,
            'customer_id' => $request->customer_id,
            'start_date' => $startDate, // Store current date and time as start date
            'end_date' => $endDate, // Calculate end date
            'price' => $request->price,
            'payment_getwey' => $request->payment_getweys, // Ensure correct gateway value is stored
            'order_id' => $request->order_id,
            'payment_screenshot' => $paymentScreenshotPath . $imageName, // Save the image path
            'payment_detail' => $request->payment_detail,
            'is_payed' => false, // Initially false until payment is verified
        ]);
    
        // Check if the property boost was successfully created
        if ($ads) {
            return response()->json([
                'error' => false,
                'message' => "Property Boosted successfully."
            ]);
        } else {
            Log::error("Failed to Boost Property");
            return response()->json([
                'error' => true,
                'message' => "Failed to update property feature status."
            ]);
        }
    }
    
    
    

    public function updateFeatureStatus(Request $request)
    {
        // Check permissions
        if (!has_permissions('update', 'property')) {
            return response()->json([
                'error' => true,
                'message' => PERMISSION_ERROR_MSG
            ]);
        }

        // Validate the request to ensure required data is provided
        $request->validate([
            'id' => 'required|exists:propertys,id',
            'featured_property' => 'required|boolean'
        ]);

        $propertyId = intval($request->id);
        $newStatus = $request->featured_property;

        // Log debug info to confirm field and status values
        Log::info("Updating featured_property with value: $newStatus for property ID: {$propertyId}");

        // Update the featured_property field
        $updated = Property::where('id', $propertyId)->update(['featured_property' => $request->featured_property]);

        // Check if the update was successful
        if ($updated) {
            Log::info("Successfully updated featured_property for Property ID {$propertyId} to {$newStatus}");
            return response()->json([
                'error' => false,
                'message' => $newStatus ? "Property Featured Successfully" : "Property Unfeatured Successfully"
            ]);
        } else {
            Log::error("Failed to update featured_property for Property ID {$propertyId}");
            return response()->json([
                'error' => true,
                'message' => "Failed to update property feature status."
            ]);
        }
    }




    // public function updateStatus(Request $request)
    // {
    //     if (!has_permissions('update', 'property')) {
    //         $response['error'] = true;
    //         $response['message'] = PERMISSION_ERROR_MSG;
    //         return response()->json($response);
    //     } else {
    //         Property::where('id', $request->id)->update(['status' => $request->status]);
    //         $Property = Property::with('customer')->find($request->id);

    //         if (!empty($Property->customer)) {
    //             if ($Property->customer->fcm_id != '' && $Property->customer->notification == 1) {

    //                 $fcm_ids = array();

    //                 $customer_id = Customer::where('id', $Property->customer->id)->where('isActive', '1')->where('notification', 1)->get();
    //                 if (!empty($customer_id)) {
    //                     $user_token = Usertokens::where('customer_id', $Property->customer->id)->pluck('fcm_id')->toArray();
    //                 }

    //                 $fcm_ids[] = $user_token;

    //                 $msg = "";
    //                 if (!empty($fcm_ids)) {
    //                     $msg = $Property->status == 1 ? 'Activated now by Administrator ' : 'Deactivated now by Administrator ';
    //                     $registrationIDs = $fcm_ids[0];

    //                     $fcmMsg = array(
    //                         'title' =>  $Property->name . 'Property Updated',
    //                         'message' => 'Your Property Post ' . $msg,
    //                         'type' => 'property_inquiry',
    //                         'body' => 'Your Property Post ' . $msg,
    //                         'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
    //                         'sound' => 'default',
    //                         'id' => (string)$Property->id,

    //                     );
    //                     send_push_notification($registrationIDs, $fcmMsg);
    //                 }
    //                 //END ::  Send Notification To Customer

    //                 Notifications::create([
    //                     'title' => $Property->name . 'Property Updated',
    //                     'message' => 'Your Property Post ' . $msg,
    //                     'image' => '',
    //                     'type' => '1',
    //                     'send_type' => '0',
    //                     'customers_id' => $Property->customer->id,
    //                     'propertys_id' => $Property->id
    //                 ]);
    //             }
    //         }
    //         $response['error'] = false;
    //         ResponseService::successResponse($request->status ? "Property Activatd Successfully" : "Property Deactivatd Successfully");
    //     }
    // }
    public function updateStatus(Request $request)
    {
        // Check permissions
        if (!has_permissions('update', 'property')) {
            return response()->json([
                'error' => true,
                'message' => PERMISSION_ERROR_MSG
            ]);
        }

        // Update property status
        Property::where('id', $request->id)->update(['status' => $request->status]);
        $Property = Property::with('customer')->find($request->id);

        // Initialize the $msg variable to ensure it's defined
        $msg = '';

        // Check if property has a customer linked
        if (!empty($Property->customer)) {
            // Ensure FCM ID is present and notifications are enabled for the customer
            if ($Property->customer->fcm_id != '' && $Property->customer->notification == 1) {

                // Prepare FCM IDs array
                $fcm_ids = [];

                // Get the customer's active tokens
                $customer_id = Customer::where('id', $Property->customer->id)
                    ->where('isActive', '1')
                    ->where('notification', 1)
                    ->exists(); // Use exists() for better performance

                if ($customer_id) {
                    $user_token = Usertokens::where('customer_id', $Property->customer->id)
                        ->pluck('fcm_id')
                        ->toArray();

                    if (!empty($user_token)) {
                        $fcm_ids = $user_token;
                    }
                }

                // Send notification if tokens are found
                if (!empty($fcm_ids)) {
                    $msg = $Property->status == 1 ? 'Activated now by Administrator' : 'Deactivated now by Administrator';
                    $registrationIDs = $fcm_ids;

                    $fcmMsg = [
                        'title' => $Property->name . ' Property Updated',
                        'message' => 'Your Property Post ' . $msg,
                        'type' => 'property_inquiry',
                        'body' => 'Your Property Post ' . $msg,
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'sound' => 'default',
                        'id' => (string)$Property->id
                    ];

                    // Call send_push_notification and handle errors if the function fails
                    try {
                        send_push_notification($registrationIDs, $fcmMsg);
                    } catch (\Exception $e) {
                        Log::error("Failed to send FCM notification: " . $e->getMessage());
                    }
                }

                // Log notification in the Notifications table
                Notifications::create([
                    'title' => $Property->name . ' Property Updated',
                    'message' => 'Your Property Post ' . $msg, // Use $msg here
                    'image' => '',
                    'type' => '1',
                    'send_type' => '0',
                    'customers_id' => $Property->customer->id,
                    'propertys_id' => $Property->id
                ]);
            }
        }

        // Return success response
        $response = [
            'error' => false,
            'message' => $request->status ? "Property Activated Successfully" : "Property Deactivated Successfully"
        ];
        return response()->json($response);
    }

    public function bulkUpdateStatus(Request $request)
    {
        // Check permissions
        if (!has_permissions('update', 'property')) {
            return response()->json([
                'error' => true,
                'message' => PERMISSION_ERROR_MSG,
            ]);
        }

        $action = $request->action;
        $ids = $request->ids;

        // Validate that $ids is an array and not empty
        if (!is_array($ids) || empty($ids)) {
            return response()->json([
                'error' => true,
                'message' => 'No property IDs provided or invalid format.',
            ]);
        }

        if ($action === 'delete') {
            // Perform deletion for the specified properties
            Property::whereIn('id', $ids)->delete();

            // Return response for delete action
            return response()->json([
                'error' => false,
                'message' => "Properties Deleted Successfully",
            ]);
        } else {
            // Determine status based on the action
            $status = $action === 'activate' ? 1 : 0;

            // Update property status
            Property::whereIn('id', $ids)->update(['status' => $status]);

            // Fetch properties with linked customers
            $properties = Property::with('customer')->whereIn('id', $ids)->get();

            foreach ($properties as $Property) {
                // Initialize the $msg variable
                $msg = $status == 1 ? 'Activated now by Administrator' : 'Deactivated now by Administrator';

                if (!empty($Property->customer)) {
                    if ($Property->customer->fcm_id != '' && $Property->customer->notification == 1) {
                        $fcm_ids = [];
                        $customer_id = Customer::where('id', $Property->customer->id)
                            ->where('isActive', '1')
                            ->where('notification', 1)
                            ->exists();

                        if ($customer_id) {
                            $user_token = Usertokens::where('customer_id', $Property->customer->id)
                                ->pluck('fcm_id')
                                ->toArray();

                            if (!empty($user_token)) {
                                $fcm_ids = $user_token;
                            }
                        }

                        if (!empty($fcm_ids)) {
                            $fcmMsg = [
                                'title' => $Property->name . ' Property Updated',
                                'message' => 'Your Property Post ' . $msg,
                                'type' => 'property_inquiry',
                                'body' => 'Your Property Post ' . $msg,
                                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                                'sound' => 'default',
                                'id' => (string)$Property->id,
                            ];

                            try {
                                send_push_notification($fcm_ids, $fcmMsg);
                            } catch (\Exception $e) {
                                Log::error("Failed to send FCM notification for property ID {$Property->id}: " . $e->getMessage());
                            }
                        }

                        Notifications::create([
                            'title' => $Property->name . ' Property Updated',
                            'message' => 'Your Property Post ' . $msg,
                            'image' => '',
                            'type' => '1',
                            'send_type' => '0',
                            'customers_id' => $Property->customer->id,
                            'propertys_id' => $Property->id,
                        ]);
                    }
                }
            }

            return response()->json([
                'error' => false,
                'message' => $action === 'activate' ? "Properties Activated Successfully" : "Properties Deactivated Successfully",
            ]);
        }
    }






    public function removeGalleryImage(Request $request)
    {

        if (!has_permissions('delete', 'slider')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
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
            return response()->json($response);
        }
    }



    public function getFeaturedPropertyList()
    {

        $offset = 0;
        $limit = 4;
        $sort = 'id';
        $order = 'DESC';

        if (isset($_GET['offset'])) {
            $offset = $_GET['offset'];
        }

        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        }

        if (isset($_GET['sort'])) {
            $sort = $_GET['sort'];
        }

        if (isset($_GET['order'])) {
            $order = $_GET['order'];
        }

        $sql = Property::with('category')->with('customer')->whereHas('advertisement')->orderBy($sort, $order);

        $sql->skip($offset)->take($limit);

        $res = $sql->get();


        $bulkData = array();

        $rows = array();
        $tempRow = array();
        $count = 1;


        $operate = '';

        foreach ($res as $row) {

            if (count($row->advertisement)) {
                if (has_permissions('update', 'property') && $row->added_by == 0) {
                    $operate = '<a  href="' . route('property.edit', $row->id) . '"  class="btn icon btn-primary btn-sm rounded-pill mt-2" id="edit_btn" title="Edit"><i class="fa fa-edit edit_icon"></i></a>';
                } else {
                    $operate = "-";
                }
                $tempRow = $row->toArray();
                $tempRow['type'] = ucfirst($row->propery_type);
                $tempRow['edit_status_url'] = 'updatepropertystatus';
                $tempRow['operate'] = $operate;
                $rows[] = $tempRow;
                $count++;
            }
        }
        $total = $sql->count();
        $bulkData['total'] = $total;
        $bulkData['rows'] = $rows;

        return response()->json($bulkData);
    }
    public function updateaccessability(Request $request)
    {
        if (!has_permissions('update', 'property')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        } else {
            Property::where('id', $request->id)->update(['is_premium' => $request->status]);
            ResponseService::successResponse("Property Updated Successfully");
        }
    }
    public function activeProperty()
    {
        if (!has_permissions('read', 'property')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $category = Category::all();
            return view('property.active_property', compact('category'));
        }
    }

    public function inactiveProperty()
    {
        if (!has_permissions('read', 'property')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $category = Category::all();
            return view('property.inactive_property', compact('category'));
        }
    }
    public function export(Request $request)
    {
        // Validate the input data
        $validated = $request->validate([
            'start_month' => 'required|date',
            'end_month' => 'required|date|after_or_equal:start_month',
        ]);

        // Log validated dates

        return Excel::download(new PropertyExport($validated['start_month'], $validated['end_month']), 'properties.csv');
    }
}
