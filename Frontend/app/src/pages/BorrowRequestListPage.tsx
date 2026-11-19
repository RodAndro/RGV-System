import { useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { useAuth } from '../auth/AuthProvider';
import { listBorrowRequests } from '../lib/api';
import { StatusBadge } from '../components/StatusBadge';
import { ErrorBanner, EmptyState, LoadingState } from '../components/States';

const PAGE_SIZE = 10;

export function BorrowRequestListPage() {
  const { isAdmin } = useAuth();
  const [searchParams, setSearchParams] = useSearchParams();
  const [searchTerm, setSearchTerm] = useState(searchParams.get('q') ?? '');

  const page = Number(searchParams.get('page') ?? '1') || 1;
  const status = searchParams.get('status') ?? '';

  const query = useQuery({
    queryKey: ['borrow-requests', { page, status, q: searchParams.get('q') ?? '' }],
    queryFn: () =>
      listBorrowRequests({
        page,
        limit: PAGE_SIZE,
        sort: '-created_at',
        status: status || undefined,
        q: searchParams.get('q') || undefined,
      }),
  });

  const updateParam = (key: string, value: string) => {
    const next = new URLSearchParams(searchParams);
    if (value) next.set(key, value);
    else next.delete(key);
    if (key !== 'page') next.delete('page');
    setSearchParams(next, { replace: true });
  };

  const rows = query.data?.data ?? [];
  const meta = query.data?.meta;

  return (
    <div className="space-y-5">
      <header className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-xl font-semibold">Borrow requests</h1>
          <p className="text-sm text-slate-600">
            {isAdmin ? 'All employee borrow requests.' : 'Your borrow requests.'}
          </p>
        </div>
        <Link to="/borrow-requests/new" className="btn-primary">
          New borrow request
        </Link>
      </header>

      <section className="card p-4" aria-label="Search and filters">
        <form
          className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3"
          onSubmit={(event) => {
            event.preventDefault();
            updateParam('q', searchTerm.trim());
          }}
        >
          <div>
            <label className="field-label" htmlFor="borrow-search">
              Search
            </label>
            <input
              id="borrow-search"
              type="search"
              className="field-input"
              placeholder="Request number or reason"
              value={searchTerm}
              onChange={(event) => setSearchTerm(event.target.value)}
            />
          </div>

          <div>
            <label className="field-label" htmlFor="borrow-status">
              Status
            </label>
            <select
              id="borrow-status"
              className="field-input"
              value={status}
              onChange={(event) => updateParam('status', event.target.value)}
            >
              <option value="">All statuses</option>
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="borrowed">Borrowed</option>
              <option value="returned">Returned</option>
              <option value="rejected">Rejected</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>

          <div className="flex items-end">
            <button type="submit" className="btn-primary">
              Apply search
            </button>
          </div>
        </form>
      </section>

      {query.isError && <ErrorBanner error={query.error} />}
      {query.isPending && <LoadingState label="Loading borrow requests…" />}
      {query.isSuccess && rows.length === 0 && (
        <EmptyState message="No borrow requests match the current filters." />
      )}

      {rows.length > 0 && (
        <>
          <div className="card overflow-x-auto">
            <table className="min-w-full divide-y divide-slate-200 text-sm">
              <caption className="sr-only">Borrow requests with status and dates</caption>
              <thead className="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                  <th scope="col" className="px-4 py-3">Request</th>
                  {isAdmin && <th scope="col" className="px-4 py-3">Employee</th>}
                  <th scope="col" className="px-4 py-3">Borrow date</th>
                  <th scope="col" className="px-4 py-3">Due date</th>
                  <th scope="col" className="px-4 py-3">Status</th>
                  <th scope="col" className="px-4 py-3">
                    <span className="sr-only">Actions</span>
                  </th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {rows.map((request) => (
                  <tr key={request.id} className="hover:bg-slate-50">
                    <td className="whitespace-nowrap px-4 py-3 font-mono text-xs">
                      {request.request_number}
                    </td>
                    {isAdmin && (
                      <td className="px-4 py-3 text-slate-600">{request.employee?.name ?? '—'}</td>
                    )}
                    <td className="whitespace-nowrap px-4 py-3">{request.borrow_date ?? '—'}</td>
                    <td className="whitespace-nowrap px-4 py-3">{request.due_date ?? '—'}</td>
                    <td className="px-4 py-3">
                      <StatusBadge status={request.status} />
                    </td>
                    <td className="px-4 py-3 text-right">
                      <Link
                        to={`/borrow-requests/${request.id}`}
                        className="text-sm font-medium text-brand-600 hover:underline"
                      >
                        View
                        <span className="sr-only"> {request.request_number}</span>
                      </Link>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          {meta && meta.total_pages > 1 && (
            <nav className="flex items-center justify-between gap-3" aria-label="Pagination">
              <p className="text-sm text-slate-600">
                Page {meta.page} of {meta.total_pages}
              </p>
              <div className="flex gap-2">
                <button
                  type="button"
                  className="btn-secondary"
                  disabled={meta.page <= 1}
                  onClick={() => updateParam('page', String(meta.page - 1))}
                >
                  Previous
                </button>
                <button
                  type="button"
                  className="btn-secondary"
                  disabled={meta.page >= meta.total_pages}
                  onClick={() => updateParam('page', String(meta.page + 1))}
                >
                  Next
                </button>
              </div>
            </nav>
          )}
        </>
      )}
    </div>
  );
}
