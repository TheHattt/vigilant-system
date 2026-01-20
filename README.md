# MIDE | Multi-Tenant ISP Dashboard
**ISP management engine with strict data isolation.**

---

## Overview
MIDE is a high-performance, single-database multi-tenant platform built for Internet Service Providers. It allows multiple independent ISP branches (Tenants) to operate on a single installation while ensuring complete data privacy through global Eloquent scoping.

## Tech Stack
* **Framework:** [Laravel 11](https://laravel.com)
* **Frontend:** [Livewire 4 (Volt)](https://livewire.laravel.com) & [Alpine.js](https://alpinejs.dev)
* **UI Components:** [Flux UI](https://fluxui.dev)
* **Authentication:** [Laravel Fortify](https://laravel.com/docs/11.x/fortify) (Customized for Multi-Tenancy)
* **Testing:** [Pest PHP](https://pestphp.com)

---

## Security & Tenant Architecture

### 1. The "Stoned Proper" Auth Flow
To prevent cross-tenant data leaks at the entry point, the login process requires a **Tenant Prefix**. 
- **Real-time Lookup:** As the user types their email, an optimised, cached API call detects the associated ISP prefix.
- **Tiered Rate Limiting:** Security is enforced via `AppServiceProvider` using dual-key limiting (IP + Email).
- **UX-First Lockout:** Instead of default `429 Too Many Requests` pages, the system catches throttle exceptions and converts them into a Livewire/Alpine.js countdown timer.



### 2. Data Isolation
Data privacy is handled at the Model layer using the `BelongsToTenant` trait.
- **Global Scoping:** Every query automatically includes `WHERE tenant_id = ?`.
- **Auto-Assignment:** New records are automatically linked to the authenticated user's tenant ID.

---

## Installation

### Prerequisites
- PHP 8.2+
- Composer
- Node.js & NPM

### Setup Steps
1. **Clone the repository:**
   ```bash
   git clone [https://github.com/your-username/mide.git](https://github.com/your-username/mide.git)
   cd mide
