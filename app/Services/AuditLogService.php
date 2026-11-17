<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Carbon;

final class AuditLogService
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        string $action,
        string $description,
        ?User $actor = null,
        ?Company $company = null,
        ?string $documentType = null,
        ?int $documentId = null,
        ?string $documentNumber = null,
        array $metadata = [],
        ?string $ipAddress = null,
        ?Carbon $occurredAt = null,
    ): AuditLog {
        return AuditLog::query()->create([
            'actor_id' => $actor?->id,
            'company_id' => $company?->id,
            'action' => $action,
            'document_type' => $documentType,
            'document_id' => $documentId,
            'document_number' => $documentNumber,
            'description' => $description,
            'metadata' => $metadata !== [] ? $metadata : null,
            'ip_address' => $ipAddress ?? request()?->ip(),
            'occurred_at' => $occurredAt ?? now(),
        ]);
    }
}
