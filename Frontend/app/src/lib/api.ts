/**
 * Typed API client.
 *
 * Every network call in this app goes through here, and every call
 * targets the team's REST API under /api/v1. The client never talks to
 * a database or an integration service directly.
 */

export type ApiErrorDetail = {
  field: string;
  message: string;
};

export class ApiError extends Error {
  readonly status: number;
  readonly code: string;
  readonly details: ApiErrorDetail[];

  constructor(status: number, code: string, message: string, details: ApiErrorDetail[] = []) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.code = code;
    this.details = details;
  }

  /** Field-level messages keyed by field name, for form display. */
  fieldErrors(): Record<string, string> {
    return this.details.reduce<Record<string, string>>((accumulator, detail) => {
      if (!accumulator[detail.field]) {
        accumulator[detail.field] = detail.message;
      }
      return accumulator;
    }, {});
  }
}

const TOKEN_KEY = 'rgv.token';

export function getToken(): string | null {
  return window.localStorage.getItem(TOKEN_KEY);
}

export function setToken(token: string | null): void {
  if (token) {
    window.localStorage.setItem(TOKEN_KEY, token);
  } else {
    window.localStorage.removeItem(TOKEN_KEY);
  }
}

export type Meta = {
  page: number;
  limit: number;
  total: number;
  total_pages: number;
};

export type ListResponse<T> = {
  data: T[];
  meta: Meta;
};

export type User = {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  is_active: boolean;
  mfa_enabled: boolean;
  roles: string[];
};

export type LoginResponse = {
  token?: string;
  token_type?: string;
  user?: User;
  mfa_required?: boolean;
  mfa_type?: string;
  mfa_token?: string;
};

type RequestOptions = {
  method?: string;
  body?: unknown;
  params?: Record<string, string | number | boolean | undefined | null>;
  signal?: AbortSignal;
};

function buildUrl(path: string, params?: RequestOptions['params']): string {
  const url = `/api/v1${path}`;
  if (!params) return url;

  const search = new URLSearchParams();
  Object.entries(params).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') {
      search.set(key, String(value));
    }
  });

  const query = search.toString();
  return query ? `${url}?${query}` : url;
}

export async function apiRequest<T>(path: string, options: RequestOptions = {}): Promise<T> {
  const { method = 'GET', body, params, signal } = options;
  const token = getToken();

  const headers: Record<string, string> = {
    Accept: 'application/json',
  };

  if (body !== undefined) {
    headers['Content-Type'] = 'application/json';
  }

  if (token) {
    headers.Authorization = `Bearer ${token}`;
  }

  const response = await fetch(buildUrl(path, params), {
    method,
    headers,
    signal,
    body: body === undefined ? undefined : JSON.stringify(body),
  });

  if (response.status === 204) {
    return undefined as T;
  }

  const contentType = response.headers.get('content-type') ?? '';
  const payload = contentType.includes('application/json') ? await response.json() : null;

  if (!response.ok) {
    const error = payload?.error;

    // A 401 on a protected call means the stored token is no longer usable.
    if (response.status === 401 && token && path !== '/auth/login') {
      setToken(null);
    }

    throw new ApiError(
      response.status,
      error?.code ?? 'REQUEST_FAILED',
      error?.message ?? 'The request could not be completed.',
      error?.details ?? [],
    );
  }

  return payload as T;
}

/* ------------------------------------------------------------------ */
/* Auth                                                                */
/* ------------------------------------------------------------------ */

export async function login(email: string, password: string): Promise<LoginResponse> {
  const response = await apiRequest<LoginResponse>('/auth/login', {
    method: 'POST',
    body: { email, password, device_name: 'web' },
  });

  if (response.token && !response.mfa_required) {
    setToken(response.token);
  }

  return response;
}

export async function logout(): Promise<void> {
  try {
    await apiRequest<unknown>('/auth/logout', { method: 'POST' });
  } finally {
    setToken(null);
  }
}

export async function fetchMe(): Promise<User> {
  const response = await apiRequest<{ data: User }>('/auth/me');
  return response.data;
}

/* ------------------------------------------------------------------ */
/* Resources                                                           */
/* ------------------------------------------------------------------ */

