# 🎓 Skillshare - Skill Exchange Platform

A modern web application that connects people who want to learn skills with those who want to teach them. Users can post skills they want to offer or request, connect with others, and communicate through an integrated chat system.

![Skillshare Banner](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white) ![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white) ![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black) ![HTML](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white) ![CSS](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)

## ✨ Features

### 🔐 User Authentication
- Secure user registration and login
- Password hashing with PHP's `password_hash()`
- Session management
- Role-based access control (User/Admin)

### 🛠️ Skill Management
- Post skills you want to offer or request
- Upload skill images
- Categorize skills (Programming, Design, Music, etc.)
- Search and filter skills
- View detailed skill descriptions

### 🤝 Connection System
- Send connection requests to other users
- Accept/reject incoming requests
- View all your connections
- Connection status tracking

### 💬 Real-time Chat
- Live messaging between connected users
- Message status indicators (read/unread)
- Auto-refresh for new messages
- Clean and intuitive chat interface

### 📊 Admin Panel
- **Dashboard**: Overview with real-time statistics
- **User Management**: View, edit, and manage all users
- **Skill Management**: Monitor and moderate skill posts
- **Connection Management**: Track user connections
- **Message Management**: Monitor chat messages
- **Reports & Analytics**: Data visualization with charts
- **Settings**: Admin profile and system settings

## 🚀 Installation

### Prerequisites
- **XAMPP** (Apache, PHP 7.4+, MySQL)
- **Web Browser** (Chrome, Firefox, Safari, Edge)
- **Git** (for cloning the repository)

### Step 1: Clone the Repository
```bash
git clone https://github.com/saubhagyakashyap/Skillshare.git
cd Skillshare
```

### Step 2: Setup XAMPP
1. Start **Apache** and **MySQL** services in XAMPP Control Panel
2. Place the project folder in `xampp/htdocs/`

### Step 3: Database Setup
1. Open **phpMyAdmin** (`http://localhost/phpmyadmin`)
2. Create a new database named `skillshare`
3. Import the database schema:
   ```sql
   -- Import the file: database/Skillshare Database .sql
   ```

### Step 4: Configure Database Connection
Update the database configuration in `includes/db.php`:
```php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "skillshare";
```

### Step 5: Setup Admin Account
1. Navigate to: `http://localhost/Skillshare/admin/setup_admin.php`
2. This will create the default admin account:
   - **Email**: `admin@skillshare.com`
   - **Password**: `admin123`

### Step 6: Access the Application
- **Main Site**: `http://localhost/Skillshare/pages/index.php`
- **Admin Panel**: `http://localhost/Skillshare/admin/admin_login.php`

## 📁 Project Structure

```
Skillshare/
├── admin/                      # Admin panel files
│   ├── admin_auth.php         # Admin authentication helper
│   ├── admin_dashboard.php    # Main admin dashboard
│   ├── admin_login.php        # Admin login page
│   ├── manage_users.php       # User management
│   ├── manage_skills.php      # Skill management
│   ├── manage_connections.php # Connection management
│   ├── manage_messages.php    # Message management
│   ├── reports.php            # Analytics and reports
│   ├── settings.php           # Admin settings
│   └── setup_admin.php        # Initial admin setup
├── assets/
│   ├── css/                   # Stylesheets
│   │   ├── admin.css         # Admin panel styles
│   │   ├── chat.css          # Chat interface styles
│   │   ├── dashboard.css     # User dashboard styles
│   │   ├── index.css         # Homepage styles
│   │   ├── login.css         # Login/register styles
│   │   ├── search.css        # Search page styles
│   │   ├── skills.css        # Skill pages styles
│   │   └── style.css         # Global styles
│   ├── images/               # Static images
│   └── js/
│       └── script.js         # JavaScript functionality
├── database/
│   └── Skillshare Database .sql # Database schema
├── includes/                  # PHP includes
│   ├── auth.php              # User authentication
│   ├── db.php                # Database connection
│   ├── footer.php            # Page footer
│   ├── functions.php         # Utility functions
│   ├── header.php            # Page header
│   └── logout.php            # Logout functionality
├── pages/                     # Main application pages
│   ├── accept_reject.php     # Handle connection requests
│   ├── chat.php              # Chat interface
│   ├── connect.php           # Send connection requests
│   ├── dashboard.php         # User dashboard
│   ├── get_messages.php      # AJAX message retrieval
│   ├── inbox.php             # Connection requests inbox
│   ├── index.php             # Homepage
│   ├── login.php             # User login
│   ├── my_skills.php         # User's posted skills
│   ├── post_skill.php        # Post new skill
│   ├── register.php          # User registration
│   ├── search.php            # Search skills/users
│   ├── send_message.php      # AJAX message sending
│   └── view_skill.php        # View skill details
├── uploads/                   # User uploaded files
└── README.md                 # This file
```

## 🎯 Usage

### For Users
1. **Register**: Create a new account with email and password
2. **Post Skills**: Add skills you want to offer or request
3. **Search**: Find skills or users you're interested in
4. **Connect**: Send connection requests to other users
5. **Chat**: Communicate with your connections
6. **Manage**: View and edit your posted skills

### For Admins
1. **Login**: Access admin panel with admin credentials
2. **Monitor**: View real-time statistics and activity
3. **Manage Users**: Add, edit, or remove user accounts
4. **Moderate Content**: Review and manage skill posts
5. **Analyze**: View reports and analytics
6. **Configure**: Manage system settings

## 🔧 Technologies Used

### Backend
- **PHP 7.4+**: Server-side scripting
- **MySQL**: Database management
- **mysqli**: Database connectivity with prepared statements

### Frontend
- **HTML5**: Markup structure
- **CSS3**: Styling with modern features (Grid, Flexbox, Gradients)
- **JavaScript**: Client-side interactions and AJAX
- **Font Awesome**: Icons
- **Chart.js**: Data visualization in admin panel

### Security Features
- **SQL Injection Prevention**: Prepared statements
- **XSS Protection**: Input sanitization
- **CSRF Protection**: Session validation
- **Password Security**: Bcrypt hashing
- **File Upload Security**: Type and size validation

## 📊 Database Schema

### Tables
- **users**: User accounts and profiles
- **skills**: Posted skills (offers/requests)
- **messages**: Connection requests and status
- **chat_messages**: Real-time chat messages
- **reviews**: Skill reviews (future feature)

### Key Relationships
- Users can post multiple skills
- Users can have multiple connections
- Connected users can exchange messages
- Skills can have multiple reviews

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🐛 Known Issues

- File upload size is limited by PHP configuration
- Real-time chat requires manual refresh (WebSocket implementation planned)
- Image optimization not implemented

## 🔮 Future Enhancements

- [ ] Real-time notifications
- [ ] Video call integration
- [ ] Skill rating and review system
- [ ] Advanced search filters
- [ ] Mobile app development
- [ ] Email notifications
- [ ] Multi-language support
- [ ] Payment integration for premium skills

## 📞 Support

For support, email saubhagyakashyap@example.com or create an issue in the GitHub repository.

## 👨‍💻 Author

**Saubhagya Kashyap**
- GitHub: [@saubhagyakashyap](https://github.com/saubhagyakashyap)
- Email: saubhagyakashyap@example.com

## 🙏 Acknowledgments

- Font Awesome for icons
- Chart.js for data visualization
- XAMPP for local development environment
- MySQL for database management
- PHP community for excellent documentation

---

⭐ **Star this repository if you find it helpful!!** ⭐
