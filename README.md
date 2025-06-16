# Search Console Sample Project

This project is a sample implementation that demonstrates how to use the Google Search Console API with Laravel. It's built on the [laravel-console-starter](https://github.com/invokable/laravel-console-starter) foundation and showcases the [laravel-google-searchconsole](https://github.com/invokable/laravel-google-searchconsole) package.

## Overview

The application automatically fetches Google Search Console data and generates daily performance reports that are sent via email. It uses service account authentication to securely access the Google Search Console API and runs daily via GitHub Actions.

## Key Features

- **Automated Daily Reports**: Generates performance summaries for the last 30 days
- **Service Account Authentication**: Uses Google service account credentials stored as JSON in GitHub Secrets
- **Email Notifications**: Sends formatted reports via AWS SES
- **GitHub Actions Integration**: Runs automatically at midnight UTC daily
- **Multi-site Support**: Handles multiple sites configured in Google Search Console

## Authentication Method

The service account authentication is configured using a JSON string stored in the `GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION` environment variable (GitHub Secret). This JSON string is converted to an array using `json_decode()` in `config/google.php`:

```php
'file' => json_decode(env('GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION', ''), true),
```

This method is particularly well-suited for GitHub Actions deployment, as it allows the entire service account credentials to be stored as a single secret.

## Main Files

### `app/Console/Commands/SearchConsoleReport.php`
The primary command that orchestrates the report generation process:
- Fetches all sites from Google Search Console
- Queries performance data for each site using the ReportQuery
- Sends consolidated reports via email notification
- Handles errors gracefully with informative messages

### `app/Notifications/SearchConsoleReportNotification.php`
Formats and sends the email reports:
- Generates performance summaries with clicks, impressions, CTR, and position data
- Creates both overall and per-site breakdowns
- Formats data into readable email content with proper number formatting

### `app/Search/ReportQuery.php`
Defines the Search Console API query parameters:
- Sets date range to last 30 days
- Configures dimensions and row limits
- Extends the abstract query class from the search console package

### `config/google.php`
Configuration for Google API authentication:
- Enables service account authentication
- Processes the JSON credentials from environment variables
- Sets required scopes for Search Console readonly access

## Setup Instructions

### Prerequisites
- PHP 8.4+
- Composer
- Google Search Console service account with appropriate permissions
- AWS SES credentials (for email delivery)

### Installation

1. Clone the repository:
```bash
git clone https://github.com/invokable/search-console-project.git
cd search-console-project
```

2. Install dependencies:
```bash
composer install
```

3. Setup environment:
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure environment variables in `.env`:
```env
# Google Search Console
GOOGLE_SERVICE_ENABLED=true
GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION='{"type":"service_account",...}'

# Email Configuration
MAIL_MAILER=ses
MAIL_FROM_ADDRESS=your-email@domain.com
MAIL_FROM_NAME="Search Console Monitor"
MAIL_TO_ADDRESS=recipient@domain.com
MAIL_TO_NAME="Report Recipient"

# AWS SES
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
```

### Google Service Account Setup

1. Create a service account in Google Cloud Console
2. Enable the Search Console API
3. Generate and download the service account key (JSON)
4. Add the service account email as a user in Google Search Console
5. Store the entire JSON content as `GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION`

## Usage

### Manual Execution
```bash
php artisan sc:report
```

### Automated Execution
The project includes a GitHub Actions workflow (`.github/workflows/cron.yml`) that runs daily at midnight UTC. Configure the following GitHub Secrets:

- `GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION`
- `AWS_ACCESS_KEY_ID`
- `AWS_SECRET_ACCESS_KEY`
- `AWS_DEFAULT_REGION`
- `MAIL_FROM_ADDRESS`
- `MAIL_FROM_NAME`
- `MAIL_TO_ADDRESS`
- `MAIL_TO_NAME`

## Testing

Run the test suite:
```bash
php artisan test
```

Check code style:
```bash
vendor/bin/pint --test
```

Fix code style issues:
```bash
vendor/bin/pint
```

## Report Format

The generated reports include:
- **Overall Performance**: Total clicks, impressions, average CTR, and average position
- **Site Breakdown**: Individual metrics for each configured site
- **Time Period**: Data covers the last 30 days
- **Timestamp**: Report generation date and time

## Troubleshooting

Common issues and solutions:

1. **"No sites found"**: Ensure the service account email is added as a user in Google Search Console
2. **Authentication errors**: Verify the service account JSON is properly formatted and has correct permissions
3. **Email delivery issues**: Check AWS SES configuration and verify sender/recipient email addresses

## LICENSE
MIT License
