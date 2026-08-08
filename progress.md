# Development Progress

Built in phases per `development_guide.md`. Checkmarks reflect the actual repo state (some later-phase features already exist in the working builder and were preserved).

## Phase by Phase To-Do List

### Phase 1 — Project Architecture ✅
- [x] Folder structure, database schema, ER design
- [x] Models & relationships
- [x] Services / Livewire / Queue / AI / Import structure
- [x] JSON schema structure

Done in this phase: architecture documented in `development_guide.md` (planning only).

### Phase 2 — Database ✅
- [x] Migrations: `forms`, `submissions`, `imports`, `ai_jobs` (+ framework `cache`/`jobs`/`sessions`)
- [x] Foreign keys & indexes
- [x] Models: `Form` (auto-UUID, hasMany Submission/AiJob), `Submission`, `Import`, `AiJob`

Done in this phase: all migrations and models created.

### Phase 3 — Base Layout ✅
- [x] App layout (`components/layouts/app.blade.php`)
- [x] Forms index page (`/forms`)
- [x] Form create page (`/forms/create`)

Done in this phase: base layout + forms listing/creation UI.

### Phase 4 — Form Builder Skeleton ✅ (current)
- [x] Builder page (`/forms/{form}/builder`)
- [x] Three-panel layout
- [x] Left panel: Field Library (`App\Livewire\Builder\Palette`) — lists all `FieldTypes`
- [x] Center panel: Canvas (`App\Livewire\Builder\Canvas`)
- [x] Right panel: Property Panel (`App\Livewire\Builder\PropertyPanel`)
- [x] Each panel is its own Livewire component, wired via events: `field-add`, `field-select`, `field-update`, `field-duplicate`, `field-delete`, `field-selected`

Done in this phase: split the monolithic builder into three panel components. Panels communicate with `App\Livewire\Forms\Builder` (owner of `schema`/`selectedField`) via Livewire events. Canvas uses `#[Reactive]` props so it updates when the parent schema changes. Existing add/save/JSON-preview behavior preserved.

Next: Phase 5 (state management for sections/steps) or Phase 8 (drag & drop via SortableJS).

### Phase 5 — Livewire State Management ✅ (current)
- [x] Selected field state (`selectedFieldId`/`selectedField` in Builder)
- [x] Current section state (`currentSectionId`)
- [x] Current step state (`currentStepId`)
- [x] Array of fields / sections / steps (`schema.steps[].sections[].fields[]`)
- [x] Events between components (`step-*`, `section-*`, `field-*`)

Done in this phase: added `steps` layer above `sections` in the schema (and normalized legacy `{title, sections}` on mount). Builder tracks `currentStepId` + `currentSectionId`; Canvas renders step tabs + section tabs and dispatches `step-selected`/`step-add`/`section-selected`/`section-add`. Selecting a field also focuses its step/section.

### Phase 6 — Click to Add Fields ✅
- [x] Click-to-add from palette (13 field types via `FieldFactory`)
- [x] Auto-generate UUID (`FieldFactory::make()`)
- [x] Auto-generate key (slugified label + 8-char UUID suffix)
- [x] Fields added via `field-add` event into current step/section

Done in this phase: added `heading` type; `FieldFactory` now generates stable `key` per field (`slug_label + uuid8`). Palette buttons use `wire:key`.

### Phase 7 — Property Editor ✅
- [x] Label / placeholder / required (real-time)
- [x] Help text
- [x] Default value
- [x] Options (dropdown / radio / checkbox) — add/remove `[{label, value}]` rows
- [x] Min / Max (number / date / rating)
- [x] Regex (text / textarea / email / phone)
- [x] Validation rules (Laravel rule string)

Done in this phase: extended `PropertyPanel` with all Phase 7 fields. Per-type conditional UI. Updates real-time via `field-update` event (same `updatedField()` hook covers all `field.*` bindings; option add/remove also dispatch explicitly). `FieldFactory` now produces shape with `min`, `max`, `regex`.

### Phase 8 — Drag & Drop ✅
- [x] SortableJS wired (`resources/js/app.js`, bundled by Vite)
- [x] Reorder steps, sections, fields (each container is `[data-sortable="…"]`)
- [x] Livewire state updated via `steps-reorder` / `sections-reorder` / `fields-reorder` events
- [x] Persist ordering via existing `Builder::save()`

