<x-layouts.app title="Audit Log Detail" header="Detail Audit Log">
    <div class="max-w-3xl">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Waktu</label>
                    <p class="text-sm text-gray-900">{{ $auditLog->created_at->format('d M Y H:i:s') }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">User</label>
                    <p class="text-sm text-gray-900">{{ $auditLog->user->full_name ?? $auditLog->user->name ?? 'System' }}</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Aksi</label>
                    <p class="text-sm"><span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-xs">{{ $auditLog->action }}</span></p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Model</label>
                    <p class="text-sm text-gray-900">{{ class_basename($auditLog->model_type) }} #{{ $auditLog->model_id }}</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">IP Address</label>
                    <p class="text-sm font-mono text-gray-900">{{ $auditLog->ip_address ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">User Agent</label>
                    <p class="text-xs text-gray-600 truncate">{{ $auditLog->user_agent ?? '-' }}</p>
                </div>
            </div>

            @if($auditLog->old_values)
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nilai Lama</label>
                <pre class="bg-red-50 border border-red-200 rounded-lg p-3 text-xs text-red-800 overflow-x-auto">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT) }}</pre>
            </div>
            @endif

            @if($auditLog->new_values)
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nilai Baru</label>
                <pre class="bg-green-50 border border-green-200 rounded-lg p-3 text-xs text-green-800 overflow-x-auto">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT) }}</pre>
            </div>
            @endif
        </div>
        <a href="{{ route('superadmin.audit-logs.index') }}" class="inline-block mt-4 text-blue-600 hover:text-blue-800 text-sm">&larr; Kembali</a>
    </div>
</x-layouts.app>
