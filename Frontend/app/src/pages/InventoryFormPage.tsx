import { useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Link, useNavigate, useParams } from 'react-router-dom';
import {
  ApiError,
  createInventory,
  getInventory,
  listCategories,
  updateInventory,
  type InventoryInput,
} from '../lib/api';
import { Field, fieldAria } from '../components/Field';
import { ErrorBanner, LoadingState } from '../components/States';

const schema = z.object({
  item_code: z.string().min(1, 'Item code is required.').max(255),
  name: z.string().min(1, 'Name is required.').max(255),
  brand: z.string().max(255).optional(),
  description: z.string().optional(),
  category_id: z.coerce.number().int().positive('Choose a category.'),
  quantity: z.coerce.number().int().min(0, 'Quantity cannot be negative.'),
  unit: z.string().min(1, 'Unit is required.').max(50),
  unit_cost: z.union([z.coerce.number().min(0, 'Cost cannot be negative.'), z.literal('')]).optional(),
  status: z.enum(['available', 'borrowed', 'maintenance', 'damaged']),
  condition: z.enum(['new', 'good', 'fair', 'poor']),
  location: z.string().max(255).optional(),
  low_stock_threshold: z.coerce.number().int().min(0, 'Threshold cannot be negative.'),
  date_added: z.string().min(1, 'Date added is required.'),
});

type FormValues = z.input<typeof schema>;

