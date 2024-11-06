<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignedOutdoorFacilities extends Model
{
    use HasFactory;
    protected $fillable = [
        'facility_id', 'property_id', 'distance','project_id',
    ];
    public function outdoorfacilities()
    {
        return $this->belongsTo(OutdoorFacilities::class, 'facility_id');
    }
    public function project()
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }
    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }
}
