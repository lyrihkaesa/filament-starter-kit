<?php

declare(strict_types=1);

namespace App\Data;

/**
 * Represents a unified active device entry, abstracting over
 * both a web browser session (from the `sessions` table)
 * and a mobile/desktop API token (from `personal_access_tokens`).
 */
final readonly class DeviceInfo
{
    /**
     * @param  'web_session'|'mobile_app'|'desktop_app'|'api_client'  $type
     */
    public function __construct(
        /** Prefixed identifier: "session:{id}" or "token:{id}", used to route revocation to the right table. */
        public string $deviceId,
        /** The device category, used to render the appropriate icon in the UI. */
        public string $type,
        /** Human-readable device label, e.g. "Safari on iOS" or "mobile:Android:SM-G998B". */
        public string $label,
        public string $ipAddress,
        public string $lastActiveAt,
        public bool $isCurrentDevice,
    ) {}
}
