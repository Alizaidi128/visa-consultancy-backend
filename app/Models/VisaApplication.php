<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\User;
use App\Models\Client;
use App\Models\VisaType;
use App\Models\Country;
use App\Models\RequiredDocument;

class VisaApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',

        // LEGACY (temporary)
        'country',
        'visa_type',

        // NORMALIZED
        'country_id',
        'visa_type_id',

        'status',
        'submission_date',
        'decision_date',
        'remarks',
        'consultant_id',
    ];

    /* -----------------------------------------------------------------
     | Relationships
     |-----------------------------------------------------------------*/

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function consultant()
    {
        return $this->belongsTo(User::class, 'consultant_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function visaType()
    {
        return $this->belongsTo(VisaType::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'visa_application_id')
            ->orderByDesc('created_at');
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'loggable');
    }

    /* -----------------------------------------------------------------
     | Document Logic (LEGACY + NORMALIZED)
     |-----------------------------------------------------------------*/

    public function documentChecklist(): array
    {
        // ✅ NORMALIZED PATH
        if ($this->visa_type_id && $this->visaType) {
            return $this->visaType
                ->requiredDocuments
                ->map(function ($doc) {
                    return [
                        'type' => $doc->document_type,
                        'mandatory' => (bool) $doc->pivot->is_mandatory,
                        'uploaded' => $this->documents
                            ->pluck('type')
                            ->contains($doc->document_type),
                    ];
                })
                ->toArray();
        }

        // ⛔ LEGACY FALLBACK (temporary)
        $required = RequiredDocument::where('country', $this->country)
            ->where('visa_type', $this->visa_type)
            ->get();

        $uploadedTypes = $this->documents->pluck('type')->toArray();

        return $required->map(function ($doc) use ($uploadedTypes) {
            return [
                'type' => $doc->document_type,
                'mandatory' => (bool) $doc->is_mandatory,
                'uploaded' => in_array($doc->document_type, $uploadedTypes),
            ];
        })->toArray();
    }

    public function documentCompletionPercentage(): int
    {
        $checklist = $this->documentChecklist();

        if (count($checklist) === 0) {
            return 0;
        }

        $uploaded = collect($checklist)
            ->where('uploaded', true)
            ->count();

        return (int) round(($uploaded / count($checklist)) * 100);
    }

    /* -----------------------------------------------------------------
     | Status Workflow
     |-----------------------------------------------------------------*/

    public const STATUS_FLOW = [
        'draft' => ['documents_pending'],
        'documents_pending' => ['submitted'],
        'submitted' => ['approved', 'rejected'],
        'approved' => [],
        'rejected' => [],
    ];

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array(
            $newStatus,
            self::STATUS_FLOW[$this->status] ?? [],
            true
        );
    }

    public function canBeSubmitted(): bool
    {
        $this->loadMissing('visaType.requiredDocuments', 'documents');

        if (! $this->visaType) {
            return false;
        }

        // Mandatory document types
        $mandatoryTypes = $this->visaType
            ->requiredDocuments
            ->where('pivot.is_mandatory', true)
            ->pluck('document_type');

        if ($mandatoryTypes->isEmpty()) {
            return true;
        }

        // Documents uploaded for mandatory types
        $uploadedDocs = $this->documents
            ->whereIn('type', $mandatoryTypes);

        // ❌ Missing uploads
        if ($uploadedDocs->count() < $mandatoryTypes->count()) {
            return false;
        }

        // ❌ Any mandatory doc not approved
        $notApproved = $uploadedDocs
            ->where('status', '!=', 'approved');

        return $notApproved->isEmpty();
    }



    public function transitionTo(string $newStatus): void
    {
        if (! $this->canTransitionTo($newStatus)) {
            throw new \Exception('Invalid status transition');
        }

        if ($newStatus === 'submitted' && ! $this->canBeSubmitted()) {
            throw new \Exception('Documents are not complete');
        }

        $this->update([
            'status' => $newStatus,
            'submission_date' =>
                $newStatus === 'submitted'
                    ? now()
                    : $this->submission_date,
            'decision_date' =>
                in_array($newStatus, ['approved', 'rejected'], true)
                    ? now()
                    : $this->decision_date,
        ]);
    }
    public function getCompletionPercentageAttribute(): int
    {
        $this->loadMissing('visaType.requiredDocuments', 'documents');

        if (! $this->visaType) {
            return 0;
        }

        $requiredDocs = $this->visaType->requiredDocuments;

        if ($requiredDocs->isEmpty()) {
            return 100;
        }

        $approvedCount = $this->documents()
            ->where('status', 'approved')
            ->count();

        return (int) round(
            ($approvedCount / $requiredDocs->count()) * 100
        );
    }

    public function getUploadCompletionPercentageAttribute(): int
    {
        $this->loadMissing('visaType.requiredDocuments', 'documents');

        if (! $this->visaType) {
            return 0;
        }

        $requiredDocs = $this->visaType->requiredDocuments;

        if ($requiredDocs->isEmpty()) {
            return 100;
        }

        $uploadedTypes = $this->documents
            ->pluck('type')
            ->unique();

        $uploadedCount = $requiredDocs
            ->pluck('document_type')
            ->intersect($uploadedTypes)
            ->count();

        return (int) round(
            ($uploadedCount / $requiredDocs->count()) * 100
        );
    }
    public function familyMembers()
    {
        return $this->belongsToMany(
            \App\Models\ClientFamilyMember::class,
            'visa_application_family_member'
        )->withPivot('role')->withTimestamps();
    }


}