export type InventoryCategory = {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  is_active: boolean;
};

export type InventoryItem = {
  id: number;
  item_code: string;
  name: string;
  brand: string | null;
  description: string | null;
  category_id: number;
  category: { id: number; name: string } | null;
  quantity: number;
  unit: string;
  unit_cost: string | null;
  status: 'available' | 'borrowed' | 'maintenance' | 'damaged';
  condition: 'new' | 'good' | 'fair' | 'poor';
  location: string | null;
  low_stock_threshold: number;
  is_low_stock: boolean;
  is_active: boolean;
  date_added: string | null;
  created_at: string | null;
  updated_at: string | null;
};

export type InventoryInput = {
  item_code: string;
  name: string;
  brand?: string;
  description?: string;
  category_id: number;
  quantity: number;
  unit: string;
  unit_cost?: number;
  status: InventoryItem['status'];
  condition: InventoryItem['condition'];
  location?: string;
  low_stock_threshold: number;
  date_added: string;
};

export type ListParams = {
  page?: number;
  limit?: number;
  sort?: string;
  q?: string;
  status?: string;
  category_id?: number;
};

export function listInventories(params: ListParams = {}) {
  return apiRequest<ListResponse<InventoryItem>>('/inventories', { params });
}

export function getInventory(id: number) {
  return apiRequest<{ data: InventoryItem }>(`/inventories/${id}`).then((response) => response.data);
}

export function createInventory(input: InventoryInput) {
  return apiRequest<{ data: InventoryItem }>('/inventories', { method: 'POST', body: input }).then(
    (response) => response.data,
  );
}

export function updateInventory(id: number, input: Partial<InventoryInput>) {
  return apiRequest<{ data: InventoryItem }>(`/inventories/${id}`, {
    method: 'PATCH',
    body: input,
  }).then((response) => response.data);
}

export function deleteInventory(id: number) {
  return apiRequest<void>(`/inventories/${id}`, { method: 'DELETE' });
}

export function listCategories() {
  return apiRequest<ListResponse<InventoryCategory>>('/inventory-categories', {
    params: { limit: 100, sort: 'name' },
  });
}

export type BorrowItem = {
  id: number;
  inventory_id: number;
  inventory: { id: number; item_code: string; name: string; unit: string } | null;
  quantity: number;
  reserved_quantity: number;
  condition_borrowed: string;
  condition_returned: string | null;
  is_returned: boolean;
  returned_at: string | null;
  damage_notes: string | null;
};

export type BorrowRequestRecord = {
  id: number;
  request_number: string;
  status: 'pending' | 'approved' | 'borrowed' | 'returned' | 'rejected' | 'cancelled';
  reason: string;
  employee_id: number;
  employee: { id: number; name: string; email: string } | null;
  approved_by: number | null;
  admin_remarks: string | null;
  borrow_date: string | null;
  due_date: string | null;
  return_date: string | null;
  return_location: string | null;
  created_at: string | null;
  items: BorrowItem[];
};

export function listBorrowRequests(params: ListParams = {}) {
  return apiRequest<ListResponse<BorrowRequestRecord>>('/borrow-requests', { params });
}

export function getBorrowRequest(id: number) {
  return apiRequest<{ data: BorrowRequestRecord }>(`/borrow-requests/${id}`).then(
    (response) => response.data,
  );
}

export type BorrowRequestInput = {
  reason: string;
  borrow_date: string;
  due_date: string;
  items: { inventory_id: number; quantity: number }[];
};

export function createBorrowRequest(input: BorrowRequestInput) {
  return apiRequest<{ data: BorrowRequestRecord }>('/borrow-requests', {
    method: 'POST',
    body: input,
  }).then((response) => response.data);
}

export function updateBorrowRequestStatus(
  id: number,
  status: 'approved' | 'rejected' | 'borrowed' | 'returned',
  extra: Record<string, unknown> = {},
) {
  return apiRequest<{ data: BorrowRequestRecord }>(`/borrow-requests/${id}`, {
    method: 'PATCH',
    body: { status, ...extra },
  }).then((response) => response.data);
}
