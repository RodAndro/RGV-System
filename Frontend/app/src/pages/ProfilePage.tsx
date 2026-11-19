import { useAuth } from '../auth/AuthProvider';

export function ProfilePage() {
  const { user, isAdmin } = useAuth();

  return (
    <div className="space-y-5">
      <header>
        <h1 className="text-xl font-semibold">Your profile</h1>
        <p className="text-sm text-slate-600">
          Account details as returned by the API at <code>/api/v1/auth/me</code>.
        </p>
      </header>

      <dl className="card grid gap-4 p-5 sm:grid-cols-2">
        <Detail label="Name" value={user?.name ?? '—'} />
        <Detail label="Email" value={user?.email ?? '—'} />
        <Detail label="Phone" value={user?.phone ?? '—'} />
        <Detail label="Roles" value={user?.roles.join(', ') ?? '—'} />
        <Detail label="Account active" value={user?.is_active ? 'Yes' : 'No'} />
        <Detail label="Multi-factor authentication" value={user?.mfa_enabled ? 'Enabled' : 'Disabled'} />
      </dl>

      <section className="card p-5">
        <h2 className="text-sm font-semibold">Account settings</h2>
        <p className="mt-2 text-sm text-slate-600">
          Password changes and multi-factor authentication are managed in the staff portal so a
          session token is never handled by this page.
        </p>
        <a className="btn-secondary mt-3 inline-flex" href="/profile">
          Open staff portal profile
        </a>
      </section>

      {isAdmin && (
        <p className="text-sm text-slate-500">
          You are signed in as an administrator, so inventory create, edit and delete actions are
          available.
        </p>
      )}
    </div>
  );
}

function Detail({ label, value }: { label: string; value: string }) {
  return (
    <div>
      <dt className="text-xs font-medium uppercase tracking-wide text-slate-500">{label}</dt>
      <dd className="mt-1 text-sm text-slate-800">{value}</dd>
    </div>
  );
}
