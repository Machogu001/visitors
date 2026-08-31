# VisitorPortal Architecture Diagrams

Comprehensive system architecture and workflow diagrams for VisitorPortal's multi-tier visitor management system.

## 1. System Architecture

```mermaid
graph TB
    subgraph "Frontend"
        PB["Public Booking"]
        ADMIN["Admin Panel<br/>Filament"]
        RECEPTION["Reception Dashboard<br/>Livewire"]
        MONITOR["Welcome Monitor<br/>Blade"]
    end
    
    subgraph "Application Layer"
        WEB["Laravel 11 Application"]
        LW["Livewire 3<br/>Components"]
        FILAMENT["Filament 3<br/>Admin Panel"]
        API["HTTP Routes<br/>Controllers"]
    end
    
    subgraph "Business Logic"
        VISIT_SVC["VisitActionService<br/>Approvals, Ushering"]
        BOOKING_SVC["PublicBookingService<br/>Appointments"]
        AVAIL_SVC["BookingAvailabilityService<br/>Slot Calculation"]
        MAIL_SVC["Mail Service<br/>SMTP Notifications"]
    end
    
    subgraph "Data Layer"
        MYSQL["MySQL/MariaDB<br/>visitorportal"]
        CACHE["Cache Store<br/>Database"]
        SESSION["Session Store<br/>Database"]
    end
    
    subgraph "External Services"
        SMTP["SMTP Server<br/>altar59.supremepanel59.com:465"]
        GOTENBERG["Gotenberg PDF<br/>Badge Generation"]
    end
    
    subgraph "Models & Data"
        VISIT["Visit Model"]
        DEPT["Department Model"]
        USER["User Model"]
        VISITOR["Visitor Model"]
        SITE["Site Model"]
    end
    
    PB --> API
    ADMIN --> FILAMENT
    RECEPTION --> LW
    MONITOR --> API
    
    API --> WEB
    FILAMENT --> WEB
    LW --> WEB
    
    WEB --> VISIT_SVC
    WEB --> BOOKING_SVC
    WEB --> AVAIL_SVC
    
    VISIT_SVC --> MAIL_SVC
    BOOKING_SVC --> MAIL_SVC
    MAIL_SVC --> SMTP
    
    VISIT_SVC --> VISIT
    BOOKING_SVC --> VISIT
    AVAIL_SVC --> VISIT
    VISIT --> MYSQL
    DEPT --> MYSQL
    USER --> MYSQL
    VISITOR --> MYSQL
    SITE --> MYSQL
    
    WEB --> CACHE
    WEB --> SESSION
    CACHE --> MYSQL
    SESSION --> MYSQL
    
    WEB --> GOTENBERG
```

## 2. Visitor Workflow - Multi-Tier System

```mermaid
graph TD
    BOOK["Public Booking<br/>Self-Service"]
    
    BOOK --> GATE{Approval<br/>Required?}
    
    GATE -->|Yes| PENDING["Status: PendingApproval<br/>Waiting for department head"]
    GATE -->|No| PLANNED["Status: Planned<br/>Confirmed appointment"]
    
    PENDING --> APPROVE{Department Head<br/>Decision}
    APPROVE -->|Approve| PLANNED
    APPROVE -->|Reject| REJECTED["Status: Rejected<br/>Visitor notified"]
    
    PLANNED --> CHECKIN["Check-In at Reception<br/>Badge printed"]
    
    CHECKIN --> NOTIFY{Department<br/>Type?}
    
    NOTIFY -->|Has Dedicated Reception| RECEPT["Notify Department Receptionist<br/>+ Host"]
    NOTIFY -->|Standard| HOST["Notify Host Only"]
    
    RECEPT --> USHERED["Receptionist Ushers<br/>Visitor to Department"]
    HOST --> USHERED
    
    USHERED --> CHECKOUT["Check-Out<br/>Badges archived"]
    
    CHECKOUT --> FIN{Finance<br/>Transaction?}
    
    FIN -->|Yes| CHEQUE["Process Cheque<br/>Digitally signed"]
    FIN -->|No| END["Visit Complete"]
    
    CHEQUE --> END
```

## 3. Notification Flow

