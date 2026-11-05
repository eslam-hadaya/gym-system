# 🏋️‍♂️ Gym Management & Smart Attendance System

A production-ready Gym & Fitness Club Management web application designed to streamline member registrations, subscription renewals, daily desk operations, and financial auditing. 

Built with a unified monolithic architecture utilizing **Laravel 13**, **Vue 3 (Composition API)**, **Inertia.js**, and **PostgreSQL (Neon)**.

---

## ⚡ Key Highlights & Architecture

- **Unified Full-Stack Architecture:** Single repository housing both backend APIs and reactive SPA interfaces via Inertia.js—zero boilerplate API overhead.
- **Offline-Resilient Attendance Engine:** Instant check-in desk system processing members via permanent QR badges (functioning even when client devices are offline) with sub-second fallback phone queries.
- **Dual Financial & Settlement Pipelines:**
  - **In-person Cash Collection:** Tracked per staff member (`collected_by`) with audit logs.
  - **Digital Payments via Sham Cash:** Automated order settlement and reconciliation using verified webhook listeners.
- **Subscription Lifecycle Engine:** Dynamic plan tiering, automated expiration triggers, and historical subscription snapshots protecting legacy financial reporting against future pricing updates.
- **Role-Based Access Control (RBAC):** Tailored dashboard views for Club Managers/Coaches vs. Member self-service portals.

---

## 🛠 Tech Stack

- **Backend:** Laravel 13 (PHP 8.4)
- **Frontend:** Vue 3, TypeScript, Inertia.js v2
- **Styling:** Tailwind CSS v4 (`dir="rtl"` native support)
- **Database:** PostgreSQL (Neon Serverless)
- **Tooling:** Vite, Composer, Pint
