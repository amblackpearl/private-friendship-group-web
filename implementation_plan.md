# Friendship Group Web Application — Implementation Plan

A private PHP + MySQL web platform for a friendship group with passcode-based registration, photo gallery, voting system, and trip agenda planning.

---

## User Review Required

> [!IMPORTANT]
> **Database credentials**: The plan uses default XAMPP/Laragon credentials (`root` with no password). Please confirm your local MySQL setup or provide different credentials.

> [!IMPORTANT]
> **Verification passcodes**: The MVP will use these default passcodes in `config/app.php`:
> - Admin: `admin-secret-code`
> - Member: `member-secret-code`
> 
> Change these before any real usage.

> [!IMPORTANT]
> **PHP server**: The plan assumes you're running Apache via XAMPP/Laragon, or will use `php -S localhost:8000` for development. Please confirm which setup you prefer.

---

## Open Questions

1. **Do you want dark mode in the MVP?** The PRD lists it as a future improvement, but the UI could be built with dark-mode-first design from the start.
2. **Agenda edit feature**: The PRD marks "members may edit their own agenda" as optional. Should we include it in the MVP?
3. **Profile photo upload**: The `users` table has a `profile_photo` column. Should we build a profile photo upload UI, or leave it as `NULL` for MVP?

---

## Proposed Changes

The entire project will be built from scratch following the PRD's recommended folder structure exactly.

### Component 1: Project Foundation & Configuration

Sets up the folder structure, database connection, and app-wide constants.

#### [NEW] [index.php](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/index.php)
- Entry point that redirects to `auth/login.php` or `pages/dashboard.php` based on session state.

#### [NEW] [config/database.php](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/config/database.php)
- PDO connection setup with error mode `EXCEPTION`, default fetch `ASSOC`, `charset=utf8mb4`.
- Default credentials: `localhost`, `root`, empty password, database `friendship_group_db`.

#### [NEW] [config/app.php](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/config/app.php)
- `APP_NAME` = `'Friendship Group Web'`
- `ADMIN_REGISTER_PASSCODE` = `'admin-secret-code'`
- `MEMBER_REGISTER_PASSCODE` = `'member-secret-code'`
- `MAX_UPLOAD_SIZE` = `5 * 1024 * 1024` (5 MB)
- `ALLOWED_IMAGE_TYPES` = `['image/jpeg', 'image/png', 'image/webp']`
- `UPLOAD_DIR` = `__DIR__ . '/../uploads/gallery/'`

#### [NEW] [database/schema.sql](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/database/schema.sql)
- Full SQL schema from PRD Section 9: `users`, `photos`, `votes`, `vote_options`, `vote_responses`, `trip_agendas`.
- All recommended indexes from PRD Section 20.
- Creates the database `friendship_group_db`.

#### [NEW] [uploads/gallery/.htaccess](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/uploads/gallery/.htaccess)
- Disables PHP execution inside upload directory (`php_flag engine off`).

#### [NEW] [uploads/gallery/.gitkeep](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/uploads/gallery/.gitkeep)
- Ensures the directory is tracked by git.

---

### Component 2: Authentication System

Handles registration with passcode validation, login, logout, and session protection.

#### [NEW] [auth/register.php](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/auth/register.php)
- GET: Renders registration form (name, username, email, password, confirm password, verification passcode).
- POST: Validates all fields per PRD §5.1.3 and §14.1.
  - Checks passcode against `ADMIN_REGISTER_PASSCODE` / `MEMBER_REGISTER_PASSCODE`.
  - Assigns `admin` or `member` role accordingly.
  - Checks unique username/email via prepared statements.
  - Hashes password with `password_hash()`.
  - Inserts user record.
  - Redirects to login page with success message.
- Uses `htmlspecialchars()` for all output.
- Uses PDO prepared statements for all queries.

#### [NEW] [auth/login.php](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/auth/login.php)
- GET: Renders login form (username/email + password).
- POST: Validates credentials.
  - Looks up user by username OR email (single query with `WHERE (username = :login OR email = :login)`).
  - Checks `deleted_at IS NULL`.
  - Verifies password with `password_verify()`.
  - On success: `session_regenerate_id(true)`, stores `user_id`, `user_name`, `user_role` in session, redirects to dashboard.
  - On failure: Shows generic error ("Invalid credentials").

