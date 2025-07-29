# Student Management System

A web-based student management system developed using **PHP**, **MySQL**, **HTML/CSS**, and **JavaScript**. The system supports role-based access for Admins, Teachers, and Students to manage assignments, attendance, grades, and submissions.

## Features

- Role-based authentication (Admin, Teacher, Student)
- Assignment creation and student submissions
- Grading and feedback management
- Attendance tracking
- Class, subject, and user management
- Secure database interactions using PDO

## Technologies Used

- PHP with PDO
- MySQL
- HTML5 / CSS3
- JavaScript
- XAMPP (Apache + MySQL)

## Setup Instructions

1. Clone the repository:
   ```bash
git clone https://github.com/Reedze6069/student-system2025.git


| Role    | Email                                               | Password   |
| ------- | --------------------------------------------------- | ---------- |
| Admin   | admin@test.com | admin123   |
| Teacher | Teacher@test.com| teacher123 |
| Student | riedleazzopardi@yahoo.com | riedle123 |


2. Import the database:
   - Open phpMyAdmin
   - Create a database named `student_system`
   - Import the `student_system2025.sql` file found in the project folder

3. Open `/config/db.php` and ensure your database credentials match  local setup (usually:
   host = `localhost`, user = `root`, password = ``, db = `student_system`)
   
4. Start Apache and MySQL in XAMPP, then visit:

http://localhost/student-system/public/login.php
