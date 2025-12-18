# Clinic Management

### Step 1: Composer Dependency Installation
Ensure all dependencies are installed using Composer:
```bash
composer install
```

### Step 2: Database Configuration
Set up the following database configurations in the `.env` file:
```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
```
### Step 3: Migration and Seeder
Run the following commands to perform migration and seeding tasks:
```bash
php artisan migrate --seed
```
### Step 4: AWS Configuration
Set up your S3 bucket name in the `.env` file:
```dotenv
AWS_ACCESS_KEY_ID=your_access_key_id
AWS_SECRET_ACCESS_KEY=your_secret_access_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your_bucket_name
```
### Step 5: Mail Configuration
```dotenv
MAIL_MAILER=smtp
MAIL_HOST=your_host_name
MAIL_PORT=your_smtp_server_port
MAIL_USERNAME=your_smtp_username
MAIL_PASSWORD=your_smtp_password
MAIL_ENCRYPTION=your_smtp_encryption_type
MAIL_FROM_ADDRESS=sender_email_address
MAIL_FROM_NAME=sender_name
```

### Step 6: Stripe Configuration

#### 1. Stripe Configuration

Set up your Stripe API keys in the `.env` file:
```dotenv
STRIPE_PUBLIC_KEY=your_stripe_public_key
STRIPE_SECRET_KEY=your_stripe_secret_key
STRIPE_ENDPOINT_SECRET=your_webhook_secret
```

#### 2. Add Endpoint

Set up the webhook endpoint in your Stripe Dashboard:

**Endpoint URL:** `/stripe/webhook`

#### 3. Add Events

Add the following events to your Stripe webhook:

- `invoice.created`
- `invoice.payment_succeeded`
- `invoice.payment_failed`

These events will notify your application about important actions and updates within Stripe, allowing you to handle them accordingly.

Ensure that your webhook endpoint is properly configured to receive and process these events from Stripe.

Once configured, your application will be notified whenever these events occur in your Stripe account.

#### 4. Handling Subscription and Invoice Statuses in Stripe

Configure the "Manage failed payments for subscriptions" setting in the billing settings.

For **Card payments**, select `Use a custom retry policy for subscriptions`.

In the custom setting options, set the retry interval to **Retry 7 days after the previous attempt**.

For the **Subscription status**, if all retries for a payment fail, the subscription remains overdue.

For the **Invoice status**, if all retries for a payment fail, the invoice remains overdue.

### Step 7: PDF library configurations
Set up  pdf library configuration in `.env` file
 
```dotenv
WKHTML_PDF_BINARY=BIN_FILE_PATH
WKHTML_IMG_BINARY=BIN_FILE_PATH
```

### Step 8: Cellcast configurations
Set up cellcat configuration in `.env` file
 
```dotenv
CELLCAST_API_KEY=
CELLCAST_USERNAME=
CELLCAST_PASSWORD=
```
### Step 9: ShortIO configurations
Set up cellcat configuration in `.env` file

```dotenv
SHORTIO_API_KEY=
SHORTIO_DOMAIN=
SHORTIO_API_BASE_URL=
```
