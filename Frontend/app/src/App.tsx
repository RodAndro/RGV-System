import { Link, Route, Routes } from 'react-router-dom';
import { AppLayout } from './components/AppLayout';
import { RequireAdmin, RequireAuth } from './components/Guards';
import { LoginPage } from './pages/LoginPage';
import { DashboardPage } from './pages/DashboardPage';
import { InventoryListPage } from './pages/InventoryListPage';
import { InventoryDetailPage } from './pages/InventoryDetailPage';
import { InventoryFormPage } from './pages/InventoryFormPage';
import { BorrowRequestListPage } from './pages/BorrowRequestListPage';
import { BorrowRequestDetailPage } from './pages/BorrowRequestDetailPage';
import { BorrowRequestFormPage } from './pages/BorrowRequestFormPage';
import { ProfilePage } from './pages/ProfilePage';

function NotFoundPage() {
  return (
    <div className="card p-8 text-center">
      <h1 className="text-lg font-semibold">Page not found</h1>
      <p className="mt-1 text-sm text-slate-600">
        <Link className="text-brand-600 hover:underline" to="/dashboard">
          Return to the dashboard
        </Link>
      </p>
    </div>
  );
}

export default function App() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />

      <Route
        element={
          <RequireAuth>
            <AppLayout />
          </RequireAuth>
        }
      >
        <Route path="/" element={<DashboardPage />} />
        <Route path="/dashboard" element={<DashboardPage />} />

        <Route path="/inventories" element={<InventoryListPage />} />
        <Route
          path="/inventories/new"
          element={
            <RequireAdmin>
              <InventoryFormPage mode="create" />
            </RequireAdmin>
          }
        />
        <Route path="/inventories/:id" element={<InventoryDetailPage />} />
        <Route
          path="/inventories/:id/edit"
          element={
            <RequireAdmin>
              <InventoryFormPage mode="edit" />
            </RequireAdmin>
          }
        />

        <Route path="/borrow-requests" element={<BorrowRequestListPage />} />
        <Route path="/borrow-requests/new" element={<BorrowRequestFormPage />} />
        <Route path="/borrow-requests/:id" element={<BorrowRequestDetailPage />} />

        <Route path="/profile" element={<ProfilePage />} />
        <Route path="*" element={<NotFoundPage />} />
      </Route>
    </Routes>
  );
}
