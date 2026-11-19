import type { ReactNode } from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import { useAuth } from '../auth/AuthProvider';

export function RequireAuth({ children }: { children: ReactNode }) {
  const { user, loading } = useAuth();
  const location = useLocation();

  if (loading) {
    return (
      <div className="grid min-h-screen place-items-center text-sm text-slate-500" role="status">
        Loading…
      </div>
    );
  }

  if (!user) {
    return <Navigate to="/login" replace state={{ from: location.pathname }} />;
  }

  return <>{children}</>;
}

export function RequireAdmin({ children }: { children: ReactNode }) {
  const { isAdmin } = useAuth();

  if (!isAdmin) {
    return (
      <div className="card p-8 text-center" role="alert">
        <h1 className="text-lg font-semibold">Not available</h1>
        <p className="mt-1 text-sm text-slate-600">
          This page requires an administrator role.
        </p>
      </div>
    );
  }

  return <>{children}</>;
}
