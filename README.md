# Clinic Management

### Step 1: Composer Dependency Installation
Ensure all dependencies are installed using Composer:
```bash
composer install
```

### Step 2: Make Copy of .env.example to add your configurations
Ensure .env file is created
```bash
copy .env.example .env
```

### Step 3: Database Configuration
Set up the following database configurations in the `.env` file:
```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
```

### Step 4: Migration and Seeder
Run the following commands to perform migration and seeding tasks:
```bash
php artisan migrate --seed
```