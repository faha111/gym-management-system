# PulseFit Gym Management System 🏋️‍♂️⚡
> **CST 226-2 Web Application Development Group Project**  
> Technology Stack: PHP (OOP), MySQL, HTML5, CSS3 (Glassmorphism), JavaScript

---

## 📁 Project Structure

```text
gym_management/
├── index.php                    # Public landing page (home, plans preview, trainers preview)
├── dashboard.php                # Admin Dashboard: Overview & Statistics
├── README.md
│
├── auth/                        # Login, registration & account activation
│   ├── login.php                #   Shared login (Admin / Trainer / Member)
│   ├── logout.php
│   ├── register_member.php      #   Public "Join Now" member sign-up
│   ├── register_trainer.php     #   Public trainer sign-up
│   ├── verify.php               #   Email verification code entry
│   └── create_login.php         #   Admin: issue login credentials to an approved member/trainer
│
├── members/                     # Admin: member management (CRUD)
│   ├── members.php              #   Members list, search & approve/delete
│   ├── member_add.php           #   Register member form (with photo upload)
│   └── member_edit.php          #   Update member details form
│
├── member-portal/               # Self-service portal for logged-in Members
│   ├── member_portal.php
│   ├── member_profile.php
│   └── member_payments.php
│
├── trainers/                    # Admin: trainer management (CRUD)
│   └── trainers.php
│
├── trainer-portal/              # Self-service portal for logged-in Trainers
│   ├── trainer_portal.php
│   ├── trainer_profile.php
│   └── trainer_clients.php
│
├── plans/                       # Admin: membership package management (CRUD)
│   └── plans.php
│
├── attendance/                  # Admin: daily check-in/check-out tracker (CRUD)
│   └── attendance.php
│
├── payments/                    # Admin: payments & billing (CRUD)
│   └── payments.php
│
├── scripts/                     # Dev-only utilities (not part of the live app flow)
│   └── seed_attendance.php      #   Generates sample attendance history for demos
│
├── config/
│   ├── Database.php             # OOP Database PDO Singleton Connection + BASE_URL helper
│   └── mail_config.php          # SMTP settings for verification emails
├── classes/
│   ├── Member.php               # OOP Member Model Class (CRUD & validation)
│   ├── Trainer.php              # OOP Trainer Model Class (CRUD)
│   ├── Plan.php                 # OOP Membership Plan Model Class (CRUD)
│   ├── Payment.php              # OOP Payment/Billing Model Class (CRUD)
│   ├── Attendance.php           # OOP Attendance Tracking Class (CRUD)
│   └── Auth.php                 # OOP Authentication / session / role class
├── includes/
│   ├── header.php               # Global Admin navigation header
│   ├── footer.php               # Global Admin page footer
│   ├── portal_header.php        # Shared header for Member/Trainer self-service portals
│   ├── portal_footer.php
│   ├── auth_layout.php          # Lightweight layout for login/register/verify pages
│   └── alerts.php               # Flash alert notification handler
├── libs/PHPMailer/              # Third-party library used for verification emails
├── database/
│   └── database.sql             # Full MySQL Database Schema & Seed Data Script
└── assets/
    ├── css/style.css            # Responsive Glassmorphic Dark UI Theme
    ├── js/main.js               # Interactive JavaScript, live filters & form validation
    ├── img/                     # Logo, hero poster
    ├── video/                   # Hero background video
    └── uploads/                 # Member & Trainer profile photo storage
```

> All internal links, form actions, redirects and asset paths are generated with a
> `BASE_URL` constant (defined automatically in `config/Database.php`), so every page
> works correctly at any folder depth — no matter what folder name the project is
> deployed under.

---

## 🚀 Beginner's Step-by-Step Guide for Localhost (XAMPP)

Follow these easy baby steps to run this project on your computer:

