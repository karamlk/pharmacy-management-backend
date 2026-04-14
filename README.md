#  Pharmacy Management System – Backend API  

A **Laravel-based RESTful API** for managing a pharmacy’s operations, including medicines, suppliers, pharmacists work hours, salaries, and inventory.  

---

# Features

- Authentication (Login/Logout)  
- User & Role Management (Admin, Pharmacist)  
- Profile management with password change  
- Supplier management  
- Medicine management (with images, stock tracking, expiry detection)  
- Categories management  
- Sales & Invoice management  
- Supplier Orders (Purchases) management  
- Pharmacist work hours & salary calculation  
- Performance analysis reports  

---

# Architecture & Code Quality Improvements

This project was refactored to follow clean architecture principles and improve maintainability as it scales.

## Service Layer Pattern
- Introduced a dedicated **Service Layer** for all modules (Users, Suppliers, Orders, Payments, Sales, etc.)
- Controllers are now thin and only handle HTTP requests/responses
- All business logic is encapsulated in service classes

---

## Form Request Validation
- Moved validation logic into **Form Request classes**
- Ensures clean controllers and reusable validation rules
- Provides consistent validation error responses

---

## Exception-Based Error Handling
- Replaced manual error handling with **custom exceptions**
- Examples:
  - `PharmacistNotFoundException`
  - `NotPharmacistException`
- Ensures consistent API error responses

---

## API Resource Standardization
- Used **Laravel API Resources** to format responses
- Ensures consistent structure across all endpoints
- Separates data representation from business logic

---

## Modular Structure
Each module follows a consistent structure:

- Controller  
- Service  
- Form Requests  
- Resources  
- Tests  

---

## Test-Driven Improvements
- Feature tests were written and used to guide refactoring
- Covered:
  - Valid scenarios
  - Validation failures
  - Edge cases (e.g., non-existent resources)
- Ensured stability while introducing architectural changes

---

## Business Logic Encapsulation
Critical operations are handled inside services:

- Supplier balance updates after orders/payments  
- Inventory updates when purchasing medicines  
- Salary calculation based on working hours  
- Payment validation against supplier balance  

---

# Usage

## Profile
- Each user has a profile of their own.
- You can update your profile credentials.
- You can change your password.

## Users
- Displays a list of all pharmacists in the system.
- Add a new user by clicking the **Add pharmacist** button.
- Edit pharmacist details via the **Edit** button.
- Delete pharmacist using the **Delete** button.

## Suppliers
- Lists all medicines suppliers.
- Add new suppliers.
- Edit or delete supplier details as needed.

## Sales
- Displays all sales made in the system.
- Add new sales by selling medicines.


## Orders
- Contains all medicine purchases.
- Add order by writing the name of every medicine 
you want to purchase from a supplier with the desired quantity.

## Medicines
- Lists all medicines in the system.
- Add medicine.
- Edit or delete medicines as needed.

  ### ➤ Out of Stock
- Medicines with zero quantity are automatically detected.
- No manual action required.

  ### ➤ Expired
- Medicines past their expiry date are automatically detected.
- No manual action required.

## Categories
- Lists all medicines categories.
- Add, edit, or delete categories via the corresponding buttons.

## Pharmacist Work Hours & Salary
- Tracks working hours from login/logout sessions.
- Admins can see the  calculated salaries based on:  
  `Hourly Rate × Total Worked Hours`

## Performance Analysis
- Admins can view:
  - Monthly supplier costs
  - Sales revenue
  - Pharmacist salaries

---

#  Tech Stack  

- **Framework:** Laravel 11  
- **Language:** PHP 8+  
- **Database:** MySQL  
- **Auth:** Laravel Sanctum  
- **Testing:** PHPUnit  

---

#  Test Coverage
 Run the test suite
 
 ```bash
 php artisan test
 ```
- **Auth**
  - Login
  - Logout

- **Profile**
  - Update info
  - Change password

- **User**
  - Create, Read, Update, Delete (CRUD)

- **Supplier**
  - CRUD operations
  - Orders
  - Payments

- **Medicine**
  - CRUD operations
  - Filters: expired, out of stock, search

- **Category**
  - CRUD operations

- **Sales**
  - Invoice creation
  - List all the sales
  - Show the details of every sale

- **Performance**
  - Pharmacist work hours tracking
  - Salary calculation
  - Monthly cost reporting

---

# Installation

## 1. Clone the Repository

```bash
git clone https://github.com/karamlk/pharmacy-management-backend.git
cd pharmacy-management-backend
```

## 2. Install Dependencies

```bash
composer install
```

## 3. Copy the example environment file 

```bash
cp .env.example .env
```

Then edit `.env` and configure your database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Generate application key

```bash
php artisan key:generate
```

### 5. Run migrations with seeders:

```bash
php artisan migrate --seed
```

### 6. Serve Locally

```bash
php artisan serve
```

---

# Deployment

The Pharmacy Management System is deployed on [Render](https://render.com) using a Docker container, with the MySQL database hosted on [Clever Cloud](https://www.clever-cloud.com). The application and its API are accessible for testing using the following details:

- **Live URL**: [https://pharmacy-management-x6ta.onrender.com](https://pharmacy-management-x6ta.onrender.com)
- **Demo Credentials**:
  - **Admin**: `admin@example.com` / Password: `password`
  - **Pharmacist**: `pharmacist@example.com` / Password: `password`

## Deployment Process
- **Docker Setup**: The application is containerized using a custom Docker image, built with Laravel dependencies and configured with environment variables for secure production use.
- **Render Configuration**: Deployed on Render’s web service, manually triggered to pull the latest Docker image from Docker Hub and it is connected to the Clever Cloud MySQL database.
- **CI/CD Pipeline**: A GitHub Actions workflow automates PHPUnit testing (95% code coverage) on every push or pull request to the `main` branch.

---

##  API Documentation

All API endpoints with examples are included in the Postman collection.  

You can import it directly in Postman:

1. Open Postman.
2. Click **Import** → **File** → Select `postman/pharmacy-management.postman_collection.json`.
3. Start testing the endpoints.

The collection file is located in the repository at: `postman/pharmacy-management.postman_collection.json`.
