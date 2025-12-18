# How to Import Database into XAMPP

This guide explains how to import the `sql/database.sql` file into your local XAMPP MySQL database.

## Prerequisites

- **XAMPP** is installed and running.
- **Apache** and **MySQL** modules are started in the XAMPP Control Panel.

---

## Method 1: Using phpMyAdmin (Recommended)

1. **Open phpMyAdmin**:
   - Go to [http://localhost/phpmyadmin](http://localhost/phpmyadmin) in your browser.

2. **Select or Create Database** (Optional but good practice):
   - The script `database.sql` already contains:

     ```sql
     CREATE DATABASE IF NOT EXISTS srmis;
     USE srmis;
     ```

   - So you can simply skip to step 3. However, if you want to be sure, you can click **New** in the left sidebar, type `srmis`, and click **Create**.

3. **Import the File**:
   - Click the **Import** tab in the top menu.
   - Click **Choose File** (or "Browse...").
   - Navigate to your project folder:
     `Desktop > PRJS > srmis > sql`
   - Select `database.sql`.
   - Scroll down and click **Import** (or "Go").

4. **Verify**:
   - You should see a green success message: *"Import has been successfully finished..."*.
   - Click on the `srmis` database in the left sidebar to see your tables (`admins`, `students`, `subjects`, etc.).

---

## Method 2: Using Command Line (Advanced)

If you prefer the terminal or have issues with file size in phpMyAdmin:

1. **Open Terminal** (Command Prompt or PowerShell).
2. **Navigate to the SQL folder**:

   ```bash
   cd "C:\Users\Abdiweli`s PC\OneDrive - amu.edu.et\Desktop\PRJS\srmis\sql"
   ```

3. **Run the Import Command**:
   Assuming your mysql user is `root` with no password (default XAMPP):

   ```bash
   mysql -u root < database.sql
   ```

   If you have a password for root:

   ```bash
   mysql -u root -p < database.sql
   ```

---

## Troubleshooting

- **Error: "Table already exists"**:
  - The script starts with `DROP TABLE IF EXISTS`, so it should handle this automatically. If not, you can manually drop the old tables or the whole database before importing.
- **Error: "Access denied"**:
  - Check your username and password in `config/database.php` and make sure they match your XAMPP settings (default is usually user: `root`, password: empty).
