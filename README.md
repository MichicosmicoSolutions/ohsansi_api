<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
</p>

# Oh Sansi - Laravel Project Api

## Requirements
- Composer version 2.8.5 
- PHP version 7.4.33
- docker lastest
- Docker Compose latest

## Installation
1. Clone the repository:
2. Navigate to the project directory:
3. Run `composer install` to install dependencies.
4. Run `cp .env.example .env` to create a new `.env` file.
5. Update your `.env` file with your database credentials and other configuration settings.
6. Run `docker-compose up -d --build` to start the Docker containers.
7. Run `php artisan key:generate` to generate a new application key.
8. Run `php artisan migrate` to run migrations.
9. Run `php artisan db:seed` to seed the database.
10. Run `php artisan serve` to start the Laravel development server.
11. You can now access your application by navigating to `http:localhost:8000` in your web browser.
