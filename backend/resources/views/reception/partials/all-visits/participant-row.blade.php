@php
    $salutation = trim((string) ($participant['salutation'] ?? ''));
    $title = trim((string) ($participant['title'] ?? ''));
    $baseName = trim((string) ($participant['name'] ?? '—'));
    $displayName = trim(collect([
        $salutation,
        $baseName !== '—' ? $baseName : null,
    ])->filter()->implode(' '));
    $displayName = $displayName !== '' ? $displayName : '—';

    $badgeUrl = $participant['badge_url'] ?? null;
    $canPrintBadge = (bool) ($participant['can_print_badge'] ?? false);
    $rawStatusLabel = $participant['status_label'] ?? ($participant['status']['label'] ?? ($participant['status'] ?? null));
    $statusLabel = $rawStatusLabel ? preg_replace('/^\s*[•·]\s*/u', '', (string) $rawStatusLabel) : '—';
    $statusClass = trim((string) ($participant['status_class'] ?? ($participant['status']['class'] ?? '')));
    $statusTextClass = $statusClass !== '' ? $statusClass : 'text-base-content/75';
    $checkInLabel = $participant['check_in_label'] ?? __('Check-in');
    $canCheckIn = (bool) ($participant['can_check_in'] ?? false);
    $canCheckOut = (bool) ($participant['can_check_out'] ?? false);
    $showParticipantControls = $showParticipantControls ?? true;
    $participantGridTemplateColumns = $participantGridTemplateColumns ?? ($showParticipantControls
        ? 'minmax(14rem, 1.55fr) minmax(10rem, 1.1fr) minmax(7rem, 0.95fr) minmax(5.25rem, 0.8fr) minmax(9rem, 0.92fr)'
        : 'minmax(14rem, 1.55fr) minmax(10rem, 1.1fr) minmax(7rem, 0.95fr)');
@endphp

<div
    class="grid items-center gap-[0.55rem] border-t border-base-200 px-[0.65rem] py-[0.34rem] first:border-t-0"
    style="grid-template-columns: {{ $participantGridTemplateColumns }};"
    wire:key="{{ $participant['row_key'] ?? ('participant-row-'.$visitId.'-'.($participant['visitor_id'] ?? uniqid('', true))) }}">
    <div class="flex min-w-0 items-center">
        <div class="flex min-w-0 items-center gap-1.5">
            @if ($title !== '')
                <span
                    class="shrink-0 rounded-full bg-base-200 px-2 py-0.5 text-[0.72rem] font-medium text-base-content/70">{{ $title }}</span>
            @endif

            <span class="truncate text-[0.82rem] text-base-content">{{ $displayName }}</span>
        </div>
    </div>

    <div class="flex min-w-0 items-center">
        <span
            class="truncate text-[0.82rem] leading-[1.24] text-base-content/70">{{ $participant['company'] ?? '—' }}</span>
    </div>

    <div class="flex min-w-0 items-center">
        <span
            class="inline-flex max-w-full min-w-0 items-center truncate text-[0.8rem] font-bold leading-[1.22] {{ $statusTextClass }}">{{ $statusLabel }}</span>
    </div>

    @if ($showParticipantControls)
        <div class="flex min-w-0 items-center">
            @if ($canPrintBadge && $badgeUrl)
                <form
                    method="POST"
                    action="{{ $badgeUrl }}"
                    target="av-badge-download-frame"
                    x-on:submit="$dispatch('av-capture-scroll')"
                >
                    @csrf
                    <button
                        type="submit"
                        class="btn btn-outline btn-sm w-auto justify-center px-2 text-center"
                        data-testid="all-visits-participant-id-card-button"
                        x-on:click="$dispatch('av-capture-scroll'); $wire.printBadge({{ (int) ($participant['visit_id'] ?? $visitId) }}, {{ (int) $participant['visitor_id'] }})"
                    >{{ __('Ausweis') }}</button>
                </form>
            @else
                <span class="text-[0.82rem] leading-[1.24] text-base-content/70">—</span>
            @endif
        </div>

        <div class="flex min-w-0 items-center">
            @if ($canCheckIn)
                <button
                    type="button"
                    wire:click="checkIn({{ (int) ($participant['visit_id'] ?? $visitId) }}, {{ (int) $participant['visitor_id'] }})"
                    x-on:click="$dispatch('av-capture-scroll')"
                    class="btn btn-primary btn-sm min-w-[8.5rem] w-[8.5rem] max-w-[8.5rem] justify-center text-center"
                    wire:loading.attr="disabled"
                    wire:target="checkIn"
                >
                    {{ $checkInLabel }}
                </button>
            @elseif ($canCheckOut)
                <button
                    type="button"
                    wire:click="checkOut({{ (int) ($participant['visit_id'] ?? $visitId) }}, {{ (int) $participant['visitor_id'] }})"
                    x-on:click="$dispatch('av-capture-scroll')"
                    class="btn btn-primary btn-sm min-w-[8.5rem] w-[8.5rem] max-w-[8.5rem] justify-center text-center"
                    wire:loading.attr="disabled"
                    wire:target="checkOut"
                >
                    {{ __('Check-out') }}
                </button>
            @else
                <span class="text-[0.82rem] leading-[1.24] text-base-content/70">—</span>
            @endif
        </div>
    @endif

    @if (!empty($visit['needs_cheque_collection_capture']) && $canCheckOut)
        <div class="col-span-full rounded-xl border border-warning/40 bg-warning/10 p-3 text-sm">
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
