# Backend 19 Project

ระบบจัดการห้องและการจอง (Room & Booking Management System)

## สิ่งที่ต้องเตรียม (Requirements)
- PHP >= 8.1
- Composer
- Node.js & NPM
- Database (MySQL/MariaDB)

## ขั้นตอนการติดตั้ง (Installation Steps)

1. **Clone Project**
   ```bash
   git clone <repository-url>
   cd backend-19
   ```

2. **ติดตั้ง Library ต่างๆ**
   ```bash
   # Install PHP dependencies
   composer install

   # Install Frontend dependencies
   npm install
   ```

3. **ตั้งค่า Environment**
   คัดลอกไฟล์ `.env.example` เป็น `.env`
   ```bash
   cp .env.example .env
   ```
   จากนั้นเปิดไฟล์ `.env` และแก้ไขค่า Database ให้ตรงกับเครื่องของคุณ:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ชื่อฐานข้อมูลของคุณ
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Generate App Key**
   ```bash
   php artisan key:generate
   ```

5. **สร้างตารางในฐานข้อมูล (Migration)**
   ```bash
   php artisan migrate
   ```

## การจำลองข้อมูลห้อง (Seeding Rooms)

ระบบได้เตรียมข้อมูลห้องตัวอย่างไว้ให้ (ห้องเรียนชั้น 2-5 รวม 24 ห้อง)
สามารถรันคำสั่งต่อไปนี้เพื่อเพิ่มข้อมูลห้องลงในฐานข้อมูล:

```bash
php artisan db:seed
```
*คำสั่งนี้จะทำการเรียก `DatabaseSeeder` ซึ่งจะเรียก `RoomSeeder` ให้โดยอัตโนมัติ*

หากต้องการ Seed เฉพาะตารางห้อง (ในกรณีที่มีข้อมูลอื่นแล้ว):
```bash
php artisan db:seed --class=RoomSeeder
```

## การเริ่มต้นใช้งาน (Running the Application)

เปิด 2 Terminal เพื่อรันระบบ:

Terminal 1 (Laravel Server):
```bash
php artisan serve
```

Terminal 2 (Vite/Frontend):
```bash
npm run dev
```

เข้าใช้งานได้ที่: http://localhost:8000
