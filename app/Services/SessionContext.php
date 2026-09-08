<?php

namespace App\Services;

use App\Models\SoSession;
use App\Models\User;
use App\Models\Team;
use App\Models\TeamMember;
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
     * Prioritas: pilihan tersimpan → sesi terbaru (langsung bisa dipakai) → null hanya jika tidak ada sesi.
     * Semua button navigasi langsung bisa diklik setelah login tanpa harus ke picker dulu.
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

        // Langsung pakai sesi terbaru agar SO Input & Verifikasi bisa diklik langsung setelah login
        // Picker Ganti Sesi tetap tersedia untuk pindah sesi
        $first = $sessions->first();
        // simpan otomatis agar konsisten antar request
        session()->put('selected_session_id', $first->id);
        return $first;
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
     * Semua sesi (active, completed, closed) yang pernah diikuti user — untuk riwayat.
     * TL: sesi yang pernah dipimpin, Petugas: sesi dimana jadi anggota.
     * Diurut terbaru, read-only untuk TL/petugas tetap blind count.
     */
    public static function allSessionsFor(User $user): Collection
    {
        $query = SoSession::orderBy('created_at', 'desc');

        if ($user->isAdminOrSuperadmin()) {
            return $query->get();
        }

        if ($user->isTeamLeader()) {
            // TL bisa jadi leader di sesi A dan anggota di sesi B → gabung keduanya
            $ledIds = Team::where('team_leader_id', $user->id)->pluck('session_id');
            $memberIds = TeamMember::where('user_id', $user->id)
                ->whereHas('team')
                ->with('team')
                ->get()
                ->pluck('team.session_id');
            $ids = $ledIds->merge($memberIds)->unique()->filter();
            if ($ids->isEmpty()) return collect();
            return SoSession::whereIn('id', $ids)->orderBy('created_at', 'desc')->get();
        }

        $memberSessionIds = TeamMember::where('user_id', $user->id)
            ->whereHas('team')
            ->with('team')
            ->get()
            ->pluck('team.session_id')
            ->unique()
            ->filter();
        if ($memberSessionIds->isEmpty()) return collect();
        return SoSession::whereIn('id', $memberSessionIds)->orderBy('created_at', 'desc')->get();
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
