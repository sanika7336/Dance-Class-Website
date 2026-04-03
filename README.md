# TaalBeats Dance Academy - PHP Backend

This directory contains the complete PHP backend for the TaalBeats Dance Academy website.

## 🚀 Quick Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- PHP extensions: PDO, PDO_MySQL, mbstring

### Installation Steps

1. **Database Setup**
   ```bash
   # Create MySQL database
   mysql -u root -p
   CREATE DATABASE taalbeats_db;
   USE taalbeats_db;
   exit;
   ```

2. **Configure Database Connection**
   - Edit `db.php` and update database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'taalbeats_db');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

3. **Run Database Setup Script**
   - Open your browser and navigate to: `http://your-domain/php/setup_database.php`
   - This will create all necessary tables and sample data

4. **Update HTML Forms**
   - Update all form actions in your HTML files to point to the PHP handlers:
   ```html
   <!-- Registration -->
   <form action="php/register.php" method="post">
   
   <!-- Login -->
   <form action="php/login.php" method="post">
   
   <!-- Contact -->
   <form action="php/contact.php" method="post">
   
   <!-- Enrollment -->
   <form action="php/enroll.php" method="post">
   
   <!-- Payment -->
   <form action="php/payment.php" method="post">
   
   <!-- Testimonial -->
   <form action="php/testimonial.php" method="post">
   ```

## 📁 File Structure

```
php/
├── db.php                    # Database connection and helper functions
├── session.php               # Session management and authentication
├── register.php              # User registration handler
├── login.php                 # User login handler
├── logout.php                # User logout handler
├── enroll.php                # Course enrollment handler
├── schedule.php              # Class schedule display
├── payment.php               # Payment processing
├── contact.php               # Contact form handler
├── testimonial.php           # Testimonial submission handler
├── generate_certificate.php  # Certificate generation
├── admin_panel.php           # Admin dashboard
├── setup_database.php        # Database setup script
└── README.md                 # This file
```

## 🔐 Authentication System

### User Registration
- Validates user input (name, email, phone, password)
- Checks for duplicate email addresses
- Hashes passwords securely using `password_hash()`
- Creates user session upon successful registration

### User Login
- Validates email and password
- Verifies password using `password_verify()`
- Creates secure session with timeout (30 minutes)
- Supports admin and regular user roles

### Session Management
- Automatic session timeout after 30 minutes
- Session regeneration for security
- Admin privilege checking
- Secure logout with session destruction

## 🗄️ Database Schema

### Core Tables
- **users**: User accounts and profiles
- **enrollments**: Course enrollments and preferences
- **schedule**: Class schedules and instructor assignments
- **payments**: Payment transactions and status
- **certificates**: Generated certificates for completed courses
- **messages**: Contact form submissions
- **testimonials**: Student testimonials and ratings
- **instructors**: Instructor profiles and specializations
- **admins**: Admin user accounts

### Key Features
- Foreign key relationships for data integrity
- Proper indexing for performance
- Enum fields for status values
- Timestamp fields for audit trails

## 👨‍💼 Admin Panel

### Access
- URL: `http://your-domain/php/admin_panel.php`
- Default credentials: `admin` / `admin123`

### Features
- **Dashboard**: Overview of users, enrollments, payments, revenue
- **User Management**: View, manage, and delete user accounts
- **Enrollment Tracking**: Monitor course enrollments and status
- **Payment Management**: Track payment transactions and revenue
- **Schedule Management**: View and manage class schedules
- **Testimonial Management**: Approve and manage student testimonials
- **Message Center**: View contact form submissions

### Security
- Admin-only access with role-based permissions
- Session-based authentication
- Input validation and sanitization
- SQL injection prevention with prepared statements

## 💳 Payment System

### Supported Methods
- **Online Payment**: Direct payment processing
- **Offline Payment**: Bank transfer with transaction tracking

### Features
- Transaction ID generation
- Payment status tracking
- Integration with enrollment system
- Offline payment instructions

## 📜 Certificate System

### Features
- Automatic certificate generation for completed courses
- Unique certificate IDs
- Professional certificate design
- HTML and PDF format support
- Eligibility verification

### Requirements
- User must be enrolled in the course
- Payment must be completed
- Course completion criteria met

## 📧 Contact & Communication

### Contact Form
- Email validation
- Spam protection
- Admin notification (optional)
- Database storage

### Testimonials
- User authentication required
- Rating system (1-5 stars)
- Comment moderation
- One testimonial per user

## 🔧 Configuration

### Database Settings
Edit `db.php` to configure:
- Database host, name, username, password
- Character set and collation
- Timezone settings

### Email Settings
Edit `contact.php` to configure:
- Admin email address
- SMTP settings (if using external mail service)
- Email templates

### Security Settings
- Session timeout duration
- Password requirements
- Admin access controls

## 🛡️ Security Features

### Input Validation
- All user inputs are sanitized
- Email format validation
- Password strength requirements
- SQL injection prevention

### Session Security
- Secure session handling
- Session timeout
- Session regeneration
- CSRF protection (recommended to implement)

### Database Security
- Prepared statements for all queries
- Password hashing with bcrypt
- Input sanitization
- Error logging without exposing sensitive data

## 🚨 Error Handling

### User-Friendly Messages
- Clear error messages for users
- Success confirmations
- Redirect with status messages

### Logging
- Error logging to server logs
- Database error tracking
- Security event logging

## 📱 Integration with Frontend

### Form Integration
Update your HTML forms to include proper action URLs:

```html
<!-- Example: Registration Form -->
<form action="php/register.php" method="post">
    <input type="text" name="name" required>
    <input type="email" name="email" required>
    <input type="password" name="password" required>
    <input type="password" name="confirm_password" required>
    <select name="dance_style" required>
        <option value="">Select Dance Style</option>
        <option value="Bharatanatyam">Bharatanatyam</option>
        <option value="Kathak">Kathak</option>
        <!-- Add more options -->
    </select>
    <button type="submit">Register</button>
</form>
```

### Session Integration
Add session checks to protected pages:

```php
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}
?>
```

## 🔄 Maintenance

### Regular Tasks
- Monitor error logs
- Backup database regularly
- Update admin passwords
- Review and approve testimonials
- Check payment statuses

### Performance Optimization
- Database indexing
- Query optimization
- Session cleanup
- Cache implementation (recommended)

## 🆘 Troubleshooting

### Common Issues
1. **Database Connection Error**
   - Check database credentials in `db.php`
   - Verify MySQL service is running
   - Check database exists

2. **Session Issues**
   - Verify PHP session configuration
   - Check file permissions
   - Clear browser cookies

3. **Form Submission Errors**
   - Check form action URLs
   - Verify required fields
   - Check file permissions

### Debug Mode
Enable error reporting for development:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📞 Support

For technical support or questions:
- Check error logs in your web server
- Verify database connectivity
- Test individual PHP files
- Review browser console for JavaScript errors

## 🔄 Updates

### Version History
- v1.0: Initial release with core functionality
- Complete user management system
- Admin panel with full features
- Payment and certificate systems

### Future Enhancements
- Email notifications
- Advanced reporting
- Mobile app integration
- Payment gateway integration
- Advanced certificate features

---

**Note**: This backend is designed for educational purposes. For production use, implement additional security measures like HTTPS, CSRF protection, and rate limiting. 