---
name: niami-ui-design-language
description: Use when designing or reviewing Niami frontend UI. Defines the product design direction, visual language, and guardrails for consistent future interface work.
---

# Niami UI Design Language

Use this skill for any frontend work on Niami: new pages, components, layouts, forms, tables, empty states, loading states, or interaction details.

Niami is a community-driven Nintendo Switch 2 backward-compatibility reporting platform. The interface should feel like a precise compatibility tool for players: calm, trustworthy, compact, and premium, with a light gaming identity that never becomes loud or decorative.

## Design Direction

Niami should look closer to a focused product interface than a marketing website. The desired feeling is:

- **quiet premium**: refined, restrained, confident;
- **dark-first**: dark mode is the primary visual reference;
- **compact and useful**: information density matters because users compare reports;
- **subtle gaming/tech flavor**: enough to fit the subject, never forced;
- **community trust**: clear signals, readable states, no confusing color semantics.

Useful inspiration: Linear, Perplexity, Vercel, Raycast, Zed, Supabase dashboard, Mercury. Do not copy their visuals directly; borrow restraint, rhythm, hierarchy, and polish.

## Validated Visual Choices

These choices should guide future UI decisions:

- Keep a **two-tone surface language**: adjacent areas should have subtle tonal contrast instead of flat same-color sections.
- Use **soft frosted/glass effects sparingly**. Glass should feel like depth, not like a visual gimmick.
- Prefer **muted borders, soft shadows, and quiet contrast** over bright outlines or strong card effects.
- Keep backgrounds **ambient and understated**. Avoid wide obvious grids, neon effects, cyberpunk decoration, or generic SaaS hero visuals.
- Use **rounded, polished surfaces**, but avoid overly playful or bubbly UI.
- Maintain **strong hierarchy through spacing, typography, and contrast**, not decoration.
- Favor **short labels, concise copy, and scannable structure**.

## Color Semantics

Color must communicate consistently.

- Green, yellow/amber, and red are reserved for compatibility or quality status.
- Do not use green/red to separate neutral categories, because users may interpret them as success/failure.
- Neutral categories should use non-semantic accent families such as cyan, violet, amber-muted, slate, or similar soft tones.
- Badges and state indicators should be soft: translucent fills, tinted borders, readable text, not saturated blocks.

Dark mode should avoid both extremes:

- not too black/flat;
- not too bright/glassy.

Aim for surfaces that are visible against the background while remaining calm.

## Interaction and UX Tone

Interactions should feel fast and clear, but not flashy.

- Loading states should be visible and immediate.
- Buttons can show disabled/loading states when requests are in progress.
- Sticky or persistent UI is acceptable when it improves navigation or search flow.
- Icon-only UI is acceptable only when meaning remains accessible through labels, titles, legends, or surrounding context.
- Dense interfaces should stay readable on mobile through grouping and separators rather than large visual noise.

## Content and Brand

Use `Niami` as the product name.

The writing style should be concise, direct, and product-oriented. Prefer clear compatibility language over marketing language. Avoid over-explaining obvious UI elements.

User-facing labels should avoid raw enum names or implementation vocabulary. Keep wording understandable for players comparing compatibility behavior.

## Technical Context

Current frontend stack:

- Symfony/Twig templates;
- Tailwind CSS;
- DaisyUI themes (`plumber-dark`, `plumber-light`);
- Symfony UX Icons;
- Symfony UX Turbo;
- Stimulus;
- AssetMapper/importmap.

Respect existing project architecture and templates. Prefer extending current visual primitives and CSS utilities instead of introducing unrelated design systems or JavaScript frameworks.

## Guardrails

When creating future UI, check:

- Does it feel like Niami: quiet, premium, compact, dark-first, gaming-adjacent?
- Does it preserve subtle two-tone depth?
- Are colors semantically safe and not misleading?
- Are states readable without being loud?
- Is information easy to scan?
- Does polish come from spacing, hierarchy, and restraint instead of decoration?
- Is user/API text escaped or safely sanitized?
