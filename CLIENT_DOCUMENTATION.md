# Kutoot — Client Documentation

> **"Shopping is Winning"**
>
> Kutoot is a loyalty rewards and coupon marketplace platform connecting consumers, merchants, and administrators through a stamp-collection, coupon-redemption, and campaign-based prize system — built for the Indian market.

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Platform Participants & Roles](#2-platform-participants--roles)
3. [Subscription Plans & Tiers](#3-subscription-plans--tiers)
4. [Revenue Model & Pricing](#4-revenue-model--pricing)
5. [Core Feature — Coupon Redemption](#5-core-feature--coupon-redemption)
6. [Core Feature — Stamp / Loyalty Ticket System](#6-core-feature--stamp--loyalty-ticket-system)
7. [Core Feature — Campaign & Bounty System](#7-core-feature--campaign--bounty-system)
8. [Core Feature — QR Code System](#8-core-feature--qr-code-system)
9. [Core Feature — Merchant Loan Program](#9-core-feature--merchant-loan-program)
10. [Authentication & Onboarding](#10-authentication--onboarding)
11. [User-Facing Screens](#11-user-facing-screens)
12. [Admin Panel](#12-admin-panel)
13. [Mobile App & API](#13-mobile-app--api)
14. [Notifications & Communications](#14-notifications--communications)
15. [Data Model Reference](#15-data-model-reference)
16. [Business Rules Summary](#16-business-rules-summary)
17. [Glossary](#17-glossary)

---

## 1. Executive Summary

Kutoot is a **three-sided marketplace** that brings together:

- **Consumers** who browse discount coupons, redeem them at merchant stores, and collect stamp tickets toward campaign prizes.
- **Merchants** who list their store locations, offer discount coupons, earn commissions, and can qualify for performance-based loans.
- **Administrators** who manage campaigns, subscription plans, merchant onboarding, QR codes, and platform operations.

### How It Works (30-Second Overview)

1. A consumer signs up (or logs in via OTP) and is auto-assigned a **free Base Plan**.
2. They browse available **discount coupons** from merchant stores.
3. At checkout they pay a discounted bill through Kutoot — the platform takes a small fee and routes the rest to the merchant.
4. For every bill payment, the consumer earns **stamps** on their chosen campaign.
5. Stamps accumulate alongside merchant commissions toward a **bounty meter**. When the bounty reaches 100%, a prize is unlocked.
6. Consumers can upgrade to **paid subscription plans** to unlock more campaigns, earn more stamps per bill, and access premium coupon categories.

### Market & Currency

- **Target market:** India
- **Currency:** Indian Rupee (₹ / INR)
- **Timezone:** Asia/Kolkata (IST)
- **Payment processor:** Razorpay (with Route split-payment)

### Platform Channels

| Channel | Technology | Purpose |
|---------|-----------|---------|
| **Web Application** | React + Inertia.js | Consumer-facing storefront (campaigns, coupons, stamps, plans) |
| **Mobile App** | NativePHP (iOS + Android) | Native mobile experience mirroring the web app |
| **Admin Panel** | Filament (server-rendered) | Back-office management for admins and merchant admins |
| **API v1** | REST / JSON (Sanctum tokens) | Powers the mobile app and potential third-party integrations |

---

## 2. Platform Participants & Roles

### 2.1 Consumers (Default Role: "User")

Regular end-users of the platform. Consumers can:

- Browse campaigns and coupon offers (even without logging in)
- Log in via OTP (email or mobile number)
- Redeem coupons at merchant stores by paying through the platform
- Collect stamps toward active campaigns
- "Pick their own numbers" on stamps (lottery-style, within a time window)
- Upgrade subscription plans for higher limits and exclusive access
- Set a **primary campaign** where their stamps are deposited
- View transaction history and stamp collections
- Download stamp tickets as PNG images

### 2.2 Merchant Admins

Business owners or operators who manage one or more merchant locations. They can:

- View and manage campaigns, coupons, and QR codes for their locations
- See transaction data, stamp issuances, and coupon redemptions scoped to their stores
- Track monthly performance targets and commission earnings
- Access the admin panel filtered to their own merchant location(s) only (multi-tenant)

### 2.3 Field Executives

Internal staff who handle physical QR code deployment. They can:

- Link pre-generated QR codes to specific merchant locations
- Generate batches of new QR codes
- Download QR code images (PNG with branding) for physical distribution

### 2.4 Super Admins

Platform operators with unrestricted access. They can:

- Manage all entities: users, merchants, locations, campaigns, coupons, plans, QR codes, loans
- View the full activity log (all create/update/delete events across the platform)
- Manage roles and permissions (Spatie Laravel Permission)
- Receive alerts when Razorpay transfers to merchants fail
- Configure campaign bounty parameters and marketing boost percentages

### Role Hierarchy

```
Super Admin  →  Full access (all resources, all locations)
Merchant Admin  →  Scoped access (own merchant locations only)
Field Executive  →  QR code management only
Consumer (User)  →  Public storefront + personal account features
```

---

## 3. Subscription Plans & Tiers

Kutoot uses a **tiered membership model** where higher plans unlock better benefits. Every new user starts on the free **Base Plan** automatically.

### 3.1 Plan Attributes

| Attribute | Description | Example (Base Plan) |
|-----------|-------------|---------------------|
| **Name** | Display name of the plan | "Base Plan" |
| **Price** | One-time purchase price (₹0 for free tier) | ₹0 |
| **Stamps on Purchase** | Bonus stamps awarded immediately when buying this plan | 0 |
| **Stamp Denomination** | Bill amount threshold to earn one unit of stamps | ₹100 |
| **Stamps per Denomination** | Number of stamps earned per denomination unit | 1 |
| **Max Discounted Bills** | Maximum number of coupon-discounted bills allowed during plan validity | 5 |
| **Max Redeemable Amount** | Total discount cap (₹) across all coupon uses during plan validity | ₹500 |
| **Duration (Days)** | Plan validity period (`null` = no expiry for free tier) | null |
| **Is Default** | Auto-assigned to every new user on signup | Yes |

### 3.2 Plan Gating & Access Control

Plans control access to two key areas:

1. **Campaigns** — Each campaign is linked to one or more plans via a `plan_campaign_access` relationship. If a user's plan doesn't include a campaign, that campaign appears as "locked" with a prompt to upgrade.

2. **Coupon Categories** — Coupons are organized into categories, and each category is linked to eligible plans via `plan_coupon_category_access`. Locked coupons display "🔒 Locked" with the name of the minimum required plan.

### 3.3 Plan Lifecycle

```
┌──────────────────┐      Purchase/Upgrade      ┌──────────────────┐
│   Base Plan      │  ────────────────────────►  │  Paid Plan       │
│   (free, auto)   │                             │  (time-limited)  │
└──────────────────┘                             └─────────┬────────┘
        ▲                                                  │
        │             Expiry (daily check)                  │
        └──────────────────────────────────────────────────┘
```

- **On signup:** User is auto-assigned the default Base Plan (free, no expiry).
- **On upgrade:** Old subscription is marked "expired", new one is created as "active". Campaign subscriptions are reconciled — inaccessible campaigns are removed, new ones are auto-subscribed. Bonus stamps are awarded.
- **On expiry:** A daily scheduled command (`subscriptions:expire`) checks for expired subscriptions and reverts users back to the Base Plan. Campaign access is re-reconciled.
- **Downgrades are NOT allowed** — users can only upgrade to a higher-tier plan.

### 3.4 Stamp Earning Formula

```
Stamps earned = floor(bill_amount / stamp_denomination) × stamps_per_denomination
```

**Example:** With a ₹100 denomination and 2 stamps per denomination, a ₹350 bill earns `floor(350/100) × 2 = 6` stamps.

---

## 4. Revenue Model & Pricing

Kutoot generates revenue through three streams:

### 4.1 Platform Fees on Coupon Redemptions

When a consumer redeems a coupon and pays through Kutoot, the platform takes a fee from the transaction:

**Payment split breakdown:**

```
Customer pays:   Original Bill − Discount = Discounted Bill
                 Discounted Bill + Platform Fee + GST on Fee = Grand Total

Kutoot keeps:    Platform Fee + GST on Platform Fee = "Kutoot Share"
Merchant gets:   Discounted Bill − Kutoot Share = "Store Share"
```

- **Platform fee** can be either a **percentage** of the discounted bill or a **fixed amount** (configurable per setup)
- **GST** (default 18%) is applied on top of the platform fee
- **Split payment** is executed automatically via Razorpay Route — funds are divided at the payment processor level, so the merchant receives their share directly into their linked Razorpay account

**Example:**

| Line Item | Amount |
|-----------|--------|
| Original bill | ₹1,000 |
| Coupon discount (20%) | −₹200 |
| **Discounted bill** | **₹800** |
| Platform fee (5%) | +₹40 |
| GST on fee (18%) | +₹7.20 |
| **Grand total (customer pays)** | **₹847.20** |
| *Kutoot share* | *₹47.20* |
| *Store share* | *₹752.80* |

### 4.2 Paid Subscription Plan Purchases

Consumers pay a one-time fee to upgrade their plan. The pricing supports three GST modes:

| Tax Mode | Behavior | Example: ₹499 plan at 18% GST |
|----------|----------|-------------------------------|
| **Inclusive** | GST is already included in the displayed price. Base amount is back-calculated. | Base: ₹423, GST: ₹76, Total: ₹499 |
| **Exclusive** | GST is added on top of the listed price. | Base: ₹499, GST: ₹90, Total: ₹589 |
| **None** | No tax applied. | Base: ₹499, GST: ₹0, Total: ₹499 |

### 4.3 Merchant Commissions

Each merchant location has a **commission percentage** (e.g., 5%). For every coupon redemption transaction at that location, a commission amount is calculated and recorded. This commission contributes to the campaign's **bounty meter** (see Section 7).

While commissions are currently tracked as part of bounty accumulation, they represent a revenue-share agreement between the platform and the merchant.

### 4.4 Financial Precision

All monetary amounts are internally stored in **Paise** (1/100th of a Rupee, i.e., integer cents) to avoid floating-point precision errors. Conversion to Rupees happens only at the display layer.

---

## 5. Core Feature — Coupon Redemption

The coupon redemption flow is the primary consumer interaction and the main revenue driver.

### 5.1 Coupon Structure

Each discount coupon has:

| Attribute | Description |
|-----------|-------------|
| **Title** | Display name (e.g., "20% off at Pizza Palace") |
| **Discount Type** | `percentage` or `fixed` amount |
| **Discount Value** | The percentage (e.g., 20) or fixed amount (e.g., ₹100) |
| **Minimum Order Value** | Minimum bill amount required to use this coupon (e.g., ₹500) |
| **Maximum Discount Amount** | Cap on the discount for percentage-based coupons (e.g., max ₹200 off) |
| **Coupon Code** | Unique redeemable code |
| **Usage Limit** | Total number of times the coupon can be used across all users |
| **Usage per User** | Maximum times a single user can redeem this coupon |
| **Validity Period** | `starts_at` and `expires_at` dates |
| **Merchant Location** | The specific store where this coupon is valid |
| **Coupon Category** | Determines which plan tiers can access this coupon |

### 5.2 Redemption Flow (3-Step Process)

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│  Step 1          │     │  Step 2          │     │  Step 3          │
│  Select Store    │ ──► │  Enter Bill      │ ──► │  Review & Pay    │
│  Location        │     │  Amount          │     │  via Razorpay    │
└─────────────────┘     └─────────────────┘     └─────────────────┘
```

**Step 1 — Select Store Location:**
The consumer chooses which merchant location they are shopping at from a dropdown of eligible locations.

**Step 2 — Enter Bill Amount:**
The consumer enters their actual bill amount. The system validates it against the coupon's minimum order value.

**Step 3 — Review Payment Breakdown & Pay:**
The consumer sees a full breakdown:
- Original bill → discount applied → discounted bill
- Platform fee → GST on fee → grand total
- Estimated stamp count preview
- Target campaign for stamps
- Remaining redemption allowance (based on plan limits)

They then proceed to pay via the Razorpay checkout popup.

### 5.3 Post-Payment Effects

When payment is successfully verified:

1. **Transaction record** is created with full financial details
2. **Coupon redemption record** is created with discount and fee breakdown
3. **Stamps are awarded** to the user's primary campaign (using the stamp formula)
4. **Commission event** is dispatched → updates the campaign's bounty meter
5. **Razorpay Route** splits funds between merchant and platform
6. **Monthly summary** for the merchant location is incrementally updated

### 5.4 Eligibility Guards

- A consumer can only redeem coupons from categories accessible to their current plan
- The consumer's `max_discounted_bills` and `max_redeemable_amount` limits are checked against plan allowances
- Expired, fully used, or per-user-limit-reached coupons are excluded
- The bill amount must meet the coupon's `min_order_value`
- The discount cannot exceed `max_discount_amount` (for percentage coupons)

---

## 6. Core Feature — Stamp / Loyalty Ticket System

Stamps are the core engagement mechanic — digital loyalty tokens that users collect toward campaign prizes.

### 6.1 How Stamps Are Earned

| Source | When Awarded | Count Formula |
|--------|--------------|---------------|
| **Bill Payment** | After a successful coupon redemption payment | `floor(bill_amount / denomination) × stamps_per_denomination` |
| **Plan Purchase** | Immediately on buying/upgrading to a paid plan | Fixed `stamps_on_purchase` value from the plan |
| **Coupon Redemption** | Same as bill payment; uses original bill amount | Same formula as bill payment |

**Key:** The stamp denomination and multiplier come from the user's **current subscription plan**, not from the campaign or coupon.

### 6.2 Stamp Code System (Lottery-Style Numbers)

Each stamp has a **unique combinatorial code** — similar to lottery ticket numbers. The code format is configured per campaign:

**Configuration per campaign:**

| Setting | Description | Example |
|---------|-------------|---------|
| `code` (prefix) | Campaign identifier prefix | `FEST` |
| `stamp_slots` | Number of number positions | 3 |
| `stamp_slot_min` | Minimum value per slot | 1 |
| `stamp_slot_max` | Maximum value per slot | 50 |

**Generated code format:** `{PREFIX}-{SLOT1}-{SLOT2}-{SLOT3}`

**Example:** `FEST-07-23-41` — three strictly ascending numbers between 1 and 50.

**Rules for stamp codes:**
- Slot values are randomly generated in **strictly ascending order**
- Each code must be **unique within its campaign** (collision detection with up to 50 retry attempts)
- Possible combinations = C(range, slots) — e.g., C(50, 3) = 19,600 unique codes
- If collisions exhaust retries, a fallback code `STP-{RANDOM8}` is generated

### 6.3 "Pick Your Numbers" Feature

For certain stamp sources (configurable per campaign), users get a **time-limited window to edit their stamp numbers** — like choosing your own lottery numbers:

- **Editable sources:** Plan Purchase stamps and/or Coupon Redemption stamps (configurable per campaign)
- **Edit window:** Default 15 minutes from stamp creation (configurable via `services.stamps.edit_duration_minutes`)
- **User interface:** Modal with individual slot inputs, live code preview, countdown timer
- **Validation:** Numbers must be within the campaign's min/max range, in strictly ascending order, and the combination must not already exist in the campaign
- **After the window closes:** Stamps become permanently locked with their current code

### 6.4 Stamp Ticket Visual

Each stamp is rendered as a visual **lottery-style ticket** with:

- Left panel: Kutoot branding logo + campaign identifier
- Dashed perforation line (visual tear-off design)
- Right panel: Stamp code in large text + source label (PLAN / COUPON / OTHER)
- **"Save as PNG"** button lets users download their tickets as images

### 6.5 Campaign Grouping

Stamps are displayed grouped by campaign. The user's **primary campaign** is highlighted with a star (⭐). Each campaign group shows:
- Campaign name and stamp count
- Individual stamp tickets
- Edit buttons for stamps still within their edit window

---

## 7. Core Feature — Campaign & Bounty System

Campaigns are the prize-draw mechanism that gives stamp collection its purpose.

### 7.1 Campaign Attributes

| Attribute | Description |
|-----------|-------------|
| **Code** | Short identifier used in stamp codes (e.g., `FEST`) |
| **Reward Name** | Prize description (e.g., "Win a Car!") |
| **Reward Cost Target** | ₹ amount of commission needed to fund the prize |
| **Stamp Target** | Number of stamps needed for campaign completion (alongside commission) |
| **Stamp Slots / Min / Max** | Code generation configuration (see Stamp section) |
| **Marketing Bounty %** | Admin-configurable base boost so new campaigns don't show 0% |
| **Status** | `Active`, `Closed`, or `Completed` |
| **Winner Announcement Date** | When the winner will be revealed |
| **Category** | Organizational grouping (via CampaignCategory) |
| **Creator Type** | Who created the campaign: Admin, Merchant, or Third Party |

### 7.2 Bounty Meter — The Campaign Progress Engine

The **bounty meter** is the animated progress bar that shows how close a campaign is to completion. It's driven by a **weighted formula** combining two inputs:

```
Bounty Progress = (Commission Weight × 66%) + (Stamp Weight × 33%)

Where:
  Commission Weight = Collected Commission / Reward Cost Target
  Stamp Weight      = Issued Stamps / Stamp Target
```

**Effective Bounty % = Organic Progress + Marketing Boost %**

- The organic progress is clamped between 0% and 100%
- The **marketing bounty percentage** is an admin-set floor that ensures campaigns don't appear at 0% when new (psychological incentive)
- The final effective percentage is capped at 100%

**Visual rendering:** The bounty meter uses a color-coded gradient:
- **Red** (< 25%) → **Amber** (< 50%) → **Orange** (< 80%) → **Green** (≥ 80%)

### 7.3 Campaign Lifecycle

```
             Admin creates
                  │
                  ▼
           ┌──────────┐
           │  Active   │ ◄── Stamps earned, commissions collected, bounty grows
           └────┬──┬───┘
                │  │
    Bounty ≥100%│  │ Admin manually closes
                │  │
                ▼  ▼
        ┌───────────┐     ┌────────┐
        │ Completed │     │ Closed │
        └───────────┘     └────────┘
              │
              ▼
     Winner Announced
```

- **Active:** Accepting stamps and commissions. Bounty meter progressing.
- **Completed:** Auto-triggered when the bounty meter reaches 100% (both commission and stamp targets met). Ready for winner announcement.
- **Closed:** Manually closed by an admin (e.g., expired, cancelled).

### 7.4 Campaign Access & Primary Campaign

- Each campaign is linked to one or more subscription plans. Consumers on plans without access see the campaign as "locked" with a badge showing which plan is required.
- A consumer must select one **primary campaign** — this is where all their newly earned stamps are deposited.
- When a user upgrades their plan, campaign subscriptions are **auto-reconciled**: access to new campaigns is granted, access to removed campaigns is revoked, and a new primary is auto-promoted if the current one becomes inaccessible.

### 7.5 Bounty Meter — Event-Driven Updates

The bounty meter updates happen asynchronously through events:

1. **Commission Earned** → queued listener recalculates commission progress and checks for completion
2. **Stamps Issued** → queued listener recalculates stamp progress and checks for completion

This ensures the meter stays up-to-date after every transaction without blocking the user experience.

---

## 8. Core Feature — QR Code System

QR codes bridge the physical and digital experience — they allow customers to scan a code at a merchant's store and instantly access relevant coupons.

### 8.1 QR Code Lifecycle

```
┌────────────┐    Executive links    ┌──────────┐    Admin deactivates    ┌──────────────┐
│ Available   │ ──────────────────►  │  Linked   │ ──────────────────────► │ Deactivated  │
│ (unlinked)  │                      │ (to store)│                         │              │
└────────────┘                      └──────────┘                         └──────────────┘
```

- **Available:** Freshly generated, not yet associated with any store.
- **Linked:** Assigned to a specific merchant location by a field executive. Scans redirect customers.
- **Deactivated:** Retired from use (e.g., store closed, QR damaged).

### 8.2 Code Format

QR codes use a sequential format: `KUT-0001`, `KUT-0002`, etc. Each code has:
- A `unique_code` (the sequential ID)
- A `token` (the scannable URL identifier)
- `linked_at` timestamp and `linked_by` user reference

### 8.3 Customer Scan Flow

```
Customer scans QR ──► /q/{token} ──► System looks up linked merchant location
                                           │
                                  ┌────────┴────────┐
                                  │                 │
                              QR linked         QR not linked
                                  │                 │
                                  ▼                 ▼
                          Redirect to           Show error
                         coupons page           message
                       (pre-filtered for
                        that location)
```

The scan event is **logged** in the activity log for analytics (who scanned, when, which location).

### 8.4 Batch Operations

- **Generate batch:** Create multiple QR codes at once with sequential numbering
- **Download PNG:** Individual QR code images with the Kutoot logo and location label embedded
- **Link to store:** Field executive selects a QR code and a merchant location to associate them

---

## 9. Core Feature — Merchant Loan Program

Kutoot offers a **performance-based lending program** for merchant locations that consistently meet their monthly targets.

### 9.1 Monthly Performance Tracking

Each merchant location can have a configurable monthly target:

| Target Type | Measured By | Example |
|-------------|-------------|---------|
| **Amount** | Total bill amount (or net amount after commission deduction, if configured) | ₹50,000/month |
| **Transaction Count** | Number of completed transactions | 100 transactions/month |

At the end of each month, an automated process:
1. Calculates the **monthly summary** for every active location (total bills, commissions, net amount, transaction count)
2. Determines if the target was **met** for that month
3. If the target was missed, triggers **streak break handling**

### 9.2 Streak System

A **streak** is the count of consecutive months where a merchant location met its target, counting backward from the most recent complete month.

```
Example streak calculation:

Month:     Jan  Feb  Mar  Apr  May  Jun  Jul
Met?:       ✓    ✓    ✗    ✓    ✓    ✓    ✓
                                              ↑
Streak = 4 months (counting backward: Jul, Jun, May, Apr)
(March break resets the older history)
```

### 9.3 Loan Eligibility & Tiers

Loan eligibility requires:
- ✅ At least **3 consecutive months** of meeting targets
- ✅ No existing **active loan** for this location
- ✅ A matching **loan tier** exists

| Loan Tier Attribute | Description | Example |
|---------------------|-------------|---------|
| **Min Streak Months** | Minimum streak required | 3 months |
| **Max Loan Amount** | Maximum loan that can be approved | ₹50,000 |
| **Interest Rate (%)** | Annual interest rate | 12% |

The system selects the **highest eligible tier** based on the current streak. For example:

| Tier | Min Streak | Max Loan | Interest |
|------|-----------|----------|----------|
| Bronze | 3 months | ₹25,000 | 15% |
| Silver | 6 months | ₹75,000 | 12% |
| Gold | 12 months | ₹2,00,000 | 10% |

A merchant with a 7-month streak would qualify for the Silver tier.

### 9.4 Streak Break Handling

When a merchant misses their target in a given month:
- The streak **resets** (count drops to 0 for loan eligibility)
- Active loans are **NOT revoked** — they remain active but get a `streak_broken_at` timestamp for audit
- The merchant must rebuild their streak to qualify for future loans

### 9.5 Loan Statuses

| Status | Meaning |
|--------|---------|
| **Active** | Loan is currently outstanding |
| **Completed** | Loan has been fully repaid |
| **Defaulted** | Loan repayment has been missed beyond acceptable limits |
| **Paused** | Loan is temporarily paused (e.g., during dispute resolution) |

---

## 10. Authentication & Onboarding

Kutoot uses a **passwordless OTP-based authentication** system — no registration form, no passwords.

### 10.1 Login Flow

```
User enters email or mobile ──► System sends OTP ──► User enters OTP ──► Verified ──► Logged in
                                           │                                    │
                                     New user?                            Existing user?
                                           │                                    │
                                           ▼                                    ▼
                                   Auto-create account              Load existing session
                                   + assign Base Plan
                                   + subscribe to campaigns
```

**Key behaviors:**
- **No separate signup flow** — entering a new email/mobile automatically creates an account on first successful OTP verification
- **OTP delivery:** SMS (via Way2mint bulk SMS provider) for mobile numbers; SMTP email for email addresses
- **OTP length:** 4 digits in production
- **OTP validity:** Configurable expiry window
- **Rate limiting:** Maximum 5 OTP requests per minute per user
- **Development mode:** OTPs are logged to the application log instead of being sent (for testing)

### 10.2 New User Auto-Setup

When a user account is created for the first time:
1. A `Registered` event fires
2. The `AssignBasePlanListener` auto-assigns the default free Base Plan
3. The user is auto-subscribed to all campaigns accessible on the Base Plan
4. The first accessible campaign is set as the user's primary campaign

---

## 11. User-Facing Screens

### 11.1 Dashboard (`/dashboard`)

The consumer's home screen after login:

- **Profile Card** — User name, email, active plan name, primary campaign
- **Plan Status Card** — All plan metrics displayed: stamps earned, bills used, discount redeemed, with a days-remaining progress bar (color-coded: green > 14 days, amber > 7 days, red ≤ 7 days remaining)
- **Quick Stats Row:**
  - Total Stamps collected
  - Coupons Used (count)
  - Total Discount Saved (₹)
  - Bills Left (remaining from plan allowance)
  - Redeem Left (₹ remaining from plan cap)
- **Campaign Selector** — Dropdown to switch primary campaign
- **Quick Links** — Stamps, Transactions, Coupons navigation

### 11.2 Campaigns Page (`/`)

The public-facing homepage showing available campaigns:

- **Campaign Grid** — Cards showing merchant logo, category, reward name, and bounty meter
- **Locked Campaigns** — Blurred cards for plan-gated campaigns, showing which plan is required
- **Bounty Meter** — Color-coded progress bar per campaign
- **Campaign Detail** (`/campaigns/{id}`):
  - Full progress breakdown: commission collected vs. target, stamps collected vs. target
  - Stamp code format details (slots, range, possible combinations)
  - "Claim Reward" button (only enabled at 100%)
  - "Set as Primary" button for subscribed users
  - Winner announcement date

### 11.3 Coupons Page (`/coupons`)

The coupon marketplace:

- **Coupon Listing** — Cards with title, discount badge, merchant info, validity dates
- **Locked Coupons** — "🔒 Locked" overlay with upgrade prompt
- **Remaining Allowance Bar** — Visual progress of `used / maxRedeemableAmount`
- **Redemption Modal** — 3-step flow (select location → enter bill → review & pay)
  - Full payment breakdown component showing every line item
  - Estimated stamp count preview
  - Razorpay checkout popup integration

### 11.4 Stamps Page (`/stamps`)

The stamp collection view:

- **Campaign Filter Tabs** — Toggle between campaigns with stamp counts
- **Primary Campaign Highlight** — Star (⭐) indicator on the primary
- **Stamp Ticket Gallery** — Visual lottery-style tickets per campaign
- **"Pick Your Numbers" Modal** — For editable stamps: slot inputs, live preview, countdown timer, validation feedback
- **Download** — Save individual stamps as PNG images

### 11.5 Subscriptions Page (`/subscriptions`)

Plan comparison and upgrade:

- **Three-Tier Display** — Plans shown in ascending order (free → mid → premium)
- **"Best Value" Badge** — Highlighted on the recommended middle tier
- **Feature Comparison** — Each plan shows: price, stamp denomination, stamps per denomination, bonus stamps, max bills, max redeemable amount, duration, accessible campaigns, accessible coupon categories
- **Current Plan Indicator** — Active plan highlighted
- **Upgrade Flow** — Free plans: instant switch. Paid plans: Razorpay checkout popup

### 11.6 Transactions Page (`/transactions`)

Transaction history:

- **Type Tabs** — Filter by "Subscriptions" or "Coupon Redemptions"
- **Transaction Cards** — Date, amount, status badge, merchant location, type
- **Status Badges** — Color-coded: Pending, Paid, Completed, Refunded, Failed

### 11.7 Additional Screens

| Screen | Path | Purpose |
|--------|------|---------|
| Profile Edit | `/profile` | Edit name, email, primary campaign; delete account |
| Executive QR | `/executive/qr/*` | QR code linking and batch management (field executive only) |
| OTP Login | `/login` | OTP-based authentication (email or mobile) |

---

## 12. Admin Panel

The admin panel is built on **Filament v5** — a server-rendered admin framework. It provides comprehensive back-office management via a sidebar-driven interface.

### 12.1 Resource Management (16 Resources)

| Resource | What Admins Can Manage |
|----------|------------------------|
| **Campaigns** | Create/edit campaigns, set reward targets, configure stamp slots, manage status |
| **Campaign Categories** | Organize campaigns into categories |
| **Subscription Plans** | Define plan tiers, pricing, limits, stamp configurations |
| **Discount Coupons** | Create coupons with discount rules, usage limits, validity periods |
| **Coupon Categories** | Categorize coupons and link to plans for access gating |
| **Merchants** | Onboard merchants, manage Razorpay account linking, logos |
| **Merchant Locations** | Manage store branches, commission percentages, monthly targets |
| **QR Codes** | View, generate, and manage QR code inventory and linking status |
| **Users** | Manage consumer accounts, view activity, assign roles |
| **Stamps** | View all issued stamps, codes, campaigns, sources |
| **Transactions** | View all payment transactions, statuses, amounts |
| **Coupon Redemptions** | View redemption records with financial breakdowns |
| **User Subscriptions** | View and manage user plan assignments |
| **Loan Tiers** | Configure loan eligibility thresholds and interest rates |
| **Roles** | Define roles (via Spatie Permission) |
| **Permissions** | Manage granular permissions |

### 12.2 Activity Log

A dedicated page showing a complete audit trail:
- Every create, update, and delete event across all models
- QR code scan events
- Filter by event type (created, updated, deleted, scanned)
- Filter by subject type (which model was affected)
- **Humanized descriptions** — e.g., "📦 Campaign 'Diwali Fest' was updated" instead of raw log data

### 12.3 Multi-Tenant Merchant View

Merchant Admins see a **scoped version** of the admin panel:
- They can only see resources related to their own merchant location(s)
- A location switcher allows them to toggle between their stores
- They cannot manage other merchants, plans, or system-wide settings

---

## 13. Mobile App & API

### 13.1 Mobile App

Kutoot has a native mobile app built with **NativePHP** that provides the full consumer experience on iOS and Android devices, including:
- OTP login
- Campaign browsing with bounty meters
- Coupon redemption flow
- Stamp collection and "Pick Your Numbers"
- Subscription management
- Transaction history
- QR code scanning (via device camera)

### 13.2 API v1 (REST)

The API follows RESTful conventions with JSON request/response format:

**Authentication:** Sanctum bearer tokens (obtained via OTP login endpoints)

| API Group | Endpoints | Description |
|-----------|-----------|-------------|
| **Auth** | `POST /api/v1/auth/otp/send`, `POST /api/v1/auth/otp/verify` | OTP-based authentication |
| **Dashboard** | `GET /api/v1/dashboard` | User stats and plan info |
| **Campaigns** | `GET /api/v1/campaigns`, `GET /api/v1/campaigns/{id}` | Campaign listing and detail |
| **Coupons** | `GET /api/v1/coupons`, `POST /api/v1/coupons/{id}/redeem`, `POST /api/v1/coupons/transactions/{id}/verify` | Coupon browsing and redemption |
| **Stamps** | `GET /api/v1/stamps`, `PUT /api/v1/stamps/{id}` | Stamp collection and code editing |
| **Subscriptions** | `GET /api/v1/subscriptions`, `POST /api/v1/subscriptions/upgrade`, `POST /api/v1/subscriptions/verify-payment/{id}` | Plan management |
| **Transactions** | `GET /api/v1/transactions` | Transaction history |
| **Profile** | `GET /api/v1/profile`, `PUT /api/v1/profile` | User profile management |
| **QR Scan** | `GET /api/v1/qr/{token}` | Process QR code scan |
| **Admin** (16 endpoints) | Full CRUD for all entities | Back-office management API |

### 13.3 Webhooks (Inbound)

| Webhook | Source | Purpose |
|---------|--------|---------|
| `POST /api/webhooks/razorpay` | Razorpay | Handles `payment.captured`, `payment.failed`, `refund.created`, `transfer.failed` events |

Webhook security: All Razorpay webhooks are verified via **HMAC SHA-256 signature validation** before processing.

---

## 14. Notifications & Communications

### 14.1 OTP Delivery

| Channel | Provider | When |
|---------|----------|------|
| **SMS** | Way2mint (bulk SMS API) | Production environment — sent to mobile numbers |
| **Email** | SMTP (via Laravel Mail) | Production environment — sent to email addresses |
| **Application Log** | File logger | Development — OTPs logged instead of sent |

**SMS template:** "Your Kutoot login OTP is: {OTP}. Valid for {X} minutes. Do not share. -Team Kutoot | Shopping is Winning"

### 14.2 Admin Alerts

| Notification | Channel | Triggered When |
|--------------|---------|----------------|
| **Transfer Failed** | Email + Database (in-app) | A Razorpay Route transfer to a merchant's account fails |

This notification includes the transfer ID, related transaction ID, and error details — sent to all Super Admin users.

### 14.3 Activity Logging

All significant actions across the platform are logged with:
- **Who** performed the action (user ID)
- **What** changed (model type, old values → new values)
- **When** it happened (timestamp)
- **Humanized descriptions** with emoji icons for quick scanning

---

## 15. Data Model Reference

### 15.1 Core Entities

| Entity | Purpose | Key Attributes |
|--------|---------|----------------|
| **User** | Consumer/merchant/admin account | `name`, `email`, `mobile`, `primary_campaign_id`, `otp_code`, `otp_expires_at` |
| **Merchant** | Business entity offering coupons | `name`, `slug`, `razorpay_account_id`, `is_active`, logo (media) |
| **MerchantLocation** | Physical store branch | `branch_name`, `commission_percentage`, `monthly_target_type`, `monthly_target_value`, `deduct_commission_from_target` |
| **Campaign** | Prize-draw campaign | `code`, `reward_name`, `reward_cost_target`, `stamp_target`, `stamp_slots`, `stamp_slot_min/max`, `marketing_bounty_percentage`, `status`, `winner_announcement_date` |
| **CampaignCategory** | Campaign grouping | `name` |
| **SubscriptionPlan** | Membership tier | `name`, `price`, `stamps_on_purchase`, `stamp_denomination`, `stamps_per_denomination`, `max_discounted_bills`, `max_redeemable_amount`, `duration_days`, `is_default` |

### 15.2 Transaction Entities

| Entity | Purpose | Key Attributes |
|--------|---------|----------------|
| **Transaction** | Financial payment record | `amount`, `original_bill_amount`, `discount_amount`, `platform_fee`, `gst_amount`, `total_amount`, `commission_amount`, `type` (coupon_redemption / plan_purchase), `payment_status`, Razorpay IDs |
| **CouponRedemption** | Record of coupon use | `discount_applied`, `original_bill_amount`, `platform_fee`, `gst_amount`, `total_paid` |
| **Stamp** | Individual loyalty token | `code`, `source` (plan_purchase / bill_payment / coupon_redemption), `editable_until` |
| **DiscountCoupon** | Discount offer | `title`, `discount_type`, `discount_value`, `min_order_value`, `max_discount_amount`, `code`, `usage_limit`, `usage_per_user`, `starts_at`, `expires_at` |

### 15.3 Subscription & Access Entities

| Entity | Purpose | Key Attributes |
|--------|---------|----------------|
| **UserSubscription** | User's plan enrollment | `status` (active / expired), `expires_at` |
| **CouponCategory** | Coupon grouping for plan gating | `name`, `slug`, `icon` |
| *plan_campaign_access* (pivot) | Which plans unlock which campaigns | plan_id, campaign_id |
| *plan_coupon_category_access* (pivot) | Which plans unlock which coupon categories | plan_id, coupon_category_id |
| *campaign_user* (pivot) | User's campaign subscriptions | user_id, campaign_id, is_primary, subscribed_at |

### 15.4 Merchant Performance Entities

| Entity | Purpose | Key Attributes |
|--------|---------|----------------|
| **MerchantLocationMonthlySummary** | Aggregated monthly metrics | `total_bill_amount`, `total_commission_amount`, `net_amount`, `transaction_count`, `target_met` |
| **LoanTier** | Loan eligibility configuration | `min_streak_months`, `max_loan_amount`, `interest_rate_percentage` |
| **MerchantLocationLoan** | Issued loan record | `amount`, `status`, `streak_months_at_approval`, `approved_at`, `streak_broken_at` |

### 15.5 Infrastructure Entities

| Entity | Purpose | Key Attributes |
|--------|---------|----------------|
| **QrCode** | Physical QR code inventory | `unique_code`, `token`, `status` (available / linked / deactivated), `linked_at`, `linked_by` |

### 15.6 Relationship Map

```
                    ┌─────────────────┐
                    │      User       │
                    └──┬──┬──┬──┬──┬──┘
                       │  │  │  │  │
          ┌────────────┘  │  │  │  └────────────────┐
          ▼               │  │  │                    ▼
  ┌───────────────┐       │  │  │          ┌─────────────────┐
  │UserSubscription│      │  │  │          │   Transaction   │
  └───────┬───────┘       │  │  │          └────────┬────────┘
          │               │  │  │                   │
          ▼               │  │  │                   ▼
  ┌───────────────┐       │  │  │          ┌─────────────────┐
  │SubscriptionPlan│      │  │  │          │CouponRedemption │
  └───────┬───────┘       │  │  │          └─────────────────┘
          │               │  │  │
          │   ┌───────────┘  │  └─────────┐
          │   ▼              │            ▼
          │  ┌─────┐    ┌───┴────┐   ┌────────────┐
          │  │Stamp│    │Campaign│   │MerchantLoc.│
          │  └─────┘    └───┬────┘   └─────┬──────┘
          │                 │              │
          │                 ▼              ▼
          │         ┌──────────────┐  ┌──────────┐
          │         │CampaignCat.  │  │ Merchant │
          │         └──────────────┘  └──────────┘
          │
          ├──► plan_campaign_access (pivot)
          └──► plan_coupon_category_access (pivot)
```

---

## 16. Business Rules Summary

A consolidated reference of all configurable business rules and formulas.

### 16.1 Stamp Rules

| Rule | Formula / Default |
|------|-------------------|
| Stamps per bill | `floor(bill_amount / plan.stamp_denomination) × plan.stamps_per_denomination` |
| Bonus stamps on plan purchase | `plan.stamps_on_purchase` (fixed count) |
| Stamp code format | `{campaign.code}-{slot1}-{slot2}-...-{slotN}` (ascending integers) |
| Stamp code slots | `campaign.stamp_slots` (number of positions) |
| Slot value range | `campaign.stamp_slot_min` to `campaign.stamp_slot_max` |
| Edit window duration | `config('services.stamps.edit_duration_minutes')` — default: **15 minutes** |
| Max code generation retries | 50 attempts before fallback |
| Editable sources | Per-campaign toggle: plan purchase stamps and/or coupon redemption stamps |

### 16.2 Bounty Meter Rules

| Rule | Value |
|------|-------|
| Commission weight | **66%** of total progress |
| Stamp weight | **33%** of total progress |
| Marketing boost | Admin-set per campaign (additive percentage) |
| Auto-completion | Campaign status → `Completed` when organic progress ≥ 100% |
| Progress cap | 100% maximum |

### 16.3 Financial Rules

| Rule | Default |
|------|---------|
| GST rate | **18%** (configurable via `app.gst_rate`) |
| Currency | INR (Indian Rupee) |
| Internal precision | Paise (integer, 1/100th of Rupee) |
| Platform fee | Percentage of discounted bill OR fixed amount (configurable) |
| Razorpay idempotency | Transaction-level `idempotency_key` on all orders |

### 16.4 Plan Rules

| Rule | Behavior |
|------|----------|
| Default plan | Auto-assigned on first login (free, no expiry) |
| Plan expiry | Daily check via `subscriptions:expire` command |
| Downgrade | Not allowed — upgrade-only |
| Post-expiry | Reverts to Base Plan, campaigns re-reconciled |
| Tax modes | Inclusive, Exclusive, None (per plan) |

### 16.5 Coupon Rules

| Rule | Behavior |
|------|----------|
| Discount types | `percentage` or `fixed` |
| Guards | `min_order_value`, `max_discount_amount`, `usage_limit`, `usage_per_user`, date range |
| Plan gating | Via coupon category → plan relationship |
| Code generation | Bulk generation up to 10,000 unique codes at once |

### 16.6 Merchant & Loan Rules

| Rule | Value |
|------|-------|
| Monthly target types | Amount (₹) or Transaction Count |
| Commission deduction | Optional flag: `deduct_commission_from_target` |
| Minimum streak for loan | **3 consecutive months** |
| Loan tier matching | Highest tier where `min_streak_months ≤ current_streak` |
| Streak break | Active loans not revoked; `streak_broken_at` recorded |
| Monthly processing | Automated via `app:recalculate-monthly-targets` command |

### 16.7 Authentication Rules

| Rule | Value |
|------|-------|
| OTP length | 4 digits (production) |
| Rate limit | 5 requests per minute |
| Auto-create | New account on first OTP verification |
| SMS provider | Way2mint (production only) |

### 16.8 Webhook Processing Rules

| Rule | Behavior |
|------|----------|
| Signature verification | HMAC SHA-256 on all inbound Razorpay webhooks |
| Idempotency | `payment.captured` events skip already-completed transactions |
| Transfer failure | Triggers notification to all Super Admin users |
| Refund processing | Updates transaction status and triggers transfer reversal if applicable |

---

## 17. Glossary

| Term | Definition |
|------|------------|
| **Stamp** | A digital loyalty token earned through bill payments, plan purchases, or coupon redemptions. Each stamp has a unique lottery-style code within its campaign. |
| **Bounty Meter** | The progress bar on a campaign showing how close it is to completion, driven by commission revenue (66% weight) and stamp count (33% weight). |
| **Campaign** | A prize-draw program where stamps are collected toward a reward. Each campaign has commission and stamp targets that fill the bounty meter. |
| **Primary Campaign** | The user's selected default campaign where all new stamps are deposited. |
| **Coupon Redemption** | The act of using a discount coupon at a merchant store and paying through the Kutoot platform. |
| **Platform Fee** | Kutoot's service charge on each coupon redemption transaction — a percentage or fixed amount on the discounted bill. |
| **Denomination** | The bill amount unit that earns stamps (e.g., ₹100 denomination means 1 stamp per ₹100 spent). Configured per subscription plan. |
| **Streak** | The count of consecutive months a merchant location has met its monthly performance target. Used for loan eligibility. |
| **Slot** | A number position in a stamp code (e.g., 3 slots = 3 numbers like `07-23-41`). Configured per campaign. |
| **Store Share** | The portion of a coupon redemption payment that goes to the merchant after Kutoot's platform fee and GST are deducted. |
| **Kutoot Share** | Platform fee + GST on the platform fee — Kutoot's revenue from each coupon redemption. |
| **Razorpay Route** | Payment split technology that automatically divides a single payment between the platform (Kutoot) and the merchant at the processor level. |
| **Bounty** | The campaign prize/reward. "Bounty meter" tracks progress toward unlocking it. |
| **Marketing Boost** | An admin-set percentage added to a campaign's bounty meter to ensure new campaigns don't appear at 0% (psychological incentive). |
| **Base Plan** | The free default subscription plan auto-assigned to every new user, with basic stamp earning and redemption limits. |
| **Pick Your Numbers** | The time-limited feature allowing users to choose their own stamp code numbers (lottery-style), replacing the auto-generated values. |
| **Edit Window** | The configurable time period (default 15 minutes) after stamp issuance during which a user can "pick their numbers." |
| **Idempotency Key** | A unique identifier sent with each Razorpay payment order to prevent duplicate charges if a request is retried. |
| **Multi-Tenant** | The admin panel architecture where Merchant Admins only see data scoped to their own merchant locations. |

---

*This document was generated from the Kutoot application codebase and reflects the platform's logic, features, and business rules as implemented.*
