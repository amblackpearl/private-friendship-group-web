# Product Requirements Document: Friendship Group Web Application

## 1. Product Overview

### 1.1 Product Name

**Friendship Group Web**

### 1.2 Purpose

The Friendship Group Web Application is a private web platform for a friendship group. The application allows users to register using a valid verification passcode, log in using username/email and password, access a dashboard, view a shared photo gallery, upload photos, create or participate in voting forms, and submit next trip agenda proposals.
The platform centralizes friendship memories, group decision-making, and trip planning in one private web application.

### 1.3 Target Users

The target users are friendship group members and one or more admins. Users can only register if they have a valid verification passcode.

There are two verification passcodes:

* Admin verification passcode
* Member verification passcode
* If the user registers using the **admin** verification passcode, the system creates an admin account.
* If the user registers using the **member** verification passcode, the system creates a member account.
* If the user enters an invalid verification passcode, registration is declined.

### 1.4 Technology Stack

* **Frontend:** HTML, CSS, JavaScript
* **Backend:** PHP
* **Database:** MySQL or MariaDB
* **Server Environment:** Apache or Nginx
* **Local Development:** XAMPP, Laragon, or similar PHP local server
* **Authentication:** PHP session-based authentication
* **File Storage:** Server-side upload directory for photos
* **Database Access:** PDO, recommended over MySQLi

---

## 2. Product Goals

### 2.1 Main Goals

* Provide private account registration using verification passcodes.
* Assign user roles automatically based on the verification passcode.
* Allow users to log in using username/email and password.
* Allow members and admins to access a dashboard.
* Allow users to view and upload photos to a shared gallery.
* Allow users to create voting forms.
* Allow users to vote once per voting form.
* Allow users to submit next trip agenda proposals.
* Allow admin to remove photos, votes, and trip agendas.
* Prevent regular members from removing any content.
* Use PHP and SQL as the main backend and database stack.

### 2.2 Success Criteria

* Users can register only with a valid verification passcode.
* Invalid verification passcodes reject registration.
* Admin passcode creates an admin account.
* Member passcode creates a member account.
* Users can log in successfully after registration.
* Users can upload photos and see them in the gallery.
* Uploaded photos are stored correctly and linked to the uploader.
* Users can create voting topics.
* Users can submit votes.
* The system prevents duplicate voting by the same user on the same vote.
* Users can submit trip agenda forms.
* Dashboard displays gallery, voting, and agenda sections clearly.
* Only admin can remove photos, votes, and trip agendas.
* Regular members cannot access delete actions directly or indirectly.
* The application works on desktop and mobile screens.

---

## 3. Scope

### 3.1 In Scope

* User registration with verification passcode.
* Role assignment based on verification passcode.
* Login using username/email and password.
* Logout.
* Session protection.
* Member dashboard.
* Shared photo gallery.
* Photo upload feature.
* Vote creation.
* Vote participation.
* Vote result display.
* Next trip agenda submission.
* Trip agenda list display.
* Admin-only removal of photos.
* Admin-only removal of votes.
* Admin-only removal of trip agendas.
* Basic admin panel.
* Responsive layout.
* SQL database schema.
* Basic security validation.

### 3.2 Out of Scope

* Open public registration without passcode.
* Payment system.
* Real-time chat.
* Advanced social media features.
* AI-based photo recognition.
* Mobile native application.
* Push notifications.
* Complex role hierarchy beyond member/admin.
* Cloud object storage integration, unless required later.

---

## 4. User Roles

### 4.1 Guest

A guest is a user who is not logged in.
**Guest permissions:**

* Open login page.
* Open register page.
* Register using a valid verification passcode.

**Guest restrictions:**

* Cannot view dashboard.
* Cannot view gallery.
* Cannot upload photo.
* Cannot create vote.
* Cannot vote.
* Cannot submit trip agenda.
* Cannot access admin pages.

### 4.2 Member

A member is a registered user who created an account using the member verification passcode.
**Member permissions:**

* Log in.
* Log out.
* View dashboard.
* View photo gallery.
* Upload photos.
* Create vote forms.
* Submit votes.
* View vote results.
* Submit next trip agenda.
* View submitted trip agendas.

**Member restrictions:**

* Cannot remove photos.
* Cannot remove votes.
* Cannot remove trip agendas.
* Cannot access admin management pages.
* Cannot remove their own uploaded photos.
* Cannot remove their own created votes.
* Cannot remove their own submitted trip agendas.

### 4.3 Admin

An admin is a registered user who created an account using the admin verification passcode.
**Admin permissions:**

* Log in.
* Log out.
* View dashboard.
* View photo gallery.
* Upload photos.
* Create vote forms.
* Submit votes.
* View vote results.
* Submit next trip agenda.
* View submitted trip agendas.
* Remove any uploaded photo.
* Remove any vote form.
* Remove any trip agenda submission.
* Access admin panel.
* Manage member data if needed.

---

## 5. Functional Requirements

### 5.1 Registration System

#### 5.1.1 Register Account

The system shall allow users to register their own account using: Name, Username, Email, Password, Confirm password, Verification passcode.
The system shall only create an account if the verification passcode is valid.

#### 5.1.2 Verification Passcode Types

The system shall support two verification passcode types:

1. Admin verification passcode
2. Member verification passcode

