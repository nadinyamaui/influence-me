# 065 - Audience Demographics Charts

**Labels:** `feature`, `analytics`, `ui`
**Depends on:** #005, #058, #059

## Description

Add audience demographics charts to the analytics dashboard showing age, gender, city, and country breakdowns.

## Implementation

### Separate Section on Analytics Page
Add a "Audience Demographics" section below the main charts.

### Charts (4 charts in a 2x2 grid)

**1. Age Distribution (bar chart)**
- X-axis: age ranges (13-17, 18-24, 25-34, 35-44, 45-54, 55-64, 65+)
- Y-axis: percentage
- Color: indigo

**2. Gender Breakdown (doughnut chart)**
- Segments: Male, Female, Other
- Colors: blue, pink, gray
- Center: total audience label

**3. Top Cities (horizontal bar chart)**
- Top 10 cities by percentage
- Horizontal bars for easy reading

**4. Top Countries (horizontal bar chart)**
- Top 10 countries by percentage
- Horizontal bars

### Data Source
Query from `audience_demographics` table:
```php
$demographics = AudienceDemographic::query()
    ->where('instagram_account_id', $this->accountId)
    ->get()
    ->groupBy('type');

$ageData = $demographics->get(DemographicType::Age->value, collect());
$genderData = $demographics->get(DemographicType::Gender->value, collect());
$cityData = $demographics->get(DemographicType::City->value, collect())->sortByDesc('value')->take(10);
$countryData = $demographics->get(DemographicType::Country->value, collect())->sortByDesc('value')->take(10);
```

### Account Selector
If user has multiple accounts, show account selector to view demographics per account.

### Empty State
"Audience demographics data is not available yet. Run a sync to fetch data. Note: Requires 100+ followers."

## Files to Modify
- `resources/views/pages/analytics/index.blade.php` — add demographics section

## Acceptance Criteria
- [ ] Age bar chart renders correctly
- [ ] Gender doughnut chart renders
- [ ] Top cities horizontal bar chart renders
- [ ] Top countries horizontal bar chart renders
- [ ] Account selector works for multi-account
- [ ] Empty state shown when no demographic data
- [ ] Feature test verifies data display
