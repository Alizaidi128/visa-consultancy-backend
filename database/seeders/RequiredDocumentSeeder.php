<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RequiredDocument;

class RequiredDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $documents = [
            'Passport',
            'National ID',
            'Birth Certificate',
            'Bank Statement (Last 6 Months)',
            'Academic Transcripts',
            'Degree Certificate',
            'Employment Letter',
            'Resume / CV',
            'Police Clearance Certificate',
            'Medical Examination Report',
            'Statement of Purpose',
            'Recommendation Letters',
            'Photographs (Passport Size)',
            'Travel History',
            'Invitation Letter',
        ];

        foreach ($documents as $doc) {
            RequiredDocument::firstOrCreate([
                'document_type' => $doc,
            ]);
        }
    }
}