* If the submitted passcode matches the **admin** verification passcode, the created user role shall be **admin**.
* If the submitted passcode matches the **member** verification passcode, the created user role shall be **member**.
* If the submitted passcode does not match either passcode, registration shall be declined.

#### 5.1.3 Registration Validation

The registration form shall validate:

* Name is required.
* Username is required.
* Email is required.
* Email must use a valid email format.
* Password is required.
* Confirm password is required.
* Password and confirm password must match.
* Verification passcode is required.
* Verification passcode must match either the admin passcode or the member passcode.
* Username must not already exist.
* Email must not already exist.

If validation fails, the system shall show a clear error message and shall not create the account.

#### 5.1.4 Registration Security

* Password must be hashed using PHP `password_hash()`.
* The system must not store plain-text passwords.
* The system must not store the submitted raw verification passcode in the users table.
* Verification passcodes must be checked only on the server side.
* Verification passcodes must not be exposed in frontend JavaScript, HTML comments, or public files.

**Recommended MVP passcode storage:**

```php
define('ADMIN_REGISTER_PASSCODE', 'change-this-admin-code');
define('MEMBER_REGISTER_PASSCODE', 'change-this-member-code');

```

**Recommended production passcode storage:**

```php
$adminPasscode = getenv('ADMIN_REGISTER_PASSCODE');
$memberPasscode = getenv('MEMBER_REGISTER_PASSCODE');

```

#### 5.1.5 Registration Result

After successful registration, the system should redirect the user to the login page.
**Recommended MVP behavior:**

* Do not automatically log the user in after registration.
* Show success message: *“Registration successful. Please log in.”*

### 5.2 Authentication System

#### 5.2.1 Login

The system shall allow registered users to log in using either username or email and password.

* **Required fields:** Username or email, Password
* **Validation:** Username/email must not be empty. Password must not be empty.
* Invalid login shall show an error message.
* Successful login shall redirect the user to the dashboard.

#### 5.2.2 Logout

The system shall allow logged-in users to log out. After logout, the session shall be destroyed. The user shall be redirected to the login page.

#### 5.2.3 Session Protection

All private pages shall require an active login session. If a user is not logged in, they shall be redirected to the login page.
**Protected pages:**
Dashboard, Gallery, Upload photo page, Vote list, Vote creation page, Vote detail page, Trip agenda form, Trip agenda list, Admin pages, Delete handlers.

#### 5.2.4 Password Security

* Passwords shall be stored using PHP `password_hash()`.
* Login verification shall use `password_verify()`.
* Plain-text passwords must never be stored in the database.

#### 5.2.5 Session Security

The system shall regenerate the session ID after successful login using:

```php
session_regenerate_id(true);

```

The session shall store at minimum: `user_id`, `user_name`, `user_role`.

### 5.3 Member Dashboard

#### 5.3.1 Dashboard Content

After login, each user shall see a dashboard containing:

* Welcome message with user name.
* Photo gallery preview.
* Button to upload a new photo.
* Active voting forms.
* Button to create a new vote.
* Next trip agenda form shortcut.
* Recent submitted trip agendas.

#### 5.3.2 Dashboard Gallery

The dashboard shall show recently uploaded photos. Each photo preview shall display:
Photo image, Photo title or caption, Uploader name, Upload date.

#### 5.3.3 Dashboard Vote Section

The dashboard shall show active votes. Each vote item shall display:
Vote title, Vote status, Total participants, Button to vote or view result.

#### 5.3.4 Dashboard Trip Agenda Section

The dashboard shall show recent trip agenda proposals. Each agenda item shall display:
Destination, Proposed date, Submitted by, Short description.

### 5.4 Photo Gallery Module

#### 5.4.1 View Gallery

Logged-in users shall be able to view all uploaded photos in a gallery layout. Each photo card shall display:
Photo, Caption, Uploader name, Upload date, Optional location.

#### 5.4.2 Add New Photo

Logged-in users shall be able to upload a new photo.

* **Required fields:** Photo file, Caption or title
* **Optional fields:** Description, Location, Trip date
* **Validation:**
* File must be an image.
* Allowed formats: JPG, JPEG, PNG, WEBP.
* Maximum file size should be configurable, recommended 5 MB.
* Caption must not be empty.
* The system shall reject unsupported file types.
* The system shall generate a unique file name before saving.



#### 5.4.3 Photo Storage

Uploaded photos shall be stored in a server directory, for example: `/uploads/gallery/`
The database shall store: File path, Caption, Description, Uploader user ID, Upload timestamp.

#### 5.4.4 Remove Photo

Only admin shall be able to remove photos from the gallery. Regular members shall not be allowed to remove any photo, including their own uploaded photos.
When admin removes a photo:

* The photo shall no longer appear in the gallery.
* The related database record shall be soft deleted using `deleted_at`, or hard deleted if the project uses physical deletion.
* The uploaded image file may be removed from server storage or archived.

**Recommended behavior:**

* Use soft delete for database records.
* Hide the photo from gallery queries using `WHERE deleted_at IS NULL`.
* Only show the remove button to admin users.
* Validate admin permission again in the backend delete handler.
* Members shall not see a remove button on photo cards.

### 5.5 Voting Module

#### 5.5.1 Create Vote

Logged-in users shall be able to create a new vote form.

