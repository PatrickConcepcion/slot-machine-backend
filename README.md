# Slot Machine Application

A slot machine game built with Laravel (backend) and Vue.js (frontend).

## Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- No database used for now because of the nature of the exam. However, the default setup is SQLite and I did not change that

## Installation

### Backend Setup (Laravel)

1. Clone the repository and navigate to the project directory:
```bash
cd slot-machine-exam
```

2. Install PHP dependencies:
```bash
composer install
```

3. Create environment file:
```bash
cp .env.example .env
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Run database migrations (optional, only run if Laravel throws SQL error):
```bash
php artisan migrate
```

6. Start the Laravel development server:
```bash
php artisan serve
```

The backend API will be available at `http://localhost:8000`

### Frontend (Vue.js)

For the ease of testing, debugging and the exam reviewers checking, I have created a simple frontend with VueJS. The Frontend focuses on debugging and testing and not functionality nor actual product deployment. The frontend can be found here:

https://github.com/PatrickConcepcion/slot-machine-frontend

The frontend will be available at `http://localhost:5173` (or another port if 5173 is in use)

## Running Tests

From the backend directory:

```bash
# Run all tests
php artisan test

```

## API Endpoint

- **POST** `/api/v1/spin` - Spin the slot machine
  - Request body: `{ "bet": <number> }`
  - Allowed bet amounts: 0.2, 0.4, 0.6, 0.8, 1, 1.2, 1.6, 2, 2.4, 2.8, 3.2, 3.6, 4, 5, 6, 8, 10, 14, 18, 24, 32, 40, 60, 80, 100, 110, 120, 130, 140, 150

## Project Implementation from Developer Standpoint

- Made sure to take SOLID and DRY principles into consideration for best practices.
- Due to long logic in the Controller, I decided to separate the logic to a service which also allows me to reuse it for testing
- Form Requests handles validation, Controller handles HTTP and respoonse, Service handles game logic
- No database were used because of the setup. This can be implemented if authentication is needed and an actual player balance is implemented.
- Game rules/setup are found in config since they are not dynamic but she remain reusable.
- Test files can be run as I created tests for certain scenarios while doing so. 


