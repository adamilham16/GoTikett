<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Comment;
use App\Models\Attachment;
use App\Models\TicketFreeze;
use App\Models\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\AttachmentService;
use App\Services\AutoAssignService;
use App\Services\FreezeService;
use App\Services\TicketExportService;
use App\Services\TicketFormatter;
use App\Services\TicketQueryService;

class TicketController extends Controller
{
    public function __construct(
        private AttachmentService  $attachments,
        private AutoAssignService  $autoAssign,
        private FreezeService      $freeze,
        private TicketExportService $exporter,
        private TicketFormatter    $formatter,
        private TicketQueryService $ticketQuery,
    ) {}

    private function currentUser(): User
    {
        return User::findOrFail(session('user_id'));
    }

    // ── Dashboard utama ──────────────────────────────────────────────────────
    public function index()
    {
        $user = $this->currentUser();

        $tickets = $this->ticketQuery->scopeForUser($user)
            ->with(['creator', 'assignee', 'approver', 'tasks', 'currentFreeze.requester'])
            ->withCount(['comments as it_comment_count' => fn($q) =>
                $q->join('users', 'users.id', '=', 'comments.user_id')
                  ->whereIn('users.type', ['it', 'manager'])
            ])
            ->get();

        $stats = [
            'total'   => $tickets->count(),
            'pending' => $tickets->where('approval', 'pending')->count(),
            'active'  => $tickets->where('approval', 'approved')->whereNull('closed_at')->count(),
            'closed'  => $tickets->whereNotNull('closed_at')->count(),
        ];

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
            'title'         => 'required|string|max:255',
            'category'      => 'required|string',
            'client'        => 'required|string',
            'type'          => 'required|in:incident,newproject,openrequest',
            'attachments'   => 'nullable|array|max:10',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,txt,zip',
        ]);

        $user = $this->currentUser();

        if ($user->type !== 'user') {
            return response()->json(['success' => false, 'message' => 'Hanya user/staff yang boleh membuat tiket.'], 403);
        }

        $assigneeId = $this->autoAssign->findAssigneeId($request->category, $request->client);

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

        Comment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'text'      => "Tiket dibuat oleh {$user->name}, menunggu persetujuan.",
        ]);

        $this->attachments->storeFromRequest($request, $ticket, $user);

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

        if ($user->type === 'user' && $ticket->creator_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        return response()->json($this->formatter->formatFull($ticket, $user));
    }

    // ── Approve tiket ────────────────────────────────────────────────────────
    public function approve(Request $request, string $ticketId)
    {
        $user = $this->currentUser();
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

        $request->validate(['reason' => 'required|string|min:10']);

        $ticket = Ticket::where('ticket_id', $ticketId)->firstOrFail();
        $reason = $request->input('reason');

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

    // ── Tutup tiket ───────────────────────────────────────────────────────────
    public function close(string $ticketId)
    {
        $user = $this->currentUser();
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

    // ── Hapus tiket ───────────────────────────────────────────────────────────
    public function destroy(string $ticketId)
    {
        $user = $this->currentUser();
        if (!in_array($user->type, ['it', 'it_manager'])) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $ticket = Ticket::with('attachments')->where('ticket_id', $ticketId)->firstOrFail();

        foreach ($ticket->attachments as $att) {
            Storage::disk('local')->delete($att->stored_name);
        }

        $ticket->delete();

        return response()->json(['success' => true]);
    }

    // ── Reassign tiket ────────────────────────────────────────────────────────
    public function reassign(Request $request, string $ticketId)
    {
        $user = $this->currentUser();
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

        if ($user->type === 'user' && $ticket->creator_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        $comment = Comment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'text'      => $request->text,
        ]);

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
                'user'     => $user->name,
                'initials' => $user->initials,
                'color'    => $user->color,
                'text'     => $comment->text,
                'time'     => $comment->created_at->format('d M Y H:i'),
                'own'      => true,
            ],
        ]);
    }

    // ── Download lampiran ─────────────────────────────────────────────────────
    public function downloadAttachment(int $id)
    {
        $user = $this->currentUser();
        $att  = Attachment::with('ticket')->findOrFail($id);

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

        $freeze = $this->freeze->request($ticket, $user, (int) $request->duration_days, $request->reason);

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

        $this->freeze->approve($freeze, $user);

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

        $this->freeze->reject($freeze, $user);

        return response()->json(['success' => true]);
    }

    // ── Unfreeze Manual ───────────────────────────────────────────────────────
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

        $this->freeze->unfreeze($ticket, $user);

        return response()->json(['success' => true]);
    }

    // ── Export Excel ──────────────────────────────────────────────────────────
    public function exportExcel()
    {
        $user = $this->currentUser();
        if ($user->type === 'user') abort(403);

        return $this->exporter->export();
    }
}
