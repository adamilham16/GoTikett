<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\AutoAssignRule;
use App\Models\AppConfig;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;

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
        $rules = AutoAssignRule::with('assignee')->get()->map(fn($r) => [
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
        return response()->json(['success' => true, 'id' => $rule->id]);
    }

    public function destroyAutoAssign(int $id)
    {
        AutoAssignRule::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    // ── App Config ────────────────────────────────────────────────────────────
    public function getConfig()
    {
        $config = AppConfig::all()->pluck('value', 'key');
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
        AppConfig::truncate();
        return response()->json(['success' => true]);
    }

    // ── Tasks (checklist dalam tiket) ─────────────────────────────────────────
    public function storeTask(Request $request, string $ticketId)
    {
        $request->validate(['title' => 'required|string']);
        $ticket = Ticket::where('ticket_id', $ticketId)->firstOrFail();
        $task   = Task::create(['ticket_id' => $ticket->id, 'title' => $request->title, 'status' => 'Todo']);
        return response()->json(['success' => true, 'id' => $task->id]);
    }

    public function toggleTask(int $taskId)
    {
        $user = User::findOrFail(session('user_id'));
        $task = Task::findOrFail($taskId);
        $ticket = Ticket::findOrFail($task->ticket_id);

        // IT SIM mana pun boleh toggle; user biasa tidak boleh
        if ($user->type !== 'it') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya IT SIM yang boleh mengubah task.'
            ], 403);
        }

        $task->update([
            'status' => $task->status === 'Todo' ? 'Done' : 'Todo'
        ]);

        return response()->json([
            'success' => true,
            'status' => $task->status
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
        $user = User::findOrFail(session('user_id'));

        $tickets = Ticket::with(['creator', 'assignee', 'tasks'])->orderByDesc('created_at')->get();
        $clients = Client::orderBy('nama')->get(['id', 'nama']);
        $itTeam  = User::where('type', 'it')->get(['id', 'name', 'color']);
        $config  = AppConfig::all()->pluck('value', 'key');
        $autoAssignRules = AutoAssignRule::with('assignee')->get();

        return response()->json([
            'user'    => [
                'id'       => $user->id,
                'name'     => $user->name,
                'username' => $user->username,
                'type'     => $user->type,
                'role'     => $user->role,
                'dept'     => $user->dept,
                'color'    => $user->color,
                'initials' => $user->initials,
                'approver' => $user->approver?->name,
            ],
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

    private function formatTicketSummary(Ticket $t): array
    {
        $sla = $t->sla;
        return [
            'id'           => $t->ticket_id,
            'title'        => $t->title,
            'type'         => $t->type,
            'status'       => $t->status,
            'status_label' => Ticket::STAGE_LABELS[$t->status] ?? $t->status,
            'approval'     => $t->approval,
            'category'     => $t->category,
            'client'       => $t->client,
            'assignee'     => $t->assignee?->name,
            'assignee_color'=> $t->assignee?->color,
            'assignee_initials'=> $t->assignee?->initials,
            'creator'      => $t->creator?->name,
            'creator_id'   => $t->creator_id,
            'created_at'   => $t->created_at->toISOString(),
            'due_date'     => $t->due_date?->format('d M Y'),
            'closed_at'    => $t->closed_at?->toISOString(),
            'progress'     => $t->progress,
            'sla'          => $sla,
            'task_total'   => $t->tasks->count(),
            'task_done'    => $t->tasks->where('status', 'Done')->count(),
        ];
    }
}