* **Required fields:** Vote title, Vote description, Vote options, Vote deadline, Vote status.
* **Minimum requirements:** A vote must have at least 2 options. Vote title must not be empty. Vote deadline must be a valid future date.

#### 5.5.2 Vote Options

Each vote can contain multiple options.

> **Example vote:**
> **Title:** Choose Next Trip Destination
> **Options:** Bali, Yogyakarta, Malang, Bandung

#### 5.5.3 Submit Vote

Logged-in users shall be able to vote on active voting forms.

* **Rules:**
* Each user can vote only once per voting form.
* The system shall prevent duplicate votes.
* The system shall only allow voting before the deadline.
* The system shall only allow voting when vote status is active.



#### 5.5.4 Vote Result

Logged-in users shall be able to view vote results. Vote result shall show:
Vote title, Total voters, Each option, Number of votes per option, Percentage per option, Winning option.

#### 5.5.5 Vote Status

A vote can have the following status: `Draft`, `Active`, `Closed`, `Expired`.

* **System behavior:**
* Active votes can receive votes.
* Closed votes cannot receive votes.
* Expired votes are automatically unavailable after the deadline.
* Draft votes are not visible to other users unless published.



#### 5.5.6 Close Vote

Admin or vote creator may close a vote manually. Once closed, no new vote submission shall be accepted.

#### 5.5.7 Remove Vote

Only admin shall be able to remove a vote. Regular members shall not be allowed to remove any vote, including votes they created.
When admin removes a vote:

* The vote shall no longer appear in the vote list.
* The vote detail page shall no longer be accessible.
* The vote result shall no longer be accessible.
* Related vote options and responses may remain stored for audit purposes if soft delete is used.

**Recommended behavior:**

* Use soft delete on the votes table using `deleted_at`.
* Hide removed votes from normal queries using `WHERE deleted_at IS NULL`.
* Only show the remove button to admin users.
* Validate admin permission again in the backend delete handler.
* Members shall not see remove controls on vote cards or vote detail pages.

### 5.6 Next Trip Agenda Module

#### 5.6.1 Submit Trip Agenda

Logged-in users shall be able to submit a next trip agenda proposal.

* **Required fields:** Destination, Proposed date, Estimated budget, Description.
* **Optional fields:** Meeting point, Transportation plan, Accommodation plan, Activity list, Notes.

#### 5.6.2 Agenda Validation

* Destination must not be empty.
* Proposed date must be a valid date.
* Estimated budget must be numeric.
* Description must not be empty.

#### 5.6.3 View Trip Agenda List

Logged-in users shall be able to view all submitted trip agendas. Each agenda card shall display:
Destination, Proposed date, Estimated budget, Submitted by, Created date, Description.

#### 5.6.4 Agenda Detail

Logged-in users shall be able to open a trip agenda detail page. The detail page shall show:
Destination, Proposed date, Budget, Meeting point, Transportation plan, Accommodation plan, Activities, Notes, Submitter name, Submission date.

#### 5.6.5 Edit Agenda

* **Optional feature:** Members may edit their own submitted agenda. Admin may edit any agenda.

#### 5.6.6 Remove Trip Agenda

Only admin shall be able to remove trip agenda submissions. Regular members shall not be allowed to remove any trip agenda, including their own submitted agenda.
When admin removes a trip agenda:

* The agenda shall no longer appear in the agenda list.
* The agenda detail page shall no longer be accessible to regular members.

**Recommended behavior:**

* Use soft delete with `deleted_at`.
* Hide removed agendas from normal queries using `WHERE deleted_at IS NULL`.
* Only show the remove button to admin users.
* Validate admin permission again in the backend delete handler.
* Members shall not see remove controls on agenda cards or agenda detail pages.

---

## 6. User Flow

### 6.1 Registration Flow

1. User opens the registration page.
2. User enters name, username, email, password, confirm password, and verification passcode.
3. System validates all input.
4. System checks whether the verification passcode matches the admin passcode or member passcode.
5. If the passcode matches the admin passcode, system assigns role as admin.
6. If the passcode matches the member passcode, system assigns role as member.
7. If the passcode is invalid, system declines registration.
8. System checks whether username or email already exists.
9. System hashes the password.
10. System creates the user account.
11. System redirects user to login page.

### 6.2 Login Flow

1. User opens the login page.
2. System displays login form.
3. User enters username/email and password.
4. System validates credentials.
5. If valid, system creates session.
6. System redirects user to dashboard.
7. If invalid, system displays error message.

### 6.3 Photo Upload Flow

1. User logs in.
2. User opens dashboard or gallery page.
3. User clicks Add New Photo.
4. System displays upload form.
5. User selects image and fills caption.
6. User submits form.
7. System validates file and input.
8. System saves image to upload folder.
9. System saves photo data to database.
10. System redirects user to gallery.

### 6.4 Admin Remove Photo Flow

1. Admin logs in.
2. Admin opens gallery page.
3. System displays remove button on each photo card.
4. Admin clicks remove photo.
5. System confirms removal action.
6. Admin confirms.
7. System verifies admin role in backend.
8. System removes or soft deletes photo record.
9. System redirects back to gallery.
*(Regular members do not see remove buttons and cannot access the delete endpoint.)*

### 6.5 Voting Flow

