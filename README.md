# L'École — School Management Platform (Vanilla PHP MVC)

L'École is a high-performance, responsive institutional school management portal built with pure Vanilla PHP 8.3, MySQL 8.0, and modern CSS/vanilla JS.

---

## Database Setup & Initialization

> **Important**: Database schema files have strict relational foreign key dependencies and must be executed in a specific order.

For the complete, step-by-step database setup instructions and Docker / native commands, see:
👉 **[database/README.md](database/README.md)**

### Quick Setup Summary (Fresh Install via Docker):

```bash
# From the MVC directory:
docker exec -i mvc-db-1 mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS l_ecole CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" && \
docker exec -i mvc-db-1 mysql -u root -proot l_ecole < database/schema.sql && \
docker exec -i mvc-db-1 mysql -u root -proot l_ecole < database/academic_schema.sql && \
docker exec -i mvc-db-1 mysql -u root -proot l_ecole < database/calendar_schema.sql && \
docker exec -i mvc-db-1 mysql -u root -proot l_ecole < database/seeds.sql && \
docker exec -i mvc-php-1 php /var/www/html/database/seed_faculty.php && \
docker exec -i mvc-php-1 php /var/www/html/database/seed_academic.php
```

---

## Portal Access & Default Credentials

All seed accounts use the default password: **`Password@123`**

| Role | Portal URL | Seed Account Identifier |
| :--- | :--- | :--- |
| **Administrator** | `http://localhost:8040/auth/admin` | `david_silva_admin@lecole.edu` |
| **Academic Management** | `http://localhost:8040/auth/management` | `sarah_vance@lecole.edu` |
| **Faculty / Teacher** | `http://localhost:8040/auth/teacher` | `alex_benjamin@lecole.edu` |
| **Student** | `http://localhost:8040/auth/student` | `STU-2026-0001` (or `2026/0001`) |
| **Parent / Guardian** | `http://localhost:8040/auth/parent` | `elena.peiris@gmail.com` |

---

## Administration CLI (Break-Glass Console)

Use the built-in system console for emergency administrative tasks:

```bash
# Inside docker container or locally:
php console.php help
php console.php list-locked
php console.php unlock <email-or-index>
php console.php reset-password <identifier> <new-password>
php console.php logs 20
```
