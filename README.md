# Mangie

<p align="center">
  <img src="app/public/favicon.svg" width="88" height="88" alt="Mangie logo">
</p>

<p align="center">
  A collaborative workspace for planning backlogs, running sprints and moving work forward.
</p>

<p align="center">
  <a href="https://mangie.vaell.dev"><strong>Open Mangie</strong></a>
</p>

## About

Mangie is a full-stack agile project management application. It combines a
product backlog, visual sprint boards and detailed task planning in one shared
workspace, with real-time updates for everyone on a board.

The application lives in [`app/`](app/).

## Features

- Drag-and-drop task movement within a board and between backlog sprints
- Real-time task, column and board updates with private board channels
- Multiple active sprints, sprint goals and burndown charts
- Board-specific backgrounds, colours and label sets
- Reusable label libraries with custom label colours
- Task assignees, priorities, story points, time logs, dates and sections
- Checklists with individually completable items
- Shared boards, participant avatars and user profile photos
- Per-user filtering and sorting preferences
- Responsive list and card views with light and dark themes

## Tech stack

| Layer | Technology |
| --- | --- |
| Application | Laravel 13, PHP 8.5 |
| UI | Blade, Tailwind CSS, Alpine.js |
| Real-time | Laravel Reverb, Laravel Echo |
| Data | MySQL in production, SQLite for local development/tests |
| Frontend build | Vite |
| Testing | Pest |
| Deployment | Docker Compose, Apache, Nginx, Cloudflare |

## Local development

Requirements:

- PHP 8.5 with the extensions required by Laravel
- Composer
- Node.js and npm
- SQLite

Clone the repository and install the application dependencies:

```bash
git clone https://github.com/Vael2196/Mangie.git
cd Mangie/app

composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan storage:link
```

Create the initial administrator, project and product backlog:

```bash
php artisan mangie:bootstrap
```

The command asks for the name, email address and password of the first Mangie
administrator. It is intended for an empty database.

Run the application, frontend compiler and real-time server in separate
terminals:

```bash
php artisan serve
```

```bash
npm run dev
```

```bash
php artisan reverb:start
```

The default local application URL is
[`http://127.0.0.1:8000`](http://127.0.0.1:8000).

## Tests

From `app/`, run:

```bash
php artisan test
npm run build
```

## Production

Production container definitions are included in
[`app/compose.production.yaml`](app/compose.production.yaml). Copy
`app/.env.production.example` to `app/.env.production`, provide real secrets,
and never commit the resulting environment file.

The server-specific deployment runbook is intentionally kept outside Git so
that operational details remain local to the deployment environment.

## Project structure

```text
Mangie/
├── app/                    Laravel application
│   ├── app/                Controllers, models, events and services
│   ├── database/           Migrations, factories and seeders
│   ├── resources/          Blade views, JavaScript and CSS
│   ├── routes/             HTTP and broadcast channel routes
│   └── tests/              Pest feature and unit tests
└── README.md
```

## Author

Built by [Vadim Filyakin](https://github.com/Vael2196).
