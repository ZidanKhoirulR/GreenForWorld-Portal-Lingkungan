# Web-Based Environmental Awareness & Public Engagement Platform (Green For World)

> **Environmental Information Platform & Interactive Contact Module Powered by Fat-Free Framework (F3)**

![PHP](https://img.shields.io/badge/PHP-%3E%3D%208.0-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Framework](https://img.shields.io/badge/Framework-Fat--Free%203.x-F15A24?style=for-the-badge)
![Database](https://img.shields.io/badge/Database-MySQL%20%7C%20MariaDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![PHPMailer](https://img.shields.io/badge/Mailer-PHPMailer%20SMTP-00599C?style=for-the-badge)
![Frontend](https://img.shields.io/badge/Frontend-HTML5%20%7C%20Skel.js%20%7C%20jQuery-2e7d32?style=for-the-badge)

---

## Overview & Case Study

**Green For World (GreW)** is a dedicated environmental web platform established to promote ecological awareness, sustainable waste management, environmental campaigns, and community participation. Operating as a public-facing digital publication, the platform disseminates curated environmental news, academic articles, photographic documentation of green initiatives, and collaborative action programs for volunteers, educational institutions, and environmental advocates.

In maintaining an impactful environmental advocacy portal, delivering a reliable, two-way communication channel between public stakeholders and organization administrators is paramount. Historically, public engagement systems faced several operational challenges:
- **Inefficient Communication Channels:** Traditional static web contact forms often lacked real-time, non-blocking feedback, requiring full-page reloads that caused lost input data upon submission errors.
- **Client & Server Data Validation Gaps:** Inadequate sanitization and absence of immediate client-side regex checks resulted in erroneous or incomplete submissions reaching organization staff.
- **Unreliable Notification Delivery:** Relying on unauthenticated server mail protocols (`mail()`) led to high spam failure rates, missed partnership inquiries, and lack of SSL/TLS transport encryption.

---

## Technology Stack

- **Backend Framework:** Fat-Free Framework 3.x (PHP 8.0+)
- **Architecture Pattern:** Model-View-Controller (MVC) with custom routing dispatcher (`index.php`) and controller-bound actions
- **Mailer Engine:** PHPMailer (authenticated SMTP over SSL/TLS, Port 465)
- **Database Engine:** MySQL / MariaDB (InnoDB, relational tables: `artikel`, `tb_news_wl`, `galeri`, `komentar`, `users`)
- **Frontend Template:** Fat-Free Template Engine (`<include>` syntax) with modular component views
- **Styling & UI Components:** Custom Green Ecological Theme, Skel.js Responsive Grid System, CSS3 Keyframe Toast Notifications
- **Client-Side Scripts:** jQuery 1.11+, Asynchronous AJAX Form Serialization, Email Regex Validator
- **Session & Security:** Role-Based Session Guards, String Parameter Sanitization, and Secure Password Hashing