1. User logs in.
2. User opens voting page.
3. User selects active vote.
4. System checks whether user already voted.
5. If not voted, system displays voting form.
6. User selects one option.
7. User submits vote.
8. System saves vote.
9. System redirects to vote result page.
*(If already voted, system directly shows result page.)*

### 6.6 Create Vote Flow

1. User logs in.
2. User opens Create Vote page.
3. User enters vote title, description, options, and deadline.
4. System validates input.
5. System saves vote and vote options.
6. System redirects to vote detail page.
7. Other users can now vote.

### 6.7 Admin Remove Vote Flow

1. Admin logs in.
2. Admin opens vote list or vote detail page.
3. System displays remove button for each vote.
4. Admin clicks remove vote.
5. System confirms removal action.
6. Admin confirms.
7. System verifies admin role in backend.
8. System removes or soft deletes vote record.
9. System redirects back to vote list.
*(Regular members do not see remove buttons and cannot access the delete endpoint.)*

### 6.8 Trip Agenda Flow

1. User logs in.
2. User opens Submit Trip Agenda page.
3. User fills destination, date, budget, and description.
4. User submits form.
5. System validates input.
6. System saves agenda to database.
7. System redirects to agenda list page.

### 6.9 Admin Remove Trip Agenda Flow

1. Admin logs in.
2. Admin opens agenda list or agenda detail page.
3. System displays remove button for each agenda.
4. Admin clicks remove agenda.
5. System confirms removal action.
6. Admin confirms.
7. System verifies admin role in backend.
8. System removes or soft deletes agenda record.
9. System redirects back to agenda list.
*(Regular members do not see remove buttons and cannot access the delete endpoint.)*

---

## 7. Page Requirements

### 7.1 Register Page

* **Path example:** `/register.php`
* **Components:** Name input, Username input, Email input, Password input, Confirm password input, Verification passcode input, Register button, Login page link, Validation message area, Success message area.

### 7.2 Login Page

* **Path example:** `/login.php`
* **Components:** Website title, Username/email input, Password input, Login button, Register page link, Error message area.

### 7.3 Dashboard Page

* **Path example:** `/dashboard.php`
* **Components:** Navigation bar, Welcome message, Photo gallery preview, Active votes section, Trip agenda preview, Quick action buttons.

### 7.4 Gallery Page

* **Path example:** `/gallery.php`
* **Components:** Photo grid, Add photo button, Photo cards, Admin-only remove photo button, Filter or search (optional).

### 7.5 Add Photo Page

* **Path example:** `/photo_create.php`
* **Components:** Photo upload input, Caption input, Description textarea, Location input, Submit button, Validation messages.

### 7.6 Vote List Page

* **Path example:** `/votes.php`
* **Components:** Active vote list, Closed vote list, Create vote button, Vote status label, Admin-only remove vote button.

### 7.7 Create Vote Page

* **Path example:** `/vote_create.php`
* **Components:** Vote title input, Description textarea, Dynamic vote option inputs, Deadline input, Submit button.

### 7.8 Vote Detail Page

* **Path example:** `/vote_detail.php?id=1`
* **Components:** Vote title, Vote description, Vote options, Vote button, Vote result chart or percentage list, Vote deadline, Vote status, Admin-only remove vote button.

### 7.9 Trip Agenda List Page

* **Path example:** `/agendas.php`
* **Components:** Agenda cards, Submit agenda button, Destination, Proposed date, Budget, Submitter, Admin-only remove agenda button.

### 7.10 Submit Trip Agenda Page

* **Path example:** `/agenda_create.php`
* **Components:** Destination input, Proposed date input, Estimated budget input, Meeting point input, Transportation plan textarea, Accommodation plan textarea, Activity list textarea, Notes textarea, Submit button.

### 7.11 Admin Page

* **Path example:** `/admin.php`
* **Components:** Member management, Photo management, Vote management, Agenda management, Admin-only delete actions.

### 7.12 Delete Handler Pages

**Recommended delete handler paths:**
`/photo_delete.php`, `/vote_delete.php`, `/agenda_delete.php`

**Each delete handler must:**

* Require active session.
* Require admin role.
* Validate request method.
* Validate target ID.
* Perform soft delete or hard delete.
* Redirect after successful action.
* Reject non-admin users with HTTP 403.

---

## 8. Database Requirements

### 8.1 Database Name

Recommended database name: `friendship_group_db`

### 8.2 Table: users

* **Purpose:** Stores registered user account data.
* **Fields:**
* `id` INT PRIMARY KEY AUTO_INCREMENT
* `name` VARCHAR(100) NOT NULL
* `username` VARCHAR(50) NOT NULL UNIQUE
* `email` VARCHAR(100) NOT NULL UNIQUE
* `password_hash` VARCHAR(255) NOT NULL
* `role` ENUM('member', 'admin') DEFAULT 'member'
* `profile_photo` VARCHAR(255) NULL
* `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
* `updated_at` DATETIME NULL
* `deleted_at` DATETIME NULL


* **Important:** The raw verification passcode is not stored in this table.

### 8.3 Table: photos

* **Purpose:** Stores photo gallery data.
* **Fields:**
* `id` INT PRIMARY KEY AUTO_INCREMENT
* `user_id` INT NOT NULL
* `caption` VARCHAR(150) NOT NULL
* `description` TEXT NULL
* `file_path` VARCHAR(255) NOT NULL
* `location` VARCHAR(150) NULL
* `trip_date` DATE NULL
* `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
* `updated_at` DATETIME NULL
* `deleted_at` DATETIME NULL


