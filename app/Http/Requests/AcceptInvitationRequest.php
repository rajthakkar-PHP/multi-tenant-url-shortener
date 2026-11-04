<?php

namespace App\Http\Requests;

use App\Services\InvitationService;
use Illuminate\Foundation\Http\FormRequest;

class AcceptInvitationRequest extends FormRequest
{
    public function authorize(InvitationService $invitations): bool
    {
        $invitations->findValid((string) $this->route('token'));

        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ];
    }
}
