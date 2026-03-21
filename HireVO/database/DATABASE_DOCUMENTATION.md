# HireVo Database Schema Documentation

## Overview
This database schema implements a job posting and recruitment platform with role-based user management. The system supports two types of users: **Employers** and **Job Seekers**, each with their own specialized data structures.

---

## Table Structure & Design Decisions

### 1. USERS TABLE (Core Authentication)

**Purpose:** Central user authentication and role management table

**Columns:**
- `user_id` - Primary key, VARCHAR(10) with prefixed IDs (e.g. `ADM-001`, `EMP-001`, `JOB-001`)
- `username` - VARCHAR(50), NOT NULL, UNIQUE - Ensures each user has a unique username
- `email` - VARCHAR(100), NOT NULL, UNIQUE - Email must be unique for password recovery and communication
- `password` - VARCHAR(255), NOT NULL - Stores hashed passwords (use SHA2 or bcrypt)
- `role` - ENUM('admin', 'job_seeker', 'employer'), NOT NULL - Restricts to three valid roles
- `status` - ENUM('active', 'inactive', 'suspended'), NOT NULL, DEFAULT 'active' - Account status management
- `created_at` - TIMESTAMP, DEFAULT CURRENT_TIMESTAMP - Auto-records account creation time
- `updated_at` - TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE - Auto-updates when record changes

**Key Features:**
- ✅ NO NULL values allowed in any column (as per requirement)
- ✅ UNIQUE constraints on username and email prevent duplicates
- ✅ ENUM types limit values to valid options (role-based access control)
- ✅ Indexes on email, username, role, status for fast query performance
- ✅ InnoDB engine ensures ACID compliance for data integrity

---

### 2. EMPLOYERS TABLE

**Purpose:** Store employer-specific information linked to users

**Columns:**
- `employer_id` - Primary key, auto-incrementing
- `user_id` - Foreign key (FK) with UNIQUE constraint - Ensures one-to-one relationship
- `company_name` - VARCHAR(150), NOT NULL - Required field for company identification
- `company_description` - TEXT, NULL - Flexible length for detailed company info
- `industry` - VARCHAR(100), NOT NULL - Type of business (Tech, Finance, etc.)
- `company_size` - ENUM('1-10', '11-50', '51-200', '201-500', '500+'), NOT NULL - Predefined sizes
- `location` - VARCHAR(150), NOT NULL - Company headquarters location
- `website` - VARCHAR(255), NULL - Optional company website
- `contactNum` - VARCHAR(20), NOT NULL - Company contact number
- `logo_url` - VARCHAR(255), NULL - Path to company logo (added enhancement)
- `verified` - BOOLEAN, DEFAULT FALSE - Admin verification flag (added enhancement)
- `created_at` & `updated_at` - TIMESTAMP fields for audit trail

**Key Features:**
- ✅ Foreign Key Constraint: `fk_employer_user` links to users.user_id with CASCADE delete/update
  - If a user is deleted, their employer record is also deleted automatically
  - If user_id changes, it updates in this table automatically
- ✅ UNIQUE constraint on user_id ensures one employer record per user
- ✅ Indexes on user_id, company_name, location, verified for query optimization

---

### 3. JOB_SEEKERS TABLE

**Purpose:** Store job seeker-specific profile information

**Columns:**
- `job_seeker_id` - Primary key, auto-incrementing
- `user_id` - Foreign key (FK) with UNIQUE constraint - One-to-one relationship
- `firstName` - VARCHAR(50), NOT NULL - Required for identification
- `lastName` - VARCHAR(50), NOT NULL - Required for identification
- `gender` - ENUM('Male', 'Female', 'Other', 'Prefer not to say'), NULL - Optional demographic
- `birthdate` - DATE, NULL - Optional age tracking
- `address` - VARCHAR(255), NULL - Residential address
- `contactNum` - VARCHAR(20), NOT NULL - Phone/mobile number (required)
- `profile_picture_url` - VARCHAR(255), NULL - Path to profile photo (added enhancement)
- `resume_file` - VARCHAR(255), NULL - Path to uploaded resume
- `skills` - TEXT, NULL - Comma-separated or JSON list of skills
- `education` - TEXT, NULL - Educational background details
- `experience` - TEXT, NULL - Work experience details
- `headline` - VARCHAR(200), NULL - Professional headline (added enhancement)
- `bio` - TEXT, NULL - Personal bio/summary (added enhancement)
- `created_at` & `updated_at` - TIMESTAMP for audit trail

**Key Features:**
- ✅ Foreign Key Constraint: `fk_jobseeker_user` links to users.user_id with CASCADE delete/update
- ✅ UNIQUE constraint on user_id ensures one seeker record per user
- ✅ Indexes on user_id, firstName, lastName for fast lookups
- ✅ TEXT fields allow flexible skill/education/experience data entry

---

## Constraint Implementation