Done in this phase: `initSortable()` global wired in `app.js` (idempotent via `_sortableInstance` flag). Canvas blade uses `@script` to re-run `initSortable()` after every Livewire update. New reorder events bubble from Canvas to `App\Livewire\Forms\Builder` and trigger `reorderByIds()` which rewrites the schema array. Ordering is persisted by `Builder::save()`.

### Phase 9 — JSON Schema ✅
- [x] Generate / pretty-print JSON (`JsonEditor` auto-syncs from schema)
- [x] Load JSON (Apply JSON button parses textarea + replaces schema)
- [x] Two-way sync (`#[Reactive] $schema` prop + `userEdited` flag prevents stomping user input)
- [x] Validate JSON (`JSON_THROW_ON_ERROR`)
- [x] Repair invalid JSON (strips trailing commas; if still invalid, surfaces error)
- [x] Pretty-print (`JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE`)

Done in this phase: implemented `App\Livewire\Builder\JsonEditor` (replaces the prior read-only preview in the builder footer). Edit the textarea, hit Apply JSON to push the parsed schema into the parent via the `schema-replace` event; `Forms\Builder::replaceSchema` normalizes the new shape and resets current step/section/selection. Reload button re-syncs from the schema.

### Phase 10 — Database Persistence ✅
- [x] Persist forms (title/description/schema/settings/metadata) via `App\Services\FormService`
- [x] Auto-save (2s debounce after edit, dispatches `content-dirty` → Alpine → `@this.autosave()`)
- [x] Draft support (status enum `draft`/`published`, publish/unpublish actions)
- [x] Version number (`version` column; bumps only when `schema` actually changes)
- [x] Additive migration: `settings`, `metadata`, `version`, `published_at`, `last_saved_at`, soft-deletes
- [x] Soft-delete + `DELETE /forms/{form}` + delete button on index; status filter on index
- [x] `FormFactory`, `tests/Feature/FormPersistenceTest` (8 tests passing)

### Phase 11 — Public Form Rendering ✅
- [x] Dynamic renderer from JSON schema (`App\Livewire\Public\Fill`, route `/forms/{form}/public`)
- [x] Reads schema → renders steps/sections/fields (text, textarea, number, email, phone, date, dropdown, radio, checkbox, rating, heading, section, file)
- [x] Server validation from schema (required, email, numeric min/max, date, in:options, regex, checkbox array)
- [x] Schema `validation` string (`min:5|max:100`) parsed and enforced server-side (Phase 11 refinement)
- [x] Client validation (native HTML5: required, type=email, pattern/min/max, minlength/maxlength from `min:`/`max:` rules)
- [x] Responsive layout (`max-w-3xl`), multi-step tab nav, submit → completion screen
- [x] No AI

### Phase 11.5 — Validation-Rule Integrity (refinement)
- [x] Builder blocks invalid validation rules (`App\Support\ValidationRules::check` + inline error, keeps last valid value)
- [x] Schema is belt-and-suspenders sanitized on save (`FormService::sanitizeSchema`, strips unknown rules / bad params / broken regex)
- [x] `PropertyPanel` coerces array/empty validation to `?string` (no crash on `FieldFactory::make()` default `[]`)
- [x] Tests: `ValidationRulesTest`, `PropertyPanelTest`, `FormPersistenceTest` (save-strips-invalid), `BuilderTest`
- [x] Fixed `sanitizeSchema` reference-loss bug caused by `foreach ($arr ?? [] as &$x)` (the `??` copies the array, breaking the `&` write-back)

### Phase 12 — Form Submission ✅
- [x] Store responses (`App\Services\SubmissionService::store`, captures data/IP/user-agent; `Fill::submit()` now persists)
- [x] Search (LIKE over `data` JSON, debounced) + Pagination (`App\Livewire\Submissions\Index`, route `/forms/{form}/submissions`)
- [x] CSV Export (`SubmissionService::exportCsv` — field labels as headers, search-filtered, temp-file download)
- [x] Validation (Phase 11 server validation reused; shared `App\Support\SchemaFields::answerable` extracted from `Fill`)
- [x] Rate limiting (Laravel `RateLimiter`, per form + IP, limit from `form.settings.rate_limit`, default 5/min)
- [x] Tests: `tests/Feature/SubmissionTest` (4 tests) — store, list/search, CSV, rate limit

