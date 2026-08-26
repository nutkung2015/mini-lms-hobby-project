# 🎓 Mini LMS

A modern Learning Management System built with **Laravel 12**, **Inertia.js**, and **Vue 3**. Supports role-based dashboards, video lesson player with real-time progress tracking, course enrollments, reviews, and bookmarks.

---

## ✨ Features

- **Role-Based Access Control** — Student / Instructor / Admin
- **Course Management** — Create, edit, publish courses with cover images
- **Video Lesson Player** — Track watch progress in real-time (every ~10s via AJAX)
- **Enrollment System** — Enroll / cancel with status tracking
- **Progress Tracking** — Per-lesson `watched_seconds`, `watched_percentage`, `is_completed`
- **Course Reviews** — 5-star ratings with comments
- **Bookmarks** — Toggle bookmark/save courses
- **Email Verification** — Built-in Laravel verification flow
- **Password Reset** — Secure token-based reset flow
- **Instructor Dashboard** — Manage own courses and lessons
- **Admin Dashboard** — Platform-wide overview

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12 (PHP 8.2+) |
| Frontend | Vue 3 + Inertia.js v2 |
| Styling | Tailwind CSS |
| Auth | Laravel Breeze + Sanctum |
| Database | MySQL / PostgreSQL / SQLite |
| Asset Build | Vite |
| Routing (JS) | Ziggy |

---

## 🚀 Local Setup

### Requirements
- PHP 8.2+
- Composer
- Node.js 18+ & npm
- MySQL or PostgreSQL (or SQLite for quick start)

### Installation

```bash
# 1. Clone the repo
git clone https://github.com/nutkung2015/mini-lms-hobby-project.git
cd mini-lms-hobby-project

# 2. Install PHP dependencies
composer install

# 3. Install JS dependencies
npm install

# 4. Setup environment
cp .env.example .env
php artisan key:generate

# 5. Configure your database in .env, then run:
php artisan migrate --seed

# 6. Build frontend assets
npm run build

# 7. Start the dev server
composer run dev
```

The app will be available at `http://localhost:8000`.

---

## ⚙️ Environment Configuration

Copy `.env.example` to `.env` and fill in these key values:

```env
APP_NAME="Mini LMS"
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=mini_lms
DB_USERNAME=your-db-username
DB_PASSWORD=your-db-password
```

> **Important:** Never commit your `.env` file. It is already listed in `.gitignore`.

---

## 🗂️ Project Structure

```
app/
├── Http/
│   ├── Controllers/      # Route handlers (Course, Lesson, Enrollment, ...)
│   ├── Requests/         # Form Request validation classes
│   └── Middleware/       # Custom middleware (EnsureUserHasRole, ...)
├── Models/               # Eloquent models
├── Policies/             # Authorization policies
└── Services/             # Business logic (CourseService, LessonProgressService)

resources/
└── js/
    └── Pages/            # Vue 3 Inertia page components
        ├── Courses/
        ├── Lessons/
        ├── Dashboard/
        └── Auth/

routes/
├── web.php               # Main application routes
└── auth.php              # Authentication routes
```

---

## 🌐 Free Deployment Options

| Service | Role | Notes |
|---|---|---|
| [Koyeb](https://koyeb.com) | App Server | Free Nano instance, no cold start |
| [Serv00.com](https://serv00.com) | App + MySQL | Shared hosting, PHP 8.2+, SSH access |
| [Neon.tech](https://neon.tech) | PostgreSQL | Always-free 0.5 GB serverless Postgres |
| [TiDB Cloud](https://tidbcloud.com) | MySQL | Always-free 5 GB MySQL-compatible |
| [Cloudflare R2](https://cloudflare.com/r2) | File Storage | Free 10 GB/mo, no egress fees |

---

## 📄 License

This is a personal hobby project. Feel free to use it as a reference or learning resource.