* **Relationship:** `user_id` references `users.id`

### 8.4 Table: votes

* **Purpose:** Stores voting form data.
* **Fields:**
* `id` INT PRIMARY KEY AUTO_INCREMENT
* `created_by` INT NOT NULL
* `title` VARCHAR(150) NOT NULL
* `description` TEXT NULL
* `status` ENUM('draft', 'active', 'closed') DEFAULT 'active'
* `deadline` DATETIME NOT NULL
* `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
* `updated_at` DATETIME NULL
* `deleted_at` DATETIME NULL


* **Relationship:** `created_by` references `users.id`

### 8.5 Table: vote_options

* **Purpose:** Stores options for each vote.
* **Fields:**
* `id` INT PRIMARY KEY AUTO_INCREMENT
* `vote_id` INT NOT NULL
* `option_text` VARCHAR(150) NOT NULL
* `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP


* **Relationship:** `vote_id` references `votes.id`

### 8.6 Table: vote_responses

* **Purpose:** Stores user vote responses.
* **Fields:**
* `id` INT PRIMARY KEY AUTO_INCREMENT
* `vote_id` INT NOT NULL
* `option_id` INT NOT NULL
* `user_id` INT NOT NULL
* `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP


* **Relationships:**
* `vote_id` references `votes.id`
* `option_id` references `vote_options.id`
* `user_id` references `users.id`


* **Important constraint:** Unique key on `vote_id` and `user_id`. This prevents one user from voting more than once on the same vote.

### 8.7 Table: trip_agendas

* **Purpose:** Stores next trip agenda proposals.
* **Fields:**
* `id` INT PRIMARY KEY AUTO_INCREMENT
* `user_id` INT NOT NULL
* `destination` VARCHAR(150) NOT NULL
* `proposed_date` DATE NOT NULL
* `estimated_budget` DECIMAL(12,2) NOT NULL
* `meeting_point` VARCHAR(150) NULL
* `transportation_plan` TEXT NULL
* `accommodation_plan` TEXT NULL
* `activity_list` TEXT NULL
* `description` TEXT NOT NULL
* `notes` TEXT NULL
* `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
* `updated_at` DATETIME NULL
* `deleted_at` DATETIME NULL


* **Relationship:** `user_id` references `users.id`

### 8.8 Optional Table: verification_passcodes

For MVP, verification passcodes can be stored in backend configuration or environment variables. For a more advanced implementation, passcodes may be stored in a database table.

**Optional table:**

```sql
CREATE TABLE verification_passcodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code_hash VARCHAR(255) NOT NULL,
    role ENUM('member', 'admin') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL
);

```

**Important:**
If this table is used, store passcodes using `password_hash()`. Verify submitted passcodes using `password_verify()`. Do not store raw passcodes.

---

## 9. Recommended SQL Schema

```sql
CREATE DATABASE friendship_group_db;
USE friendship_group_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('member', 'admin') DEFAULT 'member',
    profile_photo VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL
);

CREATE TABLE photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    caption VARCHAR(150) NOT NULL,
    description TEXT NULL,
    file_path VARCHAR(255) NOT NULL,
    location VARCHAR(150) NULL,
    trip_date DATE NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,
    CONSTRAINT fk_photos_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
);

CREATE TABLE votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    created_by INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NULL,
    status ENUM('draft', 'active', 'closed') DEFAULT 'active',
    deadline DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,
    CONSTRAINT fk_votes_user
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE CASCADE
);

CREATE TABLE vote_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vote_id INT NOT NULL,
    option_text VARCHAR(150) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_vote_options_vote
        FOREIGN KEY (vote_id) REFERENCES votes(id)
        ON DELETE CASCADE
);

CREATE TABLE vote_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vote_id INT NOT NULL,
    option_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_vote_responses_vote
        FOREIGN KEY (vote_id) REFERENCES votes(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_vote_responses_option
        FOREIGN KEY (option_id) REFERENCES vote_options(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_vote_responses_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    UNIQUE KEY unique_user_vote (vote_id, user_id)
);

CREATE TABLE trip_agendas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    destination VARCHAR(150) NOT NULL,
    proposed_date DATE NOT NULL,
    estimated_budget DECIMAL(12,2) NOT NULL,
    meeting_point VARCHAR(150) NULL,
    transportation_plan TEXT NULL,
    accommodation_plan TEXT NULL,
    activity_list TEXT NULL,
    description TEXT NOT NULL,
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,
    CONSTRAINT fk_trip_agendas_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
);

```

---

## 10. Data Rules

### 10.1 User Registration Rules

* Users may register themselves.
* Registration requires a valid verification passcode.
* Admin account creation requires the admin verification passcode.
* Member account creation requires the member verification passcode.
* Invalid passcode means registration is declined.
* The system shall reject duplicate username.
* The system shall reject duplicate email.
* The system shall store only hashed passwords.
* The system shall not store raw verification passcodes in the users table.

### 10.2 User Login Rules

