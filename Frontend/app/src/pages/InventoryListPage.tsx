import { useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { useAuth } from '../auth/AuthProvider';
import { listCategories, listInventories } from '../lib/api';
import { StatusBadge } from '../components/StatusBadge';
import { ErrorBanner, EmptyState, LoadingState } from '../components/States';

const PAGE_SIZE = 10;

export function InventoryListPage() {
  const { isAdmin } = useAuth();
  const [searchParams, setSearchParams] = useSearchParams();
  const [searchTerm, setSearchTerm] = useState(searchParams.get('q') ?? '');

  // The URL is the source of truth for list state, so filters survive a
  // refresh and can be shared or bookmarked.
  const page = Number(searchParams.get('page') ?? '1') || 1;
  const status = searchParams.get('status') ?? '';
  const categoryId = searchParams.get('category_id') ?? '';
  const sort = searchParams.get('sort') ?? '-created_at';

  const categoriesQuery = useQuery({
    queryKey: ['categories'],
    queryFn: listCategories,
  });

  const inventoryQuery = useQuery({
    queryKey: ['inventories', { page, status, categoryId, sort, q: searchParams.get('q') ?? '' }],
    queryFn: () =>
      listInventories({
        page,
        limit: PAGE_SIZE,
        sort,
        status: status || undefined,
        category_id: categoryId ? Number(categoryId) : undefined,
        q: searchParams.get('q') || undefined,
      }),
  });

  const updateParam = (key: string, value: string) => {
    const next = new URLSearchParams(searchParams);
    if (value) {
      next.set(key, value);
    } else {
      next.delete(key);
    }
    // Any filter change resets to the first page.
    if (key !== 'page') {
      next.delete('page');
    }
    setSearchParams(next, { replace: true });
  };

  const submitSearch = (event: React.FormEvent) => {
    event.preventDefault();
    updateParam('q', searchTerm.trim());
  };

  const clearFilters = () => {
    setSearchTerm('');
    setSearchParams(new URLSearchParams(), { replace: true });
  };

  const items = inventoryQuery.data?.data ?? [];
  const meta = inventoryQuery.data?.meta;
  const hasFilters = Boolean(status || categoryId || searchParams.get('q'));

  return (
    <div className="space-y-5">
      <header className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-xl font-semibold">Inventory</h1>
          <p className="text-sm text-slate-600">
            {meta ? `${meta.total} item${meta.total === 1 ? '' : 's'}` : 'Loading items…'}
          </p>
        </div>

        {isAdmin && (
          <Link to="/inventories/new" className="btn-primary">
            Add inventory item
          </Link>
        )}
      </header>

      <section className="card p-4" aria-label="Search and filters">
        <form className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4" onSubmit={submitSearch}>
          <div className="sm:col-span-2 lg:col-span-1">
            <label className="field-label" htmlFor="search">
              Search
            </label>
            <input
              id="search"
              type="search"
              className="field-input"
              placeholder="Name, code or brand"
              value={searchTerm}
              onChange={(event) => setSearchTerm(event.target.value)}
            />
          </div>

          <div>
            <label className="field-label" htmlFor="status">
              Status
            </label>
            <select
              id="status"
              className="field-input"
              value={status}
              onChange={(event) => updateParam('status', event.target.value)}
            >
              <option value="">All statuses</option>
              <option value="available">Available</option>
              <option value="borrowed">Borrowed</option>
              <option value="maintenance">Maintenance</option>
              <option value="damaged">Damaged</option>
            </select>
          </div>

          <div>
            <label className="field-label" htmlFor="category">
              Category
            </label>
            <select
              id="category"
              className="field-input"
              value={categoryId}
              onChange={(event) => updateParam('category_id', event.target.value)}
            >
              <option value="">All categories</option>
              {(categoriesQuery.data?.data ?? []).map((category) => (
                <option key={category.id} value={category.id}>
                  {category.name}
                </option>
              ))}
            </select>
          </div>

          <div>
            <label className="field-label" htmlFor="sort">
              Sort by
            </label>
            <select
              id="sort"
              className="field-input"
              value={sort}
              onChange={(event) => updateParam('sort', event.target.value)}
            >
              <option value="-created_at">Newest first</option>
              <option value="created_at">Oldest first</option>
              <option value="name">Name (A–Z)</option>
              <option value="-name">Name (Z–A)</option>
              <option value="quantity">Quantity (low to high)</option>
              <option value="-quantity">Quantity (high to low)</option>
            </select>
          </div>

          <div className="flex items-end gap-2 sm:col-span-2 lg:col-span-4">
            <button type="submit" className="btn-primary">
              Apply search
            </button>
            {hasFilters && (
              <button type="button" className="btn-secondary" onClick={clearFilters}>
                Clear filters
              </button>
            )}
          </div>
        </form>
      </section>

      {inventoryQuery.isError && <ErrorBanner error={inventoryQuery.error} />}

      {inventoryQuery.isPending && <LoadingState label="Loading inventory…" />}

      {inventoryQuery.isSuccess && items.length === 0 && (
        <EmptyState message="No inventory items match the current filters." />
      )}

      {items.length > 0 && (
        <>
          <div className="card overflow-x-auto">
            <table className="min-w-full divide-y divide-slate-200 text-sm">
              <caption className="sr-only">
                Inventory items with code, category, quantity and status
              </caption>
              <thead className="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                  <th scope="col" className="px-4 py-3">
                    Code
                  </th>
                  <th scope="col" className="px-4 py-3">
                    Name
                  </th>
                  <th scope="col" className="px-4 py-3">
                    Category
                  </th>
                  <th scope="col" className="px-4 py-3">
                    Qty
                  </th>
                  <th scope="col" className="px-4 py-3">
                    Status
                  </th>
                  <th scope="col" className="px-4 py-3">
                    <span className="sr-only">Actions</span>
                  </th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {items.map((item) => (
                  <tr key={item.id} className="hover:bg-slate-50">
                    <td className="whitespace-nowrap px-4 py-3 font-mono text-xs">{item.item_code}</td>
                    <td className="px-4 py-3">
                      <Link className="font-medium text-brand-600 hover:underline" to={`/inventories/${item.id}`}>
                        {item.name}
                      </Link>
                      {item.is_low_stock && (
                        <span className="ml-2 badge bg-orange-100 text-orange-800">Low stock</span>
                      )}
                    </td>
                    <td className="px-4 py-3 text-slate-600">{item.category?.name ?? '—'}</td>
                    <td className="px-4 py-3">
                      {item.quantity} {item.unit}
                    </td>
                    <td className="px-4 py-3">
                      <StatusBadge status={item.status} />
                    </td>
                    <td className="px-4 py-3 text-right">
                      <Link
                        to={`/inventories/${item.id}`}
                        className="text-sm font-medium text-brand-600 hover:underline"
                      >
                        View
                        <span className="sr-only"> {item.name}</span>
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
