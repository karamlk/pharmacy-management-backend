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

# 🛠️ Tech Stack  

- **Framework:** Laravel 11  
- **Language:** PHP 8+  
- **Database:** MySQL  
- **Auth:** Laravel Sanctum  
- **Testing:** PHPUnit  

---

# ✅ Test Coverage
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

- **Users**
  - Create, Read, Update, Delete (CRUD)

- **Suppliers**
  - CRUD operations
  - Orders
  - Payments

- **Medicines**
  - CRUD operations
  - Filters: expired, out of stock, search


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

## 📝 API Documentation

All API endpoints with examples are included in the Postman collection.  

You can import it directly in Postman:

1. Open Postman.
2. Click **Import** → **File** → Select `postman/pharmacy-management.postman_collection.json`.
3. Start testing the endpoints.

The collection file is located in the repository at: `postman/pharmacy-management.postman_collection.json`.
