<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Comment;
use App\Models\Attachment;
use App\Models\Task;
use App\Models\AutoAssignRule;
use App\Models\TicketFreeze;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Notification;

class TicketController extends Controller
{
    private function currentUser(): User
    {
        return User::findOrFail(session('user_id'));
    }

    // ── Dashboard utama ──────────────────────────────────────────────────────
    public function index()
    {
        $user    = $this->currentUser();
        $query   = Ticket::with(['creator', 'assignee', 'approver', 'tasks', 'currentFreeze.requester'])
            ->withCount(['comments as it_comment_count' => fn($q) =>
                $q->join('users', 'users.id', '=', 'comments.user_id')
                  ->whereIn('users.type', ['it', 'manager'])
            ])
            ->orderByDesc('created_at');

        // Role 'user' hanya melihat tiket milik sendiri
        if ($user->type === 'user') {
            $query->where('creator_id', $user->id);
        }
        // Role 'it' hanya melihat tiket yang di-assign ke dirinya
        if ($user->type === 'it') {
            $query->where('assignee_id', $user->id);
        }
        // Role 'manager' hanya melihat tiket dari User yang approver-nya adalah manager ini
        if ($user->type === 'manager') {
            $query->whereIn('creator_id', User::where('approver_id', $user->id)->select('id'));
        }
        // Role 'it_manager' melihat semua tiket yang assignee-nya IT SIM
        if ($user->type === 'it_manager') {
            $query->whereIn('assignee_id', User::where('type', 'it')->select('id'));
        }

        $tickets = $query->get();

        $stats = [
            'total'   => $tickets->count(),
            'pending' => $tickets->where('approval', 'pending')->count(),
            'active'  => $tickets->where('approval', 'approved')->whereNull('closed_at')->count(),
            'closed'  => $tickets->whereNotNull('closed_at')->count(),
        ];

        // Data tambahan yang dibutuhkan Blade/JS
        $clients         = \App\Models\Client::orderBy('nama')->get();
        $itTeam          = User::where('type', 'it')->get(['id', 'name', 'color']);
        $config          = \App\Models\AppConfig::allCached();
        $autoAssignRules = \App\Models\AutoAssignRule::allCached();

        return view('dashboard.index', compact('user', 'tickets', 'stats', 'clients', 'itTeam', 'config', 'autoAssignRules'));
    }

