<?php

namespace App\Exports;

use App\Models\Property;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PropertyExport implements FromCollection, WithHeadings, WithMapping
{
    protected $startMonth;
    protected $endMonth;

    /**
     * Constructor to initialize start and end dates.
     *
     * @param string $startMonth
     * @param string $endMonth
     */
    public function __construct($startMonth, $endMonth)
    {
        $this->startMonth = $startMonth;
        $this->endMonth = $endMonth;
    }

    /**
     * Retrieve the collection based on the date range.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {

    $startDate = $this->startMonth . ' 00:00:00';
    $endDate = $this->endMonth . ' 23:59:59';

    return Property::whereBetween('created_at', [$startDate, $endDate])->get();
    }

    /**
     * Define the headings for the exported Excel file.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Category ID',
            'Package ID',
            'Title',
            'Description',
            'Address',
            'Client Address',
            'Property Type',
            'Price',
            'Post Type',
            'City',
            'Country',
            'State',
            'Title Image',
            '3D Image',
            'Video Link',
            'Latitude',
            'Longitude',
            'Added By',
            'Status',
            'Total Clicks',
            'Created At',
            'Updated At',
            'Rent Duration',
            'Slug ID',
            'Meta Title',
            'Meta Description',
            'Meta Keywords',
            'Meta Image',
            'Is Premium',
            'Document',
            'Featured Property'
        ];
    }

    /**
     * Map the data for each row in the Excel file.
     *
     * @param $property
     * @return array
     */
    public function map($property): array
    {
        return [
            $property->id,
            $property->category_id,
            $property->package_id,
            $property->title,
            $property->description,
            $property->address,
            $property->client_address,
            $property->property_type == 0 ? 'Sell' : 'Rent',
            $property->price,
            $property->post_type == 0 ? 'Admin' : 'Customer',
            $property->city,
            $property->country,
            $property->state,
            $property->title_image,
            $property->three_d_image,
            $property->video_link,
            $property->latitude,
            $property->longitude,
            $property->added_by,
            $property->status == 1 ? 'Active' : 'Deactive',
            $property->total_click,
            $property->created_at,
            $property->updated_at,
            $property->rentduration,
            $property->slug_id,
            $property->meta_title,
            $property->meta_description,
            $property->meta_keywords,
            $property->meta_image,
            $property->is_premium,
            $property->document,
            $property->featured_property,
        ];
    }
}
