<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VisaApplication;

class VisaApplicationPolicy
{
    /* ---------------------------------------------------------
     | VIEW LIST (INDEX)
     |---------------------------------------------------------*/
    public function viewAny(User $user): bool
    {
        // Admin can view all visa applications
        if ($user->hasRole('admin')) {
            return true;
        }

        // Consultant can view assigned visas (filtered in controller)
        if ($user->hasRole('consultant')) {
            return true;
        }

        // Client can view own visas (filtered in controller)
        if ($user->hasRole('client')) {
            return true;
        }

        return false;
    }

    /* ---------------------------------------------------------
     | VIEW SINGLE VISA
     |---------------------------------------------------------*/
    public function view(User $user, VisaApplication $visa): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('consultant')) {
            return $visa->consultant_id === $user->id;
        }

        if ($user->hasRole('client')) {
            return $visa->client_id === optional($user->client)->id;
        }

        return false;
    }

    /* ---------------------------------------------------------
     | CREATE VISA
     |---------------------------------------------------------*/
    public function create(User $user): bool
    {
        // ✅ Client: can create own visa
        if ($user->hasRole('client') && $user->isApproved()) {
            return true;
        }

        // ✅ Admin: can create visa for any client
        if ($user->hasRole('admin')) {
            return true;
        }

        // ❌ Consultant: not allowed
        return false;
    }


    /* ---------------------------------------------------------
     | SUBMIT VISA
     |---------------------------------------------------------*/
    public function submit(User $user, VisaApplication $visa): bool
    {
        return $user->hasRole('client')
            && $user->isApproved()
            && in_array($visa->status, ['draft', 'documents_pending'], true)
            && $visa->client_id === optional($user->client)->id;
    }

    /* ---------------------------------------------------------
     | DOCUMENT UPLOAD
     |---------------------------------------------------------*/
    public function uploadDocument(User $user, VisaApplication $visa): bool
    {
        return $user->hasRole('client')
            && $user->isApproved()
            && in_array($visa->status, ['draft', 'documents_pending'], true)
            && $visa->client_id === optional($user->client)->id;
    }
    /* ---------------------------------------------------------
 | APPROVE VISA
 |---------------------------------------------------------*/
    public function approve(User $user, VisaApplication $visa): bool
    {
        // Admin can approve any visa
        if ($user->hasRole('admin')) {
            return true;
        }

        // Consultant can approve assigned visa
        if (
            $user->hasRole('consultant') &&
            $visa->consultant_id === $user->id
        ) {
            return true;
        }

        return false;
    }

    /* ---------------------------------------------------------
     | REJECT VISA
     |---------------------------------------------------------*/
    public function reject(User $user, VisaApplication $visa): bool
    {
        // Same rules as approve
        if ($user->hasRole('admin')) {
            return true;
        }

        if (
            $user->hasRole('consultant') &&
            $visa->consultant_id === $user->id
        ) {
            return true;
        }

        return false;
    }
    public function assign(User $user, VisaApplication $visa): bool
    {
        return $user->hasRole('admin');
    }


}
