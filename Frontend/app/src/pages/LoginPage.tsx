import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Navigate, useLocation, useNavigate } from 'react-router-dom';
import { useAuth } from '../auth/AuthProvider';
import { ApiError } from '../lib/api';
import { Field, fieldAria } from '../components/Field';
import { ErrorBanner } from '../components/States';

/**
 * The schema mirrors the server's validation rules so obvious mistakes
 * are caught before a request is made (F3). Server-side validation still
 * runs and its errors are surfaced below the form.
 */
const schema = z.object({
  email: z.string().min(1, 'Email is required.').email('Enter a valid email address.'),
  password: z.string().min(1, 'Password is required.'),
});

type FormValues = z.infer<typeof schema>;

export function LoginPage() {
  const { signIn, user } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const [serverError, setServerError] = useState<unknown>(null);

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm<FormValues>({
    resolver: zodResolver(schema),
    defaultValues: { email: '', password: '' },
  });

  if (user) {
    return <Navigate to="/dashboard" replace />;
  }

  const onSubmit = async (values: FormValues) => {
    setServerError(null);

    try {
      await signIn(values.email, values.password);
      const from = (location.state as { from?: string } | null)?.from;
      navigate(from && from !== '/login' ? from : '/dashboard', { replace: true });
    } catch (error) {
      setServerError(error);
    }
  };

  return (
    <div className="grid min-h-screen place-items-center bg-slate-50 px-4 py-10">
      <div className="w-full max-w-sm">
        <div className="mb-6 text-center">
          <span className="mx-auto grid h-12 w-12 place-items-center rounded-lg bg-brand-500 text-sm font-bold text-white">
            RGV
          </span>
          <h1 className="mt-4 text-xl font-semibold">Sign in</h1>
          <p className="mt-1 text-sm text-slate-600">
            RGV Multi-Tech Services inventory &amp; borrowing
          </p>
        </div>

        <form
          className="card space-y-4 p-6"
          onSubmit={handleSubmit(onSubmit)}
          noValidate
          aria-label="Sign in"
        >
          {serverError !== null && <ErrorBanner error={serverError} />}

          <Field id="email" label="Email address" required error={errors.email?.message}>
            <input
              type="email"
              autoComplete="email"
              className="field-input"
              placeholder="you@example.test"
              {...fieldAria('email', errors.email?.message)}
              {...register('email')}
            />
          </Field>

          <Field id="password" label="Password" required error={errors.password?.message}>
            <input
              type="password"
              autoComplete="current-password"
              className="field-input"
              {...fieldAria('password', errors.password?.message)}
              {...register('password')}
            />
          </Field>

          <button type="submit" className="btn-primary w-full" disabled={isSubmitting}>
            {isSubmitting ? 'Signing in…' : 'Sign in'}
          </button>

          <p className="text-center text-xs text-slate-500">
            Administrators with multi-factor authentication should sign in at{' '}
            <a className="underline" href="/login">
              the staff portal
            </a>
            .
          </p>
        </form>

        {serverError instanceof ApiError && serverError.code === 'RATE_LIMITED' && (
          <p className="mt-4 text-center text-xs text-slate-500">
            Too many attempts. Please wait a minute before trying again.
          </p>
        )}
      </div>
    </div>
  );
}
