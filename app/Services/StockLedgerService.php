<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\StockMovementType;
use App\Enums\StockStatus;
use App\Exceptions\InsufficientStockException;
use App\Models\Company;
use App\Models\Lot;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseRack;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class StockLedgerService
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

    public function recordOpeningBalance(
        Product $product,
        Lot $lot,
        Company $ownerCompany,
        Warehouse $warehouse,
        WarehouseRack $rack,
        float|int|string $quantity,
        User $actor,
        Company $performedCompany,
        string $referenceNumber = 'INIT-STOK-001',
        ?string $notes = null,
        ?string $idempotencyKey = null,
        ?Carbon $occurredAt = null,
    ): StockMovement {
        $this->assertRackBelongsToWarehouse($rack, $warehouse);
        $this->assertLotMatchesProduct($lot, $product);

        return $this->executeMovement(
            product: $product,
            lot: $lot,
            movementType: StockMovementType::OpeningBalance,
            fromOwnerCompany: null,
            toOwnerCompany: $ownerCompany,
            fromWarehouse: null,
            fromRack: null,
            toWarehouse: $warehouse,
            toRack: $rack,
            fromStatus: null,
            toStatus: StockStatus::Available,
            quantity: $quantity,
            referenceType: 'opening_stock',
            referenceNumber: $referenceNumber,
            referenceId: null,
            actor: $actor,
            performedCompany: $performedCompany,
            notes: $notes,
            idempotencyKey: $idempotencyKey,
            occurredAt: $occurredAt,
            auditAction: 'stock.opening_balance',
        );
    }

    public function transferBetweenRacks(
        Product $product,
        Lot $lot,
        Company $ownerCompany,
        Warehouse $warehouse,
        WarehouseRack $fromRack,
        WarehouseRack $toRack,
        StockStatus $status,
        float|int|string $quantity,
        User $actor,
        Company $performedCompany,
        ?string $notes = null,
        ?string $idempotencyKey = null,
    ): StockMovement {
        $this->assertLotMatchesProduct($lot, $product);
        $this->assertRackBelongsToWarehouse($fromRack, $warehouse);
        $this->assertRackBelongsToWarehouse($toRack, $warehouse);

        if ($fromRack->id === $toRack->id) {
            throw new InvalidArgumentException('Rak tujuan harus berbeda dari rak asal.');
        }

        if (! $fromRack->is_active || ! $toRack->is_active) {
            throw new InvalidArgumentException('Rak asal dan tujuan harus berstatus aktif.');
        }

        $referenceNumber = 'TRF-'.now()->timezone('Asia/Jakarta')->format('Ymd').'-'.strtoupper(Str::random(4));

        return $this->executeMovement(
            product: $product,
            lot: $lot,
            movementType: StockMovementType::RackTransfer,
            fromOwnerCompany: $ownerCompany,
            toOwnerCompany: $ownerCompany,
            fromWarehouse: $warehouse,
            fromRack: $fromRack,
            toWarehouse: $warehouse,
            toRack: $toRack,
            fromStatus: $status,
            toStatus: $status,
            quantity: $quantity,
            referenceType: 'rack_transfer',
            referenceNumber: $referenceNumber,
            referenceId: null,
            actor: $actor,
            performedCompany: $performedCompany,
            notes: $notes,
            idempotencyKey: $idempotencyKey,
            occurredAt: now(),
            auditAction: 'stock.rack_transferred',
        );
    }

    private function executeMovement(
        Product $product,
        Lot $lot,
        StockMovementType $movementType,
        ?Company $fromOwnerCompany,
        ?Company $toOwnerCompany,
        ?Warehouse $fromWarehouse,
        ?WarehouseRack $fromRack,
        ?Warehouse $toWarehouse,
        ?WarehouseRack $toRack,
        ?StockStatus $fromStatus,
        ?StockStatus $toStatus,
        float|int|string $quantity,
        string $referenceType,
        string $referenceNumber,
        ?int $referenceId,
        User $actor,
        Company $performedCompany,
        ?string $notes,
        ?string $idempotencyKey,
        ?Carbon $occurredAt,
        string $auditAction,
    ): StockMovement {
        $normalizedQuantity = $this->normalizePositiveQuantity($quantity);

        return DB::transaction(function () use (
            $product,
            $lot,
            $movementType,
            $fromOwnerCompany,
            $toOwnerCompany,
            $fromWarehouse,
            $fromRack,
            $toWarehouse,
            $toRack,
            $fromStatus,
            $toStatus,
            $normalizedQuantity,
            $referenceType,
            $referenceNumber,
            $referenceId,
            $actor,
            $performedCompany,
            $notes,
            $idempotencyKey,
            $occurredAt,
            $auditAction,
        ): StockMovement {
            if ($idempotencyKey !== null) {
                $existing = StockMovement::query()
                    ->where('idempotency_key', $idempotencyKey)
                    ->first();

                if ($existing !== null) {
                    return $existing;
                }
            }

            if ($fromOwnerCompany !== null && $fromStatus !== null) {
                $this->decrementBalance(
                    product: $product,
                    lot: $lot,
                    ownerCompany: $fromOwnerCompany,
                    warehouse: $fromWarehouse,
                    rack: $fromRack,
                    status: $fromStatus,
                    quantity: $normalizedQuantity,
                );
            }

            if ($toOwnerCompany !== null && $toStatus !== null) {
                $this->incrementBalance(
                    product: $product,
                    lot: $lot,
                    ownerCompany: $toOwnerCompany,
                    warehouse: $toWarehouse,
                    rack: $toRack,
                    status: $toStatus,
                    quantity: $normalizedQuantity,
                );
            }

            $timestamp = $occurredAt ?? now();

            $movement = StockMovement::query()->create([
                'movement_number' => $this->generateMovementNumber($timestamp),
                'idempotency_key' => $idempotencyKey,
                'product_id' => $product->id,
                'lot_id' => $lot->id,
                'movement_type' => $movementType,
                'from_owner_company_id' => $fromOwnerCompany?->id,
                'to_owner_company_id' => $toOwnerCompany?->id,
                'from_warehouse_id' => $fromWarehouse?->id,
                'from_rack_id' => $fromRack?->id,
                'to_warehouse_id' => $toWarehouse?->id,
                'to_rack_id' => $toRack?->id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'quantity' => $normalizedQuantity,
                'reference_type' => $referenceType,
                'reference_number' => $referenceNumber,
                'reference_id' => $referenceId,
                'performed_by' => $actor->id,
                'performed_company_id' => $performedCompany->id,
                'occurred_at' => $timestamp,
                'notes' => $notes,
            ]);

            $this->auditLogService->record(
                action: $auditAction,
                description: sprintf(
                    '%s sejumlah %s %s (Lot %s) — referensi %s.',
                    $movementType->label(),
                    $normalizedQuantity,
                    $product->unit,
                    $lot->lot_number,
                    $referenceNumber
                ),
                actor: $actor,
                company: $performedCompany,
                documentType: 'StockMovement',
                documentId: $movement->id,
                documentNumber: $movement->movement_number,
                metadata: [
                    'product_sku' => $product->sku,
                    'lot_number' => $lot->lot_number,
                    'quantity' => $normalizedQuantity,
                    'from_owner_company_id' => $fromOwnerCompany?->id,
                    'to_owner_company_id' => $toOwnerCompany?->id,
                    'from_rack_id' => $fromRack?->id,
                    'to_rack_id' => $toRack?->id,
                ],
                occurredAt: $timestamp,
            );

            return $movement;
        });
    }

    private function decrementBalance(
        Product $product,
        Lot $lot,
        Company $ownerCompany,
        ?Warehouse $warehouse,
        ?WarehouseRack $rack,
        StockStatus $status,
        string $quantity,
    ): void {
        $bucketKey = StockBalance::buildBucketKey(
            $product->id,
            $lot->id,
            $ownerCompany->id,
            $warehouse?->id,
            $rack?->id,
            $status
        );

        $balance = StockBalance::query()
            ->where('bucket_key', $bucketKey)
            ->lockForUpdate()
            ->first();

        $available = $balance !== null ? $this->normalizeScale((string) $balance->quantity) : '0.000';

        if (bccomp($available, $quantity, 3) < 0) {
            throw new InsufficientStockException(
                sprintf(
                    'Stok tidak mencukupi pada lokasi asal untuk %s (Lot %s, status %s). Tersedia: %s, diminta: %s.',
                    $product->name,
                    $lot->lot_number,
                    $status->label(),
                    $available,
                    $quantity
                )
            );
        }

        $balance->update([
            'quantity' => bcsub($available, $quantity, 3),
        ]);
    }

    private function incrementBalance(
        Product $product,
        Lot $lot,
        Company $ownerCompany,
        ?Warehouse $warehouse,
        ?WarehouseRack $rack,
        StockStatus $status,
        string $quantity,
    ): void {
        $bucketKey = StockBalance::buildBucketKey(
            $product->id,
            $lot->id,
            $ownerCompany->id,
            $warehouse?->id,
            $rack?->id,
            $status
        );

        $now = now();

        DB::table('stock_balances')->insertOrIgnore([
            'bucket_key' => $bucketKey,
            'product_id' => $product->id,
            'lot_id' => $lot->id,
            'owner_company_id' => $ownerCompany->id,
            'warehouse_id' => $warehouse?->id,
            'rack_id' => $rack?->id,
            'status' => $status->value,
            'quantity' => '0.000',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $balance = StockBalance::query()
            ->where('bucket_key', $bucketKey)
            ->lockForUpdate()
            ->firstOrFail();

        $current = $this->normalizeScale((string) $balance->quantity);

        $balance->update([
            'quantity' => bcadd($current, $quantity, 3),
        ]);
    }

    private function assertRackBelongsToWarehouse(WarehouseRack $rack, Warehouse $warehouse): void
    {
        if ($rack->warehouse_id !== $warehouse->id) {
            throw new InvalidArgumentException('Rak yang dipilih bukan bagian dari gudang terkait.');
        }
    }

    private function assertLotMatchesProduct(Lot $lot, Product $product): void
    {
        if ($lot->product_id !== $product->id) {
            throw new InvalidArgumentException('Lot tidak sesuai dengan produk yang dipilih.');
        }
    }

    private function normalizePositiveQuantity(float|int|string $quantity): string
    {
        $scaled = $this->normalizeScale((string) $quantity);

        if (bccomp($scaled, '0.000', 3) <= 0) {
            throw new InvalidArgumentException('Kuantitas pergerakan stok harus lebih besar dari nol.');
        }

        return $scaled;
    }

    private function normalizeScale(string $value): string
    {
        if (! is_numeric($value)) {
            throw new InvalidArgumentException('Format kuantitas tidak valid.');
        }

        return number_format((float) $value, 3, '.', '');
    }

    private function generateMovementNumber(Carbon $timestamp): string
    {
        $datePrefix = $timestamp->copy()->timezone('Asia/Jakarta')->format('Ymd');

        return 'SM-'.$datePrefix.'-'.strtoupper(Str::random(6));
    }
}
