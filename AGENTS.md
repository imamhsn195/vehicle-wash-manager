# AGENTS.md

## Cursor Cloud specific instructions

Vehicle Wash Manager is a single Laravel 13 + Filament v3 web app (PHP 8.3, SQLite by
default, Vite 8 + Tailwind 4 for assets). The admin panel is the whole product and lives
at `http://localhost:8000/admin` (login at `/admin/login`).

### Git / PR workflow (required)

- Every feature or fix goes on a branch named `cursor/<descriptive-name>-7c7f`.
- Open a PR into `main` for that branch (do not leave work only on a long-lived feature branch).
- Prefer **delete branch after merge**. After a PR is merged, delete the remote feature
  branch if it still exists: `git push origin --delete <branch-name>`.
- Do not keep merged feature branches around unless the user asks to keep them.

### Services

- Web app: `php artisan serve --host=0.0.0.0 --port=8000` (serves the Filament admin +
  Livewire pages). This is the only service required for end-to-end use.
- Database: SQLite file at `database/database.sqlite` (created during setup). No external DB
  needed. `DB_CONNECTION=pgsql` is a documented prod alternative only.
- Frontend assets: pre-built into `public/build` via `npm run build`. Serving already-built
  assets is enough; you do NOT need Vite running for the UI to be styled. For live asset
  editing use `npm run dev` (Vite on port 5173), or `composer dev` to run server + queue +
  logs + Vite together.
- Queue worker, scheduler (`php artisan schedule:work`), Redis, SMTP/Mailpit are all
  OPTIONAL. Mail uses the `log` driver by default, so `php artisan wash:daily-summary`
  writes to `storage/logs/laravel.log` rather than sending real email.

### Non-obvious notes

- The dev environment (PHP 8.3 + extensions, Composer) is baked into the VM snapshot; the
  startup update script only refreshes `composer`/`npm` dependencies. It does NOT run
  migrations or build assets.
- If you pull new migrations or reset data, re-run `php artisan migrate:fresh --seed` and,
  after any change to `resources/`, `npm run build` (or run `npm run dev`). Reinstalling
  Composer/npm deps is not picked up by an already-running `php artisan serve` — restart it.
- `.env` and `database/database.sqlite` are gitignored; they persist via the snapshot, not
  git. If `.env` is missing, run `cp .env.example .env && php artisan key:generate` and
  `touch database/database.sqlite` before migrating.

### Commands

- Lint (PHP): `./vendor/bin/pint` (check only: `./vendor/bin/pint --test`). No `pint.json`,
  so it uses Laravel defaults; the existing code has pre-existing style deviations.
- Tests: `php artisan test` (or `composer test`) — PHPUnit, 79 tests, uses an in-memory
  SQLite test DB (see `phpunit.xml`).
- Build assets: `npm run build`. Dev assets: `npm run dev`.

### Demo logins (from seed data)

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@carwash.test | password |
| Site Manager | karim@carwash.test | password |
| Accountant | accountant@carwash.test | password |
| Partner | partner1@carwash.test | password |
| Staff | staff1@carwash.test | password |
