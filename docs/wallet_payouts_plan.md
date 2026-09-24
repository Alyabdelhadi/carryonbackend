# Wallet, online payment and carrier payouts — plan

Status: phases 1 (backend), 2 (card payment in the app) and 3 (wallet screens in the app) implemented on 2026-09-24. A real test payment was run on 2026-09-24 with the UAE account's test keys (intent, confirmation, sync, delivery credit, refund, signed webhook, and the payment sheet on the simulator). Phase 4 items are optional.
Decisions taken: defaults below (15% / 20 USD / 3 days / 24 h), charge at acceptance, Stripe fee
absorbed in the commission, manual payouts via bank transfer / OMT / Whish / PayPal, admin decides
refunds after pickup. The Stripe account will be a UAE entity; Stripe Connect payouts to Lebanese
carriers are not possible, so payouts stay manual.

## Goal

When the admin enables Stripe, the sender pays the carrier reward by card through the
app. CarryOn holds the money, keeps a percentage, and credits the rest to the carrier's
in-app wallet once the package is delivered. The carrier requests a payout and the admin
pays it out. Cash orders keep working exactly as today (carrier collects cash, no wallet,
no commission).

## What exists today

| Piece | State |
|---|---|
| `payment_methods` table (`cash_on_delivery`, `stripe`, keys encrypted) | done, admin-editable at `/payment-methods` |
| `GET api/payment-methods` | done, returns enabled methods + Stripe publishable key |
| `parcel_orders.payment_method/payment_status/payment_reference/paid_at/payment_amount/payment_currency` | columns exist |
| `POST api/payments/stripe/create` → `StripePaymentService::createPaymentIntent` | written, never exercised |
| `POST api/payments/stripe/webhook` | written, flips `payment_status` to paid/failed/cancelled, nothing else |
| `app_users.wallet` (int) | legacy points column from the template, unused |
| Flutter order form | shows the payment method list, sends `payment_method` as the **id** (1/2) |

Bugs that block the current Stripe path (fix in phase 1):

1. The app sends `payment_method: 2` (id) but `createStripePayment` checks `payment_method === 'stripe'` (code). Normalise to the code string on create.
2. `payment_amount` is derived server-side from the free-text reward, so "Free"/"Other" become 0 and the PaymentIntent throws. Online payment must require a numeric reward.
3. `createStripePayment` returned 422 on any retry while an intent was "processing"; it now reuses the open intent.
4. The webhook did not notify anyone or unlock anything.

## Money flow (online orders)

```
Sender creates order ──► Unassigned, payment_status = unpaid
Carrier accepts      ──► Assigned, sender gets push "Pay to confirm"
Sender pays (Stripe PaymentSheet) ──► webhook: payment_status = paid, carrier notified
Carrier picks up / transit ──► only allowed when paid (online orders)
Delivered            ──► wallet credit to carrier = reward − commission
Cancelled after paid ──► Stripe refund to sender (full before pickup; admin decides after)
Expired              ──► nothing to refund (never charged)
```

Charging at acceptance instead of at creation avoids refunds for orders nobody takes
(Stripe keeps its processing fee on refunds) and needs no off-session card handling.
Alternative if faster confirmation is wanted later: save the card with a SetupIntent at
creation and charge off-session at acceptance.

Amounts:

- `reward` = what the sender types, the carrier's headline price.
- `commission = reward × commission_percent` (AppSetting, default 15%).
- `stripe fee` (about 2.9% + 0.30 USD): decision needed, either added to what the sender pays as a
  "processing fee" or absorbed inside the commission.
- `carrier_earning = reward − commission`.

## Data model (new migrations, all `2026_09_25_*`, run with `--path=`)

`wallet_transactions` (append-only ledger)

| column | notes |
|---|---|
| id, app_user_id | |
| type | `earning`, `payout`, `payout_reversal`, `refund`, `adjustment` |
| amount | decimal(10,2), signed (earning +, payout −) |
| currency | char(3) |
| parcel_order_id, payout_request_id | nullable references |
| available_at | earnings become withdrawable after `payout_hold_days` |
| note, created_by (admin id for adjustments) | |
| balance_after | denormalised for display |

`payout_requests`

| column | notes |
|---|---|
| id, app_user_id | |
| amount, currency | |
| method | `bank_transfer`, `omt`, `whish`, `paypal` (admin-editable list) |
| details | JSON: IBAN / account name / phone / PayPal email |
| status | `pending` → `paid` or `rejected`; `cancelled` by the user while pending |
| reference, admin_note, processed_by, processed_at | |

`app_users`: add `wallet_balance decimal(10,2)` and `wallet_currency char(3)`; leave the
old `wallet` int alone. Balance is always recomputed from the ledger inside the same
transaction that inserts the row.

`parcel_orders`: add `commission_amount decimal(10,2)`, `carrier_earning decimal(10,2)`,
`refund_reference`, `refunded_at`. `payment_status` values: `unpaid`, `pending`, `paid`,
`failed`, `refunded`, `cash`.

