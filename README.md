# Laravel Console Starter

A streamlined Laravel starter kit for building applications with custom artisan commands.

This starter kit accelerates the development of Laravel applications that primarily use artisan commands for their functionality. Instead of building standalone CLI tools, you create powerful Laravel console applications that leverage the full Laravel framework ecosystem - including dependency injection, notifications, scheduling, and testing tools. Perfect for building scheduled tasks, data processing workflows, monitoring scripts, and automated maintenance tools that benefit from Laravel's robust architecture without the web application overhead.

[![Ask DeepWiki](https://deepwiki.com/badge.svg)](https://deepwiki.com/invokable/laravel-console-starter)

> **Note:** If you would like to create a console only project based on the latest official Laravel skeleton, you can also use the [laravel-slim](https://github.com/invokable/laravel-slim) package.


## Key Features
- **Focus on Console Applications:** Streamlined for building Laravel applications with artisan commands, removing web-specific overhead.
- **Artisan Command Ready:** Quickly generate and organize your console commands using `php artisan make:command`.
- **Scheduled Tasks with GitHub Actions:** Includes a pre-configured example (`.github/workflows/cron.yml`) for running your commands on a schedule using GitHub Actions.
- **Laravel Framework Power:** Leverage familiar Laravel features like its robust dependency injection container, event system, configuration management, and application testing tools for your console applications.

## Requirements
- PHP ^8.2
- Laravel Framework ^12.0
- Laravel Installer ^5.14

## Installation

```shell
laravel new my-app --using=revolution/laravel-console-starter --no-interaction
```

## Usage

### Make a new command

```shell
php artisan make:command Hello --command=hello
```
This will create a new command class in `app/Console/Commands/Hello.php`. The `--command=hello` option sets the invokable name of your command, so you can run it later using `php artisan hello`.

### Google Search Console Report

This starter includes a pre-built Search Console report command that demonstrates how to integrate with Google APIs using service account authentication.

```shell
php artisan sc:report
```

#### Configuration

To use the Search Console command, you need to configure service account authentication:

1. **Create a Google Service Account:**
   - Go to [Google Cloud Console](https://console.cloud.google.com/)
   - Create a new project or select an existing one
   - Enable the Google Search Console API
   - Create a service account and download the JSON credentials

2. **Configure Environment Variables:**
   ```env
   GOOGLE_APPLICATION_NAME=YourAppName
   GOOGLE_SERVICE_ENABLED=true
   GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION={"type":"service_account","project_id":"..."}
   ```

3. **Add Service Account to Search Console:**
   - Go to [Google Search Console](https://search.google.com/search-console)
   - Add your service account email as a user with permissions

#### GitHub Actions Configuration

For GitHub Actions, store the service account JSON as a repository secret:

```yaml
- name: Run Search Console Report
  run: php artisan sc:report
  env:
    GOOGLE_SERVICE_ENABLED: true
    GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION: ${{ secrets.GOOGLE_SERVICE_ACCOUNT_JSON }}
```

### Task Scheduling in GitHub Actions

[cron.yml](./.github/workflows/cron.yml) is an example of how to run the command in GitHub Actions.
This workflow file demonstrates how to set up a cron-like schedule to execute your Artisan commands automatically. You'll need to customize it with the specific commands you want to run and their desired frequency. Remember to configure repository secrets for any sensitive information your commands might need (e.g., API keys, database credentials).

### Database

While console applications may not frequently use local databases, it's common to utilize remote databases like AWS RDS when running them in GitHub Actions. Database connection settings should be configured using secrets.

Example of environment variable configuration in a workflow:

```yaml
      - name: Run Command
        run: php artisan inspire
        env:
          APP_KEY: ${{ secrets.APP_KEY }}
          DB_HOST: ${{ secrets.DB_HOST }}
          DB_DATABASE: ${{ secrets.DB_DATABASE }}
          DB_USERNAME: ${{ secrets.DB_USERNAME }}
          DB_PASSWORD: ${{ secrets.DB_PASSWORD }}
```

## Notifications

Laravel's built-in notification system provides a convenient way to send notifications from your console commands. This is particularly useful for:

-   Alerting you when a long-running task completes.
-   Reporting errors or issues encountered during command execution.
-   Sending updates or summaries to email, Slack, or other chat platforms.

To use this feature, you'll typically create a notification class (e.g., using `php artisan make:notification TaskCompleted`) and then send it using the `Notification` facade. You will need to configure your desired notification channels (like mail, Slack, etc.) in your Laravel application. When configuring notification channels, especially those relying on external services or specific mail drivers, you may need to publish the relevant configuration files if they don't already exist in your `config` directory. You can do this using the following Artisan commands:

```shell
php artisan config:publish mail
php artisan config:publish services
```

The `config/mail.php` file allows you to configure your mailer settings, while `config/services.php` is used to store credentials and settings for various third-party services that Laravel can integrate with for notifications (e.g., Slack, Vonage). For detailed setup and usage, please refer to the official [Laravel Notification documentation](https://laravel.com/docs/notifications).

## Application Ideas

Here are some ideas for applications that can be built using this starter kit:

### Monitoring and Analytics
- Website uptime monitoring with Slack alerts
- Server resource usage reports via email
- SSL certificate expiration checks and email notifications
- Competitor price change tracking with Discord notifications
- API response time monitoring and alerts
- Database size growth reports
- Website performance score (Lighthouse) periodic checks

### Finance and Business
- Send Google AdSense revenue via email
- Notify AWS costs to Discord
- Daily cryptocurrency portfolio updates to Discord
- Stock price alerts to Slack channels
- Invoice payment deadline reminders
- Monthly expense report generation and delivery
- Subscription renewal alerts

### Data Processing and Reports
- Database backup and completion status notifications
- Old log file cleanup and storage space reports
- Data synchronization between different APIs with result reports
- CSV data import and processing result notifications
- Database integrity checks and issue reports
- Cache cleanup and optimization reports
- Periodic data exports and uploads to cloud storage

### Content and Marketing
- Website broken link checks and reports
- SEO keyword ranking monitoring and change notifications
- Social media follower count change reports
- Blog post performance metrics weekly reports
- Content publication schedule reminders
- RSS feed content aggregation and notifications
- Email marketing campaign result reports

### Development and DevOps
- GitHub repository dependency security alerts
- Codebase static analysis reports
- Test coverage report generation and notifications
- Post-deployment application health checks
- Unused cloud resource detection and notifications
- API documentation change detection and notifications
- Codebase TODO comment aggregation and reminders

### Personal Productivity
- Daily morning weather forecast notifications
- Calendar event daily summaries
- Habit tracking and reminders
- Subscription service renewal date notifications
- Important dates and anniversary reminders
- Regular backup reminders
- Health data aggregation and trend reports

### Additional Ideas

- Domain expiration monitoring and alerts  
- Suspicious access pattern detection from server logs  
- Unusual financial transaction detection reports  
- Automated OCR processing and results summary  
- Google Trends keyword monitoring and notifications  
- YouTube channel performance data aggregation  
- CI/CD failure trend analysis  
- Laravel package update detection alerts  
- Personal spending category insights and visual reports  
- Daily journal sentiment analysis and summaries

## Documentation

For detailed usage instructions and examples, please refer to our comprehensive tutorials:

- [Tutorial (English)](./docs/tutorial.md)
- [チュートリアル (日本語)](./docs/tutorial_ja.md)

## LICENSE
MIT                                                                
