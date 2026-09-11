<x-filament-panels::page>
    <div class="bp-admin-page">
        <x-filament::section
            icon="heroicon-o-adjustments-horizontal"
            :heading="__('Report period')"
            :description="__('Choose a date range of up to one year.')"
            compact
        >
            <form method="GET" class="bp-report-filter">
                <label class="bp-field">
                    <span class="bp-field-label">{{ __('From') }}</span>
                    <x-filament::input.wrapper>
                        <x-filament::input type="date" name="from" :value="$from" />
                    </x-filament::input.wrapper>
                </label>

                <label class="bp-field">
                    <span class="bp-field-label">{{ __('To') }}</span>
                    <x-filament::input.wrapper>
                        <x-filament::input type="date" name="to" :value="$to" />
                    </x-filament::input.wrapper>
                </label>

                <div class="bp-report-actions">
                    <x-filament::button type="submit" icon="heroicon-m-funnel">
                        {{ __('Apply filters') }}
                    </x-filament::button>

                    <x-filament::button
                        tag="a"
                        :href="route('admin.operations.visits', ['from' => $from, 'to' => $to])"
                        color="gray"
                        outlined
                        icon="heroicon-m-arrow-down-tray"
                    >
                        {{ __('Export CSV') }}
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        <section class="bp-stat-grid" aria-label="{{ __('Report summary') }}">
            @foreach ([
                [__('Visits'), $stats['visits'], 'heroicon-o-calendar-days', 'primary'],
                [__('Participants'), $stats['participants'], 'heroicon-o-user-group', 'info'],
                [__('Completed'), $stats['completed'], 'heroicon-o-check-circle', 'success'],
                [__('Canceled'), $stats['canceled'], 'heroicon-o-x-circle', 'danger'],
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

        <div class="bp-report-grid">
            <x-filament::section icon="heroicon-o-chart-bar" :heading="__('Daily volume')">
                @if ($daily->isEmpty())
                    <x-filament::empty-state
                        :heading="__('No visits in this period.')"
                        :description="__('Try selecting a wider date range.')"
                        icon="heroicon-o-calendar-days"
                        compact
                        :contained="false"
                    />
                @else
                    <div class="bp-table-scroll" tabindex="0">
                        <table class="bp-data-table">
                            <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Visits') }}</th></tr></thead>
                            <tbody>
                                @foreach ($daily as $date => $count)
                                    <tr><td>{{ $date }}</td><td class="bp-table-number">{{ number_format($count) }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-filament::section>

            <x-filament::section icon="heroicon-o-building-office-2" :heading="__('Sites')">
                @if ($sites->isEmpty())
                    <x-filament::empty-state
                        :heading="__('No site data in this period.')"
                        :description="__('Site comparisons appear when visits are scheduled.')"
                        icon="heroicon-o-building-office"
                        compact
                        :contained="false"
                    />
                @else
                    <div class="bp-table-scroll" tabindex="0">
                        <table class="bp-data-table bp-site-table">
                            <thead><tr><th>{{ __('Site') }}</th><th>{{ __('Visits') }}</th><th>{{ __('Participants') }}</th><th>{{ __('Completed') }}</th><th>{{ __('Canceled') }}</th></tr></thead>
                            <tbody>
                                @foreach ($sites as $site => $values)
                                    <tr>
                                        <td class="bp-table-primary">{{ $site }}</td>
                                        <td>{{ number_format($values['visits']) }}</td>
                                        <td>{{ number_format($values['participants']) }}</td>
                                        <td>{{ number_format($values['completed']) }}</td>
                                        <td>{{ number_format($values['canceled']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>