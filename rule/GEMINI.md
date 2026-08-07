# GEMINI.md - Universal Development Rules (Pro Version)

## Core Behavior

- Give clear, direct, and practical answers.
- Focus on implementation, not theory.
- Ask only necessary clarifying questions.
- Prioritize correctness, scalability, and maintainability.

## Thinking Approach

- Understand the goal before solving.
- Break problems into structured steps.
- Prefer simple, scalable solutions.
- Consider edge cases when relevant.

## Code Standards

- Write clean, modular, maintainable code.
- Use consistent naming conventions.
- Follow modern best practices.
- Avoid unnecessary complexity.
- Comment only when needed.
- Make sure the code has no error before deploying.

---

# ⚙️ TECHNOLOGY STACK RULES

## Laravel Standard

- Always use the latest version of Laravel.
- Ensure all related tools and dependencies are compatible and updated:
  - PHP
  - Composer
  - Node.js & NPM
  - Database systems
- Verify compatibility before installing/upgrading.
- Avoid outdated Laravel syntax.
- Follow Laravel best practices (MVC, Eloquent, migrations).

## General Technology Rules

- Adapt to any stack when needed, but prioritize Laravel.
- Prefer stable and widely used technologies.

---

## Debugging Rules

- Identify root cause first.
- Provide step-by-step fixes.
- Offer alternatives when needed.

## System Design

- Build with production mindset.
- Apply MVC and separation of concerns.
- Design for scalability.

## Security Rules

- Validate and sanitize inputs.
- Prevent SQL Injection, XSS, CSRF.
- Protect sensitive data.

# 🔐 GLOBAL SECURITY ENFORCEMENT (CRITICAL)

## Universal Security Rule

- Security must be applied to EVERY:
  - Page
  - Backend function
  - API endpoint
- No route or function should be accessible without proper validation and authorization.

---

## Authentication & Authorization

- All protected routes must require authentication.
- Use role-based or permission-based access control (RBAC).
- Never trust client-side validation alone.
- Always verify user identity on the server.

---

## Input Validation (STRICT)

- Validate ALL inputs on backend (required).
- Sanitize inputs before processing.
- Reject unexpected or malformed data.
- Use Laravel validation rules for all requests.

---

## API Security

- Secure all APIs using:
  - Authentication (tokens, sessions)
  - Authorization (who can access what)
- Never expose internal logic or sensitive data in API responses.
- Use rate limiting on ALL endpoints.
- Validate every request payload.

---

## Data Protection

- Never store plain passwords → always hash (bcrypt).
- Encrypt sensitive data when necessary.
- Never expose:
  - passwords
  - tokens
  - API keys
- Use environment variables for secrets.

---

## Request Protection

- Always use:
  - CSRF protection (for forms)
  - CORS configuration (for APIs)
- Prevent mass assignment vulnerabilities.
- Use Laravel `$fillable` or `$guarded`.

---

## Session & Token Security

- Use secure session handling.
- Regenerate session IDs after login.
- Expire inactive sessions.
- Use secure cookies (HTTPOnly, Secure).

---

## File Upload Security

- Validate file type and size.
- Store files securely (not directly executable).
- Rename files to prevent path attacks.

---

## Error Handling

- Do NOT expose system errors to users.
- Show generic error messages.
- Log full errors internally.

---

## Headers & Protection

- Apply security headers:
  - Content-Security-Policy (CSP)
  - XSS Protection
  - X-Frame-Options
- Prevent clickjacking and script injection.

---

## Dependency Security

- Keep all dependencies updated.
- Remove unused packages.
- Regularly check for vulnerabilities.

---

## Logging & Monitoring (Security Focus)

- Log suspicious activities:
  - Failed logins
  - Repeated requests
  - Unauthorized access attempts
- Monitor logs for unusual patterns.

---

## Security Mindset (MANDATORY)

- Assume all inputs are malicious by default.
- Never trust user input.
- Always validate, sanitize, and authorize.
- Build systems as if they are exposed to public attacks.

## Database Rules

- Optimize queries.
- Normalize when needed.
- Use indexing when beneficial.

## API Rules

- Follow RESTful standards.
- Use proper status codes.
- Maintain consistent responses.

## Git Rules

- Provide exact commands when needed.
- Use clear commit messages.

---

# 🎨 UI/UX SYSTEM (Tailwind Standard)

## Core UI Rule

- Always use Tailwind CSS.
- Avoid custom CSS unless necessary.

## Design Principles

- Clean, minimal, modern UI.
- Mobile-first responsive design.
- Prioritize usability.

## Component Strategy

- Prefer reusable components.
- Use shadcn/ui patterns for:
  - Buttons
  - Modals
  - Forms
  - Tables
  - Dialogs
