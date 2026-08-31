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

    @if (!empty($visit['needs_cheque_collection_capture']) && !empty($participant['can_check_out']))
        <div class="mt-3 rounded-xl border border-warning/40 bg-warning/10 p-3 text-sm">
            <div class="mb-2 font-semibold text-base-content">{{ __('Cheque collection details required before check-out') }}</div>
            <div class="mb-3 text-xs text-base-content/70">
                {{ __('Payee / Beneficiary: :name', ['name' => $visit['cheque_payee_or_drawer'] ?: '—']) }}
            </div>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs font-semibold">{{ __('Cheque Number') }} <span class="text-error">*</span></label>
                    <input type="text" wire:model="chequeCollectionForms.{{ $visit['id'] }}.cheque_number" class="input input-bordered input-sm w-full rounded-lg" placeholder="e.g. 000452">
                    @error('chequeCollectionForms.'.$visit['id'].'.cheque_number') <span class="mt-1 block text-xs text-error">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold">{{ __('Amount (KES)') }} <span class="text-error">*</span></label>
                    <input type="number" step="0.01" wire:model="chequeCollectionForms.{{ $visit['id'] }}.cheque_amount" class="input input-bordered input-sm w-full rounded-lg" placeholder="e.g. 45000.00">
                    @error('chequeCollectionForms.'.$visit['id'].'.cheque_amount') <span class="mt-1 block text-xs text-error">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold">{{ __('Bank Name') }} <span class="text-error">*</span></label>
                    <input type="text" wire:model="chequeCollectionForms.{{ $visit['id'] }}.cheque_bank" class="input input-bordered input-sm w-full rounded-lg" placeholder="e.g. Equity Bank, KCB, NCBA">
                    @error('chequeCollectionForms.'.$visit['id'].'.cheque_bank') <span class="mt-1 block text-xs text-error">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="mt-3 space-y-1.5" x-data="{
                isDrawing: false,
                hasSignature: false,
                ctx: null,
                init() {
                    this.ctx = this.$refs.signatureCanvas.getContext('2d');
                    this.ctx.strokeStyle = '#1e293b';
                    this.ctx.lineWidth = 2.5;
                    this.ctx.lineCap = 'round';
                },
                point(event) {
                    const rect = this.$refs.signatureCanvas.getBoundingClientRect();
                    const source = event.touches ? event.touches[0] : event;
                    return { x: source.clientX - rect.left, y: source.clientY - rect.top };
                },
                start(event) {
                    this.isDrawing = true;
                    const p = this.point(event);
                    this.ctx.beginPath();
                    this.ctx.moveTo(p.x, p.y);
                },
                draw(event) {
                    if (!this.isDrawing) return;
                    event.preventDefault();
                    const p = this.point(event);
                    this.ctx.lineTo(p.x, p.y);
                    this.ctx.stroke();
                    this.hasSignature = true;
                    $wire.set('chequeCollectionForms.{{ $visit['id'] }}.signature_data', this.$refs.signatureCanvas.toDataURL('image/png'));
                },
                stop() {
                    this.isDrawing = false;
                },
                clear() {
                    this.ctx.clearRect(0, 0, this.$refs.signatureCanvas.width, this.$refs.signatureCanvas.height);
                    this.hasSignature = false;
                    $wire.set('chequeCollectionForms.{{ $visit['id'] }}.signature_data', '');
                }
            }">
                <div class="flex items-center justify-between gap-2">
                    <label class="mb-1 block text-xs font-semibold">{{ __('Visitor Signature') }} <span class="text-error">*</span></label>
                    <button type="button" class="btn btn-ghost btn-xs text-error" @click="clear()">{{ __('Clear Signature') }}</button>
                </div>
                <div class="relative overflow-hidden rounded-lg border-2 border-dashed border-warning/50 bg-base-100 touch-none">
                    <canvas x-ref="signatureCanvas" width="520" height="110" class="block h-24 w-full cursor-crosshair" @mousedown="start($event)" @mousemove="draw($event)" @mouseup="stop()" @mouseleave="stop()" @touchstart="start($event)" @touchmove="draw($event)" @touchend="stop()"></canvas>
                    <div x-show="!hasSignature" class="pointer-events-none absolute inset-0 flex items-center justify-center text-xs font-medium text-base-content/40">
                        {{ __('Sign here before check-out') }}
                    </div>
                </div>
                @error('chequeCollectionForms.'.$visit['id'].'.signature_data') <span class="mt-1 block text-xs text-error">{{ $message }}</span> @enderror
            </div>
            <button type="button" class="btn btn-warning btn-xs mt-3 rounded-lg" wire:click="captureChequeCollectionDetails({{ $visit['id'] }}, {{ $participant['visitor_id'] }})" wire:loading.attr="disabled">
                {{ __('Save cheque collection details') }}
            </button>
        </div>
    @endif
</div>
