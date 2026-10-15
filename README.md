# BarangayDesk — Local Barangay Document Request System

BarangayDesk is a PHP/MySQL implementation of the barangay document-request system described in the midterm requirements and ERD. It replaces the original participant-management prototype with a resident/staff workflow for account registration, document catalog browsing, request submission, file upload, request tracking, notifications, and activity history.

## Setup

1. Configure the following environment variables for the web server: `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS`. Defaults are `127.0.0.1`, `barangay_document_requests`, `root`, and an empty password.
2. Make sure MySQL is running and the configured user has permission to create a database on first launch. Point the PHP document root to this directory and open `index.php`.
3. On the first request, the application automatically creates the configured database, creates the tables, and seeds the document catalog. If automatic creation is disabled by your hosting provider, manually run `database/schema.sql` instead.
4. Select **Create an account**, register with a valid email and an eight-character minimum password, then sign in. To create a staff account for testing, update the account with `UPDATE users SET user_type='staff' WHERE email='your-email@example.com';`, sign out, and sign in again.

If login still fails, confirm that the PHP MySQL extension (`pdo_mysql`) is enabled and that the four database environment variables match the MySQL server. The app now displays a readable database configuration error instead of the previous raw connection output.

The application uses PDO prepared statements, password hashing, session authentication, CSRF tokens, role-aware request access, upload validation, and server-side ownership checks. Uploaded documents are restricted to PDF/JPG/PNG files up to 5 MB and are stored in the local `uploads/` directory.

## ERD corrections applied

The submitted ERD was retained conceptually but normalized for implementation. The misspelled `documents_types` table is implemented as `document_types`; `requirments` became `requirements`; `uploiad_at` became `uploaded_at`; and the invalid `requests.log_id` relationship was removed. Authentication fields required by the functional requirements (`password_hash`, `created_at`) were added to `users`, while `staff_id`, `notes`, and other workflow fields were added to `requests`. Foreign keys are explicit and use InnoDB referential integrity.

## Requirements coverage

| Requirement area | Implementation |
|---|---|
| User registration/login and sessions | `app/bootstrap.php`, `pages/auth.php`, and the authentication forms |
| Resident dashboard and profile | Dashboard statistics, profile view, and session-aware navigation |
| Document catalog | Document cards with fees, processing days, descriptions, and requirements |
| Online submission | Purpose form creates a real request tied to the authenticated user |
| Upload and validation | PDF/JPG/PNG upload flow with size and extension validation |
| Status tracking | Resident request list and staff status controls |
| Notifications | Request status notifications and mark-all-read action |
| Activity/history | Authenticated activity log view |
| Secure ownership | Resident queries are filtered by the authenticated `user_id` |
| Database relationships | `database/schema.sql` defines all corrected tables and foreign keys |

## Main files

`index.php` is the lightweight router. Each navigation destination has its own file in `pages/`: `dashboard.php`, `catalog.php`, `requests.php`, `notifications.php`, `activity.php`, `profile.php`, and `auth.php`. The PDO connection is separated into the root-level `connection.php`; it defines the shared `db(): PDO` function, reads the database environment variables, creates the database when needed, and initializes `database/schema.sql`. `app/bootstrap.php` loads the connection and contains session handling, authentication, CSRF protection, logging, notifications, and POST actions. `classes/Request.php` contains the OOP `Request` entity, while its five operations are separated under `classes/request_operations/`. `examples/request_crud_examples.php` demonstrates adding, fetching, updating, deleting, and searching records. `app/page_data.php` loads page-specific data. Shared layout files are in `components/`, while `components/request_table.php` renders the reusable request table. `assets/style.css` contains the responsive interface. `database/schema.sql` creates the corrected schema and starter document catalog.
