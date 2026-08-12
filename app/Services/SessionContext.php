<?php

namespace App\Services;

use App\Models\SoSession;
use App\Models\User;
use Illuminate\Support\Collection;

class SessionContext
{
    /**
     * Daftar sesi aktif yang terjangkau oleh user:
     * - admin/superadmin : semua sesi aktif
     * - team_leader      : sesi aktif yang memiliki tim dipimpinnya
     * - petugas_so       : sesi aktif yang timnya memuat user
     */
    public static function activeSessionsFor(User $user): Collection
    {
        $query = SoSession::where('status', 'active');

        if ($user->isAdminOrSuperadmin()) {
            return $query->orderBy('created_at', 'desc')->get();
        }

        if ($user->isTeamLeader()) {
            return $query->whereHas('teams', fn($q) => $q->where('team_leader_id', $user->id))
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return $query->whereHas('teams.members', fn($q) => $q->where('user_id', $user->id))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Sesi aktif terpilih untuk user.
     * Prioritas: pilihan tersimpan → satu-satunya sesi aktif → null (perlu picker).
     */
    public static function resolve(User $user): ?SoSession
    {
        $sessions = static::activeSessionsFor($user);

        if ($sessions->isEmpty()) {
            return null;
        }

        $selectedId = (int) session()->get('selected_session_id', 0);

        if ($selectedId > 0) {
            $selected = $sessions->firstWhere('id', $selectedId);
            if ($selected) {
                return $selected;
            }
        }

        if ($sessions->count() === 1) {
            return $sessions->first();
        }

        return null;
    }

    public static function set(int $sessionId): void
    {
        session()->put('selected_session_id', $sessionId);
    }

    public static function clear(): void
    {
        session()->forget('selected_session_id');
    }

    /**
     * Sesi terpilih untuk tampilan admin (dashboard/monitoring).
     * Prioritas: ?session_id= → sesi active/completed terbaru.
     */
    public static function adminSelected(?int $sessionId = null): ?SoSession
    {
        if ($sessionId) {
            return SoSession::find($sessionId);
        }

        return SoSession::whereIn('status', ['active', 'completed'])
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Semua sesi untuk dropdown admin (active + completed, terbaru dulu).
     */
    public static function adminOptions(): Collection
    {
        return SoSession::whereIn('status', ['active', 'completed'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