* Only registered users can log in.
* Users may log in using username or email.
* Deleted users must not be able to log in.
* The system must verify credentials securely using `password_verify()`.

### 10.3 Photo Rules

* Only logged-in users can upload photos.
* Photos must use valid image formats.
* Photos must not exceed maximum file size.
* Photos must be visible to all logged-in users.
* Only admin can remove photos.
* Members cannot remove photos.

### 10.4 Voting Rules

* Only logged-in users can create votes.
* A vote must have at least two options.
* Only active votes can receive responses.
* A user can vote only once per vote.
* Votes after deadline are rejected.
* Vote result is visible to logged-in users.
* Only admin can remove votes.
* Members cannot remove votes.

### 10.5 Trip Agenda Rules

* Only logged-in users can submit trip agenda proposals.
* Trip agenda destination, date, budget, and description are required.
* All logged-in users can view submitted agendas.
* Only admin can remove trip agenda submissions.
* Members cannot remove trip agenda submissions.

---

## 11. Security Requirements

### 11.1 Authentication Security

* Use PHP sessions.
* Regenerate session ID after login using `session_regenerate_id(true)`.
* Use `password_hash()` and `password_verify()`.
* Do not store plain-text passwords.

### 11.2 Verification Passcode Security

* Verification passcodes must be checked only on the server side.
* Verification passcodes must not be included in frontend JavaScript.
* Verification passcodes must not be exposed in HTML.
* Verification passcodes must not be committed to public repositories.

**Recommended approach:**

* Use environment variables for production.
* Use a private PHP config file for local development.
* The system should allow passcodes to be changed without modifying user records.

### 11.3 SQL Injection Prevention

* Use prepared statements with PDO.
* Do not concatenate user input directly into SQL queries.

### 11.4 XSS Prevention

* Escape output using `htmlspecialchars()`.
* Validate and sanitize input before saving or displaying.

### 11.5 File Upload Security

* Allow only image file extensions: JPG, JPEG, PNG, WEBP.
* Validate MIME type.
* Rename uploaded files using a unique generated filename.
* Store uploaded files outside executable PHP directories if possible.
* Disable PHP execution inside upload directory.
* Limit upload size.

### 11.6 Authorization

* Members can create and view content.
* Only admin can remove content.
* Every protected action must check session user ID and role.
* Every delete action must check admin role in backend, not only in the UI.

**Required backend rule:**

```php
if ($_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit('Access denied.');
}

```

This check must exist in: `photo_delete.php`, `vote_delete.php`, `agenda_delete.php`

### 11.7 Delete Action Security

* Delete actions should use POST requests, not GET requests.
* Each delete request should include a valid content ID.

**Recommended delete request validation:**

* Check active session.
* Check admin role.
* Check request method is POST.
* Check submitted ID is numeric.
* Check target record exists.
* Apply soft delete or hard delete.
* Redirect after success.
* Reject invalid requests.

**Example:**

```php
session_start();
require_once 'auth/auth_check.php';
require_once 'config/database.php';

if ($_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit('Access denied.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

$photoId = filter_input(INPUT_POST, 'photo_id', FILTER_VALIDATE_INT);

if (!$photoId) {
    http_response_code(400);
    exit('Invalid photo ID.');
}

$stmt = $pdo->prepare("UPDATE photos SET deleted_at = NOW() WHERE id = :id");
$stmt->execute(['id' => $photoId]);

header('Location: gallery.php');
exit;

```

---

## 12. UI/UX Requirements

### 12.1 Design Style

The design should feel private, friendly, clean, and modern.
**Recommended style:**

* Soft card-based dashboard
* Rounded photo cards
* Clear navigation
* Mobile-first layout
* Simple color palette
* Readable typography

### 12.2 Navigation Menu

Navigation should contain:
Dashboard, Gallery, Votes, Trip Agenda, Logout, Admin menu if user role is admin.

### 12.3 Dashboard Layout

Dashboard should use cards:
Photo Gallery Card, Voting Card, Trip Agenda Card, Quick Action Card.

### 12.4 Empty State

* **If no photos exist:** Display: “No photos uploaded yet.” Show Add Photo button.
* **If no active votes exist:** Display: “No active votes right now.” Show Create Vote button.
* **If no agendas exist:** Display: “No trip agenda submitted yet.” Show Submit Agenda button.

### 12.5 Admin-Only UI Controls

Remove buttons shall only be visible to admin users.

**Example condition:**

```php
<?php if ($_SESSION['user_role'] === 'admin'): ?>
    <form action="photo_delete.php" method="POST">
        <input type="hidden" name="photo_id" value="<?= $photo['id']; ?>">
        <button type="submit" class="delete-btn">Remove Photo</button>
    </form>
<?php endif; ?>

```

Members shall not see delete controls. However, hiding the button is not enough. Backend validation is mandatory.

---

## 13. Non-Functional Requirements

### 13.1 Performance

* Dashboard should load within 2 seconds on normal hosting.
* Gallery should use optimized image sizes or thumbnails.
* SQL queries should use indexes for common lookups.

### 13.2 Usability

The interface should be simple and mobile-friendly. Main actions should be easy to access:
Register, Login, Add Photo, Create Vote, Submit Agenda, Vote Now, Admin Remove Action. Navigation should be clear.

### 13.3 Responsiveness

