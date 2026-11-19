import { useQuery } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { useAuth } from '../auth/AuthProvider';
import { listBorrowRequests, listInventories } from '../lib/api';
import { ErrorBanner, LoadingState } from '../components/States';

export function DashboardPage() {
  const { user, isAdmin } = useAuth();

  const inventoryQuery = useQuery({
    queryKey: ['inventories', 'dashboard'],
    queryFn: () => listInventories({ limit: 5, sort: '-created_at' }),
  });

  const borrowQuery = useQuery({
    queryKey: ['borrow-requests', 'dashboard'],
    queryFn: () => listBorrowRequests({ limit: 5, sort: '-created_at' }),
  });

  const isLoading = inventoryQuery.isPending || borrowQuery.isPending;
  const hasError = inventoryQuery.isError || borrowQuery.isError;

  return (
    <div className="space-y-5">
      <header>
        <h1 className="text-xl font-semibold">Welcome, {user?.name}</h1>
        <p className="text-sm text-slate-600">
          {isAdmin
            ? 'Administrator overview of inventory and borrow requests.'
            : 'Your inventory and borrow request overview.'}
        </p>
      </header>

      {hasError && <ErrorBanner error={inventoryQuery.error ?? borrowQuery.error} />}
      {isLoading && <LoadingState label="Loading dashboard…" />}

      {!isLoading && !hasError && (
        <>
          <div className="grid gap-4 sm:grid-cols-2">
            <SummaryCard
              title="Inventory items"
              value={inventoryQuery.data?.meta.total ?? 0}
              link="/inventories"
              linkLabel="View inventory"
            />
            <SummaryCard
              title="Borrow requests"
              value={borrowQuery.data?.meta.total ?? 0}
              link="/borrow-requests"
              linkLabel="View borrow requests"
            />
          </div>

          <div className="grid gap-4 lg:grid-cols-2">
            <RecentList
              title="Recently added inventory"
              emptyMessage="No inventory items yet."
              items={(inventoryQuery.data?.data ?? []).map((item) => ({
                key: String(item.id),
                to: `/inventories/${item.id}`,
                primary: item.name,
                secondary: `${item.item_code} · ${item.quantity} ${item.unit}`,
              }))}
            />

            <RecentList
              title="Latest borrow requests"
              emptyMessage="No borrow requests yet."
              items={(borrowQuery.data?.data ?? []).map((request) => ({
                key: String(request.id),
                to: `/borrow-requests/${request.id}`,
                primary: request.request_number,
                secondary: `${request.status} · due ${request.due_date ?? '—'}`,
              }))}
            />
          </div>
        </>
      )}
    </div>
  );
}

function SummaryCard({
  title,
  value,
  link,
  linkLabel,
}: {
  title: string;
  value: number;
  link: string;
  linkLabel: string;
}) {
  return (
    <section className="card p-5">
      <h2 className="text-xs font-medium uppercase tracking-wide text-slate-500">{title}</h2>
      <p className="mt-2 text-3xl font-semibold">{value}</p>
      <Link to={link} className="mt-2 inline-block text-sm font-medium text-brand-600 hover:underline">
        {linkLabel}
      </Link>
    </section>
  );
}

function RecentList({
  title,
  items,
  emptyMessage,
}: {
  title: string;
  items: { key: string; to: string; primary: string; secondary: string }[];
  emptyMessage: string;
}) {
  return (
    <section className="card p-5">
      <h2 className="text-sm font-semibold">{title}</h2>

      {items.length === 0 ? (
        <p className="mt-3 text-sm text-slate-500">{emptyMessage}</p>
      ) : (
        <ul className="mt-3 divide-y divide-slate-100">
          {items.map((item) => (
            <li key={item.key} className="py-2">
              <Link to={item.to} className="font-medium text-brand-600 hover:underline">
                {item.primary}
              </Link>
              <p className="text-xs text-slate-500">{item.secondary}</p>
            </li>
          ))}
        </ul>
      )}
    </section>
  );
}
