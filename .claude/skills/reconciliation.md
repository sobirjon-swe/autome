---
name: reconciliation
description: >-
  autome.uz loyihasidagi online to'lov сверка (reconciliation) logikasi.
  Click/Payme/karta to'lovlarini bank bilan solishtirish. Reconciliation
  yaratish, tasdiqlash, mismatched holatlarni boshqarishda ishlatiladi.
---

# Reconciliation — Online to'lov сверка

## Biznes konteksti

autome.uz da kassa apparati yo'q. Online to'lovlar bank ilovasiga tushadi.

### Jarayon
1. Mijoz Click/Payme orqali to'laydi
2. Hodim to'lovni ko'radi → tovarni beradi → **mijoz ketdi**
3. Hodim tizimga sotuvni kiritadi + chek rasmini yuklaydi
4. Sotuv darhol yakunlanadi — ombor kamayadi, hisobotga tushadi
5. **Сверка** — admin kun/smena oxirida bank bilan solishtiradi

> Mijoz hech narsani kutmaydi. Сверка — faqat ichki audit.

### reconciliation_status (sales jadvalida)
```
unchecked → matched
          ↘ mismatched (admin tekshiradi)
```

### reconciliations holatlari
```
pending → matched
        ↘ mismatched
```

## Fayl joylashuvi

```
app/Http/Controllers/Api/ReconciliationController.php
app/Models/Reconciliation.php
app/Models/Sale.php              ← reconciliation_status ustuni bor
app/Services/ReconciliationService.php
database/migrations/..._create_reconciliations_table.php
```

## API endpointlar

```
GET    /api/reconciliations/summary      → Online sotuvlar yig'indisi (smena/kun bo'yicha)
POST   /api/reconciliations              → Сверка yaratish
PATCH  /api/reconciliations/{id}         → Holat yangilash (matched/mismatched)
```

## ReconciliationService logikasi

```php
// Сверка summary — qaysi sotuvlar unchecked
public function getUnreconciledSummary(int $branchId, ?int $shiftId = null): array
{
    $query = Sale::where('branch_id', $branchId)
        ->where('payment_type', 'online')
        ->where('reconciliation_status', 'unchecked');

    if ($shiftId) {
        $query->where('shift_id', $shiftId);
    }

    return [
        'total_online_sales' => $query->sum('total_amount'),
        'sales_count' => $query->count(),
        'sales' => $query->with('items.product')->get(),
    ];
}

// Сверка yaratish
public function createReconciliation(array $data): Reconciliation
{
    $reconciliation = Reconciliation::create([
        'branch_id' => $data['branch_id'],
        'shift_id' => $data['shift_id'],
        'total_online_sales' => $data['total_online_sales'],
        'total_bank_received' => $data['total_bank_received'],
        'difference' => $data['total_bank_received'] - $data['total_online_sales'],
        'status' => 'pending',
        'checked_by' => auth()->id(),
    ]);

    // Bog'liq sotuvlarni yangilash
    $status = abs($reconciliation->difference) < 100 ? 'matched' : 'mismatched';
    $reconciliation->update(['status' => $status]);

    if ($status === 'mismatched') {
        // Telegram notification yuboriladi
        TelegramNotification::send('reconciliation_mismatched', $reconciliation);
    }

    return $reconciliation;
}
```

## Chek rasmi yuklash

```
POST /api/sales/{id}/receipt
```
- `receipt_photo` — `storage/receipts/` ga saqlanadi
- Faqat `payment_type = online` sotuvlar uchun
- Rasm yuklanmagan online sotuvlar сверка da ko'rinadi

## Muhim biznes qoidalar

- Сверка faqat `admin` yoki `manager` roli qila oladi (`reconcile payments` permission)
- Bir smena uchun bir martagina сверка yaratiladi
- `mismatched` holatda Telegram bildirishnomasi admin ga ketadi
- Chek rasmi — yuklash majburiy emas, lekin сверка da ko'rsatiladi

## Tekshirish ro'yxati

- [ ] Faqat `online` to'lovlar сверка ga kirsin
- [ ] `difference` avtomatik hisoblansin
- [ ] Chek rasmi storage ga to'g'ri saqlansin
- [ ] `mismatched` da Telegram notification ishlayotgan bo'lsin
- [ ] Bir smena uchun bir сверка (unique constraint yoki validation)
