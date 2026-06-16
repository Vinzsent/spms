---
trigger: always_on
---

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

---

# ⚙️ TECHNOLOGY STACK RULES

## Laravel Standard

- Always use the latest version of :contentReference[oaicite:0]{index=0}.
- Ensure all related tools and dependencies are compatible and updated, including:
    - PHP version
    - Composer packages
    - Node.js & NPM
    - Database systems (MySQL, etc.)
- Verify version compatibility before installing or upgrading.
- Avoid using outdated Laravel features or syntax.
- Follow Laravel best practices (MVC, Eloquent ORM, migrations, etc.).

## General Technology Rules

- Adapt to any stack when needed, but prioritize Laravel for web systems.
- Do not assume frameworks unless specified (except Laravel as default).
- Prefer stable and widely used technologies.

---

## Debugging Rules

- Identify root cause first.
- Provide clear, step-by-step fixes.
- Offer alternatives when needed.

## System Design

- Build with production mindset.
- Apply separation of concerns (MVC, services).
- Design for scalability and future updates.

## Security Rules

- Validate and sanitize all inputs.
- Prevent SQL Injection, XSS, CSRF.
- Never expose sensitive data.

## Database Rules

- Optimize queries.
- Normalize when needed.
- Use indexing when beneficial.

## API Rules

- Follow RESTful standards.
- Use proper status codes.
- Return consistent response formats.

## Git Rules

- Provide exact commands when needed.
- Use meaningful commit messages.
- Avoid destructive actions without warning.

---

# 🎨 UI/UX SYSTEM (Tailwind Standard)

## Core UI Rule

- Always use :contentReference[oaicite:1]{index=1}.
- Avoid custom CSS unless necessary.
- Use utility-first approach consistently.

## Design Principles

- Clean, minimal, modern UI.
- Consistent spacing and layout.
- Mobile-first responsive design.
- Prioritize usability over decoration.

## Layout System

- Use `max-w-*` containers for readability.
- Apply consistent padding: `p-4`, `p-6`, `p-8`.
- Use grid/flex (`flex`, `grid`, `gap-4`, `gap-6`).
- Center content using `mx-auto`, `items-center`, `justify-center`.

## Spacing Rules

- Small: `gap-2`, `p-2`
- Medium: `gap-4`, `p-4`
- Large: `gap-6`, `p-6`, `p-8`

## Typography Rules

- Titles: `text-xl`, `text-2xl`, `font-bold`
- Subtitles: `text-lg`, `font-semibold`
- Body: `text-sm`, `text-base`
- Secondary text: `text-gray-600`

## Color System (Default)

- Primary: `bg-blue-600`, `text-white`
- Secondary: `bg-gray-100`, `text-gray-800`
- Success: `bg-green-500`
- Danger: `bg-red-500`
- Warning: `bg-yellow-500`
- Use hover + transitions (`hover:bg-blue-700`, `transition duration-200`)

## Component Standards

### Buttons

- `px-4 py-2 rounded-lg font-medium`
- Primary: `bg-blue-600 text-white hover:bg-blue-700`
- Secondary: `bg-gray-200 hover:bg-gray-300`

### Cards

- `bg-white shadow-md rounded-2xl p-4`
- Use `space-y-3`

### Inputs

- `w-full border rounded-lg px-3 py-2`
- `focus:ring-2 focus:ring-blue-500`

### Tables

- `w-full border-collapse`
- Header: `bg-gray-100`
- Row: `border-b hover:bg-gray-50`

### Modals

- Overlay: `fixed inset-0 bg-black bg-opacity-50`
- Content: `bg-white p-6 rounded-xl shadow-lg`

## Responsive Rules

- Always use breakpoints (`sm:`, `md:`, `lg:`, `xl:`)
- Example: `grid-cols-1 md:grid-cols-2 lg:grid-cols-3`

## UX Rules

- Provide feedback (loading, success, error).
- Keep navigation intuitive.
- Avoid clutter.
- Ensure accessibility.

---
# 🌐 DEPLOYMENT SAFETY RULES (CRITICAL)

## Environment Consistency
- Always ensure local and production environments are aligned:
  - Same PHP version
  - Same Laravel version
  - Same database engine/version
- Do not rely on local-only configurations.

---

## Error Prevention
- Code must not rely on:
  - Debug mode (`APP_DEBUG=true`)
  - Local-only paths or files
- Always handle:
  - null values
  - missing data
  - unexpected inputs

---

## Configuration Management
- Never hardcode sensitive or environment-specific values.
- Always use `.env` variables for:
  - database credentials
  - API keys
  - URLs
- Ensure `.env` in production is properly configured.

---

## Case Sensitivity (IMPORTANT)
- File and folder names must match EXACT case:
  - Linux (production) is case-sensitive
  - Windows (local) is not
- Ensure:
  - correct imports
  - correct file paths

---

## Database Compatibility
- Ensure migrations are production-safe:
  - correct data types
  - no unsupported SQL syntax
- Avoid queries that only work on local DB setups.

---

## Dependency Management
- Run:
  - `composer install --no-dev --optimize-autoloader`
- Ensure all required packages are installed.
- Do not rely on dev-only dependencies in production.

---

## Caching & Optimization
- Before deployment, run:
  - `php artisan config:cache`
  - `php artisan route:cache`
  - `php artisan view:cache`
- Clear cache if issues occur:
  - `php artisan cache:clear`

---

## File & Storage Handling
- Ensure proper permissions:
  - `storage/`
  - `bootstrap/cache`
- Use Laravel storage system (not direct file paths).

---

## Error Handling
- Never expose raw errors in production.
- Use logs instead:
  - `storage/logs/laravel.log`
- Always implement fallback handling.

---

## API & Route Safety
- Ensure all routes work without relying on:
  - localhost URLs
  - hardcoded domains
- Use named routes and config-based URLs.

---

## Testing Before Deployment
- Test in production-like environment when possible.
- Check:
  - forms
  - APIs
  - authentication
  - file uploads

---

## Deployment Mindset
- Always assume production is stricter than local.
- Code must be:
  - error-tolerant
  - environment-independent
  - fully configured
---

# 📱 MOBILE & APK RULES

## Mobile Compatibility

- All systems must be fully responsive (mobile-first).
- UI must work smoothly on small screens.
- Avoid fixed widths; use flexible layouts.
- Optimize touch interactions.

## App Conversion

- Systems should be convertible into APK.
- Prefer:
    - :contentReference[oaicite:2]{index=2}
    - :contentReference[oaicite:3]{index=3}
    - :contentReference[oaicite:4]{index=4}

## PWA Standards

- Support installable apps.
- Use manifest.json + service workers.
- Enable offline support when possible.

## Performance

- Optimize assets and load time.
- Avoid heavy components.

---

## Output Format

- Use code blocks for code.
- Use structured lists.
- Keep responses clean and readable.

## Constraints

- Do not hallucinate tools or libraries.
- Do not overcomplicate solutions.
- Avoid redundancy.

## Improvement Mode

- Suggest optimizations when relevant.
- Recommend better patterns if applicable.

## Developer Context

- Assume real-world production systems.
- Optimize for speed + quality.
- Ensure reusability and scalability.
