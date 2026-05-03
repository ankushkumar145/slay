# SLAY Next Steps

This file is the product roadmap for making SLAY genuinely useful and eventually monetizable.

## Current Position

SLAY already has the base of a useful app:

- Auth
- Profile setup
- Dating signal analysis
- AI-generated bio/results
- Love Guru chat
- Mobile-style navigation

But to become something people would keep using and pay for, the app needs to move from "fun one-time generator" to "ongoing dating assistant."

The best direction is:

```text
Profile optimizer + dating coach + message assistant
```

That gives users a reason to come back before, during, and after matches.

## The Main Product Bet

People do not only need a better bio. They need help with the whole dating loop:

1. Make profile better.
2. Get better matches.
3. Know what to say.
4. Keep conversation alive.
5. Prepare for dates.
6. Understand what went wrong.

SLAY should become the app that helps with that full loop.

## Build Next

## 1. Saved Dating Kits

Right now the latest result is mostly a temporary/local experience. Make analysis results persistent on the backend.

Build:

- `dating_kits` table
- Save every generated result
- Show kit history
- Open latest kit from dashboard
- Let user rename a kit
- Add "copy bio", "copy prompts", and "copy opener" actions

Why it matters:

- Users can return later.
- The app feels real and useful.
- Creates a natural premium limit later.

Monetization angle:

- Free: 1 saved kit
- Paid: unlimited kits and version history

## 2. Message Assistant

Love Guru should become more concrete. The best version is not just open-ended chat. It should have modes.

Build modes:

- "What should I reply?"
- "Make this less dry"
- "Flirty but not cringe"
- "Ask them out"
- "Recover from a bad reply"
- "Decode this message"

User flow:

1. User pastes a message.
2. Chooses intent.
3. Gets 3 reply options:
   - Safe
   - Playful
   - Bold
4. Can copy one instantly.

Why it matters:

- This is a repeat-use feature.
- It solves a painful real moment.
- It is more monetizable than bio generation alone.

Monetization angle:

- Free: 5 message assists per day
- Paid: unlimited replies and better tone controls

## 3. Profile Score With Fixes

The app should not only generate a bio. It should tell the user what is weak and exactly what to improve.

Build:

- Profile score screen
- Score breakdown:
  - Clarity
  - Warmth
  - Specificity
  - Conversation hooks
  - Match alignment
- "Fix this" actions for each weak area
- Before/after comparison

Why it matters:

- Users understand the value.
- The app feels less like magic text generation and more like a coach.
- Better visual product for sharing/screenshots.

Monetization angle:

- Free: basic score
- Paid: detailed diagnosis and rewrites

## 4. Photo Guidance

Dating profile success is not only text. Add photo advice without needing full image AI at first.

Build first:

- Photo checklist
- Shot plan:
  - Face photo
  - Full body
  - Social proof
  - Hobby/action
  - Date-energy photo
- "What photo should I take next?" generator
- Bad photo warnings

Build later:

- Upload photos
- Rank photos
- Suggest ordering
- Detect missing photo types

Why it matters:

- Users know photos matter.
- This expands SLAY beyond bios.
- Higher perceived value.

Monetization angle:

- Paid: photo ranking and profile audit

## 5. Onboarding That Feels Useful

The current profile setup should become a guided first-run experience.

Build:

- First-run onboarding
- Ask:
  - Name
  - Age
  - Dating goal
  - Target match
  - Current biggest problem
  - Current bio
- Show a quick "your profile focus" summary before dashboard

Why it matters:

- Better first impression.
- Better AI context.
- Less confusion about what the app does.

Monetization angle:

- Sets up the user's personal plan, which can lead into premium.

## 6. Daily Dating Coach

Add lightweight recurring value.

Build:

- Daily suggestion on dashboard
- Examples:
  - "Refresh one prompt today"
  - "Send one low-pressure opener"
  - "Add one specific detail to your bio"
  - "Ask for the date after 5 good replies"
