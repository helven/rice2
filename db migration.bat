@echo off
php artisan migrate:fresh --seed
php artisan db:seed --class=DriverSeeder
php artisan db:seed --class=CustomerSeeder 
php artisan db:seed --class=MealSeeder
pause