const STATUS_STYLES: Record<string, string> = {
  available: 'bg-emerald-100 text-emerald-800',
  borrowed: 'bg-amber-100 text-amber-800',
  maintenance: 'bg-sky-100 text-sky-800',
  damaged: 'bg-red-100 text-red-800',
  pending: 'bg-amber-100 text-amber-800',
  approved: 'bg-sky-100 text-sky-800',
  returned: 'bg-emerald-100 text-emerald-800',
  rejected: 'bg-red-100 text-red-800',
  cancelled: 'bg-slate-200 text-slate-700',
  completed: 'bg-emerald-100 text-emerald-800',
};

export function StatusBadge({ status }: { status: string }) {
  const style = STATUS_STYLES[status] ?? 'bg-slate-200 text-slate-700';

  return (
    <span className={`badge ${style}`}>
      <span className="sr-only">Status: </span>
      {status}
    </span>
  );
}