### Foreign Key Constraints
```sql
CONSTRAINT fk_employer_user FOREIGN KEY (user_id) 
  REFERENCES users(user_id) 
  ON DELETE CASCADE 
  ON UPDATE CASCADE
```
- **ON DELETE CASCADE:** If a user account is deleted, all their employer/seeker records are automatically deleted
- **ON UPDATE CASCADE:** If user_id is updated, it propagates to child tables

### Role-Based Linking (ByetHost Compatible)
- Shared hosting commonly restricts advanced objects/triggers, so linking is enforced at application level in `api/process_register.php`
- Registration uses one transaction: insert into `users`, then insert into `employers` or `job_seekers`
- SQL dump includes a one-time sync section that backfills missing role rows for already existing users
- Profile inserts are idempotent (`ON DUPLICATE KEY UPDATE`) to handle retries safely

---

## How Role-Based Data Linking Works

1. **User Registration Process:**
   - User creates account in `users` table with role='employer' or role='job_seeker'
  - Application inserts corresponding record into either `employers` or `job_seekers` table in the same transaction

2. **Data Retrieval:**
   ```sql
   -- Get employer with full user info
   SELECT u.*, e.company_name, e.location 
   FROM users u
   JOIN employers e ON u.user_id = e.user_id
   WHERE u.user_id = ?;
   
   -- Get job seeker with full user info
   SELECT u.*, js.firstName, js.lastName, js.skills
   FROM users u
   JOIN job_seekers js ON u.user_id = js.user_id
   WHERE u.user_id = ?;
   ```

3. **Data Integrity:**
   - A user can NEVER exist in both employers AND job_seekers
   - The UNIQUE constraint on user_id in both tables prevents this
   - Role field serves as single source of truth

---

## Enhancements Beyond Requirements

### 1. Added Fields:
- **Employer:** `logo_url`, `verified` - For company branding and admin verification
- **Job Seeker:** `profile_picture_url`, `headline`, `bio` - For professional profiles

### 2. Audit Trail Columns:
- `created_at` & `updated_at` on all tables - Track when records are created/modified

### 3. Indexes:
- Strategic indexes on frequently queried columns (email, username, role, location)
- Improves query performance significantly

### 4. UTF8MB4 Charset:
- Supports emojis and international characters
- Modern best practice for web applications

### 5. View for User Profiles:
```sql
SELECT * FROM user_profiles;
```
- Provides unified view of all user types without complex queries

---

## How to Use in phpMyAdmin

1. **Open phpMyAdmin** → Select your database
2. **Go to SQL tab** 
3. **Copy entire SQL script** from `create_database.sql`
4. **Paste and Execute**
5. Tables will be created with all constraints and indexes

---

## Required Application Logic

### During User Registration:
```php
// When registering an employer:
$role = 'employer';
INSERT INTO users (username, email, password, role) VALUES (...);
$user_id = last_insert_id();
INSERT INTO employers (user_id, company_name, ...) VALUES ($user_id, ...);

// When registering a job seeker:
$role = 'job_seeker';
INSERT INTO users (username, email, password, role) VALUES (...);
$user_id = last_insert_id();
INSERT INTO job_seekers (user_id, firstName, lastName, ...) VALUES ($user_id, ...);
```

---

## Data Type Summary

| Datatype | Used For | Reason |
|----------|----------|--------|
| INT | IDs, primary keys | Fast indexing |
| VARCHAR(n) | Names, URLs, text fields | Fixed max length, indexed |
| TEXT | Descriptions, skills | Variable length, up to 65KB |
| ENUM | role, status, gender, size | Restricted values, memory efficient |
| BOOLEAN | verified flag | True/False values |
| DATE | Birthdate | Date only, no time |
| TIMESTAMP | created_at, updated_at | Automatic time tracking |

---

## Performance Considerations

✅ **Indexes** placed on:
- Foreign keys (for JOIN operations)
- Frequently searched columns (email, username, location, role)
- Boolean fields used in filters (verified)

✅ **Normalization:** Proper separation of concerns:
- User authentication in users table
- Role-specific data in separate tables
- Prevents data redundancy

✅ **Query Optimization:** 
- Use indices for WHERE clauses
- Use JOINS instead of multiple queries
- Use prepared statements to prevent SQL injection

---

## Security Recommendations

1. **Password Storage:** Use PHP's `password_hash()` function, NOT SHA2
2. **Input Validation:** Validate all inputs before INSERT/UPDATE
3. **Prepared Statements:** Always use parameterized queries to prevent SQL injection
4. **Encryption:** Store sensitive data like resume paths securely
5. **Audit Logging:** Consider adding audit logs for sensitive operations

---

## Future Enhancements

- Add `job_listings` table linked to employers
- Add `applications` table linked to job_seekers
- Add `saved_jobs` for job seeker bookmarks
- Add password reset tokens table
- Add activity logs for audit trail