```mermaid
graph LR
    subgraph "Trigger Events"
        APPR["Approval Granted"]
        REJCT["Approval Rejected"]
        CHKIN["Check-In"]
        CHKOUT["Check-Out"]
    end
    
    subgraph "VisitActionService"
        APPR_ACTION["approveVisit"]
        REJCT_ACTION["rejectVisit"]
        CHKIN_ACTION["notifyHostAboutCheckIn"]
    end
    
    subgraph "Notification Routes"
        DB_NOTIF["DatabaseNotification<br/>In-App Bell"]
        MAIL_NOTIF["MailNotification<br/>SMTP Email"]
    end
    
    subgraph "Recipients"
        HOST["Host User"]
        GUEST["Guest<br/>Visitor"]
        RECEPT["Department<br/>Receptionist"]
    end
    
    subgraph "Delivery"
        QUEUE["Queue Connection<br/>sync=immediate"]
        SMTP["SMTP Server<br/>465 SSL"]
    end
    
    APPR --> APPR_ACTION
    REJCT --> REJCT_ACTION
    CHKIN --> CHKIN_ACTION
    
    APPR_ACTION --> DB_NOTIF
    APPR_ACTION --> MAIL_NOTIF
    REJCT_ACTION --> DB_NOTIF
    REJCT_ACTION --> MAIL_NOTIF
    CHKIN_ACTION --> DB_NOTIF
    CHKIN_ACTION --> MAIL_NOTIF
    
    DB_NOTIF --> HOST
    DB_NOTIF --> GUEST
    DB_NOTIF --> RECEPT
    
    MAIL_NOTIF --> QUEUE
    QUEUE --> SMTP
    SMTP --> HOST
    SMTP --> GUEST
    SMTP --> RECEPT
```

## 4. Finance Cheque Service Workflow

```mermaid
graph TD
    BOOK["Public Booking Wizard"]
    
    BOOK --> DEPT{Department<br/>is_finance_department?}
    
    DEPT -->|No| STD["Standard appointment<br/>No cheque fields"]
    DEPT -->|Yes| FIN_BOOK["Finance Booking<br/>Optional cheque form"]
    
    FIN_BOOK --> CHEQUE_OPT{Cheque<br/>Transaction?}
    
    CHEQUE_OPT -->|No| NO_CHEQUE["Finance visit without cheque<br/>e.g., inquiry, payment,<br/>document pickup"]
    CHEQUE_OPT -->|Yes| CHEQUE_FORM["Cheque Details Form<br/>- Action: pick_up/drop_off<br/>- Number<br/>- Amount<br/>- Bank<br/>- Payee"]
    
    NO_CHEQUE --> SUBMIT["Submit Booking"]
    CHEQUE_FORM --> SIG["Digital Signature<br/>Canvas 2D<br/>Touch & Mouse support"]
    SIG --> SUBMIT
    
    SUBMIT --> RECORD["PublicBookingService<br/>createBooking<br/>Stores cheque fields if provided"]
    
    RECORD --> DB["Visit Model<br/>Cheque fields nullable<br/>+ signature_data nullable"]
    
    DB --> GATE{Requires<br/>Approval?}
    
    GATE -->|Yes| PENDING["Status: PendingApproval"]
    GATE -->|No| PLANNED["Status: Planned"]
    
    PENDING --> CHECKIN["Check-In at Reception"]
    PLANNED --> CHECKIN
    
    CHECKIN --> WITH_CHEQUE{Has Cheque<br/>Details?}
    
    WITH_CHEQUE -->|Yes| CHEQUE_DISPLAY["Reception Dashboard<br/>Shows cheque for tracking<br/>- pick_up/drop_off indicator<br/>- Amount, Bank, Payee"]
    WITH_CHEQUE -->|No| NO_CHEQUE_DISPLAY["Reception Dashboard<br/>Finance visit<br/>No cheque tracking needed"]
    
    CHEQUE_DISPLAY --> CHECKOUT["Check-Out"]
    NO_CHEQUE_DISPLAY --> CHECKOUT
    
    STD --> DONE["Standard Visit<br/>No cheque tracking"]
```

## 5. Database Schema - Key Entities