    // ── Buat tiket baru ──────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'category'    => 'required|string',
            'client'      => 'required|string',
            'type'        => 'required|in:incident,newproject,openrequest',
            'attachments'   => 'nullable|array|max:10',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,txt,zip',
        ]);

        $user = $this->currentUser();

        // Hanya role 'user' yang boleh membuat tiket
        if ($user->type !== 'user') {
            return response()->json(['success' => false, 'message' => 'Hanya user/staff yang boleh membuat tiket.'], 403);
        }

        // Auto assign — hanya cocok jika ada rule exact kategori + client
        // Jika tidak ada rule yang cocok, assignee_id = null (tidak di-assign otomatis)
        $assignee = AutoAssignRule::with('assignee')
            ->where('kategori', $request->category)
            ->where('client', $request->client)
            ->first();

        $assigneeId = $assignee?->assignee_id;

        // Generate ticket ID — pakai lock untuk hindari duplikat
        $ticketId = DB::transaction(function () {
            $last = Ticket::lockForUpdate()->orderByDesc('id')->first();
            $num  = $last ? ((int) substr($last->ticket_id, 4)) + 1 : 1;
            return 'TKT-' . str_pad($num, 3, '0', STR_PAD_LEFT);
        });

        $ticket = Ticket::create([
            'ticket_id'   => $ticketId,
            'title'       => $request->title,
            'desc'        => $request->desc,
            'type'        => $request->type,
            'category'    => $request->category,
            'client'      => $request->client,
            'approval'    => 'pending',
            'creator_id'  => $user->id,
            'assignee_id' => $assigneeId,
            'due_date'    => now()->addDays(14),
        ]);

        // Comment otomatis
        Comment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'text'      => "Tiket dibuat oleh {$user->name}, menunggu persetujuan.",
        ]);

        // Upload lampiran
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $stored = $file->store('attachments', 'local');
                Attachment::create([
                    'ticket_id'     => $ticket->id,
                    'user_id'       => $user->id,
                    'original_name' => $file->getClientOriginalName(),
                    'stored_name'   => $stored,
                    'mime_type'     => $file->getMimeType(),
                    'size'          => $file->getSize(),
                ]);
            }
        }

        // Notifikasi ke assignee IT bahwa ada tiket baru untuknya
        if ($assigneeId) {
            Notification::send(
                $assigneeId,
                'ticket_assigned',
                'Tiket Baru Di-assign',
                "Tiket {$ticket->ticket_id} ({$ticket->title}) dari {$user->name} menunggu persetujuan.",
                $ticket->ticket_id
            );
        }

        return response()->json(['success' => true, 'ticket_id' => $ticket->ticket_id]);
    }

    // ── Detail tiket ─────────────────────────────────────────────────────────
    public function show(string $ticketId)
    {
        $user   = $this->currentUser();
        $ticket = Ticket::with(['creator', 'assignee', 'approver', 'comments.user', 'attachments', 'tasks', 'currentFreeze.requester', 'activeFreeze'])
            ->where('ticket_id', $ticketId)
            ->firstOrFail();

        // Role 'user' hanya boleh lihat tiket miliknya sendiri
        if ($user->type === 'user' && $ticket->creator_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        return response()->json($this->formatTicket($ticket, $user));
    }

    // ── Approve tiket ────────────────────────────────────────────────────────
    public function approve(Request $request, string $ticketId)
    {
        $user   = $this->currentUser();
        if ($user->type !== 'manager') {
            return response()->json(['success' => false, 'message' => 'Hanya Dept Head yang bisa menyetujui tiket.'], 403);
        }

        $ticket = Ticket::where('ticket_id', $ticketId)->firstOrFail();

        $ticket->update([
            'approval'    => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        Comment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'text'      => "✅ Tiket disetujui oleh {$user->name}. IT dapat mulai merencanakan tugas.",
        ]);

        // Notifikasi ke creator
        if ($ticket->creator_id) {
            Notification::send(
                $ticket->creator_id,
                'ticket_approved',
                'Tiket Disetujui ✅',
                "Tiket {$ticket->ticket_id} ({$ticket->title}) telah disetujui oleh {$user->name}.",
                $ticket->ticket_id
            );
        }

        return response()->json(['success' => true]);
    }

    // ── Reject tiket ─────────────────────────────────────────────────────────
    public function reject(Request $request, string $ticketId)
    {
        $user = $this->currentUser();
        if ($user->type !== 'manager') {
            return response()->json(['success' => false, 'message' => 'Hanya Dept Head yang bisa menolak tiket.'], 403);
        }

        $request->validate([
            'reason' => 'required|string|min:10',
        ]);

        $ticket = Ticket::where('ticket_id', $ticketId)->firstOrFail();
        $reason = $request->input('reason');

        // Simpan notice penolakan ke cache agar User bisa membacanya (expire 24 jam)
        if ($ticket->creator_id) {
            Cache::put("rejected_ticket_{$ticket->creator_id}", [
                'ticket_id'   => $ticket->ticket_id,
                'title'       => $ticket->title,
                'reason'      => $reason,
                'rejected_by' => $user->name,
                'rejected_at' => now()->toDateTimeString(),
            ], now()->addHours(24));

            Notification::send(
                $ticket->creator_id,
                'ticket_rejected',
                'Tiket Ditolak ❌',
                "Tiket {$ticket->ticket_id} ({$ticket->title}) ditolak oleh {$user->name}. Alasan: {$reason}",
                $ticket->ticket_id
            );
        }

        $ticket->delete();

        return response()->json(['success' => true]);
    }

    // ── Notifikasi penolakan untuk User ──────────────────────────────────────
    public function getRejectionNotice()
    {
        $user = $this->currentUser();
        $data = Cache::get("rejected_ticket_{$user->id}");
        return response()->json([
            'has_notice' => (bool) $data,
            'data'       => $data,
        ]);
    }

    public function dismissRejectionNotice()
    {
        $user = $this->currentUser();
        Cache::forget("rejected_ticket_{$user->id}");
        return response()->json(['success' => true]);
    }

    // ── Tutup tiket (closed) ──────────────────────────────────────────────────
    public function close(string $ticketId)
    {
        $user   = $this->currentUser();
        if (!in_array($user->type, ['it', 'it_manager'])) {
            return response()->json(['success' => false, 'message' => 'Hanya IT SIM yang bisa menutup tiket.'], 403);
        }
        $ticket = Ticket::where('ticket_id', $ticketId)->firstOrFail();
        $ticket->update(['closed_at' => now()]);

        Comment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'text'      => "🔒 Tiket ditutup oleh {$user->name}.",
        ]);

        // Notifikasi ke creator
        if ($ticket->creator_id && $ticket->creator_id !== $user->id) {
            Notification::send(
                $ticket->creator_id,
                'ticket_closed',
                'Tiket Ditutup 🔒',
                "Tiket {$ticket->ticket_id} ({$ticket->title}) telah ditutup oleh {$user->name}.",
                $ticket->ticket_id
            );
        }

        return response()->json(['success' => true]);
    }

    // ── Hapus tiket (admin only) ──────────────────────────────────────────────
    public function destroy(string $ticketId)
    {
        $user = $this->currentUser();
        if (!in_array($user->type, ['it', 'it_manager'])) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $ticket = Ticket::with('attachments')->where('ticket_id', $ticketId)->firstOrFail();

        // Hapus file lampiran dari storage
        foreach ($ticket->attachments as $att) {
            Storage::disk('local')->delete($att->stored_name);
        }

        $ticket->delete();

        return response()->json(['success' => true]);
    }

    // ── Reassign tiket ────────────────────────────────────────────────────────
    public function reassign(Request $request, string $ticketId)
    {
        $user   = $this->currentUser();
        if ($user->type !== 'it_manager') {
            return response()->json(['success' => false, 'message' => 'Hanya Manager IT yang bisa melakukan reassign.'], 403);
        }

        $request->validate(['assignee_id' => 'required|exists:users,id']);
        $ticket   = Ticket::where('ticket_id', $ticketId)->firstOrFail();
        $assignee = User::findOrFail($request->assignee_id);

        $ticket->update(['assignee_id' => $assignee->id]);

        Comment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'text'      => "🔄 Tiket di-reassign ke {$assignee->name} oleh {$user->name}.",
        ]);

        // Notifikasi ke assignee baru
        if ($assignee->id !== $user->id) {
            Notification::send(
                $assignee->id,
                'ticket_assigned',
                'Tiket Di-assign ke Anda 🔄',
                "Tiket {$ticket->ticket_id} ({$ticket->title}) di-assign ke Anda oleh {$user->name}.",
                $ticket->ticket_id
            );
        }

        return response()->json(['success' => true]);
    }

    // ── Kirim komentar ────────────────────────────────────────────────────────
    public function comment(Request $request, string $ticketId)
    {
        $request->validate(['text' => 'required|string']);
        $user   = $this->currentUser();
        $ticket = Ticket::where('ticket_id', $ticketId)->firstOrFail();

        // Role 'user' hanya boleh komentar di tiket miliknya sendiri
        if ($user->type === 'user' && $ticket->creator_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        $comment = Comment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'text'      => $request->text,
        ]);

        // Notifikasi ke pihak lain di tiket (bukan si pengomentar)
        $recipients = collect([$ticket->creator_id, $ticket->assignee_id])
            ->filter(fn($id) => $id && $id !== $user->id)
            ->unique();

        foreach ($recipients as $recipientId) {
            Notification::send(
                $recipientId,
                'comment_added',
                'Komentar Baru 💬',
                "{$user->name} menambahkan komentar di tiket {$ticket->ticket_id}: \"{$request->text}\"",
                $ticket->ticket_id
            );
        }

        return response()->json([
            'success' => true,
            'comment' => [
                'user'       => $user->name,
                'initials'   => $user->initials,
                'color'      => $user->color,
                'text'       => $comment->text,
                'time'       => $comment->created_at->format('d M Y H:i'),
                'own'        => true,
            ],
        ]);
    }

    // ── Download lampiran ─────────────────────────────────────────────────────
    public function downloadAttachment(int $id)
    {
        $user = $this->currentUser();
        $att  = Attachment::with('ticket')->findOrFail($id);

        // Role 'user' hanya boleh download lampiran dari tiket miliknya sendiri
        if ($user->type === 'user' && $att->ticket->creator_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        return Storage::disk('local')->download($att->stored_name, $att->original_name);
    }

    // ── Request Freeze ────────────────────────────────────────────────────────
    public function requestFreeze(Request $request, string $ticketId)
    {
        $user = $this->currentUser();
        if (!$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Hanya IT SIM yang bisa mengajukan freeze.'], 403);
        }

        $request->validate([
            'duration_days' => 'required|integer|min:1|max:365',
            'reason'        => 'required|string|max:1000',
        ]);

        $ticket = Ticket::where('ticket_id', $ticketId)->firstOrFail();

        if ($ticket->approval !== 'approved' || $ticket->closed_at) {
            return response()->json(['success' => false, 'message' => 'Tiket harus dalam status Berjalan untuk di-freeze.'], 422);
        }
        if ($ticket->freeze_status) {
            return response()->json(['success' => false, 'message' => 'Tiket sudah dalam proses freeze atau menunggu persetujuan.'], 422);
        }

        $freeze = TicketFreeze::create([
            'ticket_id'     => $ticket->id,
            'requested_by'  => $user->id,
            'duration_days' => $request->duration_days,
            'reason'        => $request->reason,
            'status'        => 'pending_approval',
        ]);

        $ticket->update(['freeze_status' => 'pending_approval']);

        Comment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'text'      => "⏸ Request Pending/Freeze {$request->duration_days} hari oleh {$user->name}.\nAlasan: {$request->reason}\nMenunggu persetujuan manager.",
        ]);

        // Notifikasi ke semua manager (bulk insert)
        $managerIds = User::where('type', 'manager')->pluck('id')->all();
        Notification::sendMany(
            $managerIds,
            'freeze_requested',
            'Request Freeze Tiket ⏸',
            "{$user->name} mengajukan freeze {$request->duration_days} hari untuk tiket {$ticket->ticket_id} ({$ticket->title}). Alasan: {$request->reason}",
            $ticket->ticket_id
        );

        return response()->json(['success' => true, 'freeze_id' => $freeze->id]);
    }

    // ── Approve Freeze ────────────────────────────────────────────────────────
    public function approveFreeze(int $freezeId)
    {
        $user = $this->currentUser();
        if ($user->type !== 'manager') {
            return response()->json(['success' => false, 'message' => 'Hanya Manager yang bisa menyetujui freeze.'], 403);
        }

        $freeze = TicketFreeze::with('ticket')->findOrFail($freezeId);

        if ($freeze->status !== 'pending_approval') {
            return response()->json(['success' => false, 'message' => 'Request freeze ini sudah diproses.'], 422);
        }

        $now         = now();
        $freezeEnds  = $now->copy()->addDays($freeze->duration_days);

        $freeze->update([
            'status'           => 'approved',
            'approved_by'      => $user->id,
            'approved_at'      => $now,
            'freeze_starts_at' => $now,
            'freeze_ends_at'   => $freezeEnds,
        ]);

        $ticket = $freeze->ticket;
        // Perpanjang due_date sesuai durasi freeze yang disetujui
        $ticket->update([
            'freeze_status' => 'active',
            'due_date'      => $ticket->due_date->addDays($freeze->duration_days),
        ]);

        Comment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'text'      => "✅ Request freeze disetujui oleh {$user->name}.\nTiket di-freeze hingga {$freezeEnds->format('d M Y H:i')}. SLA dihentikan sementara.",
        ]);

        $recipients = collect([$freeze->requested_by, $ticket->creator_id])->filter()->unique();
        foreach ($recipients as $recipientId) {
            Notification::send(
                $recipientId,
                'freeze_approved',
                'Freeze Disetujui ✅',
                "Freeze tiket {$ticket->ticket_id} ({$ticket->title}) disetujui oleh {$user->name}. Freeze aktif hingga {$freezeEnds->format('d M Y')}.",
                $ticket->ticket_id
            );
        }

        return response()->json(['success' => true]);
    }

    // ── Reject Freeze ─────────────────────────────────────────────────────────
    public function rejectFreeze(int $freezeId)
    {
        $user = $this->currentUser();
        if ($user->type !== 'manager') {
            return response()->json(['success' => false, 'message' => 'Hanya Manager yang bisa menolak freeze.'], 403);
        }

        $freeze = TicketFreeze::with('ticket')->findOrFail($freezeId);

        if ($freeze->status !== 'pending_approval') {
            return response()->json(['success' => false, 'message' => 'Request freeze ini sudah diproses.'], 422);
        }

        $freeze->update([
            'status'      => 'rejected',
            'rejected_by' => $user->id,
            'rejected_at' => now(),
        ]);

        $ticket = $freeze->ticket;
        $ticket->update(['freeze_status' => null]);

        Comment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'text'      => "❌ Request freeze ditolak oleh {$user->name}. SLA tetap berjalan normal.",
        ]);

        $recipients = collect([$freeze->requested_by, $ticket->creator_id])->filter()->unique();
        foreach ($recipients as $recipientId) {
            Notification::send(
                $recipientId,
                'freeze_rejected',
                'Freeze Ditolak ❌',
                "Request freeze tiket {$ticket->ticket_id} ({$ticket->title}) ditolak oleh {$user->name}. SLA tetap berjalan.",
                $ticket->ticket_id
            );
        }

        return response()->json(['success' => true]);
    }

    // ── Unfreeze Manual (IT) ──────────────────────────────────────────────────
    public function unfreeze(string $ticketId)
    {
        $user = $this->currentUser();
        if (!$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Hanya IT SIM yang bisa mengaktifkan kembali tiket.'], 403);
        }

        $ticket = Ticket::where('ticket_id', $ticketId)->firstOrFail();

        if ($ticket->freeze_status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Tiket tidak sedang dalam status freeze.'], 422);
        }

        $activeFreeze = TicketFreeze::where('ticket_id', $ticket->id)
            ->where('status', 'approved')
            ->latest()
            ->firstOrFail();

        $pausedSeconds    = (int) round(now()->timestamp - $activeFreeze->freeze_starts_at->timestamp);
        $requestedSeconds = (int) ($activeFreeze->duration_days * 86400);
        // Kembalikan hari yang tidak terpakai jika di-unfreeze lebih awal dari durasi yang disetujui
        $unusedSeconds    = max(0, $requestedSeconds - $pausedSeconds);

        $activeFreeze->update(['status' => 'completed']);
        $ticket->update([
            'freeze_status'         => null,
            'freeze_paused_seconds' => $ticket->freeze_paused_seconds + $pausedSeconds,
            'due_date'              => $unusedSeconds > 0
                ? $ticket->due_date->subSeconds($unusedSeconds)
                : $ticket->due_date,
        ]);

        Comment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'text'      => "▶ Tiket diaktifkan kembali oleh {$user->name}. SLA dilanjutkan (total dijeda: " . round($pausedSeconds / 86400, 1) . " hari).",
        ]);

        $recipients = collect([$activeFreeze->requested_by, $ticket->creator_id])
            ->filter(fn($id) => $id && $id !== $user->id)
            ->unique();
        foreach ($recipients as $recipientId) {
            Notification::send(
                $recipientId,
                'freeze_ended',
                'Tiket Aktif Kembali ▶',
                "Tiket {$ticket->ticket_id} ({$ticket->title}) telah diaktifkan kembali oleh {$user->name}. SLA dilanjutkan.",
                $ticket->ticket_id
            );
        }

        return response()->json(['success' => true]);
    }

    // ── Export Excel ──────────────────────────────────────────────────────────
    public function exportExcel()
    {
        $user = $this->currentUser();
        if ($user->type === 'user') abort(403);

        // Summary: pakai query agregat langsung ke DB, tidak load semua model
        $total    = Ticket::count();
        $antrean  = Ticket::where('approval', 'pending')->count();
        $berjalan = Ticket::where('approval', 'approved')->whereNull('closed_at')->count();
        $selesai  = Ticket::whereNotNull('closed_at')->count();

        $statusSummary = collect([
            ['State' => 'Antrean',  'Jumlah' => $antrean],
            ['State' => 'Berjalan', 'Jumlah' => $berjalan],
            ['State' => 'Selesai',  'Jumlah' => $selesai],
            ['State' => 'TOTAL',    'Jumlah' => $total],
        ]);

        $assigneeSummary = Ticket::with('assignee')
            ->select('assignee_id',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN approval="approved" AND closed_at IS NULL THEN 1 ELSE 0 END) as on_progress'),
                DB::raw('SUM(CASE WHEN closed_at IS NOT NULL THEN 1 ELSE 0 END) as selesai')
            )
            ->groupBy('assignee_id')
            ->get()
            ->map(fn($r) => [
                'Assignee'    => $r->assignee?->name ?? '—',
                'Total'       => $r->total,
                'On Progress' => $r->on_progress,
                'Selesai'     => $r->selesai,
            ])->values();

        $headers = ['ID','Judul','Deskripsi','Tipe','Status','Approval','Kategori','Client',
                    'Assignee','Creator','Disetujui Oleh','Tanggal Buat','Due Date',
                    'Tanggal Close','Lead Time','SLA Status','Progress (%)'];

        $filename    = 'GoTiket_Export_' . now()->format('Y-m-d') . '.xlsx';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // Sheet 1: tulis per-chunk agar hemat memori
        $sheet1 = $spreadsheet->getActiveSheet()->setTitle('Semua Tiket');
        $sheet1->fromArray([$headers], null, 'A1');
        $sheet1->getStyle('A1:Q1')->getFont()->setBold(true);

        $rowIdx = 2;
        Ticket::with(['creator', 'assignee', 'approver', 'tasks'])
            ->orderByDesc('created_at')
            ->chunk(200, function ($chunk) use ($sheet1, &$rowIdx) {
                foreach ($chunk as $t) {
                    $sla = $t->sla;
                    $closed = !!$t->closed_at;
                    $sheet1->fromArray([[
                        $t->ticket_id,
                        $t->title,
                        $t->desc ?? '—',
                        match($t->type) { 'incident' => 'Incident', 'newproject' => 'New Project', default => 'Open Request' },
                        $closed ? 'Selesai' : ($t->approval === 'approved' ? 'Berjalan' : 'Antrean'),
                        ucfirst($t->approval),
                        $t->category ?? '—',
                        $t->client ?? '—',
                        $t->assignee?->name ?? '—',
                        $t->creator?->name ?? '—',
                        $t->approver?->name ?? '—',
                        $t->created_at->format('d M Y'),
                        $t->due_date?->format('d M Y') ?? '—',
                        $t->closed_at?->format('d M Y') ?? '—',
                        $t->lead_time,
                        $sla['label'],
                        $t->progress,
                    ]], null, 'A' . $rowIdx);
                    $rowIdx++;
                }
            });

        // Sheet 2: Ringkasan Status
        $sheet2 = $spreadsheet->createSheet()->setTitle('Ringkasan Status');
        $sheet2->fromArray([['State', 'Jumlah']], null, 'A1');
        $sheet2->fromArray($statusSummary->map(fn($r) => array_values($r))->toArray(), null, 'A2');
        $sheet2->getStyle('A1:B1')->getFont()->setBold(true);

        // Sheet 3: Ringkasan Assignee
        $sheet3 = $spreadsheet->createSheet()->setTitle('Ringkasan Assignee');
        if ($assigneeSummary->isNotEmpty()) {
            $sheet3->fromArray([array_keys($assigneeSummary->first())], null, 'A1');
            $sheet3->fromArray($assigneeSummary->map(fn($r) => array_values($r))->toArray(), null, 'A2');
            $sheet3->getStyle('A1:D1')->getFont()->setBold(true);
        }

        $spreadsheet->setActiveSheetIndex(0);
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ── Helper: format tiket ke JSON ──────────────────────────────────────────
    private function formatTicket(Ticket $ticket, User $user): array
    {
        $sla    = $ticket->sla;
        $freeze = $ticket->currentFreeze;
        return [
            'id'          => $ticket->ticket_id,
            'title'       => $ticket->title,
            'desc'        => $ticket->desc,
            'type'        => $ticket->type,
            'approval'    => $ticket->approval,
            'category'    => $ticket->category,
            'client'      => $ticket->client,
            'creator'     => ['name' => $ticket->creator?->name, 'initials' => $ticket->creator?->initials, 'color' => $ticket->creator?->color],
            'assignee'    => ['id' => $ticket->assignee_id, 'name' => $ticket->assignee?->name, 'initials' => $ticket->assignee?->initials, 'color' => $ticket->assignee?->color],
            'approved_by' => $ticket->approver?->name,
            'approved_at' => $ticket->approved_at?->format('d M Y H:i'),
            'created_at'  => $ticket->created_at->format('d M Y H:i'),
            'due_date'    => $ticket->due_date?->format('d M Y'),
            'closed_at'   => $ticket->closed_at?->format('d M Y H:i'),
            'lead_time'   => $ticket->lead_time,
            'progress'    => $ticket->progress,
            'sla'         => $sla,
            'can_delete'  => $user->isAdmin(),
            'freeze_status'    => $ticket->freeze_status,
            'freeze_id'        => $freeze?->id,
            'freeze_duration'  => $freeze?->duration_days,
            'freeze_reason'    => $freeze?->reason,
            'freeze_requester' => $freeze?->requester?->name,
            'freeze_ends_at'   => $freeze?->freeze_ends_at?->format('d M Y H:i'),
            'tasks'       => $ticket->tasks->map(fn($t) => [
                'id'       => $t->id,
                'title'    => $t->title,
                'status'   => $t->status,
                'due_date' => $t->due_date?->format('d M Y'),
                'notes'    => $t->notes,
            ]),
            'comments'    => $ticket->comments->map(fn($c) => [
                'user'     => $c->user?->name,
                'initials' => $c->user?->initials,
                'color'    => $c->user?->color,
                'text'     => $c->text,
                'time'     => $c->created_at->format('d M Y H:i'),
                'own'      => $c->user_id === $user->id,
            ]),
            'attachments' => $ticket->attachments->map(fn($a) => [
                'id'    => $a->id,
                'name'  => $a->original_name,
                'size'  => $a->formatted_size,
                'icon'  => $a->icon,
                'url'   => route('attachments.download', $a->id),
            ]),
        ];
    }
}
