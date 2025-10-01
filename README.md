<img width="1006" height="94" alt="image" src="https://github.com/user-attachments/assets/0cbf0668-5286-4a51-b6c9-179bcc8e789d" /># CoCsystem

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

## Installation Guide
For enterprise deployment, all dependancies must be installed on the server.

**1. Clone Repository**
```bash
git clone https://github.com/AleronFrancois/CoCsystem.git
```

**2. Setup Database**

Use this sql script to setup the necessary tables inside the database: 
[Database setup script](https://github.com/AleronFrancois/CoCsystem/blob/main/setup/database.sql)
