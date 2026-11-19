import { NavLink, Outlet } from 'react-router-dom';
import { useAuth } from '../auth/AuthProvider';

type NavItem = {
  to: string;
  label: string;
  adminOnly?: boolean;
};

const NAV_ITEMS: NavItem[] = [
  { to: '/dashboard', label: 'Dashboard' },
  { to: '/inventories', label: 'Inventory' },
  { to: '/borrow-requests', label: 'Borrow requests' },
  { to: '/inventories/new', label: 'Add inventory', adminOnly: true },
  { to: '/profile', label: 'Profile' },
];

/**
 * Application shell.
 *
 * Navigation is filtered by role so users only see what they may use
 * (F5). The API enforces the same rules server-side; this is purely the
 * interface layer.
 */
export function AppLayout() {
  const { user, isAdmin, signOut } = useAuth();

  return (
    <div className="min-h-screen bg-slate-50">
      <a
        href="#main-content"
        className="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50
          focus:rounded focus:bg-white focus:px-4 focus:py-2 focus:shadow"
      >
        Skip to main content
      </a>

      <header className="border-b border-slate-200 bg-white">
        <div className="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-3">
          <div className="flex items-center gap-3">
            <span className="grid h-9 w-9 place-items-center rounded-md bg-brand-500 text-sm font-bold text-white">
              RGV
            </span>
            <div>
              <p className="text-sm font-semibold leading-tight">Multi-Tech Services</p>
              <p className="text-xs text-slate-500">Inventory &amp; borrowing</p>
            </div>
          </div>

          <div className="flex items-center gap-3">
            <div className="hidden text-right sm:block">
              <p className="text-sm font-medium">{user?.name}</p>
              <p className="text-xs text-slate-500">{user?.roles.join(', ')}</p>
            </div>
            <button type="button" className="btn-secondary" onClick={() => void signOut()}>
              Sign out
            </button>
          </div>
        </div>

        <nav aria-label="Main navigation" className="mx-auto max-w-6xl px-4">
          <ul className="flex flex-wrap gap-1 pb-2">
            {NAV_ITEMS.filter((item) => !item.adminOnly || isAdmin).map((item) => (
              <li key={item.to}>
                <NavLink
                  to={item.to}
                  end={item.to === '/inventories'}
                  className={({ isActive }) =>
                    `block rounded-md px-3 py-2 text-sm font-medium transition-colors ${
                      isActive
                        ? 'bg-brand-50 text-brand-700'
                        : 'text-slate-600 hover:bg-slate-100'
                    }`
                  }
                >
                  {item.label}
                </NavLink>
              </li>
            ))}
          </ul>
        </nav>
      </header>

      <main id="main-content" className="mx-auto max-w-6xl px-4 py-6">
        <Outlet />
      </main>

      <footer className="mx-auto max-w-6xl px-4 py-6 text-xs text-slate-500">
        Machine-generated insights are advisory only. Decisions affecting people are confirmed by
        a staff member.
      </footer>
    </div>
  );
}
