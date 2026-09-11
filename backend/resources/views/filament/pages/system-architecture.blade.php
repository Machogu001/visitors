@vite('resources/js/system-architecture.js')

<x-filament-panels::page>
    <div class="space-y-8">
        <section aria-labelledby="system-layers-heading">
            <div class="mb-4">
                <h2 id="system-layers-heading" class="text-lg font-semibold text-gray-950 dark:text-white">
                    {{ __('System layers') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Requests move from the user interfaces through Laravel business services into persistent and external services.') }}
                </p>
            </div>

            <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900 sm:p-6" tabindex="0">
                <pre data-architecture-diagram aria-label="{{ __('VisitorPortal system architecture diagram') }}" class="mermaid bg-transparent text-center" style="min-width: 52rem">
flowchart LR
    subgraph UI[User interfaces]
        BOOK[Public booking]
        SELF[Signed self check-in]
        PORTAL[Staff portal]
        RECEPTION[Reception dashboard]
        ADMIN[Admin panel<br/>Operations, reports and audit]
        MONITOR[Welcome monitor]
        HEALTH[Readiness endpoints]
    end

    subgraph APP[Laravel application]
        ROUTES[Routes and controllers]
        LIVEWIRE[Livewire components]
        FILAMENT[Filament resources]
        POLICIES[Policies and permissions]
    end

    subgraph SERVICES[Business services]
        AVAILABILITY[Booking availability]
        BOOKING[Public booking service]
        ACTIONS[Visit action service]
        OCCUPANCY[Occupancy service]
        AUDIT[Audit recorder]
        NOTIFICATIONS[Notifications]
        BADGES[Badge generation]
    end

    subgraph DATA[Data and integrations]
        DATABASE[(MySQL / MariaDB)]
        CACHE[(Cache, sessions and queue)]
        STORAGE[(Private storage)]
        SMTP[SMTP server]
        GOTENBERG[Gotenberg PDF]
    end

    BOOK --> ROUTES
    SELF --> ROUTES
    PORTAL --> LIVEWIRE
    RECEPTION --> LIVEWIRE
    ADMIN --> FILAMENT
    MONITOR --> ROUTES
    HEALTH --> ROUTES
    ROUTES --> POLICIES
    LIVEWIRE --> POLICIES
    FILAMENT --> POLICIES
    POLICIES --> AVAILABILITY
    POLICIES --> BOOKING
    POLICIES --> ACTIONS
    POLICIES --> OCCUPANCY
    AVAILABILITY --> DATABASE
    BOOKING --> DATABASE
    ACTIONS --> DATABASE
    OCCUPANCY --> DATABASE
    ACTIONS --> AUDIT
    AUDIT --> DATABASE
    BOOKING --> NOTIFICATIONS
    ACTIONS --> NOTIFICATIONS
    ACTIONS --> BADGES
    NOTIFICATIONS --> CACHE
    NOTIFICATIONS --> SMTP
    BADGES --> GOTENBERG
    BADGES --> STORAGE
                </pre>
            </div>
        </section>

        <section aria-labelledby="visit-workflow-heading">
            <div class="mb-4">
                <h2 id="visit-workflow-heading" class="text-lg font-semibold text-gray-950 dark:text-white">
                    {{ __('Visitor workflow') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('The normal path from public booking through reception and automatic completion.') }}
                </p>
            </div>

            <div class="space-y-4">
                <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900 sm:p-6" tabindex="0">
                    <h3 class="mb-3 text-sm font-semibold text-gray-950 dark:text-white">{{ __('Booking and approval') }}</h3>
                    <pre data-architecture-diagram aria-label="{{ __('Booking and approval workflow diagram') }}" class="mermaid bg-transparent text-center" style="min-width: 44rem">
flowchart LR
    START([Visitor starts booking]) --> SELECT[Select site, department and slot]
    SELECT --> CREATE[Create visitor and visit records]
    CREATE --> APPROVAL{Approval required?}
    APPROVAL -- Yes --> PENDING[Pending approval]
    PENDING --> DECISION{Department head decision}
    DECISION -- Reject --> REJECTED([Rejected and visitor notified])
    DECISION -- Approve --> PLANNED[Planned visit]
    APPROVAL -- No --> PLANNED
    PLANNED -. Cancel .-> CANCELED([Cancelled])
                    </pre>
                </div>

                <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900 sm:p-6" tabindex="0">
                    <h3 class="mb-3 text-sm font-semibold text-gray-950 dark:text-white">{{ __('Reception and completion') }}</h3>
                    <pre data-architecture-diagram aria-label="{{ __('Reception and completion workflow diagram') }}" class="mermaid bg-transparent text-center" style="min-width: 44rem">
flowchart LR
    PLANNED([Planned visit]) --> ARRIVAL{Arrival method}
    ARRIVAL -- Reception --> DESK[Print badge and check in]
    ARRIVAL -- Signed link --> SELF[Visitor confirms self check-in]
    SELF --> DESK
    DESK --> NOTIFY[Notify host and usher visitor]
    NOTIFY --> CHEQUE{Cheque pick-up?}
    CHEQUE -- Yes --> CAPTURE[Capture cheque details and signature]
    CAPTURE --> CHECKOUT[Participant checks out]
    CHEQUE -- No --> CHECKOUT
    CHECKOUT --> ACTIVE{Anyone still checked in?}
    ACTIVE -- Yes --> IN_HOUSE[Visit remains in progress]
    IN_HOUSE --> CHECKOUT
    ACTIVE -- No --> END_TIME{Appointment ended?}
    END_TIME -- No --> WAIT[Visit remains planned]
    WAIT --> END_TIME
    END_TIME -- Yes --> COMPLETE([Completed])
                    </pre>
                </div>
            </div>
        </section>

        <section aria-labelledby="roles-heading">
            <div class="mb-4">
                <h2 id="roles-heading" class="text-lg font-semibold text-gray-950 dark:text-white">{{ __('Responsibilities') }}</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Each role interacts with a distinct part of the visitor journey.') }}
                </p>
            </div>
            <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900 sm:p-6" tabindex="0">
                <pre data-architecture-diagram aria-label="{{ __('Roles and responsibilities diagram') }}" class="mermaid bg-transparent text-center" style="min-width: 48rem">
flowchart TB
    PORTAL[VisitorPortal roles]
    PORTAL --> VISITOR["Visitor<br/>Book and track appointments<br/>Receive status emails"]
    PORTAL --> HOST["Host / department head<br/>Review appointment requests<br/>Receive arrival notifications"]
    PORTAL --> RECEPTION["Receptionist<br/>Register walk-ins<br/>Print badges<br/>Check in, check out and usher"]
    PORTAL --> ADMIN["Administrator<br/>Manage users, sites and departments<br/>Review operations, reports and audit trail<br/>Maintain system configuration"]
    PORTAL --> MONITOR["Welcome monitor<br/>Display site welcome content"]
                </pre>
            </div>
        </section>

        <section aria-labelledby="operations-heading">
            <div class="mb-4">
                <h2 id="operations-heading" class="text-lg font-semibold text-gray-950 dark:text-white">{{ __('Operations and assurance') }}</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Operational data is monitored, audited, exported and protected through independent backup and health paths.') }}
                </p>
            </div>
            <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900 sm:p-6" tabindex="0">
                <pre data-architecture-diagram aria-label="{{ __('Operations, monitoring and backup architecture diagram') }}" class="mermaid bg-transparent text-center" style="min-width: 52rem">
flowchart LR
    VISITS[(Visits and participants)] --> OCCUPANCY[Occupancy service]
    OCCUPANCY --> ROLLCALL[Emergency roll call]
    OCCUPANCY --> REPORTS[Reports and CSV exports]
    OCCUPANCY --> OVERDUE[Overdue checkout task]
    OVERDUE --> QUEUE[Database notifications]

    ACTIONS[Visit lifecycle actions] --> AUDIT[(Append-only audit events)]
    EXPORTS[Export downloads] --> AUDIT
    AUDIT --> REVIEW[Read-only audit trail]

    APP[Application readiness] --> HEALTH[/Health endpoints/]
    QUEUEHB[Queue heartbeat] --> HEALTH
    SCHEDHB[Scheduler heartbeat] --> HEALTH
    HEALTH --> MONITOR[External monitoring]

    DATABASE[(MariaDB)] --> BACKUP[Verified backup archive]
    STORAGE[(Private storage)] --> BACKUP
    BACKUP --> RESTORE[Isolated restore drill]
                </pre>
            </div>
        </section>

        <section aria-labelledby="finance-heading">
            <div class="mb-4">
                <h2 id="finance-heading" class="text-lg font-semibold text-gray-950 dark:text-white">{{ __('Finance branch') }}</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Cheque handling changes according to whether the visitor is dropping off or collecting a cheque.') }}
                </p>
            </div>
            <div class="overflow-x-auto rounded-lg border border-amber-200 bg-amber-50 p-4 shadow-sm dark:border-amber-500/30 dark:bg-amber-500/10 sm:p-6" tabindex="0">
                <pre data-architecture-diagram aria-label="{{ __('Finance cheque handling workflow diagram') }}" class="mermaid bg-transparent text-center" style="min-width: 48rem">
flowchart LR
    START([Finance appointment]) --> ACTION{Cheque action?}

    ACTION -- Drop-off --> BOOKING[Capture cheque details during booking]
    BOOKING --> DROP_SIGNATURE[Visitor signs during booking]
    DROP_SIGNATURE --> VISIT[Attend planned visit]

    ACTION -- Pick-up --> VISIT
    VISIT --> RECEPTION[Reception processes departure]
    RECEPTION --> PICKUP{Cheque pick-up?}
    PICKUP -- No --> CHECKOUT([Check-out complete])
    PICKUP -- Yes --> CAPTURE[Record cheque details]
    CAPTURE --> SIGNATURE[Visitor signs acknowledgement]
    SIGNATURE --> CHECKOUT
                </pre>
            </div>
        </section>
    </div>
</x-filament-panels::page>