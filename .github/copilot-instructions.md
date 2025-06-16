# Copilot Instructions

## Project Overview

This is a Laravel application that provides automated Google Search Console reporting. The application:
- Fetches Search Console data using Google's API with service account authentication
- Generates daily performance reports (last 30 days)
- Sends formatted email reports via AWS SES
- Runs automatically once daily via GitHub Actions at midnight UTC

## Architecture

### Key Components
- **SearchConsoleReport Command** (`app/Console/Commands/SearchConsoleReport.php`) - Main command that fetches data
- **SearchConsoleReportNotification** (`app/Notifications/SearchConsoleReportNotification.php`) - Email notification formatting
- **ReportQuery** (`app/Search/ReportQuery.php`) - Defines query parameters for Search Console API
- **GitHub Actions** (`.github/workflows/cron.yml`) - Automated daily execution
- **Configuration** (`config/google.php`, `config/mail.php`) - Google API and email settings

### Technology Stack
- Laravel 12.x (PHP 8.4)
- Google Search Console API (via revolution/laravel-google-searchconsole)
- AWS SES for email delivery
- GitHub Actions for CI/CD

## Development Setup

### Prerequisites
- PHP 8.4+
- Composer

### Installation Steps
```bash
# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run tests
php artisan test

# Run linting
vendor/bin/pint --test
```

### Environment Configuration
Key environment variables:
- `GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION` - Service account credentials (JSON)
- `MAIL_MAILER` - Use 'ses' for production, 'log' for development
- `MAIL_FROM_ADDRESS` / `MAIL_TO_ADDRESS` - Email configuration
- `AWS_ACCESS_KEY_ID` / `AWS_SECRET_ACCESS_KEY` - AWS SES credentials

## Testing and Development

### Running Tests
```bash
php artisan test          # Run all tests
vendor/bin/pint --test    # Check code style
vendor/bin/pint           # Fix code style
```

### Manual Testing
```bash
# Test the main command (will fail without proper credentials)
php artisan sc:report
```

### Code Quality
- Uses Laravel Pint for code styling
- Follows PSR-12 standards
- Has basic PHPUnit test coverage

## Production Deployment

### GitHub Actions Workflow
- Runs daily at midnight UTC (`0 0 * * *`)
- Uses secrets for sensitive configuration
- Automatically installs dependencies and runs the report command

### Required GitHub Secrets
- `GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION` - Service account JSON
- `AWS_ACCESS_KEY_ID` / `AWS_SECRET_ACCESS_KEY` - AWS credentials
- `MAIL_FROM_ADDRESS` / `MAIL_TO_ADDRESS` - Email configuration

## Important Limitations

### Copilot Environment Restrictions
- **Critical**: The Copilot firewall causes errors when connecting to `www.googleapis.com`
- This affects Google API calls and should be ignored in Copilot environment
- The production GitHub Actions environment has no such restrictions
- Use mock data or skip API calls when testing in Copilot

### API Dependencies
- Requires valid Google Search Console service account
- Service account must be added as a user in Google Search Console
- AWS SES must be configured for email delivery

## Troubleshooting

### Common Issues
1. **Google API Connection Errors** - Check service account credentials and permissions
2. **Email Delivery Failures** - Verify AWS SES configuration and from/to addresses
3. **No Sites Found** - Ensure service account is added to Google Search Console
4. **Copilot API Errors** - googleapis.com connections will fail due to firewall restrictions

### Debugging
```bash
# Check application logs
tail -f storage/logs/laravel.log

# Test email configuration
php artisan tinker
# >>> Mail::raw('Test', fn($msg) => $msg->to('test@example.com')->subject('Test'));
```

## File Structure
```
app/
├── Console/Commands/SearchConsoleReport.php  # Main command
├── Notifications/SearchConsoleReportNotification.php  # Email formatting
└── Search/ReportQuery.php  # API query configuration

config/
├── google.php  # Google API configuration
├── mail.php    # Email configuration
└── services.php  # Third-party services

.github/
└── workflows/
    ├── cron.yml    # Daily execution
    ├── tests.yml   # Test automation
    └── lint.yml    # Code quality
```