- Streak optional, but do not make it childish.

Why it matters:

- Gives users a reason to reopen the app.
- Makes dashboard feel alive.

Monetization angle:

- Paid: personalized weekly plan

## 7. Better Empty And Loading States

Every empty state should push the user toward one useful next action.

Build:

- No profile state
- No kit state
- No chat state
- Analysis loading state
- Love Guru typing state
- Failed AI fallback state

Why it matters:

- This makes the app feel polished.
- It reduces confusion.
- It improves conversion.

## Monetization Plan

Start simple. Do not add subscriptions too early.

## Free Plan

Free users get enough value to trust the app:

- 1 profile analysis
- 1 saved kit
- 5 Love Guru assists per day
- Basic score
- Basic bio rewrite

## Paid Plan

Possible price:

```text
$4.99/month to $9.99/month
```

Paid users get:

- Unlimited message assists
- Unlimited saved kits
- Full profile diagnosis
- Multiple bio versions
- Platform-specific prompts
- Date prep scripts
- Weekly profile improvement plan
- Photo checklist/ranking later

## One-Time Purchase

A one-time product may convert better early than a subscription.

Possible offer:

```text
$9.99 Profile Glow-Up Kit
```

Includes:

- Full profile analysis
- 5 bio versions
- Prompt pack
- Photo plan
- 20 opener/reply ideas
- Date prep checklist

This is probably the best first monetization experiment because users understand what they are buying.

## Best First Premium Feature

The strongest first paid feature is:

```text
Unlimited reply/message assistant
```

Reason:

- It is used repeatedly.
- It has urgent value.
- It is easy to limit.
- It connects directly to real dating pain.

The second strongest is:

```text
Full Profile Glow-Up Kit
```

Reason:

- Easy to sell.
- Good one-time purchase.
- Clear outcome.

## Suggested Build Order

## Phase 1: Make It Useful

Build these first:

1. Saved dating kits on backend
2. Message assistant modes
3. Better analysis loading and error states
4. Improved onboarding

Goal:

Make the app useful enough that someone would come back twice in a week.

## Phase 2: Make It Retain

Build:

1. Kit history
2. Daily dating coach card
3. Chat history
4. Profile score with fix actions

Goal:

Make the app feel like an ongoing coach, not a one-time generator.

## Phase 3: Make It Monetize

Build:

1. Usage limits
2. Premium flags in backend
3. Payment integration
4. One-time Profile Glow-Up Kit offer
5. Subscription for unlimited message assists

Goal:

Test whether users pay for repeated help or a one-time transformation.

## What Not To Build Yet

Avoid these until the core loop is strong:

- Social feed
- Public user profiles
- Complex gamification
- Too many tabs
- Full dating app/matching system
- Heavy analytics dashboard
- Random AI features that do not solve a dating moment

The app should stay focused:

```text
Help me look better. Help me text better. Help me date better.
```

## Next Engineering Tasks

Best immediate tasks:

1. Add `dating_kits` backend model, migration, controller, and routes.
2. Save analysis results to the logged-in user.
3. Replace local-only latest result with backend latest kit.
4. Add Love Guru modes in UI.
5. Add daily dashboard card with one useful next action.
6. Add better loading state for analysis generation.
7. Add simple usage counters for message assists.

## Success Metrics

Track these once the app has basic analytics:

- Signups
- Completed profiles
- Completed analyses
- Copied bios
- Copied replies
- Love Guru messages per user
- Day 2 return rate
- Day 7 return rate
- Free-to-paid conversion

The most important early metric:

```text
Does the user copy something?
```

If users copy bios or replies, the app is creating real value.

## Final Direction

SLAY should not be "AI writes your dating bio."

The stronger product is:

```text
Your dating confidence assistant.
```

It should help users improve their profile, reply better, understand romantic signals, and take the next step with more confidence.
