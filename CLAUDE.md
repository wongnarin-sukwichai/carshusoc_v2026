# CARS-HUSOC Portal

Academic service portal for the Centre for Academic and Research Services (CARS), Faculty of Humanities and Social Sciences, Mahasarakham University. Three service centers: language training (courses with prerequisites), English exam center (score import → CEFR certificates), and document translation — all gated behind bank-slip payment approval.

## Stack

- Laravel 12 + Inertia 2 + Vue 3 (`<script setup lang="ts">`) + Tailwind CSS 3
- Icons: `lucide-vue-next`. UI primitives: shadcn-vue-style components in `resources/js/components/ui/`
- i18n: `vue-i18n` (Composition API mode). SweetAlert2 for confirm dialogs and toasts.
- Not yet added: the Anuphan font, MSU SSO/OAuth client

## Run

- `composer run dev` — serves PHP + queue + Vite together
- `php artisan migrate:fresh --seed` — rebuild the DB (seeds the admin below + the 3 service centers)
- `npm run build` — production Vite build (also the fastest way to catch a broken `.vue` file)

## Two separate auth systems — do not merge them

`users` (guard `web`, table `users`) and `admins` (guard `admin`, table `admins`) are deliberately separate tables/models/guards — this was an explicit requirement, not an oversight. See `config/auth.php` for the guard/provider/broker wiring and `bootstrap/app.php` for the `redirectGuestsTo`/`redirectUsersTo` closures that route guests to the right login page based on whether the request path starts with `admin`.

- Seeded admin: `wongnarin.s@msu.ac.th` / `w123` (`database/seeders/AdminSeeder.php`, `role: 'admin'`)
- Admin login: `/admin/login`. User login: `/login` (existing Breeze-style flow, untouched)

## Page/route layout

- `resources/js/pages/Welcome.vue` — public/guest landing (unauthenticated)
- `resources/js/pages/user/*` — routed from `routes/user.php`, wrapped in `layouts/user/UserLayout.vue` (its own sidebar, `components/UserSidebar.vue`)
- `resources/js/pages/admin/*` — routed from `routes/admin.php`, wrapped in `layouts/admin/AdminLayout.vue` (its own sidebar, `components/AdminSidebar.vue`)
- `resources/js/pages/auth/*` and `resources/js/pages/settings/*` are the original starter-kit flows — left alone

The admin sidebar labels are the mockup's menu items with the `(3.1)`–`(3.7)` numbering from the spec stripped out — don't reintroduce it.

## Where the domain spec lives

`docs/exampage-mockup-reference.jsx` is the **original design spec** — a React JSX mockup (not real code, never route it) the client provided describing every screen and interaction of the target system. It is the source of truth for business rules not yet in this codebase.

Before building out real business logic for any of the three centers (payment approval, CSV score import, certificate PDF generation, prerequisite enforcement, email templates, etc.), load the `carshusoc-domain` skill — it has the extracted schema reference and business rules so you don't have to re-read the 2600-line mockup from scratch.

## Current phase: foundation + payment/scoring/certificate pipeline

Built and verified end-to-end (see `tests/Feature/ServiceCenterPagesTest.php`, plus the domain skill's verification notes): full DB schema, admin/user guard split, seeders, course enroll + exam register (with server-side prerequisite/eligibility checks), slip upload + admin approve/reject, exam score entry (manual + CSV import) with versioned CEFR lookup, course pass/fail grading, and **real certificate PDF issuance** (`App\Services\CertificateIssuer`, dompdf) with template/score-scale snapshotting so a later rescale never rewrites an already-issued certificate.

Also built: `admin/CoursesExams.vue` — full course/exam CRUD (create, edit, show/hide toggle), with server-side validation (unique code, no self-referencing prerequisite). Demo data is still seeded (`DemoContentSeeder`: 2 courses, 1 exam, the score scale, the two certificate templates) so there's something to test against out of the box, but it's no longer the *only* way to add courses/exams.

Also built: the translation center — submit (with real file upload) → admin quote → user pays → admin approves → admin delivers translated file → user downloads. Reuses the same `Payment` polymorphic flow as courses/exams (`translation_request` is now a third `payable_type`/`ALLOWED_TYPES` case everywhere that logic lives — `User\PaymentController`, `Admin\PaymentController`, `PaymentSlipDialog.vue`).

Also built: admin role permission gating. Two roles — `admin` and `staff` (`admins.role` enum, simplified from an original 4-value enum via `database/migrations/2026_07_21_100000_simplify_admin_roles.php`). `admin` can reach every admin page; `staff` shares everything *except* Staff (admin account management), Email Templates, and Certificate Templates, which are `admin`-only (`admin.role:admin` middleware, `App\Http\Middleware\EnsureAdminHasRole`). The sidebar (`AdminSidebar.vue`) hides those links for non-`admin` roles too, but that's UX only — the middleware is the real gate.

Also built: certificate template create/delete (`admin/CertificateTemplates.vue` now supports multiple templates per center, not just the 2 seeded ones), with an enforced invariant — every service center must always have exactly one `is_default` template, since `CertificateIssuer` depends on it existing. See the domain skill for the exact rules.

**Bug fixed that affects the whole app**: `HandleInertiaRequests` never shared session flash data, so every controller's `back()->with('status', ...)` (and there are many, across every admin/user action built so far) was silently invisible — no success/error feedback ever reached the user. Fixed by sharing `flash.status`/`flash.error` and adding `components/FlashMessage.vue` (wired into `AppShell.vue`, so every layout gets it for free). New code should use `->with('status', ...)` for success and `->with('error', ...)` for user-facing failures that aren't validation errors (those still go through Laravel's normal `ValidationException` → `form.errors` path, unaffected by this).

Also built: i18n, now covering the full app — every user/admin page's content plus controller-generated flash messages (`back()->with('status'/'error', ['key' => ..., 'params' => ...])`, resolved client-side by `components/FlashMessage.vue`; see the domain skill for the exact mechanism). `Welcome.vue` and the new certificate-verify page also use `$t()`. Not translated: `app/Http/Controllers/Auth/*` flash messages and `abort_if(..., 422, '...')` messages (different render path) — those stay Thai-only, by design, matching the untouched-starter-kit convention for `pages/auth/*`/`pages/settings/*`.

Also built: service-center visibility gating (`service_centers.is_visible` now actually hides a center from `Welcome.vue` and `UserSidebar.vue` — admin sidebar/routes stay unfiltered on purpose), mail-delivery-fee checkout for exam registration (`PaymentSlipDialog.vue` + `User\PaymentController`), a public certificate verification page (`/certificates/verify/{hash}` → `CertificateVerificationController` → `CertificateVerify.vue`), and prerequisite cycle detection beyond direct self-reference (`Admin\CourseController::wouldCreateCycle()`, blocks A→B→A and longer chains on update).

What does **not** exist yet: actual email sending (templates exist in DB, nothing sends), MSU SSO wiring, the Anuphan font, embedding the certificate-verify URL/QR into issued PDFs. Don't assume any of that works — check the controller/page before relying on it.
