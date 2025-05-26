# ระบบสมัครงาน UBIDS

ระบบสมัครงานออนไลน์ที่พัฒนาด้วย PHP, HTML, CSS และ JavaScript

## คุณสมบัติหลัก

- ระบบสมาชิก (สมัครสมาชิก, เข้าสู่ระบบ)
- ระบบค้นหาตำแหน่งงาน
- ระบบสมัครงานออนไลน์
- ระบบจัดการโปรไฟล์ผู้ใช้
- ระบบจัดการตำแหน่งงานสำหรับบริษัท
- ระบบแอดมิน

## ความต้องการของระบบ

- PHP 7.4 หรือสูงกว่า
- MySQL 5.7 หรือสูงกว่า
- Web Server (Apache/Nginx)

## การติดตั้ง

1. ดาวน์โหลดหรือ Clone โปรเจ็คนี้ไปยังโฟลเดอร์ web server ของคุณ:
```bash
git clone https://github.com/yourusername/job_ubids.git
```

2. สร้างฐานข้อมูลและตารางโดยนำเข้าไฟล์ SQL:
- เปิด phpMyAdmin หรือ MySQL client
- สร้างฐานข้อมูลชื่อ `job_ubids`
- นำเข้าไฟล์ `database/job_ubids.sql`

3. แก้ไขการตั้งค่าการเชื่อมต่อฐานข้อมูลในไฟล์ `config/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'job_ubids');
```

4. ตั้งค่าสิทธิ์การเขียนสำหรับโฟลเดอร์ที่ต้องการ:
```bash
chmod 777 uploads/
```

## การใช้งาน

1. เข้าสู่ระบบด้วยบัญชีแอดมิน:
- Username: admin
- Password: password123

2. สำหรับผู้สมัครงาน:
- สมัครสมาชิกใหม่
- เพิ่มประวัติและทักษะ
- ค้นหาและสมัครงาน

3. สำหรับบริษัท:
- สมัครสมาชิกในฐานะผู้จ้างงาน
- เพิ่มข้อมูลบริษัท
- ประกาศรับสมัครงาน
- จัดการใบสมัคร

## โครงสร้างโฟลเดอร์

```
job_ubids/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── config/
├── database/
├── includes/
├── uploads/
└── index.php
```

## การรักษาความปลอดภัย

- ใช้ PDO สำหรับการเชื่อมต่อฐานข้อมูล
- ป้องกัน SQL Injection
- เข้ารหัสรหัสผ่านด้วย password_hash()
- ตรวจสอบการเข้าถึงหน้าต่างๆ
- ป้องกัน XSS

## ผู้พัฒนา

UBIDS Team

## License

MIT License 