The web application shall support: Mobile screen, Tablet screen, Desktop screen. Gallery layout should adapt using responsive grid.

### 13.4 Reliability

* Uploaded files and database records must remain synchronized.
* If photo upload fails, database insert should not happen.
* If database insert fails, uploaded file should be removed.
* Delete actions must not affect unrelated records.
* Soft-deleted records must not appear in normal member views.
* Registration must not create partial or incomplete users.

### 13.5 Maintainability

Use separate PHP files for configuration, authentication, database connection, and modules.

**Recommended folder structure:**

```text
friendship-group-web/
│
├── config/
│   ├── database.php
│   └── app.php
│
├── auth/
│   ├── login.php
│   ├── register.php
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
│   └── agenda_delete.php
│
├── admin/
│   └── index.php
│
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── app.js
│
└── index.php

```

---

## 14. Validation Requirements

### 14.1 Registration Validation

Name is required. Username is required. Email is required. Email format must be valid. Password is required. Confirm password is required. Password and confirm password must match. Verification passcode is required. Verification passcode must be valid. Username must be unique. Email must be unique.

### 14.2 Login Validation

Username/email is required. Password is required. Invalid account shows a clear error.

### 14.3 Photo Upload Validation

Photo file is required. Caption is required. File type must be valid. File size must be within limit.

### 14.4 Vote Validation

Vote title is required. At least two options are required. Empty options are rejected. Deadline is required. Deadline must be a valid date.

### 14.5 Agenda Validation

Destination is required. Proposed date is required. Estimated budget is required. Description is required. Budget must be numeric.

### 14.6 Delete Validation

Delete action must require logged-in admin session. Delete action must use POST method. Delete target ID must be valid. Delete target record must exist. Non-admin users must receive access denied.

---

## 15. Acceptance Criteria

### 15.1 Registration

* Given a user enters valid name, username, email, password, confirm password, and member verification passcode, when they submit the registration form, then the system creates a new account with role member.
* Given a user enters valid name, username, email, password, confirm password, and admin verification passcode, when they submit the registration form, then the system creates a new account with role admin.
* Given a user enters an invalid verification passcode, when they submit the registration form, then the system rejects the registration and no account is created.
* Given a user enters a username that already exists, when they submit the registration form, then the system rejects the registration and shows a duplicate username error.
* Given a user enters an email that already exists, when they submit the registration form, then the system rejects the registration and shows a duplicate email error.
* Given a user enters a password and confirm password that do not match, when they submit the registration form, then the system rejects the registration.

### 15.2 Authentication

* Given a registered user, when they enter valid username/email and password, then they are redirected to dashboard.
* Given an invalid login, when the user submits the form, then the system shows an error message.
* Given a logged-out user, when they access dashboard directly, then the system redirects them to login page.

### 15.3 Photo Gallery

* Given a logged-in user, when they upload a valid image with caption, then the image appears in the gallery.
* Given an invalid file type, when the user uploads it, then the system rejects the upload.
* Given an admin, when they remove a photo, then the photo is removed from the gallery display.
* Given a regular member, when they view the gallery, then they do not see a remove photo button.
* Given a regular member, when they access the remove photo endpoint directly, then the system rejects the request with access denied.

### 15.4 Voting

* Given a user, when they create a vote with title, deadline, and at least two options, then the vote is created successfully.
* Given a user who has not voted, when they submit a vote, then the response is saved.
* Given a user who already voted, when they try to vote again, then the system prevents duplicate voting.
* Given a vote deadline has passed, when a user tries to vote, then the system rejects the vote.
* Given an admin, when they remove a vote, then the vote is removed from the vote list.
* Given a regular member, when they view votes, then they do not see a remove vote button.
* Given a regular member, when they access the remove vote endpoint directly, then the system rejects the request with access denied.

### 15.5 Trip Agenda

* Given a user, when they submit a valid trip agenda, then the agenda appears in the agenda list.
* Given missing required fields, when the user submits the form, then the system displays validation errors.
* Given an admin, when they remove an agenda, then it no longer appears in the agenda list.
* Given a regular member, when they view agendas, then they do not see a remove agenda button.
* Given a regular member, when they access the remove agenda endpoint directly, then the system rejects the request with access denied.

---

## 16. MVP Feature List

The first version should include:

* Register with verification passcode
* Admin/member role assignment based on passcode
* Login and logout
* Session protection
* Dashboard
* Photo gallery
* Photo upload
* Vote creation
* Vote submission
* Vote result
* Trip agenda submission
* Trip agenda list
* Admin-only remove photo
* Admin-only remove vote
* Admin-only remove trip agenda
* Responsive UI

---

## 17. Future Improvements

Possible future features:

* Comment system for each photo
* Like or reaction system
* Photo album grouping by trip
* Trip agenda voting
* Calendar view for trip agenda
* Notification system
* Password reset through email
* Profile customization
* Dark mode
* Image compression
* Drag-and-drop photo upload
* Export agenda to PDF
* Activity log for admin actions
* Restore soft-deleted photos, votes, and agendas
* Admin interface for changing verification passcodes
* Passcode expiration date
* Passcode usage limit

---

## 18. Development Milestones

**Milestone 1: Database, Registration, and Authentication**

