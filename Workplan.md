**Project name:** `craiova.ro`

**One-paragraph mission statement (use this everywhere):**
craiova.ro is a modular local information platform for Craiova. It automatically collects public, local data (news, construction permits, businesses), analyzes it, and publishes it in useful formats: timelines, maps, topic summaries, and a daily email. The system is built on a LAMP backend, exposes a versioned public JSON API for news and permits, and has a lightweight, SEO-friendly frontend. Long-term goal: help residents, journalists, and businesses understand what’s happening in the city right now.

**High-level system shape:**
We treat the app as a set of cooperating agents/modules with clear responsibilities and outputs.

* Core Platform (shared infrastructure)
* Module A: Data Ingestion
* Module B: Knowledge Layer
* Module C: Public UI
* Module D: Distribution

Each module can be developed, tested, and shipped independently, as long as it honors the shared schema and API contracts defined by Core.

**Backend stack target:**

* LAMP (PHP 8+, Apache/Nginx, MySQL, Linux)
* Optional framework: Laravel (preferred for routing, ORM, jobs) or custom lightweight PHP routing with PDO
* Cron-based workers for scheduled jobs
* Leaflet + OpenStreetMap for maps
* TailwindCSS for UI styling
* Optional small JS framework (Alpine.js or Vue 3) for interactive components

**Exposed public API:**

* `/api/v1/news` and `/api/v1/topics`
* `/api/v1/permits`
  These are read-only, rate-limited JSON endpoints intended for citizens, journalists, and 3rd-party developers.


### Goals

* Centralize authentication, roles, cron scheduling, persistence, logging, and API routing.
* Provide shared MySQL schemas for news, topics, companies, permits, and subscribers.
* Enforce consistent data access patterns so higher-level agents don’t each reinvent infrastructure.

### Responsibilities

* **Auth & Roles**
  * `Admin`: full access
  * `Public`: read-only
  * Token-based access for internal/admin API calls
* **Cron / Job Runner**
  * Central registry of recurring tasks (scrapers, summarizers, newsletter build)
  * Stores run logs, last run time, next run time, status
* **Database Schema (MySQL)**
  * `news_raw` — scraped articles
  * `news_topics` — grouped topics + AI summaries
  * `companies` — local businesses
  * `permits_autorizatii` — permits - build permits
  * `permits_certificate` — permits - urban planning certificates
  * `newsletter_subscribers` — emails + opt-in timestamps
  * `users` — admin/editor accounts
  * basic job run history tables (scraper_logs, summarizer_logs, etc.)
* **Admin Dashboard Shell**
  * UI for managing sources, debugging scrapers, editing companies, viewing permit diffs
* **API Baseline**
  * Routing layer (`/api/v1/...`)
  * Versioning strategy
  * Rate limiting for public endpoints
  * Authorization for private endpoints


