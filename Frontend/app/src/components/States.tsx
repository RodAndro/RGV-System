import { ApiError } from '../lib/api';

/**
 * Renders a server error the user can actually read (F3).
 *
 * Field-level details from the API's error envelope are listed
 * individually instead of being swallowed into one vague message.
 */
export function ErrorBanner({ error }: { error: unknown }) {
  if (!error) return null;

  const apiError = error instanceof ApiError ? error : null;
  const message =
    apiError?.message ?? (error instanceof Error ? error.message : 'Something went wrong.');

  const details = apiError?.details ?? [];

  return (
    <div
      className="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800"
      role="alert"
    >
      <p className="font-medium">{message}</p>

      {details.length > 0 && (
        <ul className="mt-2 list-disc space-y-1 pl-5">
          {details.map((detail) => (
            <li key={`${detail.field}-${detail.message}`}>
              <span className="font-medium">{detail.field}</span>: {detail.message}
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}

export function EmptyState({ message }: { message: string }) {
  return (
    <div className="card p-8 text-center text-sm text-slate-500" role="status">
      {message}
    </div>
  );
}

export function LoadingState({ label = 'Loading…' }: { label?: string }) {
  return (
    <div className="card p-8 text-center text-sm text-slate-500" role="status" aria-live="polite">
      {label}
    </div>
  );
}
