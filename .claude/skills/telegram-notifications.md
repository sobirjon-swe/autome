---
name: telegram-notifications
description: >-
  autome.uz Telegram bot bildirishnomalar logikasi. Smena yopildi, handover
  farq, ombor alert, KPI natijasi, inventarizatsiya va boshqa hodisalar
  uchun Telegram xabarlari. Bot komandalarini qo'shish/o'zgartirishda ishlatiladi.
---

# Telegram Notifications — Bot bildirishnomalar

## Biznes konteksti

Admin doim telefonda bo'lmaydi. Telegram orqali real-time bildirishnomalar
va masofadan buyruqlar ishlaydi.

## .env sozlamalari

```
TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_CHAT_ID=your_admin_chat_id
```

## Bildirishnoma turlari

### 1. Smena yopildi
```
✅ Smena yopildi
📍 Nuqta: 1-nuqta Chorvoq yo'li
👤 Hodim: Ali
🕐 Turi: Kechki smena
💰 Naqd: 850,000 | 💳 Online: 680,000
💸 Xarajatlar: 120,000
📊 Sof: 1,410,000
✋ Vali tasdiqladi: 850,000
```

### 2. Handover farq (MUHIM)
```
⚠️ DIQQAT! Kassa farqi!
📍 Nuqta: 1-nuqta
👤 Ali → 850,000 | 👤 Vali → 800,000
📉 Farq: 50,000 so'm
```

### 3. Ombor ogohlantiruvi
```
📦 Ombor ogohlantiruvi
📍 Nuqta: 1-nuqta
• Coca-Cola 0.5l — 3 ta qoldi
• Gorilla 0.5l — 2 ta qoldi
```

### 4. KPI natijasi
```
🏆 KPI: Ali — 1-nuqta kechki smena
🎯 Target: 1,000,000
📊 Haqiqiy: 1,350,000 (135%)
💰 Bonus: 25,000 so'm
```

### 5. Inventarizatsiya yakunlandi
```
📋 Inventarizatsiya yakunlandi
📍 Nuqta: 1-nuqta
📦 Tekshirildi: 45 | ✅ Mos: 40 | ⚠️ Farq: 5
📉 Kamomad: Coca-Cola (-3), Gorilla (-2)
```

### 6. Tovar o'tkazma
```
🔄 Tovar o'tkazma: 1-nuqta → 2-nuqta
📦 Coca-Cola 0.5l: 20 dona
📦 Gorilla 0.5l: 10 dona
⏳ Tasdiqlash kutilmoqda
```

### 7. Ertangi grafik (har kuni kechqurun avtomatik)
```
📅 Ertangi grafik
📍 1-nuqta: 🌅 Ali | ☀️ Vali | 🌙 Soli
📍 2-nuqta: 🌅 Anvar | ☀️ Botir | 🌙 Jasur
```

## Fayl joylashuvi

```
app/Services/TelegramService.php          ← asosiy xabar yuborish
app/Notifications/TelegramNotification.php ← Laravel notification
app/Jobs/SendTelegramMessageJob.php        ← queue orqali
routes/telegram.php                        ← webhook
app/Http/Controllers/TelegramController.php ← komandalar
```

## TelegramService