* Create database schema. Create users table. Create registration page. Add admin verification passcode. Add member verification passcode. Assign user role based on verification passcode. Build login page. Build logout function. Add session protection.

**Milestone 2: Dashboard**

* Build dashboard layout. Show user welcome message. Show recent photos. Show active votes. Show recent agendas.

**Milestone 3: Photo Gallery**

* Build gallery page. Build upload form. Implement image validation. Save image and database record. Display uploaded photos. Add admin-only remove photo feature.

**Milestone 4: Voting System**

* Build vote list page. Build create vote page. Save vote and options. Build vote detail page. Allow vote submission. Prevent duplicate voting. Display vote results. Add admin-only remove vote feature.

**Milestone 5: Trip Agenda System**

* Build agenda form. Save agenda to database. Build agenda list page. Build agenda detail view. Add admin-only remove agenda feature.

**Milestone 6: Admin and Security**

* Add admin-only management page. Add delete handlers. Add backend role validation. Review registration validation. Review verification passcode handling. Review input validation. Review SQL injection protection. Review upload security. Review delete authorization security.

**Milestone 7: Responsive UI and Testing**

* Improve mobile layout. Test all user flows. Test invalid input. Test duplicate vote prevention. Test file upload errors. Test registration with admin passcode. Test registration with member passcode. Test registration with invalid passcode. Test admin delete actions. Test direct endpoint access by regular members. Fix bugs.

---

## 19. Suggested PHP File Responsibilities

* **config/database.php**: Creates PDO database connection.
* **config/app.php**: Stores application configuration, including registration passcodes for local development. For production, this file should load passcodes from environment variables.
* **auth/register.php**: Handles registration form, verification passcode validation, role assignment, duplicate username/email checking, and password hashing.
* **auth/login.php**: Handles login form and credential verification.
* **auth/logout.php**: Destroys session and redirects to login page.
* **auth/auth_check.php**: Checks whether user is logged in. Redirects guest users to login page.
* **pages/dashboard.php**: Displays member/admin dashboard.
* **pages/gallery.php**: Displays all non-deleted photos.
* **pages/photo_create.php**: Handles photo upload.
* **pages/photo_delete.php**: Handles admin-only photo removal.
* **pages/votes.php**: Displays non-deleted vote list.
* **pages/vote_create.php**: Handles vote creation.
* **pages/vote_detail.php**: Displays vote form and vote result.
* **pages/vote_delete.php**: Handles admin-only vote removal.
* **pages/agendas.php**: Displays non-deleted trip agenda list.
* **pages/agenda_create.php**: Handles trip agenda submission.
* **pages/agenda_delete.php**: Handles admin-only agenda removal.
* **admin/index.php**: Handles admin management page.

---

## 20. Recommended Database Indexes

```sql
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_deleted_at ON users(deleted_at);

CREATE INDEX idx_photos_user_id ON photos(user_id);
CREATE INDEX idx_photos_created_at ON photos(created_at);
CREATE INDEX idx_photos_deleted_at ON photos(deleted_at);

CREATE INDEX idx_votes_created_by ON votes(created_by);
CREATE INDEX idx_votes_status ON votes(status);
CREATE INDEX idx_votes_deadline ON votes(deadline);
CREATE INDEX idx_votes_deleted_at ON votes(deleted_at);

CREATE INDEX idx_vote_options_vote_id ON vote_options(vote_id);

CREATE INDEX idx_vote_responses_vote_id ON vote_responses(vote_id);
CREATE INDEX idx_vote_responses_user_id ON vote_responses(user_id);

CREATE INDEX idx_trip_agendas_user_id ON trip_agendas(user_id);
CREATE INDEX idx_trip_agendas_proposed_date ON trip_agendas(proposed_date);
CREATE INDEX idx_trip_agendas_deleted_at ON trip_agendas(deleted_at);

```

---

## 21. Example Configuration for Registration Passcodes

For local development, passcodes may be placed in `config/app.php`.

```php
<?php

define('APP_NAME', 'Friendship Group Web');

define('ADMIN_REGISTER_PASSCODE', 'admin-secret-code');
define('MEMBER_REGISTER_PASSCODE', 'member-secret-code');

```

For production, use environment variables.

```php
<?php

define('APP_NAME', 'Friendship Group Web');

$adminPasscode = getenv('ADMIN_REGISTER_PASSCODE');
$memberPasscode = getenv('MEMBER_REGISTER_PASSCODE');

```

**Important:**
Do not expose these passcodes in public files. Do not commit real production passcodes to GitHub. Do not store raw submitted passcodes in the users table.

---

## 22. Final MVP Summary

The Friendship Group Web Application is a private PHP and SQL-based platform for a friendship group. Users can register only with a valid verification passcode. The system uses two passcodes: one for admin registration and one for member registration. The selected role is assigned automatically based on the submitted passcode.

After registration, users can log in, access a dashboard, view and upload photos, create and participate in voting forms, and submit next trip agenda proposals.

The admin has exclusive permission to remove photos, votes, and trip agendas. Regular members can create and submit content, but they cannot remove any content, including their own.

The MVP should focus on secure registration, secure authentication, clear dashboard experience, reliable photo upload, duplicate-safe voting, structured trip agenda submission, and strict admin-only content removal. The system should be simple, private, responsive, and easy to maintain using PHP, MySQL, and standard web technologies.