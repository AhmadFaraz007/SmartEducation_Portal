SmartEdu Portal

SmartEdu Portal is a centralized education platform designed to connect students, faculty, and administrators. It provides role-based access to each user, offering functionalities like course exploration, user management, course recommendations, and more.
Table of Contents

    Project Overview

    Features

    Technologies Used

    Installation

    Usage

    Contributing

    License

Project Overview

The SmartEdu Portal aims to streamline the educational experience for students, faculty, and admins by offering centralized access to various tools and functionalities. Students can complete their profiles, explore recommended courses, and enroll. Faculty can manage courses and review student performance, while admins oversee all activities and manage user roles.
Features

    Admin Dashboard: Manage users, monitor student and faculty performance, and control access.

    Faculty Dashboard: Upload course materials, review student profiles, and manage course content.

    Student Dashboard: Complete your profile, explore recommended courses, and enroll in courses.

    Responsive Design: Fully responsive and mobile-friendly interface.

    User Authentication: Login and registration pages for students, faculty, and admins.

    Role-Based Access Control: Admin, faculty, and student access levels with customized functionalities.

Technologies Used

    Frontend:

        HTML5

        CSS3

        JavaScript

        Responsive Design (Flexbox, Grid)

    Backend:

        PHP

        MySQL (for database management)

    Tools:

        XAMPP (for local development server)

        Apache & MySQL for server and database management

Installation

To get a copy of this project running locally, follow these steps:
1. Clone the Repository

git clone https://github.com/your-username/smartedu-portal.git
cd smartedu-portal

2. Install XAMPP

Download and install XAMPP, which provides an easy-to-use package for running Apache and MySQL locally.
3. Start Apache and MySQL

Launch XAMPP and start the Apache and MySQL services.
4. Configure Database

    Open your MySQL database (phpMyAdmin at http://localhost/phpmyadmin/).

    Create a new database for the project, e.g., smartedu_db.

    Import the SQL schema (found in the sql folder) to set up the database tables.

5. Set Up Environment

    Place the project files in the htdocs folder of XAMPP.

    Update any configuration files (e.g., database connection settings in PHP) to match your local environment.

Usage

    Start XAMPP and navigate to http://localhost/smartedu-portal/ in your web browser.

    Log in as an admin, faculty, or student using the authentication system.

    Explore the platform based on your role:

        Admin: Manage users and oversee system activities.

        Faculty: Upload and manage courses.

        Student: Complete your profile and explore courses.

Contributing

Contributions to the SmartEdu Portal are welcome! Here’s how you can contribute:

    Fork the repository.

    Create a new branch (git checkout -b feature-name).

    Make your changes and commit them (git commit -am 'Add new feature').

    Push to your branch (git push origin feature-name).

    Submit a pull request.

License

This project is licensed under the MIT License - see the LICENSE file for details.