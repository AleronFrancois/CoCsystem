# CoCsystem

**CoCsystem** is a mini **Chain of Custody (CoC)** system designed to securely manage and store digital evidence during digital-forensic investigations.

This system is collaboratively developed by ICT students from the **University of Tasmania**:
- **Aleron Francois**
- **Bronson Billing**
- **Leroy Bellchambers**

## Dependencies
- **PHP**
- **SQL Relational Database**
- **Web Server**
- **vlucas/php.env**
- **php-mysqlnd**

## Installation guide

**1. Clone repository**
```bash
git clone https://github.com/AleronFrancois/CoCsystem.git
```

**2. Setup database**

Use this sql script to setup the necessary tables inside the database: 
[Database setup script](https://github.com/AleronFrancois/CoCsystem/blob/main/setup/database.sql)

**3. Install and setup dependancies**
  **For Windows**
  **1.** On windows you can install the composer here: [Composer](https://getcomposer.org/Composer-Setup.exe)
  **NOTE:** Ensure that this line: **extension=pdo_mysql** is not commented-out in the "C:\Program Files\php\php.ini" file.

  - **For Linux**
    
For the .env file to work, ensure vlucus/php.env and php-mysqlnd is installed.
