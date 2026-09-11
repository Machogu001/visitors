<x-filament-panels::page>
    <div class="bp-admin-page">
        <section class="bp-stat-grid" aria-label="{{ __('Operational summary') }}">
            @foreach ([
                [__('Currently inside'), $stats['inside'], 'heroicon-o-user-group', 'primary'],
                [__('Overdue check-outs'), $stats['overdue'], 'heroicon-o-clock', $stats['overdue'] > 0 ? 'danger' : 'success'],
                [__('Occupied sites'), $stats['sites'], 'heroicon-o-building-office-2', 'info'],
                [__('Audit events today'), $stats['eventsToday'], 'heroicon-o-shield-check', 'gray'],
            ] as [$label, $value, $icon, $color])
                <x-filament::section compact class="bp-stat-card">
                    <div class="bp-stat-card-content">
                        <div class="bp-stat-icon fi-color fi-color-{{ $color }}">
                            <x-filament::icon :icon="$icon" />
                        </div>
                        <div>
                            <p class="bp-stat-label">{{ $label }}</p>
                            <p class="bp-stat-value">{{ number_format($value) }}</p>
                        </div>
                    </div>
                </x-filament::section>
            @endforeach
        </section>

        <x-filament::section
            icon="heroicon-o-clipboard-document-check"
            :heading="__('Emergency roll call')"
            :description="__('Everyone currently checked in, grouped by their visit site.')"
        >
            <x-slot:afterHeader>
                <div class="bp-section-actions">
                    <x-filament::button
                        tag="a"
                        :href="route('admin.operations.roll-call')"
                        color="gray"
                        outlined
                        icon="heroicon-m-arrow-down-tray"
                        size="sm"
                    >
                        {{ __('Roll call CSV') }}
                    </x-filament::button>

                    <x-filament::button
                        tag="a"
                        :href="route('admin.operations.visits')"
                        color="gray"
                        outlined
                        icon="heroicon-m-table-cells"
                        size="sm"
                    >
                        {{ __('Visits CSV') }}
                    </x-filament::button>

                    <x-filament::button
                        type="button"
                        color="gray"
                        outlined
                        icon="heroicon-m-printer"
                        size="sm"
                        onclick="window.print()"
                    >
                        {{ __('Print') }}
                    </x-filament::button>
                </div>
            </x-slot:afterHeader>

            @if ($occupants->isEmpty())
                <x-filament::empty-state
                    :heading="__('No visitors are currently checked in.')"
                    :description="__('The roll call will update as visitors check in.')"
                    icon="heroicon-o-user-group"
                    compact
                    :contained="false"
                />
            @else
                <div class="bp-table-scroll" tabindex="0">
                    <table class="bp-data-table bp-roll-call-table">
                        <thead>
                            <tr>
                                <th>{{ __('Visitor') }}</th>
                                <th>{{ __('Company') }}</th>
                                <th>{{ __('Host') }}</th>
                                <th>{{ __('Site') }}</th>
                                <th>{{ __('Checked in') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($occupants as $occupant)
                                <tr>
                                    <td class="bp-table-primary">{{ $occupant['visitor'] }}</td>
                                    <td>{{ $occupant['company'] ?: '—' }}</td>
                                    <td>{{ $occupant['host'] ?: '—' }}</td>
                                    <td>{{ $occupant['site'] ?: '—' }}</td>
                                    <td>{{ $occupant['checked_in_at']?->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <x-filament::badge
                                            :color="$occupant['overdue'] ? 'danger' : 'success'"
                                            :icon="$occupant['overdue'] ? 'heroicon-m-clock' : 'heroicon-m-check-circle'"
                                        >
                                            {{ $occupant['overdue'] ? __('Overdue') : __('On site') }}
                                        </x-filament::badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>