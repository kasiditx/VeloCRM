---
name: VeloCRM
description: A fast bilingual CRM interface for SME owners and sales teams.
colors:
  primary-50: "#f5f3ff"
  primary-100: "#ede9fe"
  primary-400: "#a78bfa"
  primary-500: "#4f46e5"
  primary-600: "#4f46e5"
  primary-700: "#4338ca"
  primary-900: "#1e1b4b"
  neutral-bg: "#f9fafb"
  neutral-surface: "#fffffe"
  neutral-surface-dark: "#111827"
  neutral-ink: "#111827"
  neutral-muted: "#6b7280"
  neutral-border: "#e5e7eb"
  neutral-bg-dark: "#030712"
  success-600: "#059669"
  warning-600: "#d97706"
  danger-600: "#e11d48"
  info-600: "#0284c7"
typography:
  display:
    fontFamily: "Inter, Prompt, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.875rem"
    fontWeight: 900
    lineHeight: 1.15
    letterSpacing: "normal"
  headline:
    fontFamily: "Inter, Prompt, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.5rem"
    fontWeight: 700
    lineHeight: 1.2
    letterSpacing: "normal"
  title:
    fontFamily: "Inter, Prompt, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1rem"
    fontWeight: 700
    lineHeight: 1.35
    letterSpacing: "normal"
  body:
    fontFamily: "Inter, Prompt, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.875rem"
    fontWeight: 400
    lineHeight: 1.5
    letterSpacing: "normal"
  label:
    fontFamily: "Inter, Prompt, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.75rem"
    fontWeight: 600
    lineHeight: 1.25
    letterSpacing: "0.025em"
rounded:
  sm: "6px"
  md: "8px"
  lg: "12px"
  xl: "16px"
spacing:
  xs: "4px"
  sm: "8px"
  md: "16px"
  lg: "24px"
  xl: "32px"
components:
  button-primary:
    backgroundColor: "{colors.primary-600}"
    textColor: "{colors.neutral-surface}"
    rounded: "{rounded.lg}"
    padding: "10px 16px"
  button-secondary:
    backgroundColor: "{colors.neutral-surface}"
    textColor: "{colors.neutral-ink}"
    rounded: "{rounded.lg}"
    padding: "10px 16px"
  input-default:
    backgroundColor: "{colors.neutral-surface}"
    textColor: "{colors.neutral-ink}"
    rounded: "{rounded.lg}"
    padding: "10px 12px"
  surface-card:
    backgroundColor: "{colors.neutral-surface}"
    textColor: "{colors.neutral-ink}"
    rounded: "{rounded.xl}"
    padding: "24px"
---

# Design System: VeloCRM

## 1. Overview

**Creative North Star: "The Sales Desk"**

VeloCRM should feel like a clean working desk for SME owners and sales teams: quick to scan, calm under pressure, and ready for the next action. The interface serves daily CRM work: capture a lead, follow up, convert, invoice, and report without forcing users through enterprise-heavy patterns.

The system is product-first. Visual style should support speed and confidence, not decoration. It must reject the feel of a Laravel default starter app: no generic scaffold pages, no placeholder branding, no developer-centric copy, and no awkward Thai/English mixing in primary workflows.

**Key Characteristics:**
- Dense but readable operational screens.
- Clear primary actions with quiet secondary actions.
- Bilingual labels that leave room for Thai text expansion.
- Recoverable, trustworthy record surfaces for leads, customers, invoices, proposals, tasks, notes, and attachments.
- Light and dark modes that preserve contrast and avoid surprise during Livewire navigation.

## 2. Colors

The palette is a restrained CRM palette: tinted neutrals carry most surfaces, one configurable indigo primary color provides action and selection, and semantic colors communicate business states.

