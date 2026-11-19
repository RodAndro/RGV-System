import { Link, useParams } from 'react-router-dom';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useState } from 'react';
import { deleteInventory, getInventory } from '../lib/api';
import { useAuth } from '../auth/AuthProvider';
import { StatusBadge } from '../components/StatusBadge';
import { ErrorBanner, LoadingState } from '../components/States';

export function InventoryDetailPage() {
  const { id } = useParams<{ id: string }>();
  const inventoryId = Number(id);
  const { isAdmin } = useAuth();
  const queryClient = useQueryClient();
  const [confirming, setConfirming] = useState(false);

  const query = useQuery({
    queryKey: ['inventory', inventoryId],
    queryFn: () => getInventory(inventoryId),
    enabled: Number.isFinite(inventoryId),
  });

  const remove = useMutation({
    mutationFn: () => deleteInventory(inventoryId),
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: ['inventories'] });
    },
  });

  if (query.isPending) return <LoadingState label="Loading item…" />;
  if (query.isError) return <ErrorBanner error={query.error} />;

  const item = query.data!;

  return (
    <div className="space-y-5">
      <nav aria-label="Breadcrumb" className="text-sm text-slate-500">
        <Link to="/inventories" className="hover:underline">
          Inventory
        </Link>
        <span aria-hidden="true"> / </span>
        <span className="text-slate-700">{item.name}</span>
      </nav>

      <header className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h1 className="text-xl font-semibold">{item.name}</h1>
          <p className="font-mono text-xs text-slate-500">{item.item_code}</p>
        </div>

        <div className="flex flex-wrap gap-2">
          <StatusBadge status={item.status} />
          {isAdmin && (
            <>
              <Link to={`/inventories/${item.id}/edit`} className="btn-secondary">
                Edit
              </Link>
              <button type="button" className="btn-danger" onClick={() => setConfirming(true)}>
                Delete
              </button>
            </>
          )}
        </div>
      </header>

      {remove.isError && <ErrorBanner error={remove.error} />}

      {confirming && (
        <div className="card border-red-200 p-4" role="alertdialog" aria-labelledby="confirm-delete">
          <h2 id="confirm-delete" className="font-medium text-red-800">
            Delete this inventory item?
          </h2>
          <p className="mt-1 text-sm text-slate-600">
            The item is soft-deleted and can be restored by an administrator.
          </p>
          <div className="mt-3 flex gap-2">
            <button
              type="button"
              className="btn-danger"
              disabled={remove.isPending}
              onClick={() => remove.mutate()}
            >
              {remove.isPending ? 'Deleting…' : 'Yes, delete'}
            </button>
            <button type="button" className="btn-secondary" onClick={() => setConfirming(false)}>
              Cancel
            </button>
          </div>
        </div>
      )}

      <dl className="card grid gap-4 p-5 sm:grid-cols-2">
        <Detail label="Category" value={item.category?.name ?? '—'} />
        <Detail label="Quantity" value={`${item.quantity} ${item.unit}`} />
        <Detail label="Condition" value={item.condition} />
        <Detail label="Location" value={item.location ?? '—'} />
        <Detail label="Unit cost" value={item.unit_cost ? `₱${item.unit_cost}` : '—'} />
        <Detail label="Brand" value={item.brand ?? '—'} />
        <Detail label="Low stock threshold" value={String(item.low_stock_threshold)} />
        <Detail label="Date added" value={item.date_added ?? '—'} />
        <div className="sm:col-span-2">
          <dt className="text-xs font-medium uppercase tracking-wide text-slate-500">Description</dt>
          <dd className="mt-1 text-sm text-slate-700">{item.description || '—'}</dd>
        </div>
      </dl>
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
