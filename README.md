# 🎓🎓 Student Result Management System (SRMIS)

<p align="center">
  <img src="https://dummyimage.com/1200x400/0f0f0f/ffffff&text=🎓🎓+SRMIS+Student+Result+Management" alt="SRMIS - Dark Banner" />
</p>

<p align="center">
  <strong>A modern, web-based platform built with PHP and MySQL for efficient academic record management.</strong>
</p>

<p align="center">
  Role-Based Dashboards • Result Entry • Dynamic Reporting • Responsive UI
</p>

<p align="center">
  <a href="https://github.com/developer-abdiweli-jama/Student-Result-Management-System"><strong>🚀 Source Code</strong></a> •
  <a href="#-features">Features</a> •
  <a href="#-tech-stack">Tech Stack</a> •
  <a href="#-installation--setup">Install</a>
</p>

---

## 🏷️ Badges

<p align="center">
  <img src="https://img.shields.io/github/stars/developer-abdiweli-jama/Student-Result-Management-System?style=for-the-badge&color=green&labelColor=0d0d0d" />
  <img src="https://img.shields.io/github/forks/developer-abdiweli-jama/Student-Result-Management-System?style=for-the-badge&color=green&labelColor=0d0d0d" />
  <img src="https://img.shields.io/github/issues/developer-abdiweli-jama/Student-Result-Management-System?style=for-the-badge&color=orange&labelColor=0d0d0d" />
  <img src="https://img.shields.io/github/license/developer-abdiweli-jama/Student-Result-Management-System?style=for-the-badge&color=green&labelColor=0d0d0d" />
  <img src="https://img.shields.io/badge/PRs-Welcome-00ff88?style=for-the-badge&labelColor=0d0d0d" />
</p>

### 🧰 Tech Versions

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.1-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/Composer-2.7-885630?style=for-the-badge&logo=composer&logoColor=white" />
  <img src="https://img.shields.io/badge/Font_Awesome-6-528DD7?style=for-the-badge&logo=fontawesome&logoColor=white" />
</p>

---

## 📸 Preview

<p align="center">
  <img src="assets/images/Home Page.png" width="100%" alt="Home Page Placeholder" />
</p>

<p align="center">
  <img src="assets/images/Admin Dashboard.png" width="100%" alt="Admin Dashboard Placeholder" />
</p>

<p align="center">
  <img src="assets/images/Student Dashboard.png" width="100%" alt="Student Dashboard Placeholder" />
</p>

> ⚡ Real screenshots and GIFs will be added as development continues.

---

## 🎯 Features

### 👨‍🎓 Student Features

* View academic performance and GPA
* Check year-wise pass/retain status
* Select academic streams (for eligible levels)
* Access results in real-time

### 👩‍🏫 Teacher Features

* Manage results entry (Single or Bulk)
* Request additional subjects
* View student performance reports
* Track class-level progress

### 🛡️ Admin Features

* Comprehensive user management (Students, Teachers, Admins)
* Subject and class level configuration
* System settings and branding (Logo uploads, etc.)
* Database migration tools

### 🔒 Security & Design

* Secure authentication and session management
* Clean, modern UI with responsive design
* GPA-based progression logic
* Dynamic subject filtering

---

## 🛠 Tech Stack

| Layer      | Technologies                          |
|------------|---------------------------------------|
| Backend    | PHP 8.1+, MySQL                       |
| Frontend   | HTML5, CSS3, JavaScript               |
| Libraries  | Dompdf (PDF export), Font Awesome     |
| Database   | MySQL with Migrations                 |
| Security   | Password Hashing, Session Protection  |
| Tools      | Git, Composer                         |

---

## 📁 Project Structure

```bash
srmis/
├── admin/          # Admin portal pages
├── teacher/        # Teacher portal pages
├── student/        # Student portal pages
├── api/            # Internal API endpoints
├── includes/       # Shared UI components
├── helpers/        # Utility functions
├── config/         # System & Database configuration
├── sql/            # Database schema and migrations
├── assets/         # CSS, JS, and Images
├── vendor/         # Composer dependencies
└── index.php       # Entry point
```

---

## ⚙️ Installation & Setup

```bash
# Clone project
git clone https://github.com/developer-abdiweli-jama/Student-Result-Management-System.git
cd Student-Result-Management-System
```

### Backend Setup

```bash
composer install
```

### Database Setup

1. Create a database named `srmis`.
2. Import the initial schema:

```bash
mysql -u root -p srmis < sql/database.sql
```

3. (Optional) Import sample data:

```bash
mysql -u root -p srmis < sql/sample_students.sql
mysql -u root -p srmis < sql/sample_results.sql
```

### Configuration

Update `config/database.php` with your local database credentials if they differ from the defaults.

---

## 🚀 Running the App

### Using Built-in Server

```bash
php -S localhost:8000
```

Access at `http://localhost:8000`.

### Default Credentials

- **Admin**: `admin` / `password123` (Check `sql/database.sql` for initial users)

---

## 🤝 Contributing

1. Fork the repo
2. Create a branch (`git checkout -b feat/add-feature`)
3. Commit (`git commit -m 'add: new feature'`)
4. Push and create a PR

---

## 👤 Author

**Abdiweli Jama Abdullahi**  
Full-Stack Developer • HIRGAL NEXUS  

* GitHub: [https://github.com/developer-abdiweli-jama](https://github.com/developer-abdiweli-jama)  
* LinkedIn: [https://www.linkedin.com/in/abdiweli-jamac-60ab44207](https://www.linkedin.com/in/abdiweli-jamac-60ab44207)  
* Email: [abdiwelijama@gmail.com](mailto:abdiwelijama@gmail.com)

<p align="center"><strong>⭐ Star this project if you like it!</strong></p>
