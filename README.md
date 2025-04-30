<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# EEC Assessment Project

This application was developed as a technical assessment for EEC Company. It demonstrates advanced Laravel features with a strong focus on events, listeners, and comprehensive testing methodologies.

## Project Overview

The project implements a user management system with automatic background information processing using Laravel's event-driven architecture. Key features include:

- Event-driven architecture with `UserSaved` event and corresponding listeners
- Background processing using Laravel queues for enhanced performance
- Comprehensive unit and feature testing
- Implementation of SOLID principles and modular design

## Technical Implementation

- **Events & Listeners**: Implemented the `UserSaved` event triggered on user creation/update, with a corresponding listener that processes user background information
- **Queue System**: Utilized Laravel's queue system to process user information in the background
- **Relationships**: Established a one-to-many relationship between User and Detail models
- **Service Layer**: Created dedicated service classes for business logic to maintain separation of concerns
- **Testing**: Implemented comprehensive unit and feature tests with mocking

## Setup Instructions

1. Clone the repository:
   ```
   git clone https://github.com/Gamalsobhy585/events-app.git
   cd events-app
   ```

2. Install dependencies:
   ```
   composer install
   npm install && npm run dev
   ```

3. Environment setup:
   - Copy `.env.example` to `.env`
   - Configure your database settings

4. Create a MySQL database named `eec_app`

5. Run migrations:
   ```
   php artisan migrate
   ```

6. Start the application:
   ```
   php artisan serve
   ```

7. Run the queue worker (in a separate terminal):
   ```
   php artisan queue:work
   ```

## Testing

Run the test suite with:
```
php artisan test
```

**Important Note**: If you want to keep the database refreshed after tests, uncomment the following line in the test files:
```php
// use RefreshDatabase;
```

## Features Implemented

### 1. Database Structure
- Created `details` table to store additional user background information
- Implemented foreign key constraints with proper cascading on DELETE and UPDATE

### 2. Models & Relationships
- Created `Detail` model with appropriate relationships
- Established one-to-many relationship between `User` and `Detail` models

### 3. Events
- Implemented `UserSaved` event triggered when a user is created or updated
- Mapped event to the `saved` event on the `User` model

### 4. Listeners
- Implemented `SaveUserBackgroundInformation` listener to process user data
- The listener generates and stores:
  - User's full name (combining first, middle, and last name)
  - Middle initial
  - Avatar data
  - Gender information based on prefix

### 5. Service Layer
- Created `UserService` to handle the business logic
- Service is injected into the listener following dependency injection principles

## Project Structure

```
app/
├── Events/
│   └── UserSaved.php
├── Listeners/
│   └── SaveUserBackgroundInformation.php
├── Models/
│   ├── User.php
│   └── Detail.php
├── Services/
│   ├── Interface/
│   │   └── UserServiceInterface.php
│   └── UserService.php
│
tests/
├── Feature/
│   └── SaveUserBackgroundInformationTest.php
└── Unit/
    └── UserSavedEventTest.php
```

## SOLID Principles Applied

- **Single Responsibility**: Each class has one responsibility
- **Open/Closed**: Classes are open for extension but closed for modification
- **Liskov Substitution**: Interface implementations can be substituted
- **Interface Segregation**: Used focused interfaces
- **Dependency Inversion**: High-level modules depend on abstractions

