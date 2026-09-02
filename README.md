Laravel 12 + Vuejs + Nodejs + PHP 8.2+
*****************************************************
** การติดตั้งโปรเจกต์ <br>
git clone แล้ว cd เข้าโปรเจกต์ <br>
composer install + npm install <br>
cp .env.example -> .env แล้ว php artisan key:generate <br>
ตั้งค่า DB ใน .env <br>
*****************************************************
นำเข้า Database ที่ส่งให้ หรือ <br>
<br>
php artisan migrate:fresh --seed <br>
php artisan storage:link <br>
*****************************************************
composer run dev
*****************************************************
** การ deploy โปรเจกต์ไปยัง server ทำเหมือนการติดตั้งโปรเจกต์ หรือจะทำตามขั้นตอนนี้ก็ได้ <br>
npm run build <br>
โยนไฟล์ขึ้น server <br>
ตั้งค่า .env ให้เหมือนกับเครื่อง local <br>
