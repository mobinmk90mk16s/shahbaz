# Shahbaz database expansion patch

Contains one additive Laravel migration, Eloquent models/relationships for new commerce entities, and a deployment guide.

Read `docs/DATABASE-COMPLETE-SCHEMA.md` before running the migration. Back up the database, confirm migration status, and run `php artisan migrate` only. Do not run `migrate:fresh`.

This is a patch for the existing Laravel project, not a standalone Laravel application. Copy/merge the included `app/`, `database/`, and `docs/` folders into the project root, preserving `.env` and all existing files.
