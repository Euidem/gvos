---
name: GVOS
colors:
  surface: '#f7f9fb'
  surface-dim: '#d8dadc'
  surface-bright: '#f7f9fb'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f2f4f6'
  surface-container: '#eceef0'
  surface-container-high: '#e6e8ea'
  surface-container-highest: '#e0e3e5'
  on-surface: '#191c1e'
  on-surface-variant: '#45464d'
  inverse-surface: '#2d3133'
  inverse-on-surface: '#eff1f3'
  outline: '#76777d'
  outline-variant: '#c6c6cd'
  surface-tint: '#565e74'
  primary: '#000000'
  on-primary: '#ffffff'
  primary-container: '#131b2e'
  on-primary-container: '#7c839b'
  inverse-primary: '#bec6e0'
  secondary: '#0058be'
  on-secondary: '#ffffff'
  secondary-container: '#2170e4'
  on-secondary-container: '#fefcff'
  tertiary: '#000000'
  on-tertiary: '#ffffff'
  tertiary-container: '#271901'
  on-tertiary-container: '#98805d'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dae2fd'
  primary-fixed-dim: '#bec6e0'
  on-primary-fixed: '#131b2e'
  on-primary-fixed-variant: '#3f465c'
  secondary-fixed: '#d8e2ff'
  secondary-fixed-dim: '#adc6ff'
  on-secondary-fixed: '#001a42'
  on-secondary-fixed-variant: '#004395'
  tertiary-fixed: '#fcdeb5'
  tertiary-fixed-dim: '#dec29a'
  on-tertiary-fixed: '#271901'
  on-tertiary-fixed-variant: '#574425'
  background: '#f7f9fb'
  on-background: '#191c1e'
  surface-variant: '#e0e3e5'
  sidebar-bg: '#0B0F19'
  status-active: '#10B981'
  status-trial: '#8B5CF6'
  status-payment-due: '#F59E0B'
  status-suspended: '#64748B'
  status-blocked: '#EF4444'
  status-completed: '#059669'
  status-urgent: '#B91C1C'
  border-subtle: '#E2E8F0'
typography:
  display-lg:
    fontFamily: Manrope
    fontSize: 48px
    fontWeight: '800'
    lineHeight: 56px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Manrope
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.01em
  headline-lg-mobile:
    fontFamily: Manrope
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
  headline-md:
    fontFamily: Manrope
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-md:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.02em
  mono-sm:
    fontFamily: JetBrains Mono
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 4px
  container-margin: 32px
  gutter: 24px
  card-padding: 24px
  input-gap: 16px
  section-gap: 40px
---

## Brand & Style
The design system for GVOS is engineered to project **institutional trust, operational precision, and premium efficiency**. It targets a high-stakes SaaS environment where security and clarity are paramount. The brand personality is "The Quiet Powerhouse"—sophisticated, unshakeable, and focused on utility without sacrificing aesthetic polish.

The visual direction is **Corporate / Modern**, heavily influenced by the "Command Center" aesthetics of Linear and modern fintech dashboards. It utilizes a high-contrast layout logic: a deep, authoritative navigation environment juxtaposed against a clean, hyper-legible "Work Surface" for content. This distinction helps users mentally separate "Where I am" (Navigation) from "What I am doing" (Workspace).

## Colors
This design system utilizes a **Bimodal Contrast** strategy. 

- **Primary Navigation:** Uses `sidebar-bg` (#0B0F19) to create a dark, focused anchor for the platform's hierarchy. 
- **Workspace Surface:** The main interface uses a crisp `neutral-color` background with white (`#FFFFFF`) cards to ensure maximum readability and a sense of cleanliness.
- **Brand & Action:** Deep slate (`primary_color_hex`) is used for primary text and structural elements, while a vibrant professional blue (`secondary_color_hex`) serves as the primary action color for buttons and links.
- **Semantic Logic:** Status colors are high-chroma but used sparingly as badges or indicators to ensure that "Urgent" or "Blocked" states immediately break the neutral harmony of the dashboard.

## Typography
The typographic system is optimized for **data density and hierarchy**. 

- **Headlines (Manrope):** Chosen for its modern, geometric character that feels refined and structural. It provides a "premium tech" feel to page titles and metrics.
- **Body (Inter):** The workhorse of the system. Used for all core interface text, task descriptions, and chat logs due to its exceptional legibility at small sizes.
- **Labels & System (JetBrains Mono):** Used for security notes, password vault states, timestamps, and technical metadata. This introduces a "developer-grade" precision to the UI, reinforcing the secure nature of the platform.

## Layout & Spacing
The design system employs a **Fixed-Fluid Hybrid** layout. 

- **Desktop:** A fixed 280px sidebar on the left, with a fluid main content area that utilizes a 12-column grid. Max content width is capped at 1440px to prevent excessive line lengths in task descriptions.
- **Spacing Rhythm:** Based on a 4px baseline grid. Cards and dashboard metrics use generous `card-padding` (24px) to ensure data feels organized rather than cramped.
- **Responsive Reflow:** On Tablet, the sidebar collapses into a rail or hamburger menu. On Mobile, the layout shifts to a single-column stack with a persistent bottom navigation bar for core "Workspace" actions.

## Elevation & Depth
Depth is communicated through **Tonal Layering and Soft Ambient Shadows** rather than heavy borders.

- **Level 0 (Surface):** The background (`neutral_color_hex`) acts as the canvas.
- **Level 1 (Cards):** White cards feature a 1px `border-subtle` and an ultra-diffused shadow (`0px 4px 20px rgba(0,0,0,0.04)`). This creates a "lifted" effect that clearly defines interactive zones.
- **Level 2 (Overlays):** Side panels for task details and dropdown menus use a more pronounced shadow to indicate temporary priority over the workspace.
- **Glassmorphism:** Reserved exclusively for the sidebar "Active State" indicators or secondary mobile navigation blurs, adding a touch of modern sophistication.

## Shapes
The shape language is **Softly Structured**. 

A `roundedness` of **2** (0.5rem / 8px) is applied to all primary components (cards, buttons, input fields). This provides a professional balance—soft enough to feel modern and accessible, but sharp enough to maintain a serious, enterprise-ready posture. 

- **Pills:** Used exclusively for status badges (e.g., "Active", "Trial") to distinguish them from interactive buttons.
- **Soft (4px):** Used for smaller elements like checkboxes or nested utility icons.

## Components

### Buttons
- **Primary:** Solid `secondary_color_hex` (Blue) with white text. 8px roundedness.
- **Secondary:** White background with `border-subtle` and `primary_color_hex` text.
- **Ghost:** No background/border, blue text. Used for low-priority actions in tables.
- **Danger:** Solid `status-blocked` (Red) for destructive actions (e.g., "Suspend User").

### Cards & Metrics
- **Metric Cards:** Large display typography for the value, `label-md` for the title, and a small trend indicator (up/down).
- **Task Cards:** White background, subtle border, with a status badge in the top right.

### Input Fields
- **Default State:** White background, `border-subtle`, 8px roundedness. 16px horizontal padding.
- **Focus State:** 2px border of `secondary_color_hex` with a soft blue outer glow.

### Sidebar Navigation
- Deep dark background. Active items use a subtle blue left-edge border and a low-opacity blue background tint to indicate the current location.

### Tables
- No vertical borders. Subtle horizontal dividers. Header row uses `label-md` with a slight gray tint to distinguish from data rows.

### Password Vault
- Uses a "Locked" state with blurred text or asterisks. "Reveal" action triggers a temporary Level 2 elevation card with the `mono-sm` font for the credential.