#### [NEW] [auth/logout.php](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/auth/logout.php)
- Destroys session, redirects to login page.

#### [NEW] [auth/auth_check.php](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/auth/auth_check.php)
- Included at the top of every protected page.
- Starts session if not started.
- Checks `$_SESSION['user_id']` exists.
- If not logged in → redirect to `auth/login.php`.

---

### Component 3: Shared UI Includes

Reusable HTML fragments for consistent layout across all pages.

#### [NEW] [includes/header.php](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/includes/header.php)
- HTML `<head>` with meta tags, Google Fonts (Inter), link to `style.css`.
- Accepts `$pageTitle` variable for dynamic `<title>`.

#### [NEW] [includes/navbar.php](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/includes/navbar.php)
- Responsive navigation bar with links: Dashboard, Gallery, Votes, Trip Agenda.
- Shows "Admin Panel" link only if `$_SESSION['user_role'] === 'admin'`.
- Shows logged-in user name and Logout button.
- Mobile hamburger menu with JS toggle.

#### [NEW] [includes/footer.php](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/includes/footer.php)
- Closing HTML tags, link to `app.js`, copyright footer.

---

### Component 4: Dashboard

The main landing page after login, showing preview cards for gallery, votes, and agendas.

#### [NEW] [pages/dashboard.php](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/pages/dashboard.php)
- Requires `auth_check.php`.
- Queries: 6 most recent non-deleted photos, 5 active non-deleted votes, 5 most recent non-deleted trip agendas.
- Renders:
  - Welcome message with user name.
  - **Photo Gallery Card**: Photo thumbnails grid, "View All" and "Upload Photo" buttons.
  - **Voting Card**: Active vote titles with status badge, participant count, "Vote" / "View Result" buttons, "Create Vote" button.
  - **Trip Agenda Card**: Recent agenda cards with destination/date/budget, "Submit Agenda" button.
- Empty states per PRD §12.4.

---

### Component 5: Photo Gallery Module

Full gallery view and photo upload with admin-only deletion.

#### [NEW] [pages/gallery.php](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/pages/gallery.php)
- Requires `auth_check.php`.
- Queries all non-deleted photos (`WHERE deleted_at IS NULL`) ordered by `created_at DESC`.
- Joins with `users` table to get uploader name.
- Renders responsive photo grid with cards showing: image, caption, uploader, date, location.
- Admin users see a "Remove" button on each card (POST form to `photo_delete.php`).
- "Add Photo" button at the top.

#### [NEW] [pages/photo_create.php](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/pages/photo_create.php)
- Requires `auth_check.php`.
- GET: Renders upload form (file input, caption, description, location, trip date).
- POST: Validates per PRD §5.4.2:
  - Checks file exists and has no upload errors.
  - Validates MIME type against allowed types.
  - Validates file size against `MAX_UPLOAD_SIZE`.
  - Generates unique filename with `uniqid()` + original extension.
  - Moves uploaded file to `uploads/gallery/`.
  - Inserts database record.
  - On any failure: removes uploaded file if it was saved, shows error.
  - On success: redirects to gallery.

#### [NEW] [pages/photo_delete.php](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/pages/photo_delete.php)
- Requires `auth_check.php`.
- Checks `$_SESSION['user_role'] === 'admin'` → 403 if not.
- Checks `$_SERVER['REQUEST_METHOD'] === 'POST'` → 405 if not.
- Validates `photo_id` with `filter_input()`.
- Soft deletes: `UPDATE photos SET deleted_at = NOW() WHERE id = :id`.
- Redirects to gallery.

---

### Component 6: Voting Module

Vote creation, participation, results display, and admin-only deletion.

#### [NEW] [pages/votes.php](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/pages/votes.php)
- Requires `auth_check.php`.
- Queries non-deleted votes, joins with users for creator name, counts responses.
- Separates into Active and Closed/Expired sections.
- Each card shows: title, status badge, total voters, creator, deadline.
- Links to `vote_detail.php?id=X`.
- "Create Vote" button.
- Admin sees "Remove" button on each card.