New `AppSetting` keys (App Settings page):
`online_payment_enabled` (mirror of the Stripe method switch, read by the app),
`commission_percent`, `payout_minimum`, `payout_hold_days`, `payment_deadline_hours`
(how long the sender has to pay after acceptance, default 24).

## Backend work

Services

- `WalletService`: `credit(user, amount, order)`, `reserveForPayout`, `releasePayout`,
  `adjust(admin, user, amount, note)`, `balance(user)` → `{available, pending, currency}`.
  Every write is in a DB transaction with a `lockForUpdate` on the user row.
- `PayoutService`: `request()`, `cancel()`, `markPaid()`, `reject()`.
- `StripePaymentService`: add `refund(order)`; `createPaymentIntent` gets an idempotency key
  (`order-{id}-{attempt}`) so retries don't double-charge.
- `ParcelOrder::deliver()`: inside the existing transaction, when `payment_status = paid`,
  call `WalletService::credit`.
- `ParcelOrder::cancel()`: when paid and not yet picked, refund; log a `refund` row.
- `ParcelOrder::pickup()` / `transit()`: reject for online orders that are not paid.
- Scheduled command `orders:release-unpaid`: assignments unpaid past
  `payment_deadline_hours` go back to Unassigned and both sides are notified.
- Webhook: on `payment_intent.succeeded` push to carrier ("Sender paid, you can pick up")
  and sender (receipt); on failure push to sender.

API (all take `user_id` like the rest)

| route | purpose |
|---|---|
| `GET api/wallet` | balance, pending, currency, min payout, last 20 transactions |
| `GET api/wallet/transactions?page=` | full ledger |
| `POST api/payouts` | create request (amount, method, details) |
| `GET api/payouts` | the user's requests with status |
| `POST api/payouts/{id}/cancel` | while pending |
| `POST api/payments/stripe/create` | existing, fixed; returns `client_secret`, `publishable_key`, amount, currency |
| `GET api/appSettings` | gains the new keys |

Admin (Blade, same CRUD template as the rest)

- `/payouts`: list with status filter, approve → "mark paid" with reference, reject with note.
- `/wallets`: per-user balance, ledger, manual adjustment form.
- `/parcel_orders` edit page: payment status, Stripe reference, Refund button.
- `/app-settings`: the new numeric fields; `/payment-methods` unchanged.
- Notification + email templates: `payment_required`, `payment_received`, `payout_paid`,
  `payout_rejected`, `assignment_released`.

## App work (Flutter)

- `flutter_stripe` package, Stripe PaymentSheet. Publishable key comes from
  `api/payment-methods`, so no key is compiled in.
- Order form: when the chosen method is Stripe, the reward field becomes numeric only
  ("Free"/"Other" chips hidden), and a line shows what the sender will pay and when.
- Order card / overview: payment badge (Unpaid, Pay now, Paid, Refunded, Cash). "Pay now"
  button for the sender after acceptance. Carrier sees "Waiting for payment" and the
  pickup action is disabled until paid. Carrier sees "You earn X" (after fee) on online orders.
- Account page: "Wallet" row with the balance, shown only when `online_payment_enabled`.
- Wallet page: balance card (available / pending), Request payout button → sheet with
  amount, method, details; ledger list; payout history with status chips.
- Push handling: tapping a payment notification opens the order.
- All strings in `intl_en.arb` / `intl_ar.arb` (`wal…` prefix); amounts with `Formatters`.

## Phases

1. DONE. Backend foundation: settings, ledger, payouts, admin pages (`/payouts`, `/wallets`,
   order refund button, App Settings block), deliver/cancel hooks, release-unpaid command,
   fixed Stripe endpoint and webhook notifications. `tests/Feature/WalletFlowTest.php`.
2. DONE. Payment flow end to end: `flutter_stripe` payment sheet, "Pay now" for the creator
   once a carrier accepted, pickup blocked for the carrier until paid, payment card and chips
   on the order screens, numeric-only reward when paying by card, push taps open the order
   (`order_id` in the FCM data), `POST api/payments/stripe/sync` confirms the intent right
   after the sheet so the app does not wait for the webhook. Verified with the account's test keys: create → confirm (test card) → sync → paid →
   deliver → wallet credit, cancel → real refund, and the webhook with a signed event.
3. DONE. Wallet page under Account (balance / available / on hold, payout request sheet with
   per-method fields, open request with cancel, payout history, ledger), shown once card
   payment is enabled or the user has wallet activity. `integration_test/wallet_smoke_test.dart`.
4. Later, optional: Stripe Connect automatic payouts (only if the Stripe account's country
   supports Connect), a platform fee on cash orders, sender store credit instead of refunds.

## Decisions needed

1. Charge at acceptance (recommended) or at order creation.
2. Commission %, minimum payout, hold days, payment deadline (proposed 15%, 20 USD, 3 days, 24 h).
3. Who pays the Stripe processing fee: sender on top, or out of the commission.
4. Payout channels to offer (bank transfer, OMT, Whish, PayPal) and which currency.
5. Refund policy after pickup (proposed: admin decides case by case).
6. Stripe does not onboard businesses registered in Lebanon; the Stripe account must belong
   to an entity in a supported country. Confirm which account will be used before phase 2.
