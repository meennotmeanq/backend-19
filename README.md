# Backend 19 Project (Room & Booking Management System)

ระบบจัดการห้องและการจอง พัฒนาด้วยเฟรมเวิร์ก **Laravel** ถูกออกแบบมาให้มีความยืดหยุ่นสูง รองรับการจองห้องหลายช่วงเวลาและการจัดการทรัพยากรห้องเรียนได้อย่างสมบูรณ์แบบ แผงผู้ดูแลระบบสามารถตรวจสอบและออกรายงานภาพรวมได้ทันที

## ฟีเจอร์ที่สำคัญ (Key Features)

- **ระบบการจัดการห้อง (Room Management):** เปิด/ปิดการใช้งานห้อง, เพิ่มห้องเรียนแบบกำหนดตึกและชั้น, และระบุความจุได้
- **ระบบการจองแบบคู่ขนาน (Advanced Bookings):**
  - **จองรายวัน (Single):** เลือกจองวันเดียวได้หลายคาบเวลา
  - **จองแบบกลุ่ม (Group):** จองหลายวัน หลายเวลาพร้อมกันสูงสุด 3 วัน โดยได้รับเป็น **รหัสจองเดียวกัน (Booking ID: `Book-ID: XXXXXX`)**
- **ตารางเวลาวันหยุดสุดสัปดาห์ (Weekend Time Slots):** รองรับคาบเรียนพิเศษสำหรับวันเสาร์-อาทิตย์ พร้อมตารางคำนวณแยกต่างหาก
- **ผู้ดูแลระบบและสถิติ (Admin Dashboard):** กระดานสรุปสถานะการใช้งานห้องเรียน, ห้องยอดฮิต, และรายงานการเข้าใช้งานจริง (Attended/Missed) สำหรับผู้ใช้งาน
- **ระบบพิมพ์เอกสาร (PDF Export):** ประมวลผลเอกสารรายงานและผลสำรวจให้เป็น PDF พร้อมฝังฟอนต์ภาษาไทย (Sarabun) สมบูรณ์ 100% 
- **ตัวกำหนดการจองล่วงหน้า (Max Advance Booking Limit):** รองรับการปรับตั้งค่าไม่ให้ผู้อื่นสามารถกดจองทะลุวันที่ระบุไว้ (บังคับใช้ทุกช่วงหน้าปฏิทินของทุกระดับชั้นผู้ใช้)
- **ระบบการแจ้งเตือนทางอีเมล (Email Notifications):** สามารถแจ้งเตือนข้อมูลการทำรายการ การอนุมัติและการยกเลิก ผ่าน Email ทันที

---

## สิ่งที่ต้องเตรียม (Requirements)
- PHP >= 8.1
- Composer
- Node.js & NPM
- Database (MySQL/MariaDB)

---

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
   *(หมายเหตุ: ระบบมีการใช้งานไลบรารีเสริม เช่น `barryvdh/laravel-dompdf` สำหรับสร้าง PDF)*

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

---

## การจำลองข้อมูล (Database Seeding)

ระบบได้เตรียมข้อมูลห้องตัวอย่างไว้ให้ (ห้องเรียนชั้น 2-5 รวม 24 ห้อง) รวมไปถึงการตั้งค่าเริ่มต้นอื่นๆ
สามารถรันคำสั่งต่อไปนี้เพื่อจำลองข้อมูลระบบ:

```bash
php artisan db:seed
```
*คำสั่งนี้จะทำการเรียก `DatabaseSeeder` ซึ่งจะเรียกคำสั่งเตรียมตารางอื่นๆ ให้อัตโนมัติ*

หากต้องการ Seed เฉพาะตารางห้อง:
```bash
php artisan db:seed --class=RoomSeeder
```

---

## การเริ่มต้นใช้งาน (Running the Application)

เพื่อให้ระบบทำงานได้ครบสมบูรณ์ทั้งหลังบ้านและหน้าบ้าน (Tailwind/Bootstrap/Vue/Vite) ควรเปิด 2 Terminal เพื่อรันระบบพร้อมกัน:

**Terminal 1 (Laravel Server):**
```bash
php artisan serve
```

**Terminal 2 (Vite/Frontend):**
```bash
npm run dev
```

เข้าใช้งานได้ที่ URL: http://localhost:8000
