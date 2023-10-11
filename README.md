<h1 align="center">CSV Import App for YoPrint</h1>

https://github.com/wakjoko/yoPrint-assesment/assets/8953339/2bc6a96c-2d92-49ad-bfba-917f329d7542

## Introduction
This app demonstrate importing CSV files into database based on specs listed in assessment docs prepared by Anbin Muniandy of YoPrint for Laravel Engineer Application Coding Assignment.

## Purpose
Apart from adhering to the specs requirements, this app was created primarily to show an example how to write testable codes and the use of best practices to minimize technical debt.

## Installation
Basic steps:
- Clone this repository `git clone https://github.com/wakjoko/yoPrint-assesment.git`
- Change directory `cd yoPrint-assesment`
- Install app dependencies `composer install` and `npm install`
- Copy environment file `cp .env.example .env`
- Set the db connection in `.env`
- Generate the app key `php artisan key:generate`
- Run db migration `php artisan migrate`
- Compile frontend assets `npm run build`
- Start the app in separate terminal `php artisan serve`
- Start the horizon in separate terminal `php artisan horizon`

## Tech stack
- [**Laravel 10**](https://laravel.com/docs/10.x)
- [**Vue 3**](https://devdocs.io/vue~3)
- [**Bootstrap 5**](https://getbootstrap.com/docs/5.3/getting-started/introduction)

## License
This application is licensed under the [MIT license](http://opensource.org/licenses/MIT).
