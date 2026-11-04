<?php

namespace App\Http\Requests;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $company = $this->route('company');

        return $company instanceof Company
            && ($this->user()?->can('inviteUsers', $company) ?? false);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => Str::lower(trim((string) $this->input('email', ''))),
        ]);
    }

    public function rules(): array
    {
        $company = $this->route('company');
        $allowedRoles = [];

        if ($company instanceof Company && $this->user()?->can('inviteAdmin', $company)) {
            $allowedRoles[] = 'admin';
        }

        if ($company instanceof Company && $this->user()?->can('inviteMember', $company)) {
            $allowedRoles[] = 'member';
        }

        return [
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'role' => ['required', 'string', Rule::in($allowedRoles)],
        ];
    }
}
