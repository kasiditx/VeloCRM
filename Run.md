รันโปรเจกต์นี้
โปรเจกต์นี้เป็น Laravel + Vite ดังนั้นขั้นตอนทั่วไปคือ:

เปิด terminal ที่โฟลเดอร์โปรเจกต์
ติดตั้ง PHP dependency:
composer install
ติดตั้ง Node dependency:
npm install
เตรียมไฟล์ environment:
cp .env.example .env
สร้าง app key:
php artisan key:generate
ถ้ามีฐานข้อมูลให้รัน migration:
php artisan migrate
เริ่มเซิร์ฟเวอร์
ตัวเลือกที่ใช้บ่อย:

รัน Laravel backend:

php artisan serve
รัน frontend Vite:

npm run dev
ถ้าต้องการรันทั้งสองพร้อมกันสามารถใช้:

composer dev