- Maintain consistent spacing and styling.

---

# 📄 PAGINATION RULES

## When to Use Pagination

- Always use pagination when handling large datasets.
- Avoid loading all records at once.
- Optimize database queries with `LIMIT`, `OFFSET`, or Laravel pagination.

## Pagination Logic

- Use server-side pagination.
- Default to Laravel `paginate()`.
- Use 10–20 items per page.

## Pagination UI Standard

- Minimal pagination:
  - Previous
  - Current page (highlighted)
  - Next

## Mobile Pagination Rule

- Show only Previous / Current / Next
- Use larger touch-friendly buttons.

---

# 🔍 SEARCH BAR RULES

## Search Performance

- Search must be fast and smooth (no noticeable lag).
- Avoid full page reloads when possible.
- Use AJAX / fetch for dynamic searching.

## Search Behavior

- Implement real-time or near real-time search (debounced).
- Add delay (300–500ms) to prevent excessive queries.
- Show results as the user types when appropriate.

## Database Optimization

- Use indexed columns for searchable fields.
- Avoid inefficient queries (`LIKE %keyword%` on large datasets unless optimized).
- Combine search with pagination for performance.

## UI/UX Standard

- Place search bar above tables or lists.
- Use clear placeholder text (e.g., "Search...").
- Provide instant feedback:
  - Loading indicator (optional)
  - “No results found” message

## Mobile Search Rule

- Make search bar full width on mobile.
- Ensure easy tapping and typing.
- Keep UI clean and uncluttered.

## UX Enhancements

- Preserve search input after results load.
- Allow clearing search easily.
- Highlight matched results when possible.

---

# 📱 MOBILE & APK RULES

## Mobile Compatibility

- Fully responsive (mobile-first).
- Optimize touch interactions.

## App Conversion

- Systems must be convertible to APK using:
  - Capacitor
  - Cordova
  - PWA techniques

## Performance

- Optimize assets and loading speed.

---

# 💰 PROMPT EFFICIENCY & TOKEN/CREDIT MINIMIZATION RULES (CRITICAL - HIGHEST PRIORITY)

## Absolute Goal

- Minimize every token and credit spent.
- Prefer the shortest possible correct response that still delivers working code.
- Treat tokens as a scarce resource. Every extra sentence costs future features.

## Response Length Control (Strict)

- Default to the shortest useful answer.
- Never write explanations unless the user explicitly asks for them.
- Never add introductions, conclusions, summaries, or “hope this helps”.
- Never repeat the user’s question or restate the goal.
- Never list multiple alternative approaches unless asked.
- Never include “why” or theoretical background unless requested.
- Maximum response style: Direct code + minimal necessary notes only.

## Code Output Rules (Token Savers)

- Return ONLY the changed/new code, never the full file unless the user specifically requests the complete file.
- When editing existing code, use clear diff-style or “replace this section with:” format.
- Never regenerate large unchanged blocks.
- Prefer small, focused snippets over complete files.
- If a file is long, return only the relevant function/class/method.
- Avoid adding comments unless they are critical for correctness.
- Prefer compact modern syntax (arrow functions, short array syntax, etc.) when it does not hurt readability.

## Context & Prompt Discipline

- Never re-include previously given code, rules, or context unless strictly required for the current change.
- Assume the AI already knows the project structure and previous conversation.
- When referring to earlier code, say “update the existing X method” instead of pasting it again.
- Keep user prompts short and precise. Prefer:
  - “Add validation to StoreUserRequest”
  - instead of long descriptions of the whole feature.

## Iteration Strategy (Most Efficient)

- Solve one small piece at a time.
- Prefer many tiny, cheap requests over one large expensive request.
- After receiving code, only ask for the next missing piece.
- Never restart from scratch if a previous answer can be extended.
- When debugging: send only the error + the relevant 10–30 lines, never the whole file.

## Output Format for Maximum Efficiency

- Use pure code blocks with language tag.
- No surrounding markdown fluff.
- No bullet lists of explanations unless the user asked for steps.
- If multiple files are needed, list them as:

  ```php
  // File: app/Http/Controllers/UserController.php
  ...

  Keep it extremely tight.
  ```

  ## Forbidden Token Waste

# Do not generate:

- Long documentation
- Usage examples (unless asked)
- Alternative implementations
- “Best practices” essays
- Future improvement suggestions (unless in Improvement Mode and asked)
- Emojis or decorative text
- Repeated security reminders already covered in the rules

## Smart Reuse

- Reference previous answers by short identifiers (“use the same pattern as the previous controller”).
- Combine related small changes into one request when they touch the same file.
- Prefer “continue from previous response” style when iterating.

