<h1 align="center">CSV Import App for YoPrint</h1>

https://github.com/wakjoko/csv-import-demo/assets/8953339/e5d2a39a-dbe2-4055-be30-1a1d46872beb

## Introduction
This app demonstrate importing CSV multiple files into database simultaneously.

## Import Process
As soon as a csv file is uploaded, a batch job will be created to parse and create multiple queue jobs with each job will upsert 1000 lines from csv file to `products` table.
When any of the queued job within a batch job has failed, the uploaded csv file will be marked as `failed`.
`completed` status will be given only when all queued job of a batch is successful.

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
- Start the app `php artisan serve`
- Start the horizon in separate terminal `php artisan horizon`

## Tech stack
- [**Laravel 10**](https://laravel.com/docs/10.x)
- [**Vue 3**](https://devdocs.io/vue~3)
- [**Bootstrap 5**](https://getbootstrap.com/docs/5.3/getting-started/introduction)

## License
This application is licensed under the [MIT license](http://opensource.org/licenses/MIT).
