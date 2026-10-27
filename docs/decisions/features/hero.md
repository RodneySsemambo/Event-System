# Feature — Homepage Hero

**PROGRESS.md**: row 6.1
**Status**: in-progress
**Last updated**: 2026-09-14

## What this is
The first screen a visitor sees: background video, headline, subhead,
primary CTAs. Sets the tone for the whole site. Governed by
`.opencode/skills/evently-ui-ux/SKILL.md`.

## Current state (as of last inspection)
<!-- OpenCode fills this in on first run. Keep it factual, 3-6 lines. -->
- `src/components/Hero.jsx`: `<video>` autoplay/muted/loop, no poster,
  no overlay gradient, no reduced-motion guard.
- Headline positioned bottom-left over video (skill-compliant).
- Navbar transparent over hero — do not break (see UI skill).

## Decisions
- Video lives in `public/videos/` (see below for the rule when adding).
- Overlay gradient uses `--color-plum` at low opacity to keep the
  headline readable across video frames.
- Reduced-motion users get the poster image, not the video.

## Known constraints
- One orchestrated entrance only (staggerChildren on load).
- No second 3D moment in this section.
- Headline is `<h1>`; there is exactly one per page.

## Changelog
- 2026-09-14 — created during feature 6.1.