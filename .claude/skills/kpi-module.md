---
name: kpi-module
description: >-
  autome.uz loyihasidagi KPI hisoblash va bosqichli bonus logikasi.
  kpi_settings (maqsad + bonus qoidalar) va kpi_results (smena natijasi)
  bilan ishlaganda, bonus hisoblashda ishlatiladi.
---

# KPI Module — Maqsad va bonus hisoblash

## Biznes konteksti

Har bir smena uchun sotuv maqsadi (target) belgilanadi. Hodim maqsaddan
oshirsa — bosqichli bonus oladi.

### kpi_settings — maqsad qoidalari

```json
{
  "tiers": [
    { "min_percent": 100, "max_percent": 120, "bonus_percent": 5 },
    { "min_percent": 120, "max_percent": 150, "bonus_percent": 10 },
    { "min_percent": 150, "max_percent": null, "bonus_percent": 15 }
  ]
}
```

**Izoh:**
- `min_percent: 100` = maqsadning 100% → bonus 5%
- `min_percent: 120` = maqsadning 120% → bonus 10%
- `min_percent: 150` = 150% va undan yuqori → bonus 15%
- `max_percent: null` = cheksiz yuqori

### kpi_settings lookup tartibi
1. `branch_id + shift_type + day_type` — eng aniq sozlama
2. `branch_id = null + shift_type + day_type` — umumiy default

### day_type aniqlash
```php
$dayOfWeek = Carbon::now()->dayOfWeek;
$dayType = in_array($dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])
    ? 'weekend'
    : 'weekday';
```

## Fayl joylashuvi

```
app/Http/Controllers/Api/KpiController.php
app/Models/KpiSetting.php
app/Models/KpiResult.php
app/Services/KpiService.php           ← asosiy logika
database/migrations/..._create_kpi_settings_table.php
database/migrations/..._create_kpi_results_table.php
```

## KpiService — asosiy logika

```php
class KpiService
{
    public function calculateForShift(Shift $shift): KpiResult
    {
        // 1. KPI settings ni topish
        $setting = $this->findSetting($shift);

        if (!$setting) {
            return null; // KPI yo'q bu smena uchun
        }

        // 2. Haqiqiy sotuv
        $actualAmount = Sale::where('shift_id', $shift->id)->sum('total_amount');

        // 3. Foiz hisoblash
        $percentage = $setting->target_amount > 0
            ? ($actualAmount / $setting->target_amount) * 100
            : 0;

        // 4. Bonus hisoblash
        $bonusAmount = $this->calculateBonus($percentage, $actualAmount, $setting);

        // 5. Natija saqlash
        return KpiResult::create([
            'shift_id' => $shift->id,
            'user_id' => $shift->user_id,
            'target_amount' => $setting->target_amount,
            'actual_amount' => $actualAmount,
            'percentage' => $percentage,
            'bonus_amount' => $bonusAmount,
        ]);
    }

    private function findSetting(Shift $shift): ?KpiSetting
    {
        $date = Carbon::parse($shift->opened_at);
        $dayType = in_array($date->dayOfWeek, [6, 0]) ? 'weekend' : 'weekday';

        // Birinchi branch-specific sozlamani qidiramiz
        return KpiSetting::where('shift_type', $shift->shift_type)
            ->where('day_type', $dayType)
            ->where(function($q) use ($shift) {
                $q->where('branch_id', $shift->branch_id)
                  ->orWhereNull('branch_id');
            })
            ->orderByRaw('branch_id IS NULL ASC') // branch-specific birinchi
            ->first();
    }

    private function calculateBonus(float $percentage, float $actual, KpiSetting $setting): float
    {
        $tiers = $setting->bonus_rules['tiers'] ?? [];
        $bonusPercent = 0;

        foreach ($tiers as $tier) {
            $min = $tier['min_percent'];
            $max = $tier['max_percent'];

            if ($percentage >= $min && ($max === null || $percentage < $max)) {
                $bonusPercent = $tier['bonus_percent'];
                break; // Eng yuqori mos kelgan tier
            }
        }

        // Bonus faqat maqsaddan oshgan qism uchun emas — JAMI sotuvdan
        return $actual * ($bonusPercent / 100);
    }
}
```

## API endpointlar

```
GET    /api/kpi/settings                 → KpiController@settings (filter: branch_id)
POST   /api/kpi/settings                 → KpiController@storeSetting
PUT    /api/kpi/settings/{id}            → KpiController@updateSetting
GET    /api/kpi/results                  → KpiController@results (filter: branch_id, user_id, date_range)
GET    /api/kpi/my                       → KpiController@myResults (auth user)
```

## Permissions

- `view own kpi` → employee o'z natijalarini ko'radi
- `manage kpi settings` → admin/manager sozlamalarni boshqaradi

## Tekshirish ro'yxati

- [ ] Tier chegaralari to'g'ri ishlayotgan bo'lsin (100%, 120%, 150%)
- [ ] `branch_id = null` default fallback ishlayotgan bo'lsin
- [ ] Weekend/weekday aniqlash to'g'ri
- [ ] Bonus faqat threshold dan o'tganda hisoblangsin
- [ ] KPI smena yopilganda avtomatik hisoblangsin
- [ ] Telegram KPI notification yuborilsin