```php
class TelegramService
{
    public function send(string $message, ?int $chatId = null): void
    {
        $chatId = $chatId ?? config('services.telegram.chat_id');

        Http::post("https://api.telegram.org/bot" . config('services.telegram.token') . "/sendMessage", [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
        ]);
    }

    public function sendShiftClosed(Shift $shift): void
    {
        $cashSales = $shift->sales()->where('payment_type', 'cash')->sum('total_amount');
        $onlineSales = $shift->sales()->where('payment_type', 'online')->sum('total_amount');
        $expenses = $shift->expenses()->sum('amount');
        $net = $cashSales + $onlineSales - $expenses;

        $message = "✅ Smena yopildi\n"
            . "📍 Nuqta: {$shift->branch->name}\n"
            . "👤 Hodim: {$shift->user->name}\n"
            . "🕐 Turi: {$shift->shift_type}\n"
            . "💰 Naqd: " . number_format($cashSales) . " | 💳 Online: " . number_format($onlineSales) . "\n"
            . "💸 Xarajatlar: " . number_format($expenses) . "\n"
            . "📊 Sof: " . number_format($net);

        $this->send($message);
    }

    public function sendHandoverDisputed(ShiftHandover $handover): void
    {
        $message = "⚠️ DIQQAT! Kassa farqi!\n"
            . "📍 Nuqta: {$handover->closingShift->branch->name}\n"
            . "👤 {$handover->closingShift->user->name} → " . number_format($handover->declared_amount) . "\n"
            . "👤 Tasdiqlagan → " . number_format($handover->confirmed_amount) . "\n"
            . "📉 Farq: " . number_format(abs($handover->difference)) . " so'm";

        $this->send($message);
    }

    public function sendLowStockAlert(Branch $branch, array $lowStocks): void
    {
        $items = collect($lowStocks)->map(fn($s) =>
            "• {$s->product->name} — {$s->stock_quantity} ta qoldi"
        )->join("\n");

        $message = "📦 Ombor ogohlantiruvi\n"
            . "📍 Nuqta: {$branch->name}\n"
            . $items;

        $this->send($message);
    }

    public function sendKpiResult(KpiResult $result): void
    {
        $message = "🏆 KPI: {$result->user->name} — {$result->shift->branch->name} {$result->shift->shift_type}\n"
            . "🎯 Target: " . number_format($result->target_amount) . "\n"
            . "📊 Haqiqiy: " . number_format($result->actual_amount) . " ({$result->percentage}%)\n"
            . "💰 Bonus: " . number_format($result->bonus_amount) . " so'm";

        $this->send($message);
    }
}
```

## Admin komandalar (webhook)

```php
// TelegramController
public function handleWebhook(Request $request): void
{
    $update = $request->all();
    $text = $update['message']['text'] ?? '';
    $chatId = $update['message']['chat']['id'] ?? null;

    // Faqat admin chat_id dan kelgan komandalar qabul qilinadi
    if ($chatId != config('services.telegram.chat_id')) return;

    match(true) {
        str_starts_with($text, '/status') => $this->handleStatus($text),
        str_starts_with($text, '/stock') => $this->handleStock($text),
        str_starts_with($text, '/confirm_handover') => $this->handleConfirmHandover($text),
        str_starts_with($text, '/confirm_transfer') => $this->handleConfirmTransfer($text),
        str_starts_with($text, '/report') => $this->handleReport($text),
        str_starts_with($text, '/debts') => $this->handleDebts($text),
        str_starts_with($text, '/kpi') => $this->handleKpi($text),
        default => null
    };
}
```

## Admin komandalar ro'yxati

| Komanda | Vazifasi |
|---------|---------|
| `/status` | Barcha nuqtalar umumiy holati |
| `/status {branch_id}` | Bitta nuqta holati |
| `/stock` | Barcha nuqtalarda kritik qoldiqlar |
| `/stock {branch_id}` | Bitta nuqta ombori |
| `/debts` | Barcha ochiq qarzlar |
| `/kpi bugun` | Bugungi KPI holati |
| `/kpi hafta` | Haftalik KPI |
| `/report bugun` | Bugungi hisobot |
| `/report hafta` | Haftalik hisobot |
| `/sverka ok {branch_id}` | Сверка tasdiqlash |
| `/confirm_handover {id}` | Masofadan handover tasdiqlash |
| `/confirm_transfer {id}` | Tovar o'tkazmani tasdiqlash |
| `/schedule {branch_id}` | Bugungi ish grafigi |

## config/services.php

```php
'telegram' => [
    'token' => env('TELEGRAM_BOT_TOKEN'),
    'chat_id' => env('TELEGRAM_CHAT_ID'),
],
```

## Tekshirish ro'yxati

- [ ] `.env` da `TELEGRAM_BOT_TOKEN` va `TELEGRAM_CHAT_ID` sozlangan bo'lsin
- [ ] Webhook URL Telegram ga ro'yxatdan o'tkazilgan bo'lsin
- [ ] Faqat admin `chat_id` dan kelgan komandalar ishlayotgan bo'lsin
- [ ] Xabarlar queue orqali yuborilsin (sekin bo'lmasin)
- [ ] Har bir bildirishnoma turi mavjud bo'lsin
