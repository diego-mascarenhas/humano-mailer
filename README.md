# Humano Mailer

A comprehensive email marketing and messaging system for Laravel applications, designed specifically for the Humano ecosystem.

## Features

- **Email Campaigns**: Create and manage email marketing campaigns
- **Multiple Providers**: Support for SMTP, Mailgun, and MailBaby
- **Message Templates**: Integration with template system for rich HTML emails
- **Contact Management**: Target specific contact categories and statuses
- **Tracking & Analytics**: Open tracking, click tracking, and detailed statistics
- **Team-based**: Multi-tenant support with team isolation
- **Rate Limiting**: Configurable sending limits and delays between emails
- **Unsubscribe Management**: Automatic unsubscribe handling
- **Campaign Controls**: Start, pause, and monitor campaigns
- **Test Sending**: Send test emails before launching campaigns

## Installation

Install the package via Composer:

```bash
composer require idoneo/humano-mailer
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="humano-mailer-migrations"
php artisan migrate
```

Optionally, publish the config file:

```bash
php artisan vendor:publish --tag="humano-mailer-config"
```

## Configuration

The package automatically registers the "Mailer" module in your application. Configure your email providers in the `.env` file:

### SMTP Configuration (Default)
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

### Email API Configuration (MailBaby, Mailgun, etc.)
For external email service providers, use these simplified variables:

```env
# Generic API configuration - works with any email service
MAIL_API_KEY=your-api-key-here
MAIL_API_DOMAIN=your-domain.com  # Optional, only if your provider needs it

# Examples:
# For MailBaby: MAIL_API_KEY=your-mailbaby-api-key
# For Mailgun: MAIL_API_KEY=your-mailgun-secret-key and MAIL_API_DOMAIN=your-domain.mailgun.org
# For SendGrid: MAIL_API_KEY=your-sendgrid-api-key
```

The package will automatically detect if you have an API key configured and enable API-based sending.

## Usage

### Creating Messages

Messages can be created through the web interface or programmatically:

```php
use Idoneo\HumanoMailer\Models\Message;

$message = Message::create([
    'name' => 'Welcome Campaign',
    'type_id' => 1,
    'template_id' => 1,
    'text' => 'Welcome to our platform!',
    'status_id' => 1,
    'team_id' => auth()->user()->currentTeam->id,
]);
```

### Starting Campaigns

Campaigns can be started through the web interface or API:

```php
$message = Message::find(1);
$message->update([
    'status_id' => 1, // Active
    'started_at' => now(),
]);
```

### Tracking

The package provides comprehensive tracking:

- **Open Tracking**: Tracks when emails are opened
- **Click Tracking**: Tracks link clicks within emails
- **Delivery Status**: Monitors delivery success/failure
- **Unsubscribe Tracking**: Handles unsubscribe requests

## Models

### Message
The main campaign model with relationships to:
- `MessageType`: Campaign type (newsletter, promotional, etc.)
- `Category`: Target contact category
- `Template`: Email template for HTML content
- `MessageDelivery`: Individual email deliveries

### MessageDelivery
Individual email delivery records with:
- Delivery status tracking
- Provider-specific data
- Open/click timestamps
- Error handling

### MessageType
Campaign types for categorizing messages.

## Routes

The package registers the following routes:

- `GET /message/list` - List all messages
- `GET /message/create` - Create new message form
- `GET /message/{id}` - View message details
- `POST /message/{id}/start` - Start campaign
- `POST /message/{id}/pause` - Pause campaign
- `POST /message/{id}/test` - Send test email

## Team Integration

The package is designed for multi-tenant applications:

- All models are scoped to teams automatically
- Email configuration can be team-specific
- Statistics and deliveries are isolated by team

## Dependencies

- `spatie/laravel-package-tools`: Package development tools
- `yajra/laravel-datatables-oracle`: DataTables integration
- `mailgun/mailgun-php`: Mailgun API support
- `tijsverkoyen/css-to-inline-styles`: Email CSS processing

## License

This package is licensed under the [GNU AGPLv3](https://www.gnu.org/licenses/agpl-3.0.html).

## Support

For support and documentation, visit the [Humano documentation](https://docs.humano.app) or contact support@idoneo.es.
