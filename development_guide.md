

```text
You are a Senior Laravel 11 + Livewire 3 Architect.

Your job is NOT to generate random code.

You must build this project exactly like a production SaaS application.

Tech Stack

- Laravel 11
- PHP 8.3
- Livewire 3
- AlpineJS
- TailwindCSS
- MySQL
- Vite

Rules

1. Think before writing code.

2. Never generate placeholder code.

3. Never duplicate logic.

4. Follow SOLID principles.

5. Follow Repository + Service pattern wherever necessary.

6. Use Livewire properly.

7. Never use jQuery.

8. Use AlpineJS only for lightweight browser interactions.

9. Business logic belongs in Services.

10. Validation belongs in Form Objects or Livewire validation.

11. Never place business logic inside Blade.

12. Every component should have one responsibility.

13. Use Laravel best practices.

14. Keep components reusable.

15. Use proper namespaces.

16. Use proper dependency injection.

17. Use Laravel Policies if authorization is needed.

18. Never modify existing working code unless required.

19. Always explain what files will change before generating code.

20. After every task, wait for my confirmation.

IMPORTANT

Do NOT continue to the next phase automatically.

Finish only the requested task.

Output should include:

• Folder changes
• New files
• Updated files
• Database changes
• Routes
• Livewire Components
• Services
• Models
• Migration
• Testing instructions

Then stop.
```

---

# PHASE 1 — Project Architecture

```text
Phase 1

Before writing any code, analyze the assignment.

Design the complete architecture.

Create

- Folder structure
- Database schema
- ER Diagram
- Models
- Relationships
- Services
- Livewire Components
- JSON Schema Structure
- Repository Structure
- Queue Structure
- AI Module Structure
- Import Module Structure

DO NOT write implementation code.

Only create architecture.

Wait for approval.
```

---

# PHASE 2 — Database

```text
Now implement ONLY the database layer.

Create

✔ migrations

✔ foreign keys

✔ indexes

✔ models

✔ relationships

✔ factories

✔ seeders

Do not create Livewire.

Do not create Blade.

Do not create AI.

Stop after database is complete.
```

---

# PHASE 3 — Base Layout

```text
Create the application layout.

Requirements

Dashboard Layout

Sidebar

Top Navbar

Toast Component

Modal Component

Reusable Button Component

Reusable Card Component

Reusable Input Components

Dark Mode Support

Tailwind Only

No Form Builder yet.

Stop after completion.
```

---

# PHASE 4 — Form Builder Skeleton

```text
Implement ONLY the Form Builder shell.

Create

Builder page

Three-panel layout

Left Panel

Field Library

Center Panel

Canvas

Right Panel

Properties

No drag-drop yet.

No saving.

No JSON.

Only UI.

Use Livewire.

Each panel should be its own component.

Stop.
```

---

# PHASE 5 — Livewire State Management

```text
Implement state management.

Builder state should be managed by Livewire.

Features

Selected field

Current section

Current step

Array of fields

Array of sections

Array of steps

Events between components

No database yet.

No drag-drop.

No JSON.

Stop.
```

---

# PHASE 6 — Click to Add Fields

```text
Implement click-to-add.

Supported fields

Text

Textarea

Number

Email

Phone

Date

Dropdown

Checkbox

Radio

File

Heading

Rating

Each click adds a field.

Auto generate UUID.

Auto generate key.

Use Livewire events.

No drag-drop.

Stop.
```

---

# PHASE 7 — Property Editor

```text
Implement the property editor.

Editable

Label

Placeholder

Help Text

Required

Default

Validation

Options

Min

Max

Regex

Updates should be real-time.

Livewire only.

Stop.
```

---

# PHASE 8 — Drag & Drop

```text
Implement drag-and-drop.

Requirements

Livewire 3

SortableJS

No jQuery

Reordering

Sections

Nested fields

Update Livewire state

Persist ordering

Stop.
```

---

# PHASE 9 — JSON Schema

```text
Implement JSON Schema.

JSON is the single source of truth.

Requirements

Generate JSON

Load JSON

Two-way sync

Validate JSON

Repair invalid JSON

Pretty print

Raw JSON editor

Do not implement AI.

Stop.
```

---

# PHASE 10 — Database Persistence

```text
Implement save/update.

Persist

Forms

Schema

Settings

Sections

Metadata

Auto-save

Draft support

Version number

Do not implement submissions.

Stop.
```

---

# PHASE 11 — Public Form Rendering

```text
Build the public renderer.

Read JSON Schema.

Generate dynamic form.

Server validation from schema.

Client validation.

Responsive.

No AI.

Stop.
```

---

# PHASE 12 — Form Submission

```text
Implement submissions.

Store responses.

Search.

Pagination.

CSV Export.

Validation.

Rate limiting.

Stop.
```

---

# PHASE 13 — AI Form Generation

```text
Implement AI Generation.

Architecture

Queue Job

AI Service

Provider Interface

Prompt Builder

Schema Validator

Retry Logic

Repair malformed JSON

Store logs

Do not block requests.

Return progress.

Stop.
```

---

# PHASE 14 — AI Editing

```text
Implement AI editing.

Examples

Add section

Remove field

Translate labels

Change validation

Never regenerate entire schema.

Modify existing JSON.

Return diff.

Stop.
```

---

# PHASE 15 — DOCX Import

```text
Implement Word Import.

Use PHPWord.

Extract

Headings

Questions

Checkboxes

Options

Preview screen

Editable mapping

Queue large files.

Stop.
```

---

# PHASE 16 — Excel Import

```text
Implement Excel Import.

Use Laravel Excel.

Support

Header row

Custom template

Preview

Mapping

Validation

Queue imports.

Stop.
```

---

# PHASE 17 — Advanced Features

```text
Implement

Undo/Redo

Autosave

Version History

Rollback

Conditional Logic

Multi Step Forms

Template Library

QR Sharing

Accessibility

Testing

Docker

CI

Stop.
```

---

## Livewire-specific rules to include in every prompt

```text
Livewire Rules

- One responsibility per component.
- Use wire:model.live where appropriate.
- Prefer computed properties over duplicated state.
- Communicate via Livewire events instead of JavaScript.
- Keep AlpineJS minimal.
- Avoid direct DOM manipulation.
- Use keyed loops (wire:key) for dynamic lists.
- Use Form Objects or validation rules for input validation.
- Use lazy loading where possible.
- Avoid unnecessary re-renders.
- Organize components into: Builder, Canvas, Sidebar, Properties, JSON Editor, Preview.
```

This phased approach maps closely to the assignment's required progression—Core Form Builder (Part A), AI generation (Part B), document import (Part C), and enhancements (Part D)—while keeping each implementation isolated and testable. 