Done in this phase: submissions are persisted end-to-end. `forms.index` gained a "Submissions" link; public `Fill` shows a `_form` error on rate-limit rejection. Note: test rollback is broken on this MySQL setup (per-test data persists within a run — AGENTS.md gotcha); `SubmissionTest` scopes queries by `form_id` to stay robust.

### Phase 13 — AI Form Generation ✅
- [x] Queue job (`App\Jobs\GenerateFormJob`, `$tries=3` + backoff, status transitions, `failed()` logs error)
- [x] AI service (`App\Services\AiService` — dispatch + process pipeline)
- [x] Provider interface (`App\AI\Providers\ProviderInterface`; `OpenAIProvider` via Laravel Http, JSON mode)
- [x] Prompt builder (`App\Support\AiPromptBuilder` — schema contract in user prompt)
- [x] Schema validator (`App\Support\SchemaValidator` — validate + repair: strips markdown fences/trailing commas, normalizes steps/sections/fields, slug + dedupes keys)
- [x] Retry logic (job `$tries`/`$backoff`; queue-level)
- [x] Store logs (`ai_jobs` row: prompt/response/model/status/error_message)
- [x] Non-blocking (queued; route `/forms/{form}/ai` polls progress via `wire:poll.2s`)
- [x] Tests: `tests/Feature/AiGenerationTest` (4 tests, FakeProvider swapped via `services.ai.provider` config)

Done in this phase: AI generation is fully queued and non-blocking. Provider is config-driven (class name in `config/services.php`, env `AI_PROVIDER`/`AI_MODEL`/`OPENAI_API_KEY`/`OPENAI_URL` added to `.env.example`). No new Composer deps (built-in HTTP client). `forms.index` gained an "AI" link. A generated schema is saved to the form via `FormService::autosave` when the job completes.

### Phase 13.5 — OpenRouter provider ✅
- [x] `App\AI\Providers\OpenRouterProvider` (OpenAI-compatible gateway; same chat-completions shape, `services.openrouter.*` config, sends `HTTP-Referer`/`X-Title`). No `response_format` — the model catalog varies and `SchemaValidator` repairs output anyway.
- [x] `config/services.php` `openrouter` block; `.env.example` + `.env` vars (`AI_PROVIDER=App\AI\Providers\OpenRouterProvider`, `AI_MODEL=openai/gpt-4o-mini`, `OPENROUTER_API_KEY=…`)
- [x] Tests: `tests/Feature/OpenRouterProviderTest` (fake HTTP — URL, auth header, body; throws on missing content)

### Phase 14 — AI Editing ✅
- [x] Edit queue job (`App\Jobs\EditSchemaJob`, mirrors GenerateFormJob)
- [x] Non-blocking: queued edit, route `/forms/{form}/ai/edit` polls progress via `wire:poll.2s`
- [x] Never regenerates schema — prompt passes existing schema, instructs in-place modification preserving `id`/`key`
- [x] Modify existing JSON (add/remove section & field, translate labels, change validation — all via instruction)
- [x] Return diff (`App\Support\SchemaDiff` — added/removed/modified steps/sections/fields + summary; stored on `ai_jobs.diff`)
- [x] Review screen: edits NOT auto-saved; user reviews diff and clicks Apply (saves via `FormService::autosave`)
- [x] Provider/validation/repair reused from Phase 13 (`AiService::provider`, `SchemaValidator`)
- [x] Migration: `kind` + `diff` columns on `ai_jobs`
- [x] Tests: `tests/Feature/AiEditTest` (4 tests)

Done in this phase: AI editing reuses the Phase 13 pipeline (`EditSchemaJob` → `AiService::processEdit`). Add/remove section & field, translate labels, change validation all work from a single instruction. The edit job computes a structural diff (`SchemaDiff`) and stores it (plus the validated result schema) on the job row; the Edit Livewire page shows the diff and applies on user confirmation — the schema is never auto-overwritten. `forms.index` gained an "AI Edit" link.

