# D2 — User Manual (source)

**Status:** structure seeded. Screenshots must be captured from the deployed system using sample
data only. Capture points are marked `TODO(team)`.

---

## 1. Introduction

RGV Multi-Tech Services provides public service booking and tracks the equipment inventory used by
its staff. This manual explains how each role uses the system.

## 2. Getting started

1. Open the system URL in a current version of Chrome, Edge, Firefox, or Safari.
2. Sign in with the account issued by your administrator.
3. To reset a password, use the "Forgot password" link on the sign-in screen.

**Screenshot:** `TODO(team)` — sign-in screen.

## 3. Public visitor

1. From the home page, browse available inventory.
2. To look up a specific item, scan the QR code printed on the item, or enter its code.
3. To book a service, open **Booking**, complete the form, and submit.
4. Note the reference number shown after submitting; use **Track** to check its status.

**Screenshots:** `TODO(team)` — home, booking form, tracking result.

## 4. Employee

1. Sign in. If multi-factor authentication is enabled, enter the six-digit code or a recovery code.
2. The dashboard shows your open borrow requests and recently added inventory.
3. Open **Inventory** and use the search box and filters to find an item.
4. Open an item to see quantity, condition, and location.
5. To borrow, open **Borrow requests → New borrow request**, choose the items and quantities, set
   the borrow and due dates, and submit.
6. Stock is reserved as soon as the request is submitted.
7. To cancel a pending request, open it and cancel. Stock is released.
8. To return items, open the request after it has been marked borrowed and record the condition.

**Screenshots:** `TODO(team)` — dashboard, inventory list, item detail, borrow form, request detail.

## 5. Administrator

1. Sign in to the staff portal.
2. **Inventory:** add an item, edit an item, or delete an item (soft delete, restorable from Trash).
3. **Borrow requests:** approve or reject pending requests, mark approved requests as borrowed,
   and record returns.
4. **Bookings:** review, approve, reject, and complete service bookings.
5. **Users:** create staff accounts, assign the administrator or employee role, and deactivate
   accounts.
6. **Reports and audit:** generate reports and review the audit history.
7. **AI assistant:** open the reports assistant and ask about report data. Answers are advisory and
   are labelled as machine-generated. If the local model is unavailable, a deterministic summary is
   shown instead.

**Screenshots:** `TODO(team)` — inventory form, approval screen, user form, AI assistant.

## 6. Troubleshooting and FAQ

| Symptom | Cause | Action |
|---|---|---|
| "These credentials do not match our records." | Wrong email or password | Re-enter; after several failures wait a minute (login is rate limited). |
| "This account is inactive." | Account deactivated | Ask an administrator to reactivate it. |
| "Insufficient stock for …" | Another request reserved the stock first | Reduce the quantity or choose another item. |
| AI assistant replies that generation is offline | Local model not running | Deterministic report summaries remain available. |
| "You do not have permission to perform this action." | Role lacks the permission | Ask an administrator; restrictions are enforced by the server. |

## 7. Contact and support

`TODO(team)`
