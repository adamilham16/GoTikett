<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Client;
use App\Models\AutoAssignRule;
use App\Models\AppConfig;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Comment;
use App\Models\LoginLog;
use App\Models\PasswordResetToken;
use App\Models\Notification;

class AdminController extends Controller
{
    // ── Clients ───────────────────────────────────────────────────────────────
    public function getClients()
    {
        return response()->json(Client::orderBy('nama')->get());
    }

    public function storeClient(Request $request)
    {
        $request->validate(['nama' => 'required|string|unique:clients,nama']);
        $client = Client::create(['nama' => $request->nama]);
        return response()->json(['success' => true, 'client' => $client]);
    }

    public function destroyClient(int $id)
    {
        Client::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    // ── Auto Assign Rules ─────────────────────────────────────────────────────
    public function getAutoAssign()
    {
        $rules = AutoAssignRule::allCached()->map(fn($r) => [
            'id'       => $r->id,
            'kategori' => $r->kategori,
            'client'   => $r->client,
            'assignee' => $r->assignee?->name,
        ]);
        return response()->json($rules);
    }

    public function storeAutoAssign(Request $request)
    {
        $request->validate([
            'kategori'    => 'required|string',
            'client'      => 'required|string',
            'assignee_id' => 'required|exists:users,id',
        ]);

        $rule = AutoAssignRule::create($request->only(['kategori', 'client', 'assignee_id']));
        AutoAssignRule::clearCache();
        return response()->json(['success' => true, 'id' => $rule->id]);
    }

    public function destroyAutoAssign(int $id)
    {
        AutoAssignRule::findOrFail($id)->delete();
        AutoAssignRule::clearCache();
        return response()->json(['success' => true]);
    }

    // ── App Config ────────────────────────────────────────────────────────────
    public function getConfig()
    {
        $config = AppConfig::allCached();
        return response()->json($config);
    }

    public function saveConfig(Request $request)
    {
        $allowed = ['appName', 'appSubtitle', 'appIcon', 'bgType', 'bgColor', 'bgGradient', 'bgImage'];
        foreach ($allowed as $key) {
            if ($request->has($key)) {
                AppConfig::set($key, $request->input($key));
            }
        }
        return response()->json(['success' => true]);
    }

    public function resetConfig()
    {
        $actor = request()->attributes->get('auth_user') ?? User::find(session('user_id'));
        Log::warning('AppConfig reset by user', ['user_id' => $actor?->id, 'name' => $actor?->name]);
        AppConfig::truncate();
        Cache::forget('app_config_all');
        return response()->json(['success' => true]);
    }

    // ── Tasks (checklist dalam tiket) ─────────────────────────────────────────
    public function storeTask(Request $request, string $ticketId)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'due_date' => 'nullable|date',
            'notes'    => 'nullable|string',
        ]);
        $ticket = Ticket::where('ticket_id', $ticketId)->firstOrFail();
        $task   = Task::create([
            'ticket_id' => $ticket->id,
            'title'     => $request->title,
            'status'    => 'Todo',
            'due_date'  => $request->due_date,
            'notes'     => $request->notes,
        ]);
        return response()->json(['success' => true, 'id' => $task->id]);
    }

    public function updateTask(Request $request, int $taskId)
    {
        $user = request()->attributes->get('auth_user') ?? User::findOrFail(session('user_id'));
        if (!in_array($user->type, ['it', 'it_manager'])) {
            return response()->json(['success' => false, 'message' => 'Hanya IT SIM yang boleh mengubah task.'], 403);
        }
        $request->validate([
            'title'    => 'sometimes|string|max:255',
            'due_date' => 'nullable|date',
            'notes'    => 'nullable|string',
        ]);
        $task = Task::findOrFail($taskId);
        $task->update($request->only(['title', 'due_date', 'notes']));
        return response()->json(['success' => true]);
    }

    public function toggleTask(int $taskId)
    {
        $user   = request()->attributes->get('auth_user') ?? User::findOrFail(session('user_id'));
        $task   = Task::findOrFail($taskId);
        $ticket = Ticket::findOrFail($task->ticket_id);

        if (!in_array($user->type, ['it', 'it_manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya IT SIM yang boleh mengubah task.'
            ], 403);
        }

        $newStatus = $task->status === 'Todo' ? 'Done' : 'Todo';
        $task->update(['status' => $newStatus]);

        // Auto-close: jika semua task Done, tutup tiket otomatis
        if ($newStatus === 'Done') {
            $allTasks = Task::where('ticket_id', $task->ticket_id)->get();
            if ($allTasks->count() > 0 && $allTasks->every(fn($t) => $t->status === 'Done')) {
                $ticket->update(['closed_at' => now()]);
                Comment::create([
                    'ticket_id' => $ticket->id,
                    'user_id'   => $user->id,
                    'text'      => "✅ Semua tugas selesai. Tiket ditutup otomatis oleh sistem.",
                ]);

                // Notifikasi ke creator bahwa tiket auto-close
                if ($ticket->creator_id && $ticket->creator_id !== $user->id) {
                    Notification::send(
                        $ticket->creator_id,
                        'ticket_closed',
                        'Tiket Selesai ✅',
                        "Tiket {$ticket->ticket_id} \"{$ticket->title}\" telah selesai — semua tugas sudah diselesaikan.",
                        $ticket->ticket_id
                    );
                }
            }
        }

        return response()->json([
            'success' => true,
            'status'  => $newStatus,
        ]);
    }

    public function destroyTask(int $taskId)
    {
        Task::findOrFail($taskId)->delete();
        return response()->json(['success' => true]);
    }

    // ── Helper: data awal untuk JS (dipanggil saat halaman load) ─────────────
    public function appData()
    {
        $user = request()->attributes->get('auth_user') ?? User::findOrFail(session('user_id'));

        $limit = min((int) request('limit', 500), 1000);

        $ticketQuery = Ticket::with(['creator', 'assignee', 'tasks', 'currentFreeze.requester', 'activeFreeze'])->orderByDesc('created_at');
        if ($user->type === 'user') {
            $ticketQuery->where('creator_id', $user->id);
        }
        // Role 'it' hanya melihat tiket yang di-assign ke dirinya
        if ($user->type === 'it') {
            $ticketQuery->where('assignee_id', $user->id);
        }
        // Role 'manager' hanya melihat tiket dari User yang approver-nya adalah manager ini
        if ($user->type === 'manager') {
            $ticketQuery->whereIn('creator_id', User::where('approver_id', $user->id)->select('id'));
        }
        // Role 'it_manager' melihat semua tiket yang assignee-nya IT SIM
        if ($user->type === 'it_manager') {
            $ticketQuery->whereIn('assignee_id', User::where('type', 'it')->select('id'));
        }
        $totalCount = $ticketQuery->count();
        $tickets    = $ticketQuery->limit($limit)->get();

        $clients = Client::orderBy('nama')->get(['id', 'nama']);
        $itTeam  = User::where('type', 'it')->get(['id', 'name', 'color']);
        $config  = AppConfig::allCached();
        $autoAssignRules = AutoAssignRule::allCached();

        return response()->json([
            'user'    => [
                'id'       => $user->id,
                'name'     => $user->name,
                'type'     => $user->type,
                'role'     => $user->role,
                'dept'     => $user->dept,
                'color'    => $user->color,
                'initials' => $user->initials,
                'approver' => $user->approver?->name,
            ],
            'tickets_total'   => $totalCount,
            'tickets'         => $tickets->map(fn($t) => $this->formatTicketSummary($t)),
            'clients'         => $clients,
            'itTeam'          => $itTeam,
            'config'          => $config,
            'autoAssignRules' => $autoAssignRules->map(fn($r) => [
                'id'       => $r->id,
                'kategori' => $r->kategori,
                'client'   => $r->client,
                'assignee' => $r->assignee?->name,
            ]),
        ]);
    }

    // ── Security: Login Logs & Pending Reset Requests ─────────────────────────

    public function loginLogs()
    {
        $logs = LoginLog::orderByDesc('created_at')->limit(200)->get();
        return response()->json($logs);
    }

    public function resetRequests()
    {
        $requests = PasswordResetToken::with('user:id,username,name,email')
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($r) => [
                'id'           => $r->id,
                'username'     => $r->user->username,
                'name'         => $r->user->name,
                'email_sent'   => !empty($r->user->email),
                // Token di-mask untuk keamanan; gunakan share_url hanya jika user tidak punya email
                'token_prefix' => substr($r->token, 0, 8) . '...',
                'share_url'    => empty($r->user->email)
                    ? url("/reset-password/{$r->token}")
                    : null,
                'expires_at'   => $r->expires_at->format('d M Y H:i'),
                'created_at'   => $r->created_at->diffForHumans(),
            ]);

        return response()->json($requests);
    }

    private function formatTicketSummary(Ticket $t): array
    {
        $sla    = $t->sla;
        $freeze = $t->currentFreeze;
        return [
            'id'               => $t->ticket_id,
            'title'            => $t->title,
            'type'             => $t->type,
            'approval'         => $t->approval,
            'category'         => $t->category,
            'client'           => $t->client,
            'assignee'         => $t->assignee?->name,
            'assignee_color'   => $t->assignee?->color,
            'assignee_initials'=> $t->assignee?->initials,
            'creator'          => $t->creator?->name,
            'creator_id'       => $t->creator_id,
            'created_at'       => $t->created_at->toISOString(),
            'due_date'         => $t->due_date?->format('d M Y'),
            'closed_at'        => $t->closed_at?->toISOString(),
            'progress'         => $t->progress,
            'sla'              => $sla,
            'task_total'       => $t->tasks->count(),
            'task_done'        => $t->tasks->where('status', 'Done')->count(),
            'freeze_status'    => $t->freeze_status,
            'freeze_id'        => $freeze?->id,
            'freeze_duration'  => $freeze?->duration_days,
            'freeze_reason'    => $freeze?->reason,
            'freeze_requester' => $freeze?->requester?->name,
            'freeze_ends_at'   => $freeze?->freeze_ends_at?->format('d M Y'),
        ];
    }
}
