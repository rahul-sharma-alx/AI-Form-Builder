# AI Form Builder

A production-style SaaS **form builder** built with **Laravel 11** and **Livewire 3**. Build multi-step, multi-section forms from a visual drag-and-drop canvas, generate or edit them with AI, import from DOCX/XLSX, and collect/search/export submissions — all driven by a single JSON schema.

### Live Demo
👉 [AI Form Builder](https://ai-form-builder-yqb1.onrender.com/forms)

## Features

**Form Builder**
- Three-panel builder: field palette, drag-and-drop canvas, property inspector (SortableJS, no jQuery)
- 13 field types: text, textarea, number, email, phone, date, dropdown, radio, checkbox, file, heading, rating, section
- Multi-step / multi-section forms with reorderable steps, sections, and fields
- Real-time property editing: label, placeholder, help text, required, default, options, min/max, regex, validation rules
- Conditional visibility rules (`{field, op, value}` with `equals` / `not_equals` / `empty` / `not_empty`)
- Undo/redo with per-mutation snapshots

**JSON Schema**
- JSON is the single source of truth (`forms.schema`)
- Raw JSON editor with two-way sync, pretty-print, validation, and auto-repair of malformed JSON
- Server-side validation-rule integrity checks (invalid rules blocked in builder, sanitized on save)

**Persistence & History**
- Auto-save (debounced) with draft/publish status
- Version history (`form_versions`, capped snapshots) with one-click rollback
- Soft deletes

**Public Facing**
- Dynamic public renderer from the schema (responsive, multi-step tab nav)
- Server-side validation derived from the schema + native HTML5 client validation
- Rate limiting (per form + IP) and completion screen

**Submissions**
- Persisted responses with IP / user-agent capture
- Search (debounced JSON LIKE) + pagination
- CSV export

**AI**
- Non-blocking, queued **form generation** from a natural-language prompt
- Queued **AI editing** of the existing schema (add/remove sections & fields, translate labels, change validation) with a review screen and structural diff — never auto-overwrites
- Pluggable providers: **OpenAI**, **OpenRouter**, **Gemini** (via `services.ai.provider` config)
- Schema validator + repair pipeline (strips markdown fences, trailing commas, dedupes keys)

**Imports**
- **DOCX** import (PHPWord): parses headings, questions, checkboxes, table options → editable preview/mapping
- **XLSX** import (Laravel Excel): header row, custom template download, bounded preview, auto-mapping, queued processing

**Extras**
- Template library (contact, event, feedback, application)
- QR code sharing for form links (zero-dependency image API)
- Dark-mode-aware base layout, AlpineJS-only light interactions
- Docker + Render config, GitHub Actions CI
- 60 tests / 214 assertions

## Tech Stack

| Layer    | Tech |
|----------|------|
| Backend  | Laravel 11, PHP ^8.2 (developed on 8.3) |
| Frontend | Livewire 3, AlpineJS, TailwindCSS, Vite |
| Database | MySQL (local), Postgres (Render) |
| Queue    | Database driver (`QUEUE_CONNECTION=database`) |
| Packages | `livewire/livewire`, `maatwebsite/excel`, `phpoffice/phpword`, `predis/predis` |

## Getting Started

### Requirements

- PHP ^8.2 with Composer
- Node.js + npm
- MySQL (or use the Dockerfile / Render config)

### Installation

```bash
# 1. Clone & install dependencies
git clone <repo-url> edunett-assess
cd edunett-assess
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Configure .env — the app expects MySQL by default:
#    DB_CONNECTION=mysql
#    DB_HOST=127.0.0.1
#    DB_PORT=3306
#    DB_DATABASE=edunet_assess
#    DB_USERNAME=root
#    DB_PASSWORD=
#    QUEUE_CONNECTION=database
#    CACHE_STORE=database
#    SESSION_DRIVER=database

# 4. Create the database and run migrations
php artisan migrate

# 5. Build assets
npm run build

# 6. Run
composer dev        # serve + queue listener + pail + vite, all at once
```

> `composer dev` runs `artisan serve`, `queue:listen --tries=1`, `pail` (logs), and `npm run dev` concurrently. The database-backed queue/cache/session drivers require the migrations from step 4 to exist.

### AI Setup (optional)

Only needed for the AI generate / AI edit features. Pick a provider in `.env`:

```dotenv
# OpenAI
AI_PROVIDER=App\AI\Providers\OpenAIProvider
AI_MODEL=gpt-4o-mini
OPENAI_API_KEY=

# or OpenRouter
AI_PROVIDER=App\AI\Providers\OpenRouterProvider
AI_MODEL=openai/gpt-4o-mini
OPENROUTER_API_KEY=

# or Gemini
AI_PROVIDER=App\AI\Providers\GeminiProvider
GEMINI_API_KEY=
```

## Routes

| Route | Name | Description |
|-------|------|-------------|
| `/` | – | Redirects to `/forms` |
| `/forms` | `forms.index` | Form list (search, status filter, delete, share/QR) |
| `/forms/create` | `forms.create` | Create a form (from blank or a template) |
| `/forms/{form}/builder` | `forms.builder` | Visual builder |
| `/forms/{form}/versions` | `forms.versions` | Version history + rollback |
| `/forms/{form}/submissions` | `forms.submissions` | Submissions list, search, CSV export |
| `/forms/{form}/ai` | `forms.ai` | AI generate (queued) |
| `/forms/{form}/ai/edit` | `forms.ai.edit` | AI edit + diff review |
| `/forms/{form}/public` | `forms.public` | Public fill-in page |
| `/imports/docx` | `imports.docx` | DOCX import |
| `/imports/xlsx` | `imports.xlsx` | XLSX import |
| `/imports/xlsx/template` | `imports.xlsx.template` | Download XLSX import template |
| `/settings` | `settings.index` | Settings |
| `DELETE /forms/{form}` | `forms.destroy` | Soft-delete a form |

## JSON Schema

The schema is the single source of truth, stored in `forms.schema` (JSON column, cast to array):

```json
{
  "title": "My Form",
  "steps": [
    {
      "id": "step-uuid",
      "title": "Step 1",
      "sections": [
        {
          "id": "section-uuid",
          "title": "Contact",
          "fields": [
            {
              "id": "field-uuid",
              "type": "text",
              "key": "full_name_8f3a2c1d",
              "label": "Full name",
              "placeholder": "",
              "help": "",
              "required": true,
              "default": "",
              "min": null,
              "max": null,
              "regex": null,
              "validation": "min:2|max:100",
              "options": [],
              "visibility": null
            }
          ]
        }
      ]
    }
  ]
}
```

Legacy `{title, sections: [...]}` schemas are auto-normalized to a single step on mount.

## Project Structure

```
app/
├── AI/Providers/           # ProviderInterface + OpenAI / OpenRouter / Gemini
├── Exports/                # ImportTemplate Excel export
├── Http/Controllers/       # ImportTemplateController
├── Jobs/                   # GenerateForm, EditSchema, ProcessDocx/ExcelImport
├── Livewire/
│   ├── Forms/              # Index, Create, Builder, Versions
│   ├── Builder/            # Palette, Canvas, PropertyPanel, JsonEditor
│   ├── Public/             # Fill (public renderer)
│   ├── Submissions/        # Index
│   ├── Ai/                 # Generate, Edit
│   ├── Imports/            # Docx, Excel
│   └── Settings/           # Index
├── Models/                 # Form, Submission, AiJob, Import, FormVersion
├── Services/               # FormService, SubmissionService, AiService, ImportService
└── Support/                # FieldTypes, FieldFactory, SchemaValidator, SchemaDiff,
                            # SchemaConditions, DocxParser, ExcelReader, ValidationRules…
config/form_templates.php    # Template library (contact, event, feedback, application)
database/migrations/         # 11 migrations (framework + forms/submissions/ai_jobs/imports/form_versions)
```

**Builder architecture:** `App\Livewire\Forms\Builder` owns `schema`, `currentStepId`, `currentSectionId`, and `selectedField`. It hosts the four panel components — `Palette`, `Canvas` (`#[Reactive]` props), `PropertyPanel` (local working copy), `JsonEditor` (`#[Reactive]` schema) — which talk back through Livewire events (`field-add`, `field-select`, `field-update`, `steps-reorder`, `schema-replace`, …).

## Testing

```bash
php artisan test
```

The suite is 60 tests / 214 assertions, covering form persistence, public fill-in validation, submissions (store/search/CSV/rate-limit), AI generation & editing (faked providers), DOCX/XLSX imports, undo/redo history, templates, and conditional logic.

> Note: `phpunit.xml` ships with `sqlite/:memory:` commented out, so tests run against your configured DB. Uncomment those lines to isolate tests in-memory.

## Deployment

- **Docker** — `Dockerfile` included
- **Render.com** — `render.yaml` wires a web service + Postgres; set `QUEUE_CONNECTION=sync` on free tier (no worker) or run a background worker with `database` queue
- **CI** — `.github/workflows/tests.yml` runs the full suite on PHP 8.3 + MySQL 8

## License

MIT — see [LICENSE](https://opensource.org/licenses/MIT).
