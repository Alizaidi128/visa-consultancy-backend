<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\VisaType;

class VisaTypeSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // USA
            ['country' => 'USA', 'code' => 'F1', 'name' => 'Student Visa (F-1)'],
            ['country' => 'USA', 'code' => 'EB2-NIW', 'name' => 'EB-2 National Interest Waiver'],
            ['country' => 'USA', 'code' => 'H1B', 'name' => 'Specialty Occupation (H-1B)'],

            // UK
            ['country' => 'UK', 'code' => 'SWV', 'name' => 'Skilled Worker Visa'],
            ['country' => 'UK', 'code' => 'STUDENT', 'name' => 'Student Visa'],

            // Canada
            ['country' => 'Canada', 'code' => 'STUDY', 'name' => 'Study Permit'],
            ['country' => 'Canada', 'code' => 'EE', 'name' => 'Express Entry'],

            // Germany
            ['country' => 'Germany', 'code' => 'STUDY', 'name' => 'German Student Visa'],
            ['country' => 'Germany', 'code' => 'JOBSEEK', 'name' => 'Job Seeker Visa'],

            // Australia
            ['country' => 'Australia', 'code' => '500', 'name' => 'Student Visa (Subclass 500)'],
            ['country' => 'Australia', 'code' => '189', 'name' => 'Skilled Independent Visa (189)'],
        ];

        foreach ($data as $item) {
            $country = Country::where('name', $item['country'])->first();

            VisaType::firstOrCreate(
                [
                    'country_id' => $country->id,
                    'code' => $item['code'],
                ],
                [
                    'name' => $item['name'],
                ]
            );
        }
    }
}
