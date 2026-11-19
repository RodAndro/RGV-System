import { useFieldArray, useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Link, useNavigate } from 'react-router-dom';
import { ApiError, createBorrowRequest, listInventories, type BorrowRequestInput } from '../lib/api';
import { Field, fieldAria } from '../components/Field';
import { ErrorBanner } from '../components/States';

const schema = z
  .object({
    reason: z.string().min(1, 'A reason is required.').max(1000),
    borrow_date: z.string().min(1, 'Borrow date is required.'),
    due_date: z.string().min(1, 'Due date is required.'),
    items: z
      .array(
        z.object({
          inventory_id: z.coerce.number().int().positive('Choose an item.'),
          quantity: z.coerce.number().int().min(1, 'Quantity must be at least 1.'),
        }),
      )
      .min(1, 'Add at least one item.'),
  })
  .refine((values) => values.due_date >= values.borrow_date, {
    path: ['due_date'],
    message: 'The due date cannot be before the borrow date.',
  });

type FormValues = z.input<typeof schema>;

export function BorrowRequestFormPage() {
  const navigate = useNavigate();
  const queryClient = useQueryClient();

  const inventoryQuery = useQuery({
    queryKey: ['inventories', 'available-for-borrow'],
    queryFn: () => listInventories({ limit: 100, status: 'available', sort: 'name' }),
  });

  const {
    register,
    control,
    handleSubmit,
    setError,
    formState: { errors, isSubmitting },
  } = useForm<FormValues>({
    resolver: zodResolver(schema),
    defaultValues: {
      reason: '',
      borrow_date: new Date().toISOString().slice(0, 10),
      due_date: new Date(Date.now() + 2 * 86_400_000).toISOString().slice(0, 10),
      items: [{ inventory_id: '' as unknown as number, quantity: 1 }],
    },
  });

  const { fields, append, remove } = useFieldArray({ control, name: 'items' });

  const mutation = useMutation({
    mutationFn: (payload: BorrowRequestInput) => createBorrowRequest(payload),
    onSuccess: async (saved) => {
      await queryClient.invalidateQueries({ queryKey: ['borrow-requests'] });
      await queryClient.invalidateQueries({ queryKey: ['inventories'] });
      navigate(`/borrow-requests/${saved.id}`);
    },
    onError: (error) => {
      if (error instanceof ApiError) {
        Object.entries(error.fieldErrors()).forEach(([field, message]) => {
          setError(field as keyof FormValues, { type: 'server', message });
        });
      }
    },
  });

  const onSubmit = (values: FormValues) => {
    mutation.mutate({
      reason: values.reason.trim(),
      borrow_date: values.borrow_date,
      due_date: values.due_date,
      items: values.items.map((item) => ({
        inventory_id: Number(item.inventory_id),
        quantity: Number(item.quantity),
      })),
    });
  };

  const available = inventoryQuery.data?.data ?? [];
  const itemsError = (errors.items as { message?: string } | undefined)?.message;

  return (
    <div className="space-y-5">
      <header>
        <h1 className="text-xl font-semibold">New borrow request</h1>
        <p className="text-sm text-slate-600">
          Stock is reserved as soon as the request is submitted and released if it is rejected or
          cancelled.
        </p>
      </header>

      {mutation.isError && <ErrorBanner error={mutation.error} />}

      <form className="card space-y-4 p-5" onSubmit={handleSubmit(onSubmit)} noValidate>
        <Field id="reason" label="Reason" required error={errors.reason?.message}>
          <textarea
            rows={3}
            className="field-input"
            {...fieldAria('reason', errors.reason?.message)}
            {...register('reason')}
          />
        </Field>

        <div className="grid gap-4 sm:grid-cols-2">
          <Field id="borrow_date" label="Borrow date" required error={errors.borrow_date?.message}>
            <input
              type="date"
              className="field-input"
              {...fieldAria('borrow_date', errors.borrow_date?.message)}
              {...register('borrow_date')}
            />
          </Field>

          <Field id="due_date" label="Due date" required error={errors.due_date?.message}>
            <input
              type="date"
              className="field-input"
              {...fieldAria('due_date', errors.due_date?.message)}
              {...register('due_date')}
            />
          </Field>
        </div>

        <fieldset className="space-y-3">
          <legend className="text-sm font-semibold">Items</legend>

          {itemsError && (
            <p className="field-error" role="alert">
              {itemsError}
            </p>
          )}

          {fields.map((field, index) => {
            const itemErrors = errors.items?.[index];
            const selectId = `items-${index}-inventory`;
            const quantityId = `items-${index}-quantity`;

            return (
              <div key={field.id} className="grid gap-3 rounded-md border border-slate-200 p-3 sm:grid-cols-[1fr_140px_auto]">
                <Field
                  id={selectId}
                  label={`Item ${index + 1}`}
                  required
                  error={itemErrors?.inventory_id?.message}
                >
                  <select
                    className="field-input"
                    {...fieldAria(selectId, itemErrors?.inventory_id?.message)}
                    {...register(`items.${index}.inventory_id`)}
                  >
                    <option value="">Select an item</option>
                    {available.map((item) => (
                      <option key={item.id} value={item.id}>
                        {item.name} ({item.quantity} {item.unit} available)
                      </option>
                    ))}
                  </select>
                </Field>

                <Field
                  id={quantityId}
                  label="Quantity"
                  required
                  error={itemErrors?.quantity?.message}
                >
                  <input
                    type="number"
                    min={1}
                    className="field-input"
                    {...fieldAria(quantityId, itemErrors?.quantity?.message)}
                    {...register(`items.${index}.quantity`)}
                  />
                </Field>

                <div className="flex items-end">
                  <button
                    type="button"
                    className="btn-secondary"
                    disabled={fields.length === 1}
                    onClick={() => remove(index)}
                  >
                    Remove
                    <span className="sr-only"> item {index + 1}</span>
                  </button>
                </div>
              </div>
            );
          })}

          <button
            type="button"
            className="btn-secondary"
            onClick={() => append({ inventory_id: '' as unknown as number, quantity: 1 })}
          >
            Add another item
          </button>
        </fieldset>

        <div className="flex flex-wrap gap-2">
          <button type="submit" className="btn-primary" disabled={isSubmitting || mutation.isPending}>
            {mutation.isPending ? 'Submitting…' : 'Submit request'}
          </button>
          <Link to="/borrow-requests" className="btn-secondary">
            Cancel
          </Link>
        </div>
      </form>
    </div>
  );
}
