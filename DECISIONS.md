# DECISIONS.md

Architecture and design decisions for the AI Form Builder. Kept as a decision record so future work has a reference for *why* things are shaped the way they are.

## ADR-001 — JSON schema is the single source of truth

- **Status:** Accepted
- **Context:** The builder, public renderer, submissions, AI, and imports all need to agree on form structure.
- **Decision:** The full form structure lives in `forms.schema` (JSON column, cast to array) as `{title, steps: [{id, title, sections: [{id, title, fields: [...]}]}]}`. Legacy `{title, sections:[...]}` is auto-normalized to a single step on mount.
- **Consequences:** One definition to render and validate against; no separate tables for steps/sections/fields. This is what makes AI generation and DOCX/XLSX import trivially "write a schema".

## ADR-002 — Builder is split into panels, not one monolith

- **Status:** Accepted
- **Context:** The original builder was a single monolithic Livewire component; it grew hard to maintain.
- **Decision:** `App\Livewire\Forms\Builder` owns `schema`/`currentStepId`/`currentSectionId`/`selectedField` and hosts four panel components: `Palette`, `Canvas` (`#[Reactive]` props), `PropertyPanel` (local working copy), `JsonEditor` (`#[Reactive]` schema). Panels communicate only via Livewire events (`field-add`, `field-select`, `field-update`, `steps-reorder`, `sections-reorder`, `fields-reorder`, `schema-replace`, …).
- **Consequences:** One responsibility per component. Parent owns state, panels are stateless views. JSON editor needs a `userEdited` guard so reactive schema sync doesn't stomp in-progress typing.

## ADR-003 — Business logic in Services, validation in rules, no logic in Blade

- **Status:** Accepted
- **Context:** Kept bloat out of Livewire components and blade.
- **Decision:** `FormService`, `SubmissionService`, `AiService`, `ImportService` hold business logic. Validation lives in Livewire `rules()` / `ValidationRules` / `SchemaValidator`. Blade renders only.
- **Consequences:** Components stay thin; logic is unit-testable.

## ADR-004 — Schema validation/repair is centralized, applied at every boundary

- **Status:** Accepted
- **Context:** Invalid schemas arrive from AI output, raw JSON editing, and imports.
- **Decision:** `App\Support\SchemaValidator` normalizes, validates, and repairs (strips markdown fences/trailing commas, normalizes steps/sections/fields, slug + dedupes keys). `FormService::sanitizeSchema` strips unknown validation rules / bad params / broken regex on save. `ValidationRules::check` blocks invalid rules in the property panel.
- **Consequences:** The DB never holds a schema that can't render. Note: avoid `foreach ($arr ?? [] as &$x)` — the `??` copies the array and silently breaks the `&` write-back (fixed bug).

## ADR-005 — AI is non-blocking and never auto-applies destructive edits

- **Status:** Accepted
- **Context:** Requests must not block on LLM latency; AI output is unreliable; edits must be safe.
- **Decision:** Generation and editing run as queued jobs (`GenerateFormJob`, `EditSchemaJob`), polled via `wire:poll.2s`. Providers are pluggable behind `ProviderInterface` (`OpenAI`, `OpenRouter`, `Gemini`) and swapped via `config/services.php` + env. **Editing** passes the existing schema and instructs in-place modification (preserving ids/keys); the job stores a structural diff (`SchemaDiff`) and the result is applied only after a human clicks Apply on the review screen.
- **Consequences:** Generation saves automatically on completion; editing requires explicit confirmation. Zero new Composer deps — Laravel's built-in HTTP client is used.

## ADR-006 — Field types & factory-driven creation

- **Status:** Accepted
- **Context:** 13 field types, each with its own shape and property panel.
- **Decision:** `App\Support\FieldTypes` enumerates the types; `FieldFactory::make()` creates a field with auto-UUID and a stable slugified `key` + unique suffix. `FieldFactory` is the single place a new field type is defined.
- **Consequences:** Adding a type = touching one class. Renderer and builder agree because both read the schema.

## ADR-007 — SortableJS for drag-and-drop, no jQuery

- **Status:** Accepted
- **Context:** Reordering steps/sections/fields with Livewire state sync.
- **Decision:** `sortablejs` wired globally in `resources/js/app.js` via `initSortable()` (idempotent). Any `[data-sortable="steps|sections|fields"]` element is draggable; on drop, JS dispatches `*-reorder` events to Livewire, which rewrites the schema array (`reorderByIds`). Canvas blade re-runs `initSortable()` via `@script` after every Livewire update.
- **Consequences:** Ordering is persisted through the normal save path. AlpineJS remains the only other client-side tool — no jQuery anywhere.

## ADR-008 — Version history as capped snapshots

- **Status:** Accepted
- **Context:** Rollback required, but full event sourcing was overkill.
- **Decision:** A snapshot is written to `form_versions` on every schema change in `FormService::persist`, capped at 25 per form. `FormService::rollback` restores a snapshot, bumps `version`, and records a new history entry. The `version` column bumps only when the schema actually changes.
- **Consequences:** Simple, understandable, bounded storage. Add pruning if long-lived forms need more history.

## ADR-009 — Conditional logic via a field-level `visibility` rule

- **Status:** Accepted
- **Context:** Show/hide fields based on other fields without a full rules engine.
- **Decision:** Each field may carry `visibility: {field, op, value}` with ops `equals`/`not_equals`/`empty`/`not_empty`. `SchemaConditions` resolves it; `Fill` skips validation of hidden fields, strips hidden answers on submit, and gates rendering via `wire:model.live`. Builder UI in `PropertyPanel` uses `candidateFields` passed on `field-selected`.
- **Consequences:** Covers the common cases with a tiny model. No expression language to maintain.

## ADR-010 — Imports share one schema-building pipeline

- **Status:** Accepted
- **Context:** DOCX and XLSX both end at "preview → mapping → build a form".
- **Decision:** Both parsers produce normalized items; `ImportService` turns items into a schema (`buildSchema`) and a draft form (`buildForm`). Parsing of large files is queued; previews are bounded (`WithLimit`/`WithChunkReading`, 15 rows). XLSX has a downloadable template.
- **Consequences:** Adding a new import source = write a parser, reuse `ImportService`.

## ADR-011 — No auth, all routes public (by design, for now)

- **Status:** Accepted
- **Context:** The product phase is form building/rendering; the builder and public fill share the same app.
- **Decision:** All routes are public. `Fill` is rate-limited per form + IP (`form.settings.rate_limit`, default 5/min).
- **Consequences:** Anyone with the builder URL can edit forms. Deferred to a later phase — revisit with Laravel auth / policies before any public deployment.

## ADR-012 — Tests run against the configured DB

- **Status:** Accepted (local convenience)
- **Context:** `phpunit.xml:25-26` has `sqlite/:memory:` commented out.
- **Decision:** Tests currently run against the dev MySQL DB. Test queries are scoped by `form_id` to stay robust despite per-test rollback quirks on this MySQL setup.
- **Consequences:** Isolate via `sqlite/:memory:` before relying on test isolation in CI. CI (`.github/workflows/tests.yml`) spins up a clean MySQL 8 service, so it is not affected.
