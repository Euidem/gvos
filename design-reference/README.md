# GVOS — Design Reference

## Source of Truth

The Stitch UI export is the **approved visual design source of truth** for GVOS.

All screens, layouts, component patterns, color systems, and typography decisions in the Stitch export are the definitive design direction for this project.

---

## Rules for Implementation

> **Claude Code must preserve the Stitch-inspired UI direction. Do not redesign screens unless the project owner explicitly requests it. Implement phase by phase and keep the interface premium, clean, secure, workspace-centered and role-aware.**

1. **Do not redesign** any screen that has a Stitch reference without explicit approval.
2. **Do not change** color scheme, typography, or spacing conventions without approval.
3. **Do implement** each screen using the Stitch HTML as a guide, adapting it to React/Inertia/Tailwind.
4. **Do preserve** the visual hierarchy and information density shown in the Stitch designs.
5. **Do maintain** role-aware views — each role should see a distinctly appropriate interface.

---

## Stitch Screen Index

See `/design-reference/stitch-export-notes.md` for a full breakdown of each screen and its implementation notes.

See `/design-reference/screens/stitch_gvos_operations_platform/` for the extracted screen files:
- Each folder contains `code.html` (HTML structure) and `screen.png` (visual reference)
- The `gvos_stitch_ui_prompt_pack.md` file contains design prompts and notes

---

## Design Language Summary

Based on the Stitch screens, GVOS uses:

| Element | Direction |
|---------|-----------|
| Color scheme | Dark navy/slate sidebar, clean white content area |
| Typography | Clean sans-serif, strong hierarchy |
| Cards | Rounded, subtle shadows, tight data density |
| Status badges | Color-coded (green=active, amber=warning, red=suspended/urgent) |
| Navigation | Role-specific sidebar navigation |
| Tables | Clean, sortable, with action buttons inline |
| Forms | Simple, label-above-field pattern |
| Mobile | Condensed bottom navigation, cards stack vertically |

---

## Portal Design Themes

| Portal | Primary Feel |
|--------|-------------|
| GVOS Ops Console (Filament) | Data-dense, admin-optimized, Filament default customized |
| GVOS Manager Console | Oversight-focused, monitoring-heavy |
| GVOS Talent Portal | Work-focused, task-driven, simple |
| GVOS Client Portal | Clean, summary-oriented, non-technical |
| Lead Pre-Access | Minimal, welcoming, professional |

---

## How to Use This Reference When Implementing

1. Open the relevant `screen.png` to understand the visual design
2. Open the `code.html` to understand the HTML structure
3. Implement in React using Tailwind classes that match the design
4. Do not copy the HTML directly — adapt it to Inertia page components
5. If the Stitch design is unclear or incomplete, ask for clarification before designing yourself
