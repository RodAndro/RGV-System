import { describe, expect, it, vi } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { MemoryRouter, Route, Routes } from 'react-router-dom';
import { AuthProvider } from '../auth/AuthProvider';
import { InventoryListPage } from '../pages/InventoryListPage';

/**
 * Main workflow test (Q2): the inventory list loads from the REST API,
 * searching re-queries with the right parameter, and pagination moves to
 * the next page.
 */

const categories = {
  data: [
    { id: 1, name: 'Tools', slug: 'tools', description: null, is_active: true },
    { id: 2, name: 'Electrical', slug: 'electrical', description: null, is_active: true },
  ],
  meta: { page: 1, limit: 100, total: 2, total_pages: 1 },
};

function inventoryPage(names: string[], meta: Record<string, number>) {
  return {
    data: names.map((name, index) => ({
      id: index + 1,
      item_code: `INV-000${index + 1}`,
      name,
      brand: null,
      description: null,
      category_id: 1,
      category: { id: 1, name: 'Tools' },
      quantity: 10,
      unit: 'pcs',
      unit_cost: '12.00',
      status: 'available',
      condition: 'good',
      location: 'Main Warehouse',
      low_stock_threshold: 3,
      is_low_stock: false,
      is_active: true,
      date_added: '2026-11-01',
      created_at: '2026-11-01T00:00:00Z',
      updated_at: '2026-11-01T00:00:00Z',
    })),
    meta,
  };
}

function jsonResponse(body: unknown, status = 200) {
  return new Response(JSON.stringify(body), {
    status,
    headers: { 'Content-Type': 'application/json' },
  });
}

function renderInventory() {
  const queryClient = new QueryClient({
    defaultOptions: { queries: { retry: false } },
  });

  window.localStorage.setItem('rgv.token', 'test-token');

  return render(
    <QueryClientProvider client={queryClient}>
      <MemoryRouter initialEntries={['/inventories']}>
        <AuthProvider>
          <Routes>
            <Route path="/inventories" element={<InventoryListPage />} />
          </Routes>
        </AuthProvider>
      </MemoryRouter>
    </QueryClientProvider>,
  );
}

describe('InventoryListPage workflow', () => {
  const user = {
    id: 1,
    name: 'Demo Admin',
    email: 'admin@example.test',
    phone: null,
    is_active: true,
    mfa_enabled: false,
    roles: ['admin'],
  };

  it('lists inventory returned by the API', async () => {
    vi.spyOn(globalThis, 'fetch').mockImplementation(async (input) => {
      const url = String(input);
      if (url.includes('/auth/me')) return jsonResponse({ data: user });
      if (url.includes('/inventory-categories')) return jsonResponse(categories);
      return jsonResponse(
        inventoryPage(['Adjustable Wrench', 'Pipe Wrench'], {
          page: 1,
          limit: 10,
          total: 2,
          total_pages: 1,
        }),
      );
    });

    renderInventory();

    expect(await screen.findByRole('link', { name: 'Adjustable Wrench' })).toBeInTheDocument();
    expect(screen.getByRole('link', { name: 'Pipe Wrench' })).toBeInTheDocument();
    expect(screen.getByText(/2 items/i)).toBeInTheDocument();
  });

  it('re-queries the API with the search term', async () => {
    const fetchedUrls: string[] = [];

    vi.spyOn(globalThis, 'fetch').mockImplementation(async (input) => {
      const url = String(input);
      fetchedUrls.push(url);

      if (url.includes('/auth/me')) return jsonResponse({ data: user });
      if (url.includes('/inventory-categories')) return jsonResponse(categories);
      if (url.includes('q=pipe')) {
        return jsonResponse(
          inventoryPage(['Pipe Wrench'], { page: 1, limit: 10, total: 1, total_pages: 1 }),
        );
      }

      return jsonResponse(
        inventoryPage(['Adjustable Wrench', 'Pipe Wrench'], {
          page: 1,
          limit: 10,
          total: 2,
          total_pages: 1,
        }),
      );
    });

    const userEventInstance = userEvent.setup();
    renderInventory();

    await screen.findByRole('link', { name: 'Adjustable Wrench' });

    await userEventInstance.type(screen.getByLabelText(/^search$/i), 'pipe');
    await userEventInstance.click(screen.getByRole('button', { name: /apply search/i }));

    await waitFor(() => {
      expect(fetchedUrls.some((url) => url.includes('/api/v1/inventories') && url.includes('q=pipe'))).toBe(
        true,
      );
    });

    await waitFor(() => {
      expect(screen.queryByRole('link', { name: 'Adjustable Wrench' })).not.toBeInTheDocument();
    });
    expect(screen.getByRole('link', { name: 'Pipe Wrench' })).toBeInTheDocument();
  });

  it('requests the next page when pagination is used', async () => {
    const fetchedUrls: string[] = [];

    vi.spyOn(globalThis, 'fetch').mockImplementation(async (input) => {
      const url = String(input);
      fetchedUrls.push(url);

      if (url.includes('/auth/me')) return jsonResponse({ data: user });
      if (url.includes('/inventory-categories')) return jsonResponse(categories);
      if (url.includes('page=2')) {
        return jsonResponse(
          inventoryPage(['Second Page Item'], { page: 2, limit: 10, total: 15, total_pages: 2 }),
        );
      }

      return jsonResponse(
        inventoryPage(['First Page Item'], { page: 1, limit: 10, total: 15, total_pages: 2 }),
      );
    });

    const userEventInstance = userEvent.setup();
    renderInventory();

    await screen.findByRole('link', { name: 'First Page Item' });
    expect(screen.getByText(/page 1 of 2/i)).toBeInTheDocument();

    await userEventInstance.click(screen.getByRole('button', { name: /^next$/i }));

    expect(await screen.findByRole('link', { name: 'Second Page Item' })).toBeInTheDocument();
    expect(
      fetchedUrls.some((url) => url.includes('/api/v1/inventories') && url.includes('page=2')),
    ).toBe(true);
  });
});
