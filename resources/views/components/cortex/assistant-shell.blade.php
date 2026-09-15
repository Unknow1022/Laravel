<div class="cortex-container m-0 border-0" style="background: transparent;">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 mb-4 mb-md-5 px-3 px-md-5 pt-3 pt-md-5">
        <div class="cortex-header">
            <span class="ai-badge mb-2 d-inline-block">Neural Analysis Console</span>
            <h2 class="m-0 text-white" style="font-weight: 800; font-size: 2.2rem; letter-spacing: -1px;">
                Cortex <span class="text-primary">Assistant</span>
            </h2>
        </div>
        <div class="cortex-nodes d-flex flex-wrap gap-3">
            <x-cortex.status-node label="System Health" status="online" />
            <x-cortex.status-node label="Security" :status="$attributes->get('security-status', 'secure')" />
        </div>
    </div>

    <div class="px-3 px-md-5 pb-3 pb-md-5">
        {{ $slot }}
    </div>

    <div class="cortex-footer mt-4 mt-md-5 px-3 px-md-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div class="system-meta small text-muted">
            <i class="fa-solid fa-microchip mr-1"></i> Engine: v3.2.0-NEURAL | Latency: 12ms
        </div>
        <div class="small text-muted" style="opacity: 0.5;">
            Cortex Intelligence Core © 2026
        </div>
    </div>
</div>
