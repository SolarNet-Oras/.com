# ISP Billing System - Project Structure

## Directory Layout

```
/app/
├── backend/                    # Laravel 12 Backend
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/   # API Controllers
│   │   │   └── Middleware/    # Custom Middleware
│   │   ├── Models/            # Eloquent Models
│   │   ├── Services/          # Business Logic Layer
│   │   ├── Repositories/      # Data Access Layer
│   │   └── DTOs/              # Data Transfer Objects
│   ├── config/                # Configuration files
│   ├── database/
│   │   ├── migrations/        # Database migrations
│   │   ├── seeders/           # Database seeders
│   │   └── factories/         # Model factories
│   ├── routes/
│   │   ├── api.php           # API routes
│   │   ├── web.php           # Web routes
│   │   └── console.php       # Console routes
│   ├── public/               # Public assets
│   ├── storage/              # Storage (logs, cache)
│   ├── tests/                # Backend tests
│   ├── .env                  # Environment config
│   └── composer.json         # PHP dependencies
│
├── frontend/                  # React + TypeScript Frontend
│   ├── src/
│   │   ├── components/       # React components
│   │   │   ├── ui/          # shadcn/ui components
│   │   │   └── layout/      # Layout components
│   │   ├── pages/           # Page components
│   │   ├── services/        # API services
│   │   ├── lib/             # Utility functions
│   │   ├── hooks/           # Custom hooks
│   │   ├── context/         # React context
│   │   ├── types/           # TypeScript types
│   │   ├── App.tsx          # Main app component
│   │   └── main.tsx         # Entry point
│   ├── public/              # Static assets
│   ├── .env                 # Environment config
│   ├── package.json         # Node dependencies
│   ├── tsconfig.json        # TypeScript config
│   ├── vite.config.ts       # Vite config
│   └── tailwind.config.js   # Tailwind config
│
├── docker/                   # Docker configuration
│   ├── php/
│   │   └── Dockerfile       # PHP 8.4 container
│   └── nginx/
│       └── default.conf     # Nginx config
│
├── docker-compose.yml       # Docker services
├── README.md               # Project documentation
└── memory/                 # Project memory/notes
    └── structure.md        # This file
```

## Technology Stack

### Backend
- **Framework:** Laravel 12.62.0
- **Language:** PHP 8.2
- **Database:** PostgreSQL 15
- **Cache:** Redis 7
- **Queue:** Laravel Queue (Redis driver)
- **Authentication:** JWT (php-open-source-saver/jwt-auth)
- **API:** RESTful with versioning

### Frontend
- **Framework:** React 19
- **Language:** TypeScript 6.0
- **Build Tool:** Vite 8.1
- **Routing:** React Router 7
- **HTTP Client:** Axios 1.18
- **Styling:** Tailwind CSS 4.3
- **UI Components:** shadcn/ui
- **State Management:** React Context (can add Redux/Zustand later)

### Infrastructure
- **Process Manager:** Supervisor
- **Web Server:** Nginx (in Docker)
- **Container:** Docker + Docker Compose

## Database Schema (Planned)

### Core Tables
1. **users** - System users (staff)
2. **roles** - User roles
3. **permissions** - System permissions
4. **role_permission** - Role-permission pivot
5. **customers** - ISP subscribers
6. **customer_devices** - Customer devices (MAC binding)
7. **routers** - MikroTik routers
8. **service_plans** - Bandwidth packages
9. **dhcp_leases** - DHCP lease records
10. **queues** - MikroTik queue configuration
11. **invoices** - Billing invoices
12. **payments** - Payment transactions
13. **suspension_logs** - Service suspension history
14. **tickets** - Support tickets
15. **inventory** - Equipment inventory
16. **audit_logs** - System audit trail

## API Structure

### Version 1 (v1)
```
/api/v1/
├── auth/
│   ├── POST /login
│   ├── POST /register
│   ├── POST /logout
│   ├── POST /refresh
│   └── POST /password/reset
│
├── users/
│   ├── GET    /users
│   ├── POST   /users
│   ├── GET    /users/{id}
│   ├── PUT    /users/{id}
│   └── DELETE /users/{id}
│
├── customers/
│   ├── GET    /customers
│   ├── POST   /customers
│   ├── GET    /customers/{id}
│   ├── PUT    /customers/{id}
│   └── DELETE /customers/{id}
│
├── routers/
│   ├── GET    /routers
│   ├── POST   /routers
│   ├── GET    /routers/{id}
│   ├── PUT    /routers/{id}
│   ├── DELETE /routers/{id}
│   └── POST   /routers/{id}/test
│
├── dhcp/
│   ├── GET    /dhcp/leases
│   ├── POST   /dhcp/sync
│   └── GET    /dhcp/leases/{id}
│
├── service-plans/
│   ├── GET    /service-plans
│   ├── POST   /service-plans
│   ├── GET    /service-plans/{id}
│   ├── PUT    /service-plans/{id}
│   └── DELETE /service-plans/{id}
│
├── billing/
│   ├── GET    /invoices
│   ├── POST   /invoices
│   ├── GET    /invoices/{id}
│   └── GET    /invoices/{id}/download
│
├── payments/
│   ├── GET    /payments
│   ├── POST   /payments
│   └── GET    /payments/{id}
│
├── tickets/
│   ├── GET    /tickets
│   ├── POST   /tickets
│   ├── GET    /tickets/{id}
│   ├── PUT    /tickets/{id}
│   └── POST   /tickets/{id}/comment
│
├── inventory/
│   ├── GET    /inventory
│   ├── POST   /inventory
│   ├── GET    /inventory/{id}
│   ├── PUT    /inventory/{id}
│   └── DELETE /inventory/{id}
│
├── reports/
│   ├── GET    /reports/revenue
│   ├── GET    /reports/subscribers
│   ├── GET    /reports/bandwidth
│   └── GET    /reports/export
│
└── dashboard/
    └── GET    /dashboard/metrics
```

