<p>Hello,</p>

<p>{{ $invitation->inviter->name }} invited you to join {{ $invitation->company->name }} as {{ $invitation->role->name }}.</p>

<p><a href="{{ $acceptanceUrl }}">Accept your invitation</a></p>

<p>This invitation expires on {{ $invitation->expires_at->toDayDateTimeString() }}.</p>