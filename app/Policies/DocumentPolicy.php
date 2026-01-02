<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use App\Models\VisaApplication;

class DocumentPolicy
{
    /**
     * Determine whether the user can view a document.
     */
    public function view(User $user, Document $document): bool
    {
        $visa = $document->visaApplication;

        // Admin can view all documents
        if ($user->hasRole('admin')) {
            return true;
        }

        // Consultant can view documents of assigned visa
        if (
            $user->hasRole('consultant') &&
            $visa &&
            $visa->consultant_id === $user->id
        ) {
            return true;
        }

        // Client can view their own documents
        if (
            $user->hasRole('client') &&
            $visa &&
            $visa->client &&
            $visa->client->user_id === $user->id
        ) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can upload a document.
     */
    public function upload(User $user, VisaApplication $visa): bool
    {
        // Only clients can upload documents
        if (! $user->hasRole('client')) {
            return false;
        }

        // Must own the visa
        if (! $visa->client || $visa->client->user_id !== $user->id) {
            return false;
        }

        // Cannot upload if visa is approved/rejected
        if (in_array($visa->status, ['approved', 'rejected'], true)) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete a document.
     */
    public function delete(User $user, Document $document): bool
    {
        $visa = $document->visaApplication;

        // Admin can delete any document
        if ($user->hasRole('admin')) {
            return true;
        }

        // Client can delete their own document ONLY if not approved
        if (
            $user->hasRole('client') &&
            $visa &&
            $visa->client &&
            $visa->client->user_id === $user->id &&
            $document->status !== 'approved'
        ) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can review (approve/reject) a document.
     */
    public function review(User $user, Document $document): bool
    {
        $visa = $document->visaApplication;

        // Admin can review all documents
        if ($user->hasRole('admin')) {
            return true;
        }

        // Consultant can review documents of assigned visa
        if (
            $user->hasRole('consultant') &&
            $visa &&
            $visa->consultant_id === $user->id
        ) {
            return true;
        }

        return false;
    }
}
