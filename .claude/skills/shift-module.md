---
name: shift-module
description: >-
  autome.uz loyihasidagi smena (shift) logikasini boshqaradi: ochish, yopish,
  handover tasdiqlash. Smena bilan bog'liq har qanday o'zgartirish, bug fix,
  yoki yangi feature qo'shishda ishlatiladi.
---

# Shift Module — Smena boshqaruvi

## Biznes konteksti

autome.uz — multi-branch mavsumiy POS tizimi. 3 smena: **morning / day / night**.

### Smena holatlari (status)
```
active → pending_confirmation → confirmed
                              ↘ disputed (admin ga bildirishnoma)
```

### Handover jarayoni (MUHIM qoida)
1. Ali "Smenani yopish" bosadi → kassadagi pulni sanab kiritadi
2. Shift status: `pending_confirmation`
3. Vali (keyingi hodim) bildirishnoma oladi
4. **Vali Ali yozgan summani KO'RMAYDI** — mustaqil sanash uchun
5. Vali summasini kiritadi:
   - Mos: `confirmed` + yangi smena ochiladi
   - Mos emas: `disputed` + admin Telegram notification
6. **Tungi smena**: Admin masofadan `/confirm_handover {id}` orqali tasdiqlaydi

## Fayl joylashuvi

```
app/Http/Controllers/Api/ShiftController.php
app/Http/Controllers/Api/HandoverController.php
app/Models/Shift.php
app/Models/ShiftHandover.php
app/Services/ShiftService.php
app/Services/KpiService.php        ← shift yopilganda KPI hisoblanadi
database/migrations/..._create_shifts_table.php
database/migrations/..._create_shift_handovers_table.php
```

## Muhim biznes qoidalar

### Smena ochish
- Bir nuqtada faqat bitta `active` smena bo'lishi mumkin
- `opening_cash` — oldingi smenadan qolgan naqd pul
- `shift_type` — vaqt bo'yicha avtomatik aniqlanishi mumkin yoki qo'lda
- Schedule bilan solishtiriladi: boshqa hodim kelsa → `swapped`

### Smena yopish
- Faqat `active` holatdagi smena yopilishi mumkin
- `closing_cash_declared` kiritiladi
- KPI result avtomatik hisoblanadi (KpiService orqali)
- Telegram notification yuboriladi: admin + keyingi navbatchi

### Handover tasdiqlash
```php
// difference avtomatik hisoblanadi
$handover->difference = $handover->confirmed_amount - $handover->declared_amount;
$handover->status = abs($difference) < 1000 ? 'matched' : 'mismatched';
```

### Tungi smena masofadan tasdiqlash
- Telegram `/confirm_handover {id}` komandasi
- Admin chat_id dan kelgan bo'lishi kerak
- Tasdiqlanganda yangi shift ochiladi, oldingi `confirmed` bo'ladi

## API endpointlar

```
POST   /api/shifts/open                  → ShiftController@open
POST   /api/shifts/close                 → ShiftController@close
GET    /api/shifts/current               → ShiftController@current
GET    /api/shifts                       → ShiftController@index
GET    /api/shifts/{id}                  → ShiftController@show
POST   /api/handovers/confirm            → HandoverController@confirm
POST   /api/handovers/dispute            → HandoverController@dispute
GET    /api/handovers                    → HandoverController@index
```

## Muhim: KPI integratsiya

Smena yopilganda `ShiftService::closeShift()` ichida:
```php
$kpiResult = $this->kpiService->calculateForShift($shift);
// Telegram ga KPI natijasi yuboriladi
```

## Tekshirish ro'yxati

- [ ] Bir nuqtada ikki `active` smena bo'lib qolmasin
- [ ] Handover da Vali Ali summani ko'rmasin
- [ ] `disputed` da admin Telegram xabar olsin
- [ ] KPI smena yopilganda hisoblansa
- [ ] Tungi smena Telegram orqali tasdiqlansin
