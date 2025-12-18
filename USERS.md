# Default User Credentials

This project comes with pre-populated data in `database.sql`. All default users share the same password.

**Default Password:** `admin123`

## Admin Users

| Username | Role  | Notes |
| :--- | :--- | :--- |
| `admin` | Admin | Superuser access |

## Teachers

| Reg No | Name | Role |
| :--- | :--- | :--- |
| `TCH1001` | Alice Thompson | Teacher |
| `TCH1002` | Benjamin Carter | Teacher |

## Students

| Reg No | Name | Role | Year |
| :--- | :--- | :--- | :--- |
| `SRM23101` | John Smith | Student | 2 |
| `SRM23102` | Emma Johnson | Student | 2 |
| `SRM23103` | Michael Brown | Student | 1 |
| `SRM23104` | Sarah Davis | Student | 3 |
| `SRM23105` | David Wilson | Student | 2 |
| `SRM23106` | Lisa Anderson | Student | 1 |
| `SRM23107` | Robert Garcia | Student | 3 |
| `SRM23108` | Jennifer Martinez | Student | 2 |

---

## Managing Passwords

### Generating a New Hash

To generate a new password hash for the database, run:

```bash
php scripts/generate_hash.php "your_new_password"
```

Copy the output hash string into `database.sql` or update via SQL query.

### Verifying a Hash

To verify if a password matches a hash:

```bash
php scripts/verify_hash.php "password" "hash_string"
```
