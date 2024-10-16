<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Property extends Model
{
    use HasFactory;

    protected $table = 'propertys';

    protected $fillable = [
        'category_id',
        'title',
        'description',
        'address',
        'client_address',
        'propery_type',
        'price',
        'title_image',
        'state',
        'country',
        'state',
        'status',
        'total_click',
        'latitude',
        'longitude',
        'three_d_image',
        'document'

    ];
    protected $hidden = [
        'updated_at',
        'deleted_at'
    ];

    protected $appends = [
        'gallery',
        'promoted',
        'is_favourite'
    ];

    public function category()
    {
        return $this->hasOne(Category::class, 'id', 'category_id')->select('id', 'category', 'parameter_types', 'image');
    }
    public function customer()
    {
        return $this->hasOne(Customer::class, 'id', 'added_by', 'fcm_id', 'notification');
    }
    public function user()
    {
        return $this->hasMany(User::class, 'id', 'added_by', 'fcm_id', 'notification');
    }

    public function assignParameter()
    {
        return  $this->morphMany(AssignParameters::class, 'modal');
    }

    public function parameters()
    {
        return $this->belongsToMany(parameter::class, 'assign_parameters', 'modal_id', 'parameter_id')->withPivot('value');
    }
    public function assignfacilities()
    {
        return $this->hasMany(AssignedOutdoorFacilities::class);
    }

    public function favourite()
    {
        return $this->hasMany(Favourite::class);
    }
    public function interested_users()
    {
        return $this->hasMany(InterestedUser::class);
    }
    // public function assign_parameter()
    // {
    //     return $this->hasMany(AssignParameters::class);
    // }
    public function advertisement()
    {
        return $this->hasMany(Advertisement::class);
    }

    public function getGalleryAttribute()
    {
        $data = PropertyImages::select('id', 'image')->where('propertys_id', $this->id)->get();


        foreach ($data as $item) {
            if ($item['image'] != '') {
                $item['image'] = $item['image'];
                $item['image_url'] = ($item['image'] != '') ? url('') . config('global.IMG_PATH') . config('global.PROPERTY_GALLERY_IMG_PATH') . $this->id . "/" . $item['image'] : '';
            }
        }
        return $data;
    }
    public function getTitleImageAttribute($image)
    {

        return $image != '' ? url('') . config('global.IMG_PATH') . config('global.PROPERTY_TITLE_IMG_PATH') . $image : '';
    }


    public function getMetaImageAttribute($image)
    {

        return $image != '' ? url('') . config('global.IMG_PATH') . config('global.PROPERTY_SEO_IMG_PATH') . $image : '';
    }
    public function getThreeDImageAttribute($image)
    {
        return $image != '' ? url('') . config('global.IMG_PATH') . config('global.3D_IMG_PATH') . $image : '';
    }

    public function getProperyTypeAttribute($value){
        if ($value == 0) {
            return "sell";
        } elseif ($value == 1) {
            return "rent";
        } elseif ($value == 2) {
            return "sold";
        } elseif ($value == 3) {
            return "rented";
        }
    }


    public function getPromotedAttribute() {
        $id = $this->id;
        return $this->whereHas('advertisement',function($query) use($id){
            $query->where('property_id',$id);
        })->count() ? true : false;
    }

    public function getIsFavouriteAttribute() {
        $propertyId = $this->id;
        $auth = Auth::guard('sanctum');
        if($auth->check()){
            $userId = $auth->user()->id;
            return $this->whereHas('favourite',function($query) use($userId,$propertyId){
                $query->where(['user_id' => $userId, 'property_id' => $propertyId]);
            })->count() >= 1 ? 1 : 0;
        }
        return 0;
    }

    public function getParametersAttribute(){

        $parameterQueryData = $this->parameters()->get();
        if(isset($parameterQueryData) && !empty($parameterQueryData)){
            $parameters = [];
            foreach ($parameterQueryData as $res) {
                    $res = (object)$res;
                    if (is_string($res['pivot']['value']) && is_array(json_decode($res['pivot']['value'], true))) {
                        $value = json_decode($res['pivot']['value'], true);
                    } else {
                        if ($res['type_of_parameter'] == "file") {
                            if ($res['pivot']['value'] == "null") {
                                $value = "";
                            } else {
                                $value = url('') . config('global.IMG_PATH') . config('global.PARAMETER_IMG_PATH') . '/' .  $res['pivot']['value'];
                            }
                        } else {
                            if ($res['pivot']['value'] == "null") {
                                $value = "";
                            } else {
                                $value = $res['pivot']['value'];
                            }
                        }
                    }

                    $parameters[] = [
                        'id' => $res->id,
                        'name' => $res->name,
                        'image' => $res->image,
                        'type_of_parameter' => $res->type_of_parameter,
                        'type_values' => $res->type_values,
                        'value' => $value,
                    ];
                }
            }
        return $parameters ?? null;
    }
    public function getAssignFacilitiesAttribute(){
        $assignFacilitiesQuery = $this->assignfacilities()->with('outdoorfacilities')->get();
        if(collect($assignFacilitiesQuery)->isNotEmpty()){
            $assignFacilitiesData = [];
            foreach ($assignFacilitiesQuery as $facility) {
                if(collect($facility->outdoorfacilities)->isNotEmpty()){
                    $assignFacilitiesData[] = [
                        'id' => $facility->id,
                        'property_id' => $facility->property_id,
                        'facility_id' => $facility->facility_id,
                        'distance' => $facility->distance,
                        'created_at' => $facility->created_at,
                        'updated_at' => $facility->updated_at,
                        'name' => $facility->outdoorfacilities->name,
                        'image' => $facility->outdoorfacilities->image,
                    ];
                }
            }
        }
        return !empty($assignFacilitiesData) ? $assignFacilitiesData :  array();
    }

    protected $casts = [
        'category_id' => 'integer',
        'status' => 'integer'
    ];
}

