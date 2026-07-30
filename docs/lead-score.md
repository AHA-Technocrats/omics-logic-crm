# Lead Score — How It Works

This document describes the **global Lead Score** model shipped in OmicsLogic CRM (v1). Score lives on the **Person** (contact), not on individual Leads. Web form, import, portal sync, and manual create all use the same calculator.

---

## Formula

```
Lead Score (0–100) =
    Product Interest   (0–35)
  + Email Domain       (0–25)
  + Country            (0–20)
  + Profile            (0–20)
```

After the total is computed, a **Score Band** is set and stored on the Person:

| Band    | Default range | Meaning                          |
|---------|---------------|----------------------------------|
| Hot     | 75–100        | Contact within 24 hours          |
| Warm    | 55–74         | Contact within 48 hours          |
| Nurture | 35–54         | Automated nurture + light touch  |
| Low     | 0–34          | Self-serve; no manual outreach   |

Band thresholds are admin-editable (Configuration → Lead Score → Score bands).

The Person also stores a **breakdown** so sales can see why a score is what it is:

- `product_interest_points`
- `email_domain_points`
- `country_points`
- `profile_points`
- `lead_score` (total)
- `score_band`

---

## Factor details

### 1. Product Interest (0–35)

- **Campaign = Product** in this CRM (Campaigns admin screen).
- Each Campaign has a **Product Interest score** (presets `35 / 30 / 25 / 20 / 15 / 10 / 5`, or custom `0–35`).
- For a Person with several Leads linked to Campaigns, the score uses the **maximum** Campaign score.
- If the Person has **no** Campaign-linked Lead (and no primary Campaign), Product Interest defaults to **5**.

### 2. Email Domain (0–25)

Derived from the Person’s email(s), matched against admin lists in this order:

| Match              | Points |
|--------------------|--------|
| Institutional      | 25     |
| Company            | 15     |
| Personal / unknown | 5      |

- Lists are edited under **Configuration → Lead Score → Email domains**.
- One domain or suffix per line (e.g. `gmail.com`, `edu`, `ac.in`).
- If a Person has multiple emails, an **institutional** address is preferred for scoring.

### 3. Country (0–20)

| Case                         | Points |
|------------------------------|--------|
| Tier 1                       | 20     |
| Tier 2                       | 16     |
| Tier 3                       | 12     |
| Tier 4                       | 8      |
| Blank / unknown              | 14     |
| Present but not in any tier  | 12 (treated as Tier 3) |

- Country comes from the Person (or their Organization).
- Tier membership is edited under **Configuration → Lead Score → Country tiers**.
- Names are normalized (e.g. `IN` → India) before lookup.

### 4. Profile — education only (0–20)

Uses `education_level` only (no separate Role field):

| Education                         | Points |
|-----------------------------------|--------|
| Faculty or PhD                    | 20     |
| Industry or Masters               | 16     |
| Undergraduate                     | 10     |
| Missing / other                   | 6      |

---

## When the score recalculates

| Event                                         | What happens                                      |
|-----------------------------------------------|---------------------------------------------------|
| Person create / update                        | Score + band + breakdown recalculated             |
| Lead create / update / delete (Campaign link) | Linked Person is re-scored                        |
| Campaign Product Interest score changes       | Queued job re-scores all Persons on that Campaign |

All intake channels (web form, CSV/Excel import, Firebase/portal sync, manual CRM entry) go through Person/Lead persistence, so they inherit this behaviour. There is **no per-channel scorer**.

---

## Admin setup

### Campaigns

1. Open **Campaigns** (Products).
2. On create/edit, set **Product Interest score** (preset or custom 0–35).
3. Saving a changed score queues re-scoring of related Persons (queue worker must be running).

### Configuration

**Settings → Configuration → Lead Score**

| Card            | What you edit                                      |
|-----------------|----------------------------------------------------|
| Email domains   | Institutional / company / personal lists           |
| Country tiers   | Tier 1–4 country lists                             |
| Score bands     | Hot / Warm / Nurture minimum thresholds            |

Breadcrumbs on these pages follow:  
`Dashboard / Configuration / Lead Score / …`

### One-time / bulk re-score

After deploy or large config changes:

```bash
php artisan omicslogic:rescore-persons
```

Optional chunk size: `--chunk=200`.

Ensure a queue worker is running so Campaign score fan-out jobs process:

```bash
php artisan queue:work
```

---

## Worked examples

| Person                                         | Calculation              | Result        |
|------------------------------------------------|--------------------------|---------------|
| US faculty, university email, Campaign = 30    | 30 + 25 + 20 + 20        | **95 · Hot**  |
| India Masters, gmail, Campaign = 20            | 20 + 5 + 16 + 16         | **57 · Warm** |
| Undergrad, gmail, no country, Campaign = 10    | 10 + 5 + 14 + 10         | **39 · Nurture** |
| Thin lead, gmail, no Campaign, undergrad       | 5 + 5 + 14 + 10          | **34 · Low**  |

---

## What is out of scope (v1)

These are **not** part of the score math yet:

- Lead flags (UPSELL, REPEAT_CUSTOMER, INCOMPLETE_DATA, etc.)
- Organization-based assignment
- Dual-email merge by phone
- Engagement / purchase / portal lesson activity (planned for a later version)

Flags and assignment may be designed in a follow-up; they must not change the four-factor total.

---

## Technical map (for developers)

| Piece                         | Role                                                                 |
|-------------------------------|----------------------------------------------------------------------|
| `LeadScoreCalculator`         | Single seam: evaluate Person → total, factors, band                  |
| `PersonRepository`            | Applies calculator on create/update; `rescore()` helper              |
| `LeadRepository`              | Re-scores Person after Lead↔Campaign changes / delete                |
| `RescorePersonsForCampaign`   | Queued fan-out when Campaign score changes                           |
| `omicslogic:rescore-persons`  | Full-table re-score command                                          |
| Configuration (`core_config`) | Editable domain lists, country tiers, band thresholds                |
| Campaign `product_interest_score` | Stored on Product; UI on Campaign create/edit                     |

Kanban and list views show a color badge: score · band

| Band | Color |
|------|-------|
| Hot | Red |
| Warm | Amber |
| Nurture | Sky blue |
| Low | Gray |

Demo data (2 Persons + Leads per band):

```bash
php artisan db:seed --class="AHATechnocrats\\OmicsLogic\\Database\\Seeders\\LeadScoreBandDemoSeeder"
```