#### [NEW] [pages/vote_create.php](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/pages/vote_create.php)
- Requires `auth_check.php`.
- GET: Renders form with title, description, dynamic option inputs (min 2, JS "Add Option" button), deadline datetime input, status select.
- POST: Validates title not empty, at least 2 non-empty options, deadline is valid future datetime.
  - Inserts into `votes` table.
  - Inserts each option into `vote_options` table.
  - Redirects to `vote_detail.php?id=NEW_ID`.

#### [NEW] [pages/vote_detail.php](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/pages/vote_detail.php)
- Requires `auth_check.php`.
- Loads vote by `$_GET['id']`, checks not deleted.
- Loads options with vote counts.
- Checks if current user has already voted.
- **If vote is active AND user hasn't voted**: Shows voting form (radio buttons for options, submit).
- **If user has voted OR vote is closed/expired**: Shows results with bar chart (CSS-based), percentages, total voters, winning option highlighted.
- POST: Validates option_id exists for this vote, checks no duplicate (unique constraint), checks deadline not passed, checks status is active, inserts into `vote_responses`.
- Admin sees "Remove Vote" button.

#### [NEW] [pages/vote_delete.php](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/pages/vote_delete.php)
- Same pattern as `photo_delete.php` but for votes table.
- Soft deletes vote record.
- Redirects to votes list.

---

### Component 7: Trip Agenda Module

Agenda submission, listing, detail view, and admin-only deletion.

#### [NEW] [pages/agendas.php](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/pages/agendas.php)
- Requires `auth_check.php`.
- Queries non-deleted agendas, joins users for submitter name, ordered by proposed_date.
- Renders agenda cards: destination, proposed date, budget (formatted), submitter, description excerpt.
- Links to `agenda_detail.php?id=X`.
- "Submit New Agenda" button.
- Admin sees "Remove" button on each card.

#### [NEW] [pages/agenda_create.php](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/pages/agenda_create.php)
- Requires `auth_check.php`.
- GET: Renders form with all fields from PRD §5.6.1 (destination, proposed date, estimated budget, description, meeting point, transportation, accommodation, activities, notes).
- POST: Validates required fields per PRD §5.6.2. Inserts into `trip_agendas`. Redirects to agendas list.

#### [NEW] [pages/agenda_detail.php](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/pages/agenda_detail.php)
- Requires `auth_check.php`.
- Loads agenda by `$_GET['id']`, checks not deleted.
- Displays all fields: destination, date, budget, meeting point, transportation, accommodation, activities, notes, submitter, submission date.
- Admin sees "Remove" button.

#### [NEW] [pages/agenda_delete.php](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/pages/agenda_delete.php)
- Same pattern as other delete handlers.
- Soft deletes agenda record.
- Redirects to agendas list.

---

### Component 8: Admin Panel

#### [NEW] [admin/index.php](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/admin/index.php)
- Requires `auth_check.php` + admin role check.
- Dashboard-style overview:
  - Total members / admins count.
  - Total photos / votes / agendas count.
  - Recent activity.
- Member management section: list all users with name, username, email, role, registration date.
- Quick links to manage photos, votes, agendas.

---

### Component 9: Frontend Assets — CSS & JS

