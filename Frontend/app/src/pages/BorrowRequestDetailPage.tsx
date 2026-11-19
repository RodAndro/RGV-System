import { Link, useParams } from 'react-router-dom';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { getBorrowRequest, updateBorrowRequestStatus } from '../lib/api';
import { useAuth } from '../auth/AuthProvider';
import { StatusBadge } from '../components/StatusBadge';
import { ErrorBanner, LoadingState } from '../components/States';

export function BorrowRequestDetailPage() {
  const { id } = useParams<{ id: string }>();
  const requestId = Number(id);
  const { isAdmin } = useAuth();
  const queryClient = useQueryClient();

  const query = useQuery({
    queryKey: ['borrow-request', requestId],
    queryFn: () => getBorrowRequest(requestId),
    enabled: Number.isFinite(requestId),
  });

  const transition = useMutation({
    mutationFn: (status: 'approved' | 'rejected' | 'borrowed') =>
      updateBorrowRequestStatus(requestId, status),
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: ['borrow-request', requestId] });
      await queryClient.invalidateQueries({ queryKey: ['borrow-requests'] });
    },
  });

  if (query.isPending) return <LoadingState label="Loading request…" />;
  if (query.isError) return <ErrorBanner error={query.error} />;

  const request = query.data!;
  const canApprove = isAdmin && request.status === 'pending';
  const canMarkBorrowed = isAdmin && request.status === 'approved';

  return (
    <div className="space-y-5">
      <nav aria-label="Breadcrumb" className="text-sm text-slate-500">
        <Link to="/borrow-requests" className="hover:underline">
          Borrow requests
        </Link>
        <span aria-hidden="true"> / </span>
        <span className="text-slate-700">{request.request_number}</span>
      </nav>

      <header className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h1 className="text-xl font-semibold">{request.request_number}</h1>
          <p className="text-sm text-slate-600">{request.reason}</p>
        </div>
        <StatusBadge status={request.status} />
      </header>

      {transition.isError && <ErrorBanner error={transition.error} />}

      {isAdmin && (canApprove || canMarkBorrowed) && (
        <section className="card space-y-3 p-4" aria-label="Administrator actions">
          <h2 className="text-sm font-semibold">Administrator actions</h2>
          <div className="flex flex-wrap gap-2">
            {canApprove && (
              <>
                <button
                  type="button"
                  className="btn-primary"
                  disabled={transition.isPending}
                  onClick={() => transition.mutate('approved')}
                >
                  Approve
                </button>
                <button
                  type="button"
                  className="btn-danger"
                  disabled={transition.isPending}
                  onClick={() => transition.mutate('rejected')}
                >
                  Reject
                </button>
              </>
            )}
            {canMarkBorrowed && (
              <button
                type="button"
                className="btn-primary"
                disabled={transition.isPending}
                onClick={() => transition.mutate('borrowed')}
              >
                Mark as borrowed
              </button>
            )}
          </div>
        </section>
      )}

      <dl className="card grid gap-4 p-5 sm:grid-cols-2">
        <Detail label="Employee" value={request.employee?.name ?? '—'} />
        <Detail label="Borrow date" value={request.borrow_date ?? '—'} />
        <Detail label="Due date" value={request.due_date ?? '—'} />
        <Detail label="Return date" value={request.return_date ?? '—'} />
        <Detail label="Return location" value={request.return_location ?? '—'} />
        <Detail label="Remarks" value={request.admin_remarks ?? '—'} />
      </dl>

      <section className="card overflow-x-auto">
        <table className="min-w-full divide-y divide-slate-200 text-sm">
          <caption className="sr-only">Items in this borrow request</caption>
          <thead className="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
            <tr>
              <th scope="col" className="px-4 py-3">Item</th>
              <th scope="col" className="px-4 py-3">Quantity</th>
              <th scope="col" className="px-4 py-3">Returned</th>
              <th scope="col" className="px-4 py-3">Condition returned</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100">
            {request.items.map((item) => (
              <tr key={item.id}>
                <td className="px-4 py-3">
                  <span className="font-medium">{item.inventory?.name ?? '—'}</span>
                  <span className="ml-2 font-mono text-xs text-slate-500">
                    {item.inventory?.item_code}
                  </span>
                </td>
                <td className="px-4 py-3">
                  {item.quantity} {item.inventory?.unit}
                </td>
                <td className="px-4 py-3">{item.is_returned ? 'Yes' : 'No'}</td>
                <td className="px-4 py-3">{item.condition_returned ?? '—'}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </section>
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