> ⚠️ **Important:** After extracting the ZIP, the folder you copy into `htdocs` **must
> contain `index.php`, `config/`, `classes/`, etc. directly inside it** — not nested
> one level deeper (e.g. avoid `htdocs\gym_management\gym_management\index.php`).
> If your ZIP tool extracted an extra outer folder, move the inner folder up one level
> before copying it into `htdocs`.

### Step 1: Copy Project Files to XAMPP `htdocs`
1. Open XAMPP directory on your PC (Usually located at `C:\xampp`).
2. Open the `htdocs` folder (`C:\xampp\htdocs`).
3. Copy the entire project folder into `C:\xampp\htdocs\gym_management` (you can rename
   this folder to anything you like — e.g. `pulsefit`, `CST226_Project` — the app works
   correctly under any folder name, see the `BASE_URL` note above).

### Step 2: Start Apache and MySQL in XAMPP
1. Open **XAMPP Control Panel** on Windows.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.
*(Both modules should turn green).*

### Step 3: Create Database & Import Schema (`database.sql`)
1. Open your web browser and go to: `http://localhost/phpmyadmin`
2. Click on **Databases** at the top menu.
3. Type database name: `gym_db` and click **Create**.
4. Select `gym_db` from the left menu.
5. Click on the **Import** tab at the top.
6. Click **Choose File** and select `database.sql` from your `C:\xampp\htdocs\gym_management\database\database.sql`.
7. Scroll down to the bottom and click **Import**.
*(You will see a success message: "Import has been successfully finished")*.

### Step 4: Open the Web Application
1. Open your browser and navigate to the folder name you actually used in Step 1, e.g.:
   👉 **`http://localhost/gym_management`** (replace `gym_management` with your folder
   name if you renamed it).
2. You will see the **PulseFit Gym Management System** landing page.
3. To log in as Admin, go to `http://localhost/gym_management/auth/login.php` and use
   the default admin account below (it's created automatically the first time the app
   runs — no manual database edit needed):

   | Field | Value |
   |---|---|
   | Email | `admin@pulsefitgym.com` |
   | Password | `Admin@123` |

---

## ✅ Troubleshooting

| Symptom | Cause | Fix |
|---|---|---|
| Blank white page or "500 Internal Server Error" | Project folder is nested one level too deep, or PHP error reporting is hiding the message | Confirm `index.php` sits directly inside the `htdocs\<folder>` folder, not inside a subfolder of the same name |
| "SQLSTATE[HY000] [1049] Unknown database 'gym_db'" | Database wasn't created/imported yet | Repeat Step 3 — create a database named exactly `gym_db` and import `database/database.sql` into it |
| Page loads but has no styling (looks unstyled/plain HTML) | Apache isn't serving the `assets/` folder correctly, usually because of the nested-folder issue above | Same fix as the first row — flatten the folder structure |
| Registration works but no verification email arrives | This is expected out of the box — `config/mail_config.php` has `MAIL_ENABLED` set to `false` by default | The verification code is shown directly on the `verify.php` screen instead ("Demo Mode"), so you can still test registration without setting up email. To send real emails, follow the Gmail App Password instructions inside `config/mail_config.php` |
| "Connection refused" / can't reach `localhost` at all | Apache and/or MySQL aren't running | Open XAMPP Control Panel and make sure both **Apache** and **MySQL** show green/"Running" |

---

## 🛠️ GitHub Repository Setup Guide (When You're Ready to Push)

When you are ready to upload this code to GitHub for submission, follow these terminal steps:

1. Open Command Prompt inside `C:\xampp\htdocs\gym_management`:
   ```bash
   cd C:\xampp\htdocs\gym_management
   ```
2. Initialize Git:
   ```bash
   git init
   ```
3. Stage all files:
   ```bash
   git add .
   ```
4. Create initial commit:
   ```bash
   git commit -m "Initial commit of Gym Management System"
   ```
5. Connect to your GitHub repository URL:
   ```bash
   git remote add origin https://github.com/YOUR_USERNAME/gym-management-system.git
   ```
6. Push code to GitHub:
   ```bash
   git branch -M main
   git push -u origin main
   ```