#### [NEW] [assets/css/style.css](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/assets/css/style.css)
- **Design system**: CSS custom properties for colors, spacing, typography, border-radius, shadows.
- **Color palette**: Modern, warm palette — deep navy primary (#1a1a2e), gradient accents (teal → violet), soft card backgrounds.
- **Typography**: Google Font "Inter" for body, clean hierarchy.
- **Components**: 
  - Cards with glassmorphism effect (backdrop-filter, subtle borders).
  - Buttons with gradient backgrounds and hover animations.
  - Form inputs with focus transitions.
  - Navigation bar with blur effect.
  - Photo grid with masonry-like responsive layout.
  - Vote result bars with animated fill.
  - Status badges (active/closed/expired) with distinct colors.
  - Alert/message components (success, error, warning).
  - Modal confirmation for delete actions.
- **Responsive**: Mobile-first with breakpoints at 576px, 768px, 1024px.
- **Animations**: Subtle fade-ins on page load, hover scale on cards, smooth transitions on all interactive elements.

#### [NEW] [assets/js/app.js](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/assets/js/app.js)
- Mobile navigation toggle (hamburger menu).
- Dynamic vote option add/remove in create vote form.
- Delete confirmation modal (prevents accidental deletions).
- Image preview on photo upload.
- Form validation feedback (client-side, supplementary to server-side).
- Auto-dismiss flash messages after 5 seconds.

---

### Component 10: Security Hardening

#### [NEW] [.htaccess](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/.htaccess)
- Deny access to `config/` directory from web.
- Set security headers.

#### [NEW] [config/.htaccess](file:///home/dibow/Documents/Projects/AliansiAntah-Berantah/config/.htaccess)
- `Deny from all` — prevents direct web access to config files.

---

## File Structure Summary

```
AliansiAntah-Berantah/
├── index.php
├── .htaccess
├── PRD.md
│
├── config/
│   ├── .htaccess
│   ├── database.php
│   └── app.php
│
├── database/
│   └── schema.sql
│
├── auth/
│   ├── register.php
│   ├── login.php
│   ├── logout.php
│   └── auth_check.php
│
├── includes/
│   ├── header.php
│   ├── navbar.php
│   └── footer.php
│
├── uploads/
│   └── gallery/
│       ├── .htaccess
│       └── .gitkeep
│
├── pages/
│   ├── dashboard.php
│   ├── gallery.php
│   ├── photo_create.php
│   ├── photo_delete.php
│   ├── votes.php
│   ├── vote_create.php
│   ├── vote_detail.php
│   ├── vote_delete.php
│   ├── agendas.php
│   ├── agenda_create.php
│   ├── agenda_detail.php
│   └── agenda_delete.php
│
├── admin/
│   └── index.php
│
└── assets/
    ├── css/
    │   └── style.css
    └── js/
        └── app.js
```

---

## Verification Plan

### Automated Tests

1. **Database schema**: Run `schema.sql` against MySQL and verify all tables create successfully.
2. **Registration flow**: Register with admin passcode → verify role is `admin`. Register with member passcode → verify role is `member`. Register with invalid passcode → verify rejection.
3. **Login flow**: Login with valid credentials → verify session created. Login with invalid credentials → verify error.
4. **Photo upload**: Upload valid JPG → verify file saved and DB record created. Upload invalid file type → verify rejection.
5. **Voting**: Create vote → verify DB records. Submit vote → verify response saved. Submit duplicate vote → verify rejection.
6. **Delete authorization**: Attempt delete as member → verify 403. Attempt delete as admin → verify success.

### Manual Verification

1. **Visual inspection**: Open all pages in desktop and mobile viewports.
2. **Full user flow walkthrough**: Register → Login → Dashboard → Upload Photo → Create Vote → Vote → Submit Agenda → Logout.
3. **Admin flow**: Login as admin → verify remove buttons visible → remove photo/vote/agenda → verify items disappear.
4. **Member flow**: Login as member → verify no remove buttons → attempt direct access to delete endpoints → verify 403.

---

## Execution Order

Following the PRD milestones:

1. **Milestone 1**: `database/schema.sql` → `config/` → `auth/` (register, login, logout, auth_check)
2. **Milestone 2**: `includes/` (header, navbar, footer) → `assets/css/style.css` → `pages/dashboard.php`
3. **Milestone 3**: `pages/gallery.php` → `pages/photo_create.php` → `pages/photo_delete.php`
4. **Milestone 4**: `pages/votes.php` → `pages/vote_create.php` → `pages/vote_detail.php` → `pages/vote_delete.php`
5. **Milestone 5**: `pages/agendas.php` → `pages/agenda_create.php` → `pages/agenda_detail.php` → `pages/agenda_delete.php`
6. **Milestone 6**: `admin/index.php` → security hardening (`.htaccess` files)
7. **Milestone 7**: `assets/js/app.js` → responsive polish → `index.php`