## When More Detail Is Needed

# Only expand if the user says one of these:

- “explain”
- “why”
- “show full file”
- “add comments”
- “give alternatives”

Otherwise stay minimal.

## Credit-Saving Mindset (Mandatory)

- Before generating any response, ask internally: “Can this be 30–50% shorter while remaining correct?”
- Prefer working code over perfect code when the difference is only stylistic.
- Ship the smallest change that solves the immediate request.

## 📊 REPORTING PAGE RULES

# Report Page Structure (MANDATORY)

- Reports must follow this layout:
- Filters Panel (left or top)
- Preview Section (main content)
- Export Actions (PDF, CSV, etc.)

## Filters Section

# Include:

- Search input
- Category/status filters (checkbox, dropdown)
- Filters must NOT auto-run heavy queries.
- Require user action (e.g., "Preview" button).

## Preview Behavior

- Data should only load after clicking "Preview".
- Show empty state before preview (e.g., "Set filters and click Preview").
- Display results in:
- Table format (default)
- Clean and readable layout
- Must support pagination for large data.

## Export Functionality

# Provide export options:

- PDF
- CSV

- Export must respect applied filters.
- Ensure exported data matches preview data.

## UI/UX Standard

- Use cards for sections (rounded, shadow, p-4).
- Keep layout clean and structured.
- Clearly separate Filters and Preview areas.
- Use icons + labels for actions (Preview, Export).

## Performance Rules

- Do NOT load all data by default.
- Use filtered queries only.

# Combine:

- Filters
- Search
- Pagination

## Mobile Behavior

- Filters collapse into dropdown or modal.
- Preview becomes full width.
- Buttons (Preview, Export) must be large and touch-friendly.

## UX Enhancements

- Show loading indicator when generating preview.
- Show "No results found" when empty.
- Preserve filters after preview.

## 🚀 SCALABILITY & PRODUCTION RULES

# Database Optimization

- Always add indexes to frequently queried columns:
- WHERE, JOIN, ORDER BY, FOREIGN KEYS

- Avoid over-indexing (balance read vs write performance).
- Use composite indexes when needed.

## Schema Design

- Ensure database schema is clean and optimized:
- Proper data types (no unnecessary VARCHAR/TEXT)
- Use foreign keys for relationships
- Normalize data where appropriate

- Avoid redundant or duplicated data.
- Use migrations to manage schema changes properly.

## Stateless Architecture

- Design systems to be stateless:
- Do not store session data in application memory
- Use external storage (DB, cache like Redis) for sessions

- Ensure any instance can handle any request.
- Prepare system for horizontal scaling (multiple servers).

## Rate Limiting

- Apply rate limiting on all critical endpoints:
- Login
- API routes
- API cost
- Form submissions

- Prevent abuse, spam, and brute-force attacks.
- Use middleware-level rate limiting.

## Logging & Monitoring

# Log all important system activities:

- Errors
- User actions (login, transactions)
- API requests (optional but recommended)

- Use structured logging (JSON when possible).
- Do not log sensitive data (passwords, tokens).
- Enable log rotation and storage management.

## Performance Strategy

# Always combine:

- Pagination
- Search optimization
- Indexed queries

- Avoid N+1 query problems (use eager loading in Laravel).
- Cache frequently accessed data when needed.

## Scalability Mindset

- Build systems assuming growth in:
- Users
- Data
- Requests

- Avoid hard-coded limits.
- Design APIs and DB for future expansion.

## Output Format

- Use code blocks for code.
- Use structured lists.

## Constraints

- Do not hallucinate tools.
- Do not overcomplicate solutions.

## Improvement Mode

- Suggest optimizations when relevant.

## Developer Context

- Assume production systems.
- Focus on scalability and clean architecture.

## text### Key additions that will reduce token & credit usage

| Rule Category             | Impact                                    | Why it saves credits                 |
| ------------------------- | ----------------------------------------- | ------------------------------------ |
| Strict response length    | Cuts 40–70% of typical output             | No fluff, no explanations by default |
| Code-only / partial files | Avoids regenerating large files           | Biggest single saver                 |
| Tiny iteration strategy   | Many small cheap requests vs one huge one | Lower cost + better control          |
| Forbidden token waste     | Explicitly bans common expensive patterns | Prevents accidental long answers     |
| Credit-saving mindset     | Forces the model to self-check length     | Consistent discipline                |

### Recommended usage tips (for you)

1. Start every new feature request with a very short prompt, e.g.:  
   `Add store method for Product with validation + FormRequest`
2. When you need changes:  
   `Update only the store method – add image upload`
3. Only ask for full files or explanations when you really need them.
4. Keep the conversation focused on one small piece at a time.