export function InventoryFormPage({ mode }: { mode: 'create' | 'edit' }) {
  const { id } = useParams<{ id: string }>();
  const inventoryId = Number(id);
  const navigate = useNavigate();
  const queryClient = useQueryClient();

  const categoriesQuery = useQuery({ queryKey: ['categories'], queryFn: listCategories });

  const existing = useQuery({
    queryKey: ['inventory', inventoryId],
    queryFn: () => getInventory(inventoryId),
    enabled: mode === 'edit' && Number.isFinite(inventoryId),
  });

  const {
    register,
    handleSubmit,
    reset,
    setError,
    formState: { errors, isSubmitting },
  } = useForm<FormValues>({
    resolver: zodResolver(schema),
    defaultValues: {
      item_code: '',
      name: '',
      brand: '',
      description: '',
      category_id: '',
      quantity: 0,
      unit: 'pcs',
      unit_cost: '',
      status: 'available',
      condition: 'good',
      location: '',
      low_stock_threshold: 5,
      date_added: new Date().toISOString().slice(0, 10),
    } as unknown as FormValues,
  });

  useEffect(() => {
    if (mode === 'edit' && existing.data) {
      const item = existing.data;
      reset({
        item_code: item.item_code,
        name: item.name,
        brand: item.brand ?? '',
        description: item.description ?? '',
        category_id: item.category_id,
        quantity: item.quantity,
        unit: item.unit,
        unit_cost: item.unit_cost ?? '',
        status: item.status,
        condition: item.condition,
        location: item.location ?? '',
        low_stock_threshold: item.low_stock_threshold,
        date_added: item.date_added ?? '',
      } as unknown as FormValues);
    }
  }, [mode, existing.data, reset]);

  const mutation = useMutation({
    mutationFn: (payload: InventoryInput) =>
      mode === 'create'
        ? createInventory(payload)
        : updateInventory(inventoryId, payload),
    onSuccess: async (saved) => {
      await queryClient.invalidateQueries({ queryKey: ['inventories'] });
      await queryClient.invalidateQueries({ queryKey: ['inventory', saved.id] });
      navigate(`/inventories/${saved.id}`);
    },
    onError: (error) => {
      // Map server field errors onto the matching inputs (F3).
      if (error instanceof ApiError) {
        Object.entries(error.fieldErrors()).forEach(([field, message]) => {
          setError(field as keyof FormValues, { type: 'server', message });
        });
      }
    },
  });

  const onSubmit = (values: FormValues) => {
    const payload: InventoryInput = {
      item_code: values.item_code.trim(),
      name: values.name.trim(),
      brand: values.brand?.trim() || undefined,
      description: values.description?.trim() || undefined,
      category_id: Number(values.category_id),
      quantity: Number(values.quantity),
      unit: values.unit.trim(),
      unit_cost: values.unit_cost === '' || values.unit_cost === undefined ? undefined : Number(values.unit_cost),
      status: values.status,
      condition: values.condition,
      location: values.location?.trim() || undefined,
      low_stock_threshold: Number(values.low_stock_threshold),
      date_added: values.date_added,
    };

    mutation.mutate(payload);
  };

  if (mode === 'edit' && existing.isPending) return <LoadingState label="Loading item…" />;

  return (
    <div className="space-y-5">
      <header>
        <h1 className="text-xl font-semibold">
          {mode === 'create' ? 'Add inventory item' : 'Edit inventory item'}
        </h1>
        <p className="text-sm text-slate-600">
          Fields marked <span className="text-red-600">*</span> are required.
        </p>
      </header>

      {mutation.isError && <ErrorBanner error={mutation.error} />}

      <form className="card space-y-4 p-5" onSubmit={handleSubmit(onSubmit)} noValidate>
        <div className="grid gap-4 sm:grid-cols-2">
          <Field id="item_code" label="Item code" required error={errors.item_code?.message}>
            <input className="field-input" {...fieldAria('item_code', errors.item_code?.message)} {...register('item_code')} />
          </Field>

          <Field id="name" label="Name" required error={errors.name?.message}>
            <input className="field-input" {...fieldAria('name', errors.name?.message)} {...register('name')} />
          </Field>

          <Field id="category_id" label="Category" required error={errors.category_id?.message}>
            <select
              className="field-input"
              {...fieldAria('category_id', errors.category_id?.message)}
              {...register('category_id')}
            >
              <option value="">Select a category</option>
              {(categoriesQuery.data?.data ?? []).map((category) => (
                <option key={category.id} value={category.id}>
                  {category.name}
                </option>
              ))}
            </select>
          </Field>

          <Field id="brand" label="Brand" error={errors.brand?.message}>
            <input className="field-input" {...fieldAria('brand', errors.brand?.message)} {...register('brand')} />
          </Field>

          <Field id="quantity" label="Quantity" required error={errors.quantity?.message}>
            <input
              type="number"
              min={0}
              className="field-input"
              {...fieldAria('quantity', errors.quantity?.message)}
              {...register('quantity')}
            />
          </Field>

          <Field id="unit" label="Unit" required error={errors.unit?.message} hint="For example pcs, set, box.">
            <input className="field-input" {...fieldAria('unit', errors.unit?.message, 'For example pcs, set, box.')} {...register('unit')} />
          </Field>

          <Field id="unit_cost" label="Unit cost" error={errors.unit_cost?.message}>
            <input
              type="number"
              step="0.01"
              min={0}
              className="field-input"
              {...fieldAria('unit_cost', errors.unit_cost?.message)}
              {...register('unit_cost')}
            />
          </Field>

          <Field id="low_stock_threshold" label="Low stock threshold" required error={errors.low_stock_threshold?.message}>
            <input
              type="number"
              min={0}
              className="field-input"
              {...fieldAria('low_stock_threshold', errors.low_stock_threshold?.message)}
              {...register('low_stock_threshold')}
            />
          </Field>

          <Field id="status" label="Status" required error={errors.status?.message}>
            <select className="field-input" {...fieldAria('status', errors.status?.message)} {...register('status')}>
              <option value="available">Available</option>
              <option value="borrowed">Borrowed</option>
              <option value="maintenance">Maintenance</option>
              <option value="damaged">Damaged</option>
            </select>
          </Field>

          <Field id="condition" label="Condition" required error={errors.condition?.message}>
            <select className="field-input" {...fieldAria('condition', errors.condition?.message)} {...register('condition')}>
              <option value="new">New</option>
              <option value="good">Good</option>
              <option value="fair">Fair</option>
              <option value="poor">Poor</option>
            </select>
          </Field>

          <Field id="location" label="Location" error={errors.location?.message}>
            <input className="field-input" {...fieldAria('location', errors.location?.message)} {...register('location')} />
          </Field>

          <Field id="date_added" label="Date added" required error={errors.date_added?.message}>
            <input
              type="date"
              className="field-input"
              {...fieldAria('date_added', errors.date_added?.message)}
              {...register('date_added')}
            />
          </Field>
        </div>

        <Field id="description" label="Description" error={errors.description?.message}>
          <textarea
            rows={3}
            className="field-input"
            {...fieldAria('description', errors.description?.message)}
            {...register('description')}
          />
        </Field>

        <div className="flex flex-wrap gap-2">
          <button type="submit" className="btn-primary" disabled={isSubmitting || mutation.isPending}>
            {mutation.isPending ? 'Saving…' : mode === 'create' ? 'Create item' : 'Save changes'}
          </button>
          <Link to="/inventories" className="btn-secondary">
            Cancel
          </Link>
        </div>
      </form>
    </div>
  );
}
