<?php

namespace App\Exports;

use App\Models\Property;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

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
        // Fetch all properties
        return Property::whereBetween('created_at', [$this->startMonth, $this->endMonth])->get();

    }

    public function headings(): array
    {
        return [
            'Property ID',
            'Category ID',
            'Package ID',
            'Property Title',
            'Property Description',
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
            'Rent Duration',
            'Slug ID',
            'Meta Title',
            'Meta Description',
            'Meta Keywords',
            'Meta Image',
            'Is Premium',
            'Document',
            'Featured Property',
            'Notification Seen'
        ];
    }

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
            $property->propery_type,
            $property->price,
            $property->post_type,
            $property->city,
            $property->country,
            $property->state,
            $property->title_image,
            $property->three_d_image,
            $property->video_link,
            $property->latitude,
            $property->longitude,
            $property->added_by,
            $property->status == 1 ? 'Active' : 'Inactive',  // Translate status to readable text
            $property->total_click,
            $property->rentduration,
            $property->slug_id,
            $property->meta_title,
            $property->meta_description,
            $property->meta_keywords,
            $property->meta_image,
            $property->is_premium == 1 ? 'Yes' : 'No',  // Translate is_premium to readable text
            $property->document,
            $property->featured_property,
            $property->notification_seen == 1 ? 'Seen' : 'Not Seen'  // Translate notification_seen
        ];
    }
}
