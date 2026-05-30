# Friendship Group Web Application (Aliansi Antah-Berantah)

Welcome to the **Friendship Group Web Application**, a private web platform designed specifically for friendship groups. It centralizes shared memories, group decision-making, and vacation planning in one beautifully designed, secure environment.

---

## 🎯 What it does

This application serves as a private digital hub for a close-knit group of friends. Because privacy is paramount, users cannot register freely—they must possess a valid secret passcode to join. Once inside, members are greeted by a modern, interactive dashboard where they can upload photos to a shared gallery, participate in group votes for collective decisions, and pitch detailed itineraries for the next group trip. Admins maintain full control over the platform's content to keep the environment organized and safe.

---

## ✨ Highlights

* **Passcode-Protected Registration**: Two distinct registration passcodes (Admin and Member) ensure that only authorized friends can join and roles are assigned automatically.
* **Modern "Glassmorphism" UI**: Built with a custom, mobile-first CSS architecture that leverages CSS variables, sleek translucent cards, and smooth micro-animations.
* **Strict Role-Based Security**: Complete separation of privileges. Standard members can interact with the app, but destructive actions (like removing content) are strictly guarded and accessible only by admins via secure POST endpoints.
* **No Bloat**: A clean, vanilla PHP architecture utilizing PDO for database interactions, eliminating the need for heavy frameworks while maintaining high security standards.

---

## 🧩 Module Market

The application is structured into several powerful, standalone modules:

* **📸 Photo Gallery Module**: A dedicated space for shared memories. Features responsive masonry layouts, drag-and-drop file uploads, strict image validation (JPG/PNG/WEBP, <5MB), and detailed photo metadata (captions, locations, dates).
* **🗳️ Voting Module**: The ultimate tool for settling group debates. Create multi-option polls with strict deadlines, prevent duplicate votes, and view real-time animated bar charts of the results.
* **🗺️ Trip Agenda Module**: Designed to turn ideas into reality. Submit comprehensive trip proposals including estimated budgets, destinations, meeting points, transportation & accommodation plans, and key activities.

---

## 📋 Feature catalog

### Authentication & Security
- Username/Email & Password login
- Secure password hashing (`password_hash`)
- Session fixation protection & ID regeneration
- `.htaccess` folder protection (prevents direct config/upload execution)

### Member Capabilities
- View the unified Dashboard feed
- Upload photos with rich descriptions
- Create new group voting forms
- Cast votes on active polls
- Pitch detailed Trip Agendas

### Admin Capabilities
- Access the dedicated Admin Panel
- View platform-wide statistics
- View list of all registered members
- Soft-delete any Photo
- Soft-delete any Vote
- Soft-delete any Trip Agenda

---

## 🛠 Tech stack

* **Frontend**: HTML5, Vanilla JavaScript, CSS3 (Custom Properties & Animations)
* **Backend**: PHP (Vanilla)
* **Database**: MySQL / MariaDB (via PDO)
* **Server**: Apache / Nginx (XAMPP/Laragon for local development)
* **Design Philosophy**: Mobile-first, responsive, Glassmorphism aesthetic

---

## 🚀 Build

To get the application running locally, follow these steps:

### Prerequisites
* A local server environment like XAMPP, Laragon, or a LAMP/LEMP stack.
* PHP 7.4+ or PHP 8.x
* MySQL or MariaDB

### Installation

1. **Clone the Repository**
   Place the project files inside your server's root directory (e.g., `htdocs/` or `www/`).

2. **Set up the Database**
   * Open your MySQL console or a tool like phpMyAdmin.
   * Import the database schema by running the `database/schema.sql` script. This will create the `friendship_group_db` database and all necessary tables.

3. **Configure the Application**
   * By default, the app expects a local MySQL user `root` with no password. If your setup is different, modify the credentials inside `config/database.php`.
   * Ensure the PDO MySQL extension (`extension=pdo_mysql`) is enabled in your `php.ini`.

4. **Launch the Application**
   * Start your local server.
   * Alternatively, use PHP's built-in web server by navigating to the project root in your terminal and running:
     ```bash
     php -S localhost:8000
     ```
   * Open your browser and navigate to `http://localhost:8000`.

5. **Register Accounts**
   * Go to the **Register** page.
   * Use the default passcodes defined in `config/app.php`:
     * For Admin access: `admin-secret-code`
     * For Member access: `member-secret-code`

---

## 🤝 Contributing

Since this is a private web application tailored for a specific friendship group, contributions are typically internal. However, if you are an invited developer:

1. **Review the PRD**: Check the `PRD.md` file for deep technical requirements and database structures.
2. **Coding Standards**: Stick to Vanilla PHP and Vanilla CSS. Do not introduce heavy frontend frameworks (like React or Tailwind) without consensus.
3. **Security First**: Always validate user input, use PDO prepared statements for database queries, and ensure new endpoints utilize the `auth_check.php` session guard.
4. **Pull Requests**: Submit your features on a separate branch, ensuring all `.htaccess` security rules remain intact.