```mermaid
erDiagram
    VISITS ||--|| USERS : "approved_by"
    VISITS ||--|| USERS : "rejected_by"
    VISITS ||--|| USERS : "ushered_by"
    VISITS ||--|| USERS : "created_by"
    VISITS ||--o| USERS : "host"
    VISITS ||--o| USERS : "substitute"
    VISITS }o--|| DEPARTMENTS : "department"
    VISITS }o--|| VISITORS : "visitor"
    VISITS }o--|| SITES : "site"
    
    DEPARTMENTS ||--o| USERS : "receptionist_user_id"
    DEPARTMENTS ||--o| USERS : "head_user_id"
    DEPARTMENTS }o--|| SITES : "site"
    
    USERS ||--o{ DEPARTMENTS : "receptionist"
    USERS }o--|| SITES : "primary_site"
    
    VISITS {
        bigint id
        string status
        text approval_reason
        text rejection_reason
        datetime approved_at
        datetime rejected_at
        datetime ushered_at
        string cheque_number
        decimal cheque_amount
        string cheque_bank
        string cheque_payee
        string cheque_action
        longText signature_data
        datetime created_at
        datetime updated_at
    }
    
    DEPARTMENTS {
        bigint id
        string name
        boolean requires_approval
        boolean has_dedicated_reception
        boolean is_finance_department
        bigint receptionist_user_id
        bigint head_user_id
    }
    
    USERS {
        bigint id
        string name
        string email
        string password
        boolean mfa_enabled
    }
    
    VISITORS {
        bigint id
        string first_name
        string last_name
        string email
        string phone
        string company
    }
    
    SITES {
        bigint id
        string name
        boolean public_booking_available
        string public_booking_email
    }
```

## 6. Reception Dashboard Data Flow

```mermaid
graph TB
    subgraph "Dashboard State"
        STATS["KPI Statistics<br/>- Arrivals today<br/>- Currently on site<br/>- Badges to prepare"]
        FILTER["Filters<br/>- Date<br/>- Status<br/>- Department"]
        SORT["Sorting<br/>- Time<br/>- Status<br/>- Department"]
    end
    
    subgraph "Data Loading"
        TODAY_VISITS["todayVisits()<br/>Query visits for today"]
        VISITS_QUERY["visits()<br/>Apply filters & sorting"]
    end
    
    subgraph "Database"
        MYSQL["MySQL Query<br/>SELECT * FROM visits<br/>WHERE date = today<br/>+ joins"]
    end
    
    subgraph "Data Transformation"
        MAP["mapParticipantResult<br/>Transform to display format<br/>Include:<br/>- Approvals<br/>- Ushering status<br/>- Cheque details<br/>- Receptionist name"]
    end
    
    subgraph "Display Components"
        ROW["Dashboard Row<br/>Participant card"]
        ACTIONS["Action Buttons<br/>- Approve<br/>- Reject<br/>- Usher<br/>- Edit<br/>- Delete"]
    end
    
    STATS --> TODAY_VISITS
    FILTER --> VISITS_QUERY
    SORT --> VISITS_QUERY
    
    TODAY_VISITS --> MYSQL
    VISITS_QUERY --> MYSQL
    
    MYSQL --> MAP
    MAP --> ROW
    MAP --> ACTIONS
    
    ACTIONS -->|approveVisit| VISIT_SVC["VisitActionService<br/>Updates visit + sends notifications"]
    ACTIONS -->|rejectVisit| VISIT_SVC
    ACTIONS -->|usherVisit| VISIT_SVC
    
    VISIT_SVC --> NOTIFY["Send Notifications<br/>Email + In-App"]
    NOTIFY --> REFRESH["Refresh Dashboard<br/>Real-time update"]
```

## 7. Public Booking Wizard - Step Flow

