import { describe, expect, it, vi, beforeEach } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { MemoryRouter, Route, Routes } from 'react-router-dom';
import { AuthProvider } from '../auth/AuthProvider';
import { LoginPage } from '../pages/LoginPage';
import { DashboardPage } from '../pages/DashboardPage';

function renderLogin() {
  const queryClient = new QueryClient({
    defaultOptions: { queries: { retry: false }, mutations: { retry: false } },
  });

  return render(
    <QueryClientProvider client={queryClient}>
      <MemoryRouter initialEntries={['/login']}>
        <AuthProvider>
          <Routes>
            <Route path="/login" element={<LoginPage />} />
            <Route path="/dashboard" element={<DashboardPage />} />
          </Routes>
        </AuthProvider>
      </MemoryRouter>
    </QueryClientProvider>,
  );
}

describe('LoginPage', () => {
  beforeEach(() => {
    window.localStorage.clear();
  });

  it('renders labelled email and password fields', () => {
    renderLogin();

    expect(screen.getByLabelText(/email address/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/^password/i)).toBeInTheDocument();
    expect(screen.getByRole('button', { name: /sign in/i })).toBeInTheDocument();
  });

  it('shows client-side validation errors and does not call the API', async () => {
    const fetchSpy = vi.spyOn(globalThis, 'fetch');
    const user = userEvent.setup();
    renderLogin();

    await user.click(screen.getByRole('button', { name: /sign in/i }));

    expect(await screen.findByText(/email is required/i)).toBeInTheDocument();
    expect(screen.getByText(/password is required/i)).toBeInTheDocument();
    expect(fetchSpy).not.toHaveBeenCalled();
  });

  it('rejects a malformed email before submitting', async () => {
    const fetchSpy = vi.spyOn(globalThis, 'fetch');
    const user = userEvent.setup();
    renderLogin();

    await user.type(screen.getByLabelText(/email address/i), 'not-an-email');
    await user.type(screen.getByLabelText(/^password/i), 'secret-password');
    await user.click(screen.getByRole('button', { name: /sign in/i }));

    expect(await screen.findByText(/valid email address/i)).toBeInTheDocument();
    expect(fetchSpy).not.toHaveBeenCalled();
  });

  it('surfaces the server error message when credentials are rejected', async () => {
    vi.spyOn(globalThis, 'fetch').mockResolvedValue(
      new Response(
        JSON.stringify({
          error: {
            code: 'UNAUTHENTICATED',
            message: 'These credentials do not match our records.',
            details: [],
          },
        }),
        { status: 401, headers: { 'Content-Type': 'application/json' } },
      ),
    );

    const user = userEvent.setup();
    renderLogin();

    await user.type(screen.getByLabelText(/email address/i), 'employee@example.test');
    await user.type(screen.getByLabelText(/^password/i), 'wrong-password');
    await user.click(screen.getByRole('button', { name: /sign in/i }));

    expect(await screen.findByRole('alert')).toHaveTextContent(
      /these credentials do not match our records/i,
    );
  });

  it('stores the token and renders the dashboard after a successful login', async () => {
    const user = {
      id: 2,
      name: 'Demo Employee',
      email: 'employee@example.test',
      phone: null,
      is_active: true,
      mfa_enabled: false,
      roles: ['employee'],
    };

    vi.spyOn(globalThis, 'fetch').mockImplementation(async (input) => {
      const url = String(input);

      if (url.includes('/auth/login')) {
        return new Response(
          JSON.stringify({ token: 'test-token', token_type: 'Bearer', user }),
          { status: 200, headers: { 'Content-Type': 'application/json' } },
        );
      }

      if (url.includes('/auth/me')) {
        return new Response(JSON.stringify({ data: user }), {
          status: 200,
          headers: { 'Content-Type': 'application/json' },
        });
      }

      // Dashboard data queries.
      return new Response(
        JSON.stringify({ data: [], meta: { page: 1, limit: 5, total: 0, total_pages: 0 } }),
        { status: 200, headers: { 'Content-Type': 'application/json' } },
      );
    });

    const userEventInstance = userEvent.setup();
    renderLogin();

    await userEventInstance.type(screen.getByLabelText(/email address/i), user.email);
    await userEventInstance.type(screen.getByLabelText(/^password/i), 'secret-password');
    await userEventInstance.click(screen.getByRole('button', { name: /sign in/i }));

    await waitFor(() => {
      expect(window.localStorage.getItem('rgv.token')).toBe('test-token');
    });

    expect(await screen.findByText(/welcome, demo employee/i)).toBeInTheDocument();
  });
});
