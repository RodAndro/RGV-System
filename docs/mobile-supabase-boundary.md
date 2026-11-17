# Mobile app and Supabase dependency boundary

The native Flutter app and Laravel system run independently and share the same
Supabase project.

```text
Flutter employee app ──Supabase Auth + restricted RPCs──┐
                                                       ├── Supabase project
Laravel web app ──────PostgreSQL + Auth Admin API──────┘
```

- Flutter does not call Laravel, Sanctum, or the standalone mobile service.
- Laravel does not need to be running for native sign-in or employee workflows.
- Laravel provisions employee identities with a server-only Supabase secret.
- Flutter uses only the project URL and public publishable key.
- Both applications share employee, inventory, borrow-request, and return data
  in the same Supabase database.
- The native app uses Supabase Auth and the restricted functions in
  `database/supabase/mobile_backend.sql`; the service-role secret and database
  password are never included in the APK.

Apply the SQL script after the Laravel migrations. Existing employee rows with
matching confirmed Supabase Auth users are linked by email. For employees
without an Auth identity, use the Laravel admin edit form to set a password and
create the linked Auth identity.
