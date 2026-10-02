# 🐾 Petfolio — Backend

The REST API behind Petfolio, a web app for keeping track of your pets' lives. It handles user accounts, pets, and the events (vaccinations, new tricks, trips and more) that make up each pet's history.

This repository contains the **backend**. The web client lives in a separate repository (see [Related Repositories](#related-repositories)).

## Features

- **Authentication:** user registration and login secured with [Laravel Sanctum](https://laravel.com/docs/sanctum)
- **Pets:** each user can manage their own pets
- **Events:** log milestones such as vaccinations, tricks and trips against each pet
- **Production hosting:** deployed on [Railway](https://railway.com/) with a managed MySQL database

## Tech Stack

| Area | Technology |
| --- | --- |
| Framework | [Laravel 12](https://laravel.com/) |
| Language | [PHP 8.2+](https://www.php.net/) |
| Authentication | [Laravel Sanctum](https://laravel.com/docs/sanctum) |
| Database | [MySQL](https://www.mysql.com/) |
| Build tooling | [Vite](https://vite.dev/) (Laravel default) |
| Hosting | [Railway](https://railway.com/) (built with [Nixpacks](https://nixpacks.com/)) |

## Getting Started

### Prerequisites

- [PHP](https://www.php.net/downloads) 8.2 or later
- [Composer](https://getcomposer.org/) 2
- [MySQL](https://dev.mysql.com/downloads/) running locally (or use [Laravel Sail](https://laravel.com/docs/sail), which provides a MySQL container)
- [Node.js](https://nodejs.org/) and npm (only needed if you use the `composer dev` shortcut below)

### Installation

1. **Clone the repository**

   ```bash
   git clone https://github.com/davidallert/pet_progress_backend.git
   cd pet_progress_backend
   ```

2. **Install dependencies**

   ```bash
   composer install
   ```

3. **Set up your environment file**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure the database**

   Create an empty MySQL database, for example:

   ```sql
   CREATE DATABASE petfolio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

   Then set your connection details in `.env` (see [Environment Variables](#environment-variables)):

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=petfolio
   DB_USERNAME=your_mysql_user
   DB_PASSWORD=your_mysql_password
   ```

5. **Run the migrations**

   ```bash
   php artisan migrate
   ```

6. **Start the development server**

   ```bash
   php artisan serve
   ```

   The API is now available at [http://localhost:8000](http://localhost:8000).

> **Tip:** `composer dev` starts the web server, queue listener, log viewer and Vite together in a single terminal.

### Connecting the frontend

Point the [Petfolio frontend](https://github.com/davidallert/pet_progress_frontend) at this API by setting its API URL to `http://localhost:8000`. Your frontend's origin also needs to be allowed by the backend's CORS configuration (`config/cors.php`).

<!-- TODO: if you use Sanctum's cookie-based SPA auth, document SANCTUM_STATEFUL_DOMAINS here. -->

## Environment Variables

Configuration lives in `.env` (copied from `.env.example`). The most important variables:

| Variable | Description |
| --- | --- |
| `APP_ENV` | Application environment (`local`, `production`) |
| `APP_KEY` | Encryption key, generated with `php artisan key:generate` |
| `APP_DEBUG` | Set to `false` in production |
| `APP_URL` | Public URL of the API |
| `DB_CONNECTION` | Database driver, `mysql` for this project |
| `DB_HOST`, `DB_PORT` | Address of the MySQL server |
| `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | MySQL database name and credentials |

<!-- TODO: add any project-specific variables (e.g. frontend URL, allowed origins). -->

## API Overview

The API is organized around three resources:

- **Users:** register, log in and log out
- **Pets:** belong to a user
- **Events:** belong to a pet (vaccinations, tricks, trips, ...)

## Useful Commands

| Command | Description |
| --- | --- |
| `php artisan serve` | Start the local development server |
| `composer dev` | Run server, queue, logs and Vite together |
| `php artisan migrate` | Run database migrations |
| `php artisan migrate:fresh` | Drop all tables and re-run migrations (**destroys data**) |
| `php artisan route:list` | List all registered routes |
| `composer test` | Run the test suite |
| `./vendor/bin/pint` | Format code with Laravel Pint |

## Deployment

The API is hosted on [Railway](https://railway.com/) with a Railway-provisioned MySQL database. The repository includes a `nixpacks.toml` that tells Railway how to build and start the app.

To deploy your own copy:

1. Create a new Railway project from this GitHub repository.
2. Add a **MySQL** service to the project.
3. In the backend service's **Variables** tab, set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` and an `APP_KEY` (generate one locally with `php artisan key:generate --show`).
4. Connect the database by mapping the MySQL service's variables to Laravel's `DB_*` variables. Railway's variable references make this easy, for example:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=${{MySQL.MYSQLHOST}}
   DB_PORT=${{MySQL.MYSQLPORT}}
   DB_DATABASE=${{MySQL.MYSQLDATABASE}}
   DB_USERNAME=${{MySQL.MYSQLUSER}}
   DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
   ```

   (Replace `MySQL` with the name of your database service if it differs.)
5. Run migrations against the production database on each deploy:

   ```bash
   php artisan migrate --force
   ```

6. Make sure your production frontend URL is allowed in the CORS configuration.

## Roadmap

- [ ] Endpoints to support a per-pet timeline view
- [ ] Social features (sharing pets and milestones with friends)

## Related Repositories

- **Petfolio Frontend:** [davidallert/pet_progress_frontend](https://github.com/davidallert/pet_progress_frontend) ([live demo](https://www.petfolio.se/))

## Author

**David Allert**: [@davidallert](https://github.com/davidallert)