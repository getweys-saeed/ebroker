<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomerExport implements FromCollection, WithHeadings, WithMapping
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
        return Customer::whereBetween('created_at', [$this->startMonth, $this->endMonth])->get();
    }

    /**
     * Define the column headings.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'User ID',
            'Name',
            'Email',
            'Mobile',
            'Address',
            'City',
            'State',
            'Country',
            'Document',
            'Verification Status',
            'Is Active',
        ];
    }

    /**
     * Map each row to the respective fields.
     *
     * @param $user
     * @return array
     */
    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->mobile,
            $user->address,
            $user->city,
            $user->state,
            $user->country,
            $user->user_document,
            $user->doc_verification_status == 1 ? 'Verified' : 'Not Verified', // Translate verification status
            $user->isActive == 0 ? 'InActive' : 'Active', // Translate isActive to readable text
        ];
    }
}
