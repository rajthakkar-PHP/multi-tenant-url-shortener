<?php

namespace App\Services;

use App\Mail\InvitationMail;
use App\Models\Company;
use App\Models\Invitation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvitationService
{
    public function send(User $inviter, Company $company, Role $role, string $email): Invitation
    {
        $email = Str::lower(trim($email));

        if (User::where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'An account already exists for this email address.',
            ]);
        }

        $plainToken = bin2hex(random_bytes(32));

        $invitation = DB::transaction(function () use ($inviter, $company, $role, $email, $plainToken): Invitation {
            Company::query()->whereKey($company->id)->lockForUpdate()->firstOrFail();

            $pendingInvitationExists = Invitation::query()
                ->where('company_id', $company->id)
                ->where('email', $email)
                ->whereNull('accepted_at')
                ->where('expires_at', '>', now())
                ->exists();

            if ($pendingInvitationExists) {
                throw ValidationException::withMessages([
                    'email' => 'An active invitation already exists for this company and email address.',
                ]);
            }

            return Invitation::create([
                'company_id' => $company->id,
                'email' => $email,
                'role_id' => $role->id,
                'invited_by' => $inviter->id,
                'token_hash' => hash('sha256', $plainToken),
                'expires_at' => now()->addDays(7),
            ]);
        });

        Mail::to($invitation->email)->send(new InvitationMail(
            $invitation->load(['company', 'role', 'inviter']),
            $plainToken,
        ));

        return $invitation;
    }

    public function findValid(string $plainToken): Invitation
    {
        $invitation = Invitation::query()
            ->with(['company', 'role', 'inviter'])
            ->where('token_hash', hash('sha256', $plainToken))
            ->firstOrFail();

        $this->ensureUsable($invitation);

        return $invitation;
    }

    /** @param array{name: string, email: string, password: string} $data */
    public function accept(string $plainToken, array $data): User
    {
        $tokenHash = hash('sha256', $plainToken);

        return DB::transaction(function () use ($tokenHash, $data): User {
            /** @var Invitation $invitation */
            $invitation = Invitation::query()
                ->with('role')
                ->where('token_hash', $tokenHash)
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureUsable($invitation);

            $providedEmail = Str::lower(trim($data['email']));
            $invitedEmail = Str::lower($invitation->email);

            if (! hash_equals($invitedEmail, $providedEmail)) {
                throw ValidationException::withMessages([
                    'email' => 'This email address does not match the invitation.',
                ]);
            }

            if (User::where('email', $invitation->email)->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'An account already exists for this email address.',
                ]);
            }

            $user = User::create([
                'name' => $data['name'],
                'email' => $invitation->email,
                'password' => $data['password'],
                'company_id' => $invitation->company_id,
                'role_id' => $invitation->role_id,
            ]);

            $invitation->forceFill(['accepted_at' => now()])->save();

            return $user;
        });
    }

    private function ensureUsable(Invitation $invitation): void
    {
        if ($invitation->accepted_at !== null || $invitation->expires_at->isPast()) {
            abort(410, 'This invitation has expired or has already been accepted.');
        }

        if (! in_array($invitation->role?->slug, ['admin', 'member'], true)) {
            abort(410, 'This invitation has an invalid role.');
        }
    }
}
