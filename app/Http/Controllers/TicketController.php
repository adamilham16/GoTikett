<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Comment;
use App\Models\Attachment;
use App\Models\Task;
use App\Models\AutoAssignRule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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
        $tickets = Ticket::with(['creator', 'assignee', 'approver', 'tasks'])
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'total'   => $tickets->count(),
            'pending' => $tickets->where('approval', 'pending')->count(),
            'dev'     => $tickets->where('status', 'development')->where('approval', 'approved')->count(),
            'live'    => $tickets->where('status', 'golive')->count(),
        ];

        // Data tambahan yang dibutuhkan Blade/JS
        $clients         = \App\Models\Client::orderBy('nama')->get();
        $itTeam          = User::where('type', 'it')->get(['id', 'name', 'color']);
        $config          = \App\Models\AppConfig::all()->pluck('value', 'key');
        $autoAssignRules = \App\Models\AutoAssignRule::with('assignee')->get();

        return view('dashboard.index', compact('user', 'tickets', 'stats', 'clients', 'itTeam', 'config', 'autoAssignRules'));
    }

    // ── Buat tiket baru ──────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'category' => 'required|string',
            'client'   => 'required|string',
            'type'     => 'required|in:incident,newproject,openrequest',
        ]);

        $user = $this->currentUser();

        // Auto assign
        $assignee = AutoAssignRule::with('assignee')
            ->where('kategori', $request->category)
            ->where('client', $request->client)
            ->first()
            ?? AutoAssignRule::with('assignee')->where('kategori', $request->category)->first();

        $assigneeId = $assignee?->assignee_id
            ?? User::where('role', 'ALL')->where('type', 'it')->first()?->id;

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
            'status'      => 'userreq',
            'approval'    => 'pending',
            'creator_id'  => $user->id,
            'assignee_id' => $assigneeId,
            'due_date'    => now()->addDays(14),
            'stage_log'   => [],
            'stage_due'   => [],
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

        return response()->json(['success' => true, 'ticket_id' => $ticket->ticket_id]);
    }

    // ── Detail tiket ─────────────────────────────────────────────────────────
    public function show(string $ticketId)
    {
        $user   = $this->currentUser();
        $ticket = Ticket::with(['creator', 'assignee', 'approver', 'comments.user', 'attachments', 'tasks'])
            ->where('ticket_id', $ticketId)
            ->firstOrFail();

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
        $stageLog = $ticket->stage_log ?? [];
        $stageLog['userreq'] = now()->toISOString();

        $ticket->update([
            'approval'    => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'stage_log'   => $stageLog,
        ]);

        Comment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'text'      => "✅ Tiket disetujui oleh {$user->name}. Silakan mulai tahap Permintaan Pengguna.",
        ]);

        return response()->json(['success' => true]);
    }

    // ── Reject tiket ─────────────────────────────────────────────────────────
    public function reject(string $ticketId)
    {
        $user = $this->currentUser();
        if ($user->type !== 'manager') {
            return response()->json(['success' => false, 'message' => 'Hanya Dept Head yang bisa menolak tiket.'], 403);
        }

        $ticket = Ticket::where('ticket_id', $ticketId)->firstOrFail();
        $ticket->delete();

        return response()->json(['success' => true]);
    }

    // ── Simpan due date per stage ─────────────────────────────────────────────
    public function saveStageDue(Request $request, string $ticketId)
    {
        $ticket   = Ticket::where('ticket_id', $ticketId)->firstOrFail();
        $user     = $this->currentUser();
        $stageDue = [];

        $stages = array_filter(Ticket::STAGES, fn($s) => !in_array($s, ['userreq', 'golive']));
        foreach ($stages as $stage) {
            $val = $request->input($stage);
            if ($val) $stageDue[$stage] = $val;
        }

        $ticket->update(['stage_due' => $stageDue]);

        $filled = count($stageDue);
        $total  = count($stages);
        Comment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'text'      => "📅 Due date per stage telah diset oleh {$user->name} ({$filled}/{$total} stage diisi).",
        ]);

        return response()->json(['success' => true]);
    }

    // ── Advance stage ────────────────────────────────────────────────────────
    public function advance(string $ticketId)
    {
        $user   = $this->currentUser();
        if (!$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Hanya IT SIM yang bisa advance stage.'], 403);
        }

        $ticket = Ticket::where('ticket_id', $ticketId)->firstOrFail();
        $stages = Ticket::STAGES;
        $idx    = array_search($ticket->status, $stages);

        if ($idx === false || $idx >= count($stages) - 1) {
            return response()->json(['success' => false, 'message' => 'Sudah di stage terakhir.']);
        }

        $nextStage = $stages[$idx + 1];
        $stageLog  = $ticket->stage_log ?? [];
        $stageLog[$nextStage] = now()->toISOString();

        $updates = ['status' => $nextStage, 'stage_log' => $stageLog];
        if ($nextStage === 'golive') {
            $updates['closed_at'] = now();
        }

        $ticket->update($updates);

        Comment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'text'      => "⏩ Stage dimajukan ke **" . Ticket::STAGE_LABELS[$nextStage] . "** oleh {$user->name}.",
        ]);

        return response()->json(['success' => true, 'next_stage' => $nextStage]);
    }

    // ── Tutup tiket (closed) ──────────────────────────────────────────────────
    public function close(string $ticketId)
    {
        $user   = $this->currentUser();
        $ticket = Ticket::where('ticket_id', $ticketId)->firstOrFail();
        $ticket->update(['closed_at' => now()]);

        Comment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'text'      => "🔒 Tiket ditutup oleh {$user->name}.",
        ]);

        return response()->json(['success' => true]);
    }

    // ── Hapus tiket (admin only) ──────────────────────────────────────────────
    public function destroy(string $ticketId)
    {
        $user = $this->currentUser();
        if (!$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $ticket = Ticket::where('ticket_id', $ticketId)->firstOrFail();

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
        if (!$user->isAdmin()) {
            return response()->json(['success' => false], 403);
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

        return response()->json(['success' => true]);
    }

    // ── Kirim komentar ────────────────────────────────────────────────────────
    public function comment(Request $request, string $ticketId)
    {
        $request->validate(['text' => 'required|string']);
        $user   = $this->currentUser();
        $ticket = Ticket::where('ticket_id', $ticketId)->firstOrFail();

        $comment = Comment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'text'      => $request->text,
        ]);

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
        $att = Attachment::findOrFail($id);
        return Storage::disk('local')->download($att->stored_name, $att->original_name);
    }

    // ── Export Excel ──────────────────────────────────────────────────────────
    public function exportExcel()
    {
        $user = $this->currentUser();
        if ($user->type === 'user') abort(403);

        $tickets = Ticket::with(['creator', 'assignee'])->orderByDesc('created_at')->get();

        // Build data rows
        $rows = $tickets->map(function ($t) {
            $sla = $t->sla;
            return [
                'ID'              => $t->ticket_id,
                'Judul'           => $t->title,
                'Deskripsi'       => $t->desc ?? '—',
                'Tipe'            => match($t->type) {
                    'incident'    => 'Incident',
                    'newproject'  => 'New Project',
                    default       => 'Open Request',
                },
                'Status'          => Ticket::STAGE_LABELS[$t->status] ?? $t->status,
                'Approval'        => ucfirst($t->approval),
                'Kategori'        => $t->category ?? '—',
                'Client'          => $t->client ?? '—',
                'Assignee'        => $t->assignee?->name ?? '—',
                'Creator'         => $t->creator?->name ?? '—',
                'Disetujui Oleh'  => $t->approver?->name ?? '—',
                'Tanggal Buat'    => $t->created_at->format('d M Y'),
                'Due Date'        => $t->due_date?->format('d M Y') ?? '—',
                'Tanggal Close'   => $t->closed_at?->format('d M Y') ?? '—',
                'Lead Time'       => $t->lead_time,
                'SLA Status'      => $sla['label'],
                'Progress (%)'    => $t->progress,
            ];
        });

        // Status summary
        $statusSummary = collect(Ticket::STAGES)->map(fn($s) => [
            'Stage'  => Ticket::STAGE_LABELS[$s],
            'Jumlah' => $tickets->where('status', $s)->count(),
        ]);
        $statusSummary->push(['Stage' => 'TOTAL', 'Jumlah' => $tickets->count()]);

        // Assignee summary
        $assigneeSummary = $tickets->groupBy('assignee_id')->map(function ($group) {
            $assignee = $group->first()->assignee;
            return [
                'Assignee'    => $assignee?->name ?? '—',
                'Total'       => $group->count(),
                'On Progress' => $group->filter(fn($t) => $t->approval === 'approved' && $t->status !== 'golive' && !$t->closed_at)->count(),
                'GO Live'     => $group->where('status', 'golive')->count(),
                'Closed'      => $group->whereNotNull('closed_at')->count(),
            ];
        })->values();

        $filename = 'GoTiket_Export_' . now()->format('Y-m-d') . '.xlsx';

        // Gunakan PhpSpreadsheet langsung (tanpa package Maatwebsite)
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // Sheet 1: Semua Tiket
        $sheet1 = $spreadsheet->getActiveSheet()->setTitle('Semua Tiket');
        if ($rows->isNotEmpty()) {
            $headers = array_keys($rows->first());
            $sheet1->fromArray([$headers], null, 'A1');
            $sheet1->fromArray($rows->map(fn($r) => array_values($r))->toArray(), null, 'A2');
            // Bold header
            $sheet1->getStyle('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers)) . '1')
                ->getFont()->setBold(true);
        }

        // Sheet 2: Ringkasan Status
        $sheet2 = $spreadsheet->createSheet()->setTitle('Ringkasan Status');
        $sheet2->fromArray([['Stage', 'Jumlah']], null, 'A1');
        $sheet2->fromArray($statusSummary->map(fn($r) => array_values($r))->toArray(), null, 'A2');
        $sheet2->getStyle('A1:B1')->getFont()->setBold(true);

        // Sheet 3: Ringkasan Assignee
        $sheet3 = $spreadsheet->createSheet()->setTitle('Ringkasan Assignee');
        if ($assigneeSummary->isNotEmpty()) {
            $sheet3->fromArray([array_keys($assigneeSummary->first())], null, 'A1');
            $sheet3->fromArray($assigneeSummary->map(fn($r) => array_values($r))->toArray(), null, 'A2');
            $sheet3->getStyle('A1:E1')->getFont()->setBold(true);
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
        $sla = $ticket->sla;
        return [
            'id'          => $ticket->ticket_id,
            'title'       => $ticket->title,
            'desc'        => $ticket->desc,
            'type'        => $ticket->type,
            'status'      => $ticket->status,
            'status_label'=> Ticket::STAGE_LABELS[$ticket->status] ?? $ticket->status,
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
            'stage_log'   => $ticket->stage_log ?? [],
            'stage_due'   => $ticket->stage_due ?? [],
            'next_stage'  => $ticket->next_stage,
            'can_advance' => $user->isAdmin() && $ticket->approval === 'approved' && $ticket->status !== 'golive',
            'can_delete'  => $user->isAdmin(),
            'tasks'       => $ticket->tasks->map(fn($t) => ['id' => $t->id, 'title' => $t->title, 'status' => $t->status]),
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