## Frontend Routes

```
/
├── /login                    # Login page
├── /register                 # Registration page
├── /forgot-password          # Password reset
│
├── /dashboard               # Main dashboard
│
├── /customers               # Customer management
│   ├── /customers/list
│   ├── /customers/create
│   ├── /customers/:id/view
│   └── /customers/:id/edit
│
├── /routers                 # Router management
│   ├── /routers/list
│   ├── /routers/create
│   └── /routers/:id/view
│
├── /dhcp                    # DHCP management
│   ├── /dhcp/leases
│   └── /dhcp/sync
│
├── /service-plans           # Service plans
│   ├── /service-plans/list
│   ├── /service-plans/create
│   └── /service-plans/:id/edit
│
├── /billing                 # Billing
│   ├── /billing/invoices
│   ├── /billing/payments
│   └── /billing/create
│
├── /tickets                 # Support tickets
│   ├── /tickets/list
│   ├── /tickets/create
│   └── /tickets/:id/view
│
├── /inventory               # Inventory
│   ├── /inventory/list
│   ├── /inventory/create
│   └── /inventory/:id/edit
│
├── /reports                 # Reports
│   ├── /reports/revenue
│   ├── /reports/subscribers
│   └── /reports/bandwidth
│
├── /settings                # Settings
│   ├── /settings/profile
│   ├── /settings/users
│   └── /settings/system
│
└── /customer-portal         # Customer portal
    ├── /customer-portal/dashboard
    ├── /customer-portal/invoices
    ├── /customer-portal/payments
    └── /customer-portal/tickets
```

## Architecture Patterns

### Backend (Laravel)

**Clean Architecture:**
```
Request → Controller → Service → Repository → Database
                    ↓
                   DTO
```

**Components:**
- **Controllers:** Handle HTTP requests, validate input
- **Services:** Business logic layer
- **Repositories:** Data access layer
- **DTOs:** Data transfer objects for type safety
- **Events:** Trigger side effects
- **Jobs:** Asynchronous tasks
- **Middleware:** Request filtering
- **Policies:** Authorization logic

### Frontend (React)

**Component Architecture:**
```
App → Pages → Layouts → Components → UI Components
```

**State Management:**
- **Local State:** useState
- **Global State:** Context API
- **Server State:** React Query (if needed)
- **Form State:** React Hook Form

## Development Workflow

### Adding a New Feature

1. **Backend:**
   - Create migration
   - Create model with relationships
   - Create repository
   - Create service
   - Create controller
   - Add routes
   - Write tests

2. **Frontend:**
   - Create types
   - Create API service
   - Create components
   - Create pages
   - Add routes
   - Write tests

## Current Implementation Status

### ✅ Completed
- [x] Project structure created
- [x] Laravel 12 installed
- [x] PostgreSQL configured
- [x] Redis configured
- [x] React + TypeScript setup
- [x] Tailwind CSS configured
- [x] JWT authentication installed
- [x] API routing structure
- [x] CORS configuration
- [x] Supervisor setup
- [x] Services running

### 🔄 Next (Phase 2)
- [ ] Authentication system
- [ ] RBAC implementation
- [ ] User management
- [ ] Login/Register UI

### 📅 Future Phases
- Phase 3: Dashboard & Core UI
- Phase 4: Customer Management
- Phase 5: MikroTik Integration
- Phase 6: DHCP Synchronization
- Phase 7: Service Plans
- Phase 8: Billing Engine
- Phase 9: Suspension/Restoration
- Phase 10: Customer Portal
- Phase 11: Ticketing
- Phase 12: Inventory
- Phase 13: OLT Module
- Phase 14: Reports
- Phase 15: Notifications & Audit

---

Last Updated: July 1, 2026
Phase 1: Foundation - COMPLETE ✅
