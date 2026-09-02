Laravel 12 + Vuejs + Nodejs + PHP 8.2+
*****************************************************
git clone แล้ว cd เข้าโปรเจกต์,
composer install + npm install
cp .env.example -> .env แล้ว php artisan key:generate
ตั้งค่า DB ใน .env
*****************************************************
นำเข้า Database ที่ส่งให้ หรือ
php artisan migrate:fresh --seed
php artisan storage:link
*****************************************************
composer run dev

การ deploy โปรเจกต์ไปยัง server ให้ทำตามขั้นตอนดังนี้
npm run build
