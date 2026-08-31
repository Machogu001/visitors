<div wire:key="dashboard-participant-{{ $participant['row_key'] }}" class="rounded-xl border border-base-300 bg-base-100 px-3 py-3">
    <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-medium text-base-content">{{ $participant['name'] }}</span>
                <span class="badge rounded-full px-2 py-2 text-[11px] font-semibold {{ $participant['status_class'] }}">{{ $participant['status_label'] }}</span>

                @if (!empty($visit['has_dedicated_reception']))
                    <span class="badge badge-secondary badge-outline text-[11px] font-semibold">
                        {{ __('Director Tier') }}: {{ $visit['receptionist_name'] ?: __('Executive Reception') }}
                    </span>
                @endif

                @if (!empty($visit['cheque_number']))
                    <span class="badge badge-warning badge-outline text-[11px] font-mono font-semibold">
                        {{ $visit['cheque_action'] === 'pick_up' ? __('Cheque Pick-up') : __('Cheque Drop-off') }}: #{{ $visit['cheque_number'] }} (KES {{ $visit['cheque_amount'] }})
                    </span>
                @endif

                @if (!empty($visit['is_ushered']))
                    <span class="badge badge-success text-[11px] font-semibold">
                        ✓ {{ __('Ushered In') }} ({{ $visit['ushered_label'] }})
                    </span>
                @endif
            </div>
            @if ($participant['company'])
                <div class="mt-1 text-sm text-base-content/65">{{ $participant['company'] }}</div>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if (($visit['status'] ?? '') === 'pending_approval')
                <button
                    type="button"
                    class="btn btn-success btn-xs rounded-lg text-white"
                    wire:click="approve({{ $visit['id'] }})"
                    wire:loading.attr="disabled"
                >{{ __('Approve Visit') }}</button>
            @endif

            @if ($participant['can_print_badge'])
                <form method="POST" action="{{ $participant['badge_url'] }}" target="dashboard-badge-download-frame">
                    @csrf
                    <button
                        type="submit"
                        class="btn btn-outline btn-xs rounded-lg"
                        data-testid="dashboard-participant-id-card-button"
                        x-on:click="$wire.printBadge({{ $visit['id'] }}, {{ $participant['visitor_id'] }})"
                    >{{ __('Ausweis') }}</button>
                </form>
            @endif

            @if ($participant['can_check_in'])
                <button
                    type="button"
                    class="btn btn-primary btn-xs rounded-lg"
                    wire:click="checkIn({{ $visit['id'] }}, {{ $participant['visitor_id'] }})"
                    wire:loading.attr="disabled"
                    wire:target="checkIn({{ $visit['id'] }}, {{ $participant['visitor_id'] }}), checkOut({{ $visit['id'] }}, {{ $participant['visitor_id'] }})"
                >{{ $participant['check_in_label'] }}</button>
            @endif

            @if (!empty($participant['can_check_out']))
                @if (empty($visit['is_ushered']))
                    <button
                        type="button"
                        class="btn btn-accent btn-xs rounded-lg"
                        wire:click="usher({{ $visit['id'] }})"
                        wire:loading.attr="disabled"
                        title="{{ __('Usher guest into Host / Director office') }}"
                    >
                        {{ $visit['has_dedicated_reception'] ? __('Usher to Director') : __('Usher to Host') }}
                    </button>
                @endif

                <button
                    type="button"
                    class="btn btn-primary btn-xs rounded-lg"
                    wire:click="checkOut({{ $visit['id'] }}, {{ $participant['visitor_id'] }})"
                    wire:loading.attr="disabled"
                    wire:target="checkIn({{ $visit['id'] }}, {{ $participant['visitor_id'] }}), checkOut({{ $visit['id'] }}, {{ $participant['visitor_id'] }})"
                >{{ __('Check-out') }}</button>
            @endif
        </div>
    </div>
</div>