```mermaid
graph TD
    START["Step 1: Appointment Type"]
    START -->|Meeting with Head| STEP2A["Step 2: Select Department Head"]
    START -->|General Visit| STEP2B["Step 2: Reception"]
    
    STEP2A --> STEP3A["Step 3: Occasion Category"]
    STEP2B --> STEP3B["Step 3: Occasion Category"]
    
    STEP3A --> STEP4["Step 4: Date & Time<br/>Check available slots<br/>BookingAvailabilityService"]
    STEP3B --> STEP4
    
    STEP4 --> SLOT["Select slot<br/>Livewire reactive update"]
    
    SLOT --> STEP5["Step 5: Your Details<br/>- Name<br/>- Email<br/>- Phone<br/>- Company"]
    
    STEP5 --> FINANCE{Booking is<br/>Finance<br/>Department?}
    
    FINANCE -->|Yes| CHEQUE_SECTION["Add Cheque Details<br/>- Action<br/>- Number<br/>- Amount<br/>- Bank<br/>- Payee<br/>- Signature"]
    FINANCE -->|No| SUBMIT_FINAL["Submit Booking"]
    
    CHEQUE_SECTION --> SUBMIT_FINAL
    
    SUBMIT_FINAL --> SERVICE["PublicBookingService<br/>createBooking"]
    
    SERVICE --> GATE{Requires<br/>Approval?}
    
    GATE -->|Yes| PENDING_STATUS["Set Status: PendingApproval<br/>Send notification to dept head"]
    GATE -->|No| PLANNED_STATUS["Set Status: Planned<br/>Send confirmation email"]
    
    PENDING_STATUS --> SUCCESS["Booking Confirmation<br/>- Booking Code<br/>- Calendar (.ics)"]
    PLANNED_STATUS --> SUCCESS
```

## 8. Authentication & Authorization Flow

```mermaid
graph LR
    subgraph "Auth Methods"
        LOCAL["Local Login<br/>Email + Password"]
        SSO["OpenID Connect SSO<br/>Enterprise Provider"]
    end
    
    subgraph "Auth Layer"
        FORTIFY["Laravel Fortify"]
        OIDC["OIDC Middleware"]
    end
    
    subgraph "MFA Check"
        MFA_PROMPT["MFA Required?<br/>Check APP_MFA_REQUIRED_ROLES"]
        MFA_SCREEN["Two-Factor Screen<br/>OTP/Recovery Code"]
    end
    
    subgraph "Authorization"
        ROLES["Spatie Roles<br/>& Permissions"]
        SHIELD["Filament Shield<br/>Role-based Access"]
    end
    
    subgraph "Access Control"
        POLICY["Model Policies<br/>viewAny, view,<br/>create, update, delete"]
        ADMIN["Admin Panel<br/>Protected Routes"]
        RECEPTION["Reception Dashboard<br/>Role: reception"]
        HOST["Host Functions<br/>View own visits"]
    end
    
    LOCAL --> FORTIFY
    SSO --> OIDC
    
    FORTIFY --> MFA_PROMPT
    OIDC --> MFA_PROMPT
    
    MFA_PROMPT -->|Enabled| MFA_SCREEN
    MFA_PROMPT -->|Disabled| SESSION["Create Session"]
    
    MFA_SCREEN --> SESSION
    
    SESSION --> ROLES
    ROLES --> SHIELD
    SHIELD --> POLICY
    
    POLICY --> ADMIN
    POLICY --> RECEPTION
    POLICY --> HOST
```

---

## Architecture Principles

### Multi-Tier Visitor Workflow
- **Public Self-Service**: Booking via public wizard with optional approval gates
- **Department Approval**: Optional approval workflow with rejection handling
- **Receptionist Routing**: Dedicated receptionists can receive targeted check-in notifications
- **Finance Service**: Specialized workflow for cheque transactions with digital signatures
- **Ushering**: Department receptionists hand off visitors with tracking

### Notification System
- **Real-time**: Queue connection set to `sync` for instant delivery
- **Multi-channel**: Database notifications (in-app bell) + email via SMTP
- **Smart Routing**: Notifications route to host, guest, and/or receptionist based on department settings

### Database Design
- **Relational**: Foreign keys link visits to users, departments, sites, and visitors
- **Audit Trail**: `created_at`, `approved_at`, `rejected_at`, `ushered_at` timestamps
- **Extensible**: Cheque fields stored on visit for finance workflows
- **Digital Signature**: `signature_data` stored as base64 for compliance

### Security & Access Control
- **Role-Based**: Spatie permissions + Filament Shield
- **Model Policies**: Fine-grained authorization per resource
- **MFA Support**: Optional for users, required for privileged roles
- **Session Isolation**: Database sessions with 120-minute TTL

