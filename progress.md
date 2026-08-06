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

### Phase 11 — Public Form Rendering
- [ ] Dynamic renderer from JSON schema
- [ ] Server + client validation

### Phase 12 — Form Submission
- [ ] Store responses
- [ ] Search / pagination / CSV export / rate limiting

### Phase 13 — AI Form Generation
- [ ] Queue job, AI service, provider interface, prompt builder
- [ ] Schema validator, retry logic, repair malformed JSON

### Phase 14 — AI Editing
- [ ] Add/remove section & field, translate labels, change validation, return diff

### Phase 15 — DOCX Import
- [ ] PHPWord extraction (headings, questions, checkboxes, options)
- [ ] Preview screen, editable mapping, queue large files

### Phase 16 — Excel Import
- [ ] Laravel Excel (header row, template, preview, mapping, validation)
- [ ] Queue imports

### Phase 17 — Advanced Features
- [ ] Undo/redo, autosave, version history, rollback
- [ ] Conditional logic, multi-step forms
- [ ] Template library, QR sharing, accessibility
- [ ] Testing, Docker, CI
