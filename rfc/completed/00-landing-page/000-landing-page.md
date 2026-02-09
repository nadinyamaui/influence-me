# 000 - Public Landing Page

**Labels:** `feature`, `ui`, `marketing`, `authentication`
**Depends on:** #013

## Description

Create a public landing page at `/` that clearly communicates the app's value for influencers, includes a complete pricing section, and provides clear calls to action for authentication.

This page should convert visitors into users by explaining what the platform does, why it is useful, and which plan they should choose.

## Implementation

### Route and Access

- Public route at `/`
- Must be accessible without authentication
- If user is already authenticated, still allow viewing the landing page

### Page Structure

Build the landing page with these sections in order:

1. Hero section
2. Social proof / trust indicators
3. Benefits section
4. Features breakdown
5. How it works
6. Pricing
7. FAQ
8. Final CTA footer

### Section Requirements

#### 1) Hero section
- Clear headline focused on business outcomes for influencers
- Supporting subheadline explaining end-to-end workflow (content, clients, proposals, invoices, analytics)
- Primary CTA: "Get Started" linking to register page
- Secondary CTA: "Log In" linking to login page

#### 2) Social proof / trust indicators
- Lightweight credibility content (example metrics, badges, or brand placeholders)
- No fabricated customer claims; use generic placeholders if no real data exists yet

#### 3) Benefits section
Explain concrete benefits in plain language:
- Save time by centralizing client operations
- Increase revenue by sending proposals and invoices faster
- Improve client satisfaction with client portal visibility
- Make better decisions using performance analytics

#### 4) Features breakdown
Highlight key product capabilities with short descriptions:
- Instagram account sync and media insights
- Client and campaign management
- Proposal creation, preview, and approval workflow
- Invoice generation and payment link support
- Client portal for proposals, invoices, and analytics

#### 5) How it works
Simple 3-step explanation:
1. Connect accounts and import data
2. Build proposals and campaign deliverables
3. Share with clients, get approvals, and get paid

#### 6) Pricing section
Use this follower-based pricing model:

- **Free** — **$0/month** for creators with fewer than 1,000 followers
- **Growth** — **$25/month** for 1,000 to 10,000 followers
- **Creator** — **$49/month** for 10,001 to 99,999 followers
- **Scale** — **$75/month** for 100,000 to 300,000 followers
- **Pro** — **$100/month** for 300,000 to 600,000 followers
- **Enterprise** — **Talk to us** for more than 600,000 followers

Plan details:
- Show monthly pricing prominently
- Include the follower range on each pricing card
- Enterprise card CTA must be "Talk to us"
- Include clear note about how follower count is determined (connected Instagram audience size)

#### 7) FAQ section
Include concise answers for key objections:
- Can I cancel anytime?
- Do you support annual billing?
- Is there a free trial?
- Can clients access a portal without full accounts?

#### 8) Final CTA footer
- Strong final conversion message
- Repeat both authentication actions:
  - "Create Account" -> register route
  - "Log In" -> login route

### Authentication Links

Landing page must include visible links/buttons to:
- Login page (`route('login')`)
- Register page (`route('register')`)

Include these links in:
- Header/navigation
- Hero CTA area
- Final CTA/footer area

### Design Guidelines (Modern + Unique)

Use this design direction for implementation:

- Visual tone: clean editorial + data-product aesthetic (not generic SaaS template)
- Layout: generous whitespace, asymmetric section rhythm, and clear visual hierarchy
- Brand feel: confident, premium, creator-focused

#### Color and Theme
- Define semantic tokens in Tailwind v4 `@theme` (brand, accent, surface, border, text)
- Prefer warm neutrals + one bold accent color for CTAs and highlights
- Avoid default blue/purple SaaS styling unless it matches existing brand colors
- Ensure WCAG-friendly contrast for text and button states

#### Typography
- Use a distinctive heading style and highly readable body text
- Strong scale contrast between hero heading, section headings, and body copy
- Keep line lengths comfortable for readability on desktop and mobile

#### Components and UI Patterns
- Use Flux UI Free components when available (`<flux:button>`, `<flux:badge>`, `<flux:heading>`, `<flux:separator>`, `<flux:icon>`)
- Pricing cards should have clear plan hierarchy and one visually dominant recommended tier
- CTA buttons should be high-contrast and consistent in style across all sections
- Use icon-supported benefit/feature cards to improve scanning

#### Section-Specific Design Notes
- Hero: impactful headline, concise supporting copy, dual CTAs, subtle background treatment
- Social proof: lightweight badges/stat chips, avoid fake testimonials
- Benefits/features: card-based layout with consistent spacing and iconography
- How it works: 3-step horizontal flow on desktop, stacked on mobile
- Pricing: follower-range labels must be immediately visible, enterprise card distinct
- FAQ: collapsible or clearly separated items for fast scanning
- Final CTA: visually prominent close with repeated login/register actions

#### Motion and Interaction
- Use subtle transitions for hover/focus and section reveal (no heavy animation)
- Interactive elements must have clear hover, active, and focus-visible states
- Keep interactions performant and non-blocking on lower-end mobile devices

#### Responsive Behavior
- Mobile-first approach with intentional breakpoints
- Maintain CTA visibility and readability across viewport sizes
- Cards should reflow from multi-column to single-column without content loss

#### Accessibility and Content Quality
- All actionable controls must be keyboard-accessible
- Provide clear aria labels for icon-only controls
- Avoid marketing fluff; copy should be specific and outcome-driven
- Use realistic placeholders where proof metrics are not yet available

## Files to Create
- `resources/views/pages/landing.blade.php`

## Files to Modify
- `routes/web.php` — register root landing route
- `resources/views/components/layouts/app/header.blade.php` (or active public header file) — add Login/Register links

## Acceptance Criteria
- [ ] Public landing page renders at `/`
- [ ] All required sections exist in the specified order
- [ ] Benefits are clearly explained in user-facing language
- [ ] Pricing section includes Free (<1,000), Growth (1,000-10,000), Creator (10,001-99,999), Scale (100,000-300,000), Pro (300,000-600,000), Enterprise (>600,000)
- [ ] Each pricing card displays its follower range and monthly price or contact CTA
- [ ] Login and Register links are present in header, hero, and final CTA
- [ ] Login link points to `route('login')`
- [ ] Register link points to `route('register')`
- [ ] Landing page is responsive on mobile and desktop
- [ ] Feature test covers guest access and presence of Login/Register CTAs
