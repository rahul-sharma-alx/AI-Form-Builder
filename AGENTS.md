# AGENTS.md

Laravel 11 + Livewire 3 "AI Form Builder". JSON-driven form builder, built in phases per `development_guide.md`.

## Commands

- `composer dev` — runs `artisan serve` + `queue:listen --tries=1` + `pail` + `vite` concurrently (main dev command)
- `npm run build` / `npm run dev` — Vite assets (input: `resources/css/app.css`, `resources/js/app.js`)
- Tests: `php artisan test` (no `composer test` script)
- `vendor/bin/pint` — formatter available, but repo is not pint-clean; don't reformat whole files

## Environment gotchas

- `.env.example` is stale: it says sqlite, but the real `.env` uses **MySQL** (`edunet_assess`, WAMP 127.0.0.1). Trust `.env`.
- `QUEUE_CONNECTION`/`CACHE_STORE`/`SESSION_DRIVER` = `database` → run migrations (jobs/cache/sessions tables) before serving.
- `phpunit.xml:25-26` has sqlite/`:memory:` **commented out** → tests currently run against the dev MySQL DB. Uncomment to isolate.
- No auth anywhere; all routes are public.

## Architecture

- JSON schema in `forms.schema` (json column, cast to array) is the single source of truth. Shape: `{title, steps: [{id, title, sections: [{id, title, fields: [...]}]}]}` (legacy `{title, sections:[...]}` is auto-normalized to a single step on mount).
- Field types (`app/Support/FieldTypes.php`): text, textarea, number, email, phone, date, dropdown, radio, checkbox, file, heading, rating, section. New fields via `FieldFactory::make()` (auto-UUID + slugified `key` with unique suffix).
- **Builder is split into panels (Phase 4):** `App\Livewire\Forms\Builder` owns `schema`/`currentStepId`/`currentSectionId`/`selectedField` and hosts the three panel components `App\Livewire\Builder\Palette` (field library), `Canvas` (`#[Reactive]` props), `PropertyPanel` (local working copy), `JsonEditor` (`#[Reactive]` schema, two-way sync with `userEdited` guard). Panels talk to the parent via Livewire events: `step-selected`/`step-add`/`section-selected`/`section-add`/`field-add`/`field-select`/`field-update`/`field-duplicate`/`field-delete`/`field-selected`/`schema-replace`/`steps-reorder`/`sections-reorder`/`fields-reorder`.
- Routes (`routes/web.php`): `/forms`, `/forms/create`, `/forms/{form}/builder`.
- Models: `Form` (auto-UUID, hasMany Submission/AiJob), `Submission`, `Import`, `AiJob`.
- `sortablejs` installed and wired (Phase 8): any `[data-sortable="steps|sections|fields"]` element in the canvas is draggable; on drop, JS dispatches `*-reorder` events to Livewire. Initialised by `window.initSortable()` (called from `app.js` on DOMContentLoaded/`livewire:init` and from the canvas blade via `@script`).

## Conventions (from `development_guide.md`)

- No jQuery; AlpineJS only for light interactions.
- Business logic in Services; validation in Form Objects/Livewire rules; no business logic in Blade.
- One responsibility per Livewire component; communicate via Livewire events.