### Primary
- **Action Indigo** (#4f46e5): Primary actions, active navigation, focus rings, selected states, chart highlights, and brand accents. It is configurable through admin settings and CSS variables, so new UI must reference `primary-*` tokens rather than hardcoded accent colors.
- **Soft Indigo Wash** (#f5f3ff / #ede9fe): Low-pressure selected backgrounds, badges, icon containers, and hover states.
- **Deep Indigo Anchor** (#1e1b4b): Dark accent backgrounds and high-contrast primary tints.

### Secondary
- **Success Green** (#059669): Paid, accepted, restored, completed, and positive confirmation states.
- **Warning Amber** (#d97706): Contacted, medium priority, pending review, and edit affordances.
- **Danger Rose** (#e11d48): Overdue, lost, destructive actions, delete confirmations, and validation errors.
- **Info Sky** (#0284c7): Sent, open, informational metrics, and neutral progress states.

### Neutral
- **Workspace Mist** (#f9fafb): Light app background.
- **Paper Surface** (#fffffe): Cards, tables, forms, popovers, and primary working surfaces. Existing Tailwind `white` surfaces should drift toward this tinted white when touched.
- **Ink** (#111827): Primary text in light mode.
- **Muted Slate** (#6b7280): Secondary labels, helper text, timestamps, and metadata.
- **Fine Border** (#e5e7eb): Dividers, card borders, table rules, and input strokes.
- **Night Workspace** (#030712): Dark app background.
- **Night Surface** (#111827): Dark cards, sidebar, topbar, and form containers.

### Named Rules

**The One Primary Rule.** Use the configured primary color for action and selection only. Do not introduce another dominant brand accent on core product screens.

**The Status Must Speak Rule.** Semantic colors must be paired with text or icon context. Never rely on red, green, amber, or blue alone to communicate status.

## 3. Typography

**Display Font:** Inter with Prompt fallback.
**Body Font:** Inter with Prompt fallback.
**Label/Mono Font:** Inter for labels, system mono only for code-like placeholders.

**Character:** The type system is compact and practical. Inter provides crisp data-table scanning, while Prompt keeps Thai labels legible and balanced in navigation, buttons, and forms.

### Hierarchy

- **Display** (900, 1.875rem, 1.15): Dashboard KPI values and rare high-emphasis numbers only.
- **Headline** (700, 1.5rem, 1.2): Page titles such as Leads, Customers, Reports, and Settings.
- **Title** (700, 1rem, 1.35): Card headings, section titles, table group names, and widget headers.
- **Body** (400, 0.875rem, 1.5): Form content, table cells, empty states, and descriptions. Cap long prose at roughly 65 to 75 characters per line.
- **Label** (600, 0.75rem, 0.025em): Field labels, table headers, small uppercase section labels, and metadata.

### Named Rules

**The Thai Space Rule.** Thai strings are often longer after translation. Buttons, nav items, table headers, and filters must allow wrapping or truncation without breaking layout.

**The No Marketing Voice Rule.** Product screens use direct operational language. Avoid promotional copy inside dashboards, forms, and admin tools.

## 4. Elevation

VeloCRM uses a hybrid of tonal layering, borders, and restrained shadows. Surfaces are mostly flat at rest with `shadow-sm` and light borders. Stronger shadows appear for dropdowns, hover lift on dashboard cards, modals, mobile drawers, and floating notifications.

### Shadow Vocabulary

- **Surface Rest** (`shadow-sm`): Default cards, tables, form panels, report panels, and dashboard widgets.
- **Surface Hover** (`shadow-lg` with small upward transform): Dashboard KPI cards and mobile list cards that respond to hover.
- **Overlay** (`shadow-xl` / `shadow-2xl`): Dropdowns, user menus, mobile drawers, and modals.
- **Notification** (`shadow-lg`): Flash messages that must float above the app shell.

### Named Rules

**The Flat At Rest Rule.** Do not use heavy shadows to make routine CRM panels feel premium. Use borders and surface contrast first.

**The Overlay Earns Depth Rule.** Strong shadows belong to elements that truly float above the workflow, such as menus, modals, drawers, and notifications.

## 5. Components

### Buttons

- **Shape:** Rounded, compact command controls (`12px` radius for most new buttons, `8px` for dense icon-only controls).
- **Primary:** `primary-600` background, white text, `10px 16px` padding, medium or semibold weight, optional leading icon.
- **Hover / Focus:** Use `primary-700` hover, `focus:ring-2 focus:ring-primary-500`, and short color transitions. Avoid animating width, height, or layout properties.
- **Secondary / Ghost:** White or dark-surface background, gray border, neutral text, subtle hover background. Secondary actions must not compete with the primary action.
- **Destructive:** Rose text or rose-tinted backgrounds. Permanent delete actions must be clearly labeled and confirmed.

### Chips

- **Style:** Rounded pills with soft tinted backgrounds and semibold `text-xs` labels.
- **State:** Use semantic color pairs for status: green for won/paid/completed, rose for lost/overdue/destructive, amber for contacted/edit/pending, sky/blue for informational progress, gray for neutral.
- **Rule:** Chips must show readable text, not just color.

### Cards / Containers

- **Corner Style:** Most working panels use `16px` radius. Smaller repeated items use `12px`.
- **Background:** Light mode uses white on `gray-50`; dark mode uses `gray-900` on `gray-950`.
- **Shadow Strategy:** `shadow-sm` at rest. Hover lift is allowed on dashboard KPI cards and compact mobile cards.
- **Border:** Use `gray-100` to `gray-200` in light mode and `gray-800` in dark mode.
- **Internal Padding:** Use `20px` for filter panels and compact reports, `24px` for main cards and forms.

### Inputs / Fields

- **Style:** Rounded fields (`8px` to `12px`) with neutral border, white or dark-surface fill, and compact `text-sm` type.
- **Focus:** Primary border and primary ring. Focus states must be visible in light and dark modes.
- **Error / Disabled:** Error messages use rose text below the field. Disabled fields should reduce opacity and keep text readable.

### Navigation

- **Style:** Persistent sidebar on desktop with grouped sections: Main, Tools, Admin. Mobile uses a topbar and drawer.
- **Active State:** Primary-tinted background, primary text, and a thin leading active mark. Keep this subtle so the sidebar stays quiet.
- **Hover State:** Neutral background and slightly stronger text. Avoid making hover states look selected.
- **Collapse:** Collapsed sidebar keeps icons and tooltips via title attributes; expanded sidebar keeps labels visible.
- **Language and Theme:** EN/TH and dark/light controls belong in the app shell, not buried in settings.

### Tables and Lists

- **Style:** Tables are dense, scan-first surfaces with uppercase small headers, row hover, and right-aligned numeric values.
- **Mobile:** Convert dense rows to compact record cards with the same actions and status visibility.
- **Actions:** Use icon buttons for repeated row actions and text buttons for major workflow actions.

### Forms

- **Style:** Use one working surface per form, not nested cards. Group related fields in two-column grids on desktop and one column on mobile.
- **Footer:** Put cancel and save actions at the bottom, separated by a light border.
- **Copy:** Labels must be practical and bilingual-ready. Avoid internal database names.

### App Shell

- **Layout:** Desktop uses fixed sidebar plus top utility bar. Mobile uses topbar plus drawer.
- **Theme:** Both light and dark mode are supported. Dark mode must avoid white flashes during Livewire navigation.
- **Search:** Global search is a utility, not a page hero. Keep it compact and available from navigation.

## 6. Do's and Don'ts

### Do:

- **Do** use `primary-600` for the one main action or selected state on a screen.
- **Do** keep operational screens dense enough for repeated CRM work while preserving clear section hierarchy.
- **Do** make create, convert, invoice, follow up, restore, and report actions easy to find.
- **Do** write Thai labels as natural business language, not literal English translations.
- **Do** use status text with semantic color for paid, overdue, won, lost, accepted, draft, and pending states.
- **Do** keep data tables readable on desktop and provide mobile card equivalents where needed.
- **Do** use borders and tonal contrast before adding stronger shadows.

### Don't:

- **Don't** make VeloCRM look like a Laravel default starter app.
- **Don't** use placeholder branding or developer-centric language in product screens.
- **Don't** mix Thai and English in primary workflows unless the English term is unavoidable or user-facing industry language.
- **Don't** overload screens with enterprise CRM complexity. SME users should not feel like they need implementation consultants.
- **Don't** add colored side-stripe borders to cards, lists, alerts, or callouts.
- **Don't** use gradient text for new UI. Prefer solid text color and hierarchy through weight, spacing, and content.
- **Don't** use decorative glassmorphism, blurred panels, or floating effects as a default style.
- **Don't** hide destructive actions behind vague labels. Use clear labels and confirmations.