### Phase 15 — DOCX Import ✅
- [x] PHPWord extraction (`App\Support\DocxParser` — headings, questions, checkbox glyphs, table options)
- [x] Preview screen + editable mapping (`App\Livewire\Imports\Docx` — edit labels, field type, options; add/remove rows; form title)
- [x] Queue large files (`App\Jobs\ProcessDocxImportJob` → `ImportService::processDocs`; `wire:poll.2s` progress)
- [x] Applies to a new draft Form via `ImportService::buildForm`/`buildSchema` (headings → sections, options → dropdown/radio/checkbox)
- [x] Migration: `form_id` FK on `imports`; `Import` model fillable/casts/relationship
- [x] Route `/imports/docx` + "Import DOCX" button on forms index
- [x] Tests: `tests/Feature/DocxImportTest` (4 tests, real PHPWord-written fixture)

Done in this phase: upload stores the file, creates a pending `Import`, and queues the parse — nothing blocks. The parser reads real Word headings (`w:pStyle` → `Title` element), paragraphs, checkbox lines (`☐`/`[x]`/`( )`), and table rows (col 0 = question, rest = options). The preview shows an editable mapping; "Create Form" builds a schema (sections from headings, fields from questions) and creates a draft form.

### Phase 16 — Excel Import ✅
- [x] Laravel Excel (`App\Support\ExcelReader` — preview via `WithLimit`+`WithChunkReading`, full `rows()`)
- [x] Header row (`hasHeader` toggle skips first row)
- [x] Custom template (`ImportTemplateController` — downloadable `.xlsx` with type/label/required/placeholder/help/options/section/validation)
- [x] Preview (15-row bounded read, letter-column table) + editable mapping (`App\Livewire\Imports\Excel`, auto-detect via `ImportService::detectMapping`)
- [x] Validation (required file, title; at-least-one-label guard; row parsing in `ImportService::buildItemsFromRows`)
- [x] Queue imports (`App\Jobs\ProcessExcelImportJob` → `ImportService::buildSchema`/`buildForm`; `wire:poll.2s` progress)
- [x] Routes `/imports/xlsx` + `/imports/xlsx/template`; "Import XLSX" button on forms index
- [x] Tests: `tests/Feature/ExcelImportTest` (6 tests) — preview bound, auto-mapping, item parsing, job→form, Livewire flow, template download

Done in this phase: full upload→preview→map→queue→build pipeline. Rows become items (headings from `section` column, options from pipe-delimited `options` column), built into a new draft Form. Reuses `ImportService` schema-building shared with DOCX import.

### Phase 17 — Advanced Features ✅
- [x] Undo/redo (per-mutation schema snapshots in `Builder`; undo/redo stacks + toolbar buttons; redo cleared on new edit)
- [x] Autosave (already existed — Phase 10)
- [x] Version history (new `form_versions` table + `FormVersion` model; snapshot recorded on every schema change in `FormService::persist`, capped at 25; `/forms/{form}/versions` page)
- [x] Rollback (`FormService::rollback` restores a snapshot, bumps version, records a new history entry; `Versions` page Restore buttons)
- [x] Conditional logic (field `visibility` `{field, op, value}` with ops `equals`/`not_equals`/`empty`/`not_empty`; `SchemaConditions` helper; builder UI in `PropertyPanel` via `candidateFields` passed on `field-selected`; `Fill` skips validation of hidden fields, strips hidden answers on submit, gated rendering with `wire:model.live`)
- [x] Multi-step forms (already existed — steps in schema + step tabs + renderer nav)
- [x] Template library (`config/form_templates.php` — contact, event, feedback, application; `Create` page cards; falls back to blank on unknown id)
- [x] QR sharing (share modal on forms index: copy link + QR via qrserver.com image API — zero deps)
- [x] Accessibility (native HTML5 labels/required/patterns — inherited from Phase 11)
- [x] Testing (new: `BuilderHistoryTest`, `TemplateLibraryTest`, `ConditionalLogicTest`; fixed stock `ExampleTest` to assert the `/` → `/forms` redirect + index render)
- [x] Docker (already present — `Dockerfile`, `render.yaml`)
- [x] CI (`.github/workflows/tests.yml` — PHP 8.3 + MySQL 8 service, composer install, env from `.env.example` via sed, migrate, `php artisan test`)

Done in this phase: full suite is 60 tests / 214 assertions green.
