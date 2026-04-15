---
name: branch-stock
description: >-
  autome.uz multi-branch ombor logikasi: branch_stocks, stock_transfers,
  inventories. Har bir nuqtaning alohida ombori, nuqtalar orasida tovar
  ko'chirish, inventarizatsiya o'tkazishda ishlatiladi.
---

# Branch Stock — Multi-branch ombor

## Biznes konteksti

Har bir savdo nuqtasi (branch) — **alohida ombor**. Mahsulot global,
lekin miqdor nuqtaga bog'liq (`branch_stocks` jadval).

## Jadval strukturasi

### branch_stocks
```
branch_id (FK) + product_id (FK) → UNIQUE constraint
stock_quantity  — hozirgi qoldiq
min_stock_alert — minimal qoldiq (alert uchun)
```

### Ombor o'zgarish sabablari
1. **Sotuv** → `stock_quantity -= quantity` (sale yaratilganda)
2. **Stock transfer qabul** → `stock_quantity += quantity`
3. **Stock transfer yuborish** → `stock_quantity -= quantity`
4. **Inventarizatsiya** → `stock_quantity = actual_quantity`

## Fayl joylashuvi

```
app/Http/Controllers/Api/BranchStockController.php
app/Http/Controllers/Api/StockTransferController.php
app/Http/Controllers/Api/InventoryController.php
app/Models/BranchStock.php
app/Models/StockTransfer.php
app/Models/StockTransferItem.php
app/Models/Inventory.php
app/Models/InventoryItem.php
app/Services/StockService.php
database/migrations/..._create_branch_stocks_table.php
database/migrations/..._create_stock_transfers_table.php
database/migrations/..._create_stock_transfer_items_table.php
database/migrations/..._create_inventories_table.php
database/migrations/..._create_inventory_items_table.php
```

## StockService — asosiy logika

```php
class StockService
{
    // Sotuv yaratilganda chaqiriladi
    public function decrementForSale(int $branchId, array $items): void
    {
        foreach ($items as $item) {
            $stock = BranchStock::where('branch_id', $branchId)
                ->where('product_id', $item['product_id'])
                ->lockForUpdate()
                ->first();

            if (!$stock || $stock->stock_quantity < $item['quantity']) {
                throw new InsufficientStockException($item['product_id']);
            }

            $stock->decrement('stock_quantity', $item['quantity']);

            // Min alert tekshiruvi
            if ($stock->stock_quantity <= $stock->min_stock_alert) {
                $this->sendLowStockAlert($branchId, $stock);
            }
        }
    }

    // Transfer tasdiqlanganda chaqiriladi
    public function confirmTransfer(StockTransfer $transfer): void
    {
        DB::transaction(function () use ($transfer) {
            foreach ($transfer->items as $item) {
                // From branch: kamaytirish
                BranchStock::where('branch_id', $transfer->from_branch_id)
                    ->where('product_id', $item->product_id)
                    ->decrement('stock_quantity', $item->quantity);

                // To branch: ko'paytirish (agar mavjud bo'lmasa — yaratish)
                BranchStock::updateOrCreate(
                    ['branch_id' => $transfer->to_branch_id, 'product_id' => $item->product_id],
                    ['stock_quantity' => 0, 'min_stock_alert' => 5]
                )->increment('stock_quantity', $item->quantity);
            }

            $transfer->update([
                'status' => 'confirmed',
                'confirmed_by' => auth()->id(),
                'confirmed_at' => now(),
            ]);
        });
    }
}
```

## API endpointlar

```
GET    /api/branches/{id}/stocks         → BranchStockController@index
PATCH  /api/branches/{id}/stocks/{pid}   → BranchStockController@update
GET    /api/branches/{id}/stocks/alerts  → BranchStockController@alerts

POST   /api/stock-transfers              → StockTransferController@store
GET    /api/stock-transfers              → StockTransferController@index
PATCH  /api/stock-transfers/{id}/confirm → StockTransferController@confirm
PATCH  /api/stock-transfers/{id}/cancel  → StockTransferController@cancel

POST   /api/inventories                  → InventoryController@store
GET    /api/inventories                  → InventoryController@index
GET    /api/inventories/{id}             → InventoryController@show
PUT    /api/inventories/{id}/items       → InventoryController@updateItems
PATCH  /api/inventories/{id}/complete    → InventoryController@complete
```

## Inventarizatsiya logikasi

```php
// Inventarizatsiya boshlanganda system_quantity ni snapshot qilish
public function startInventory(int $branchId, string $type): Inventory
{
    $inventory = Inventory::create([...]);

    // Barcha mahsulotlar uchun joriy qoldiqni snapshot
    $stocks = BranchStock::where('branch_id', $branchId)->get();
    foreach ($stocks as $stock) {
        InventoryItem::create([
            'inventory_id' => $inventory->id,
            'product_id' => $stock->product_id,
            'system_quantity' => $stock->stock_quantity,
            'actual_quantity' => null, // Hodim to'ldiradi
            'difference' => 0,
        ]);
    }

    return $inventory;
}

// Yakunlashda branch_stocks ni yangilash
public function completeInventory(Inventory $inventory): void
{
    DB::transaction(function () use ($inventory) {
        foreach ($inventory->items as $item) {
            $item->update([
                'difference' => $item->actual_quantity - $item->system_quantity,
            ]);

            BranchStock::where('branch_id', $inventory->branch_id)
                ->where('product_id', $item->product_id)
                ->update(['stock_quantity' => $item->actual_quantity]);
        }

        $inventory->update(['status' => 'completed', 'completed_at' => now()]);
    });
}
```

## Permissions

- `view stock` → barcha rollar
- `update stock` → admin/manager
- `transfer stock` → admin/manager
- `conduct inventory` → barcha rollar
- `manage inventory` → admin/manager

## Tekshirish ro'yxati

- [ ] Sotuv paytida qoldiq yetarli bo'lmasa — xato qaytarsin
- [ ] Transfer `pending` holatdan `confirmed` ga o'tganda ombor yangilangsin
- [ ] Min stock alert Telegram bildirishnomasi yuborilsin
- [ ] Inventarizatsiya yakunida `branch_stocks` yangilangsin
- [ ] `lockForUpdate()` concurrent sotuvlarda race condition bo'lmasin
