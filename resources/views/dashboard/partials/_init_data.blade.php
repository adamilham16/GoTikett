@php
$_curUser = ['id'=>$user->id,'name'=>$user->name,'type'=>$user->type,'role'=>$user->role,'dept'=>$user->dept,'color'=>$user->color,'initials'=>$user->initials,'approver'=>$user->approver?->name];
$_tickets = $tickets->map(function($t){ $f=$t->currentFreeze; return ['id'=>$t->ticket_id,'title'=>$t->title,'type'=>$t->type,'approval'=>$t->approval,'category'=>$t->category,'client'=>$t->client,'assignee'=>$t->assignee?->name,'assignee_color'=>$t->assignee?->color,'assignee_initials'=>$t->assignee?->initials,'creator'=>$t->creator?->name,'creator_id'=>$t->creator_id,'created_at'=>$t->created_at->toISOString(),'due_date'=>$t->due_date?->format('d M Y'),'closed_at'=>$t->closed_at?->toISOString(),'lead_time'=>$t->lead_time,'progress'=>$t->progress,'sla'=>$t->sla,'task_total'=>$t->tasks->count(),'task_done'=>$t->tasks->where('status','Done')->count(),'it_comment_count'=>$t->it_comment_count??0,'freeze_status'=>$t->freeze_status,'freeze_id'=>$f?->id,'freeze_duration'=>$f?->duration_days,'freeze_reason'=>$f?->reason,'freeze_requester'=>$f?->requester?->name,'freeze_ends_at'=>$f?->freeze_ends_at?->format('d M Y')]; })->values();
$_clients = $clients->map(fn($c)=>['id'=>$c->id,'nama'=>$c->nama])->values();
$_aa = $autoAssignRules->map(fn($r)=>['id'=>$r->id,'kategori'=>$r->kategori,'client'=>$r->client,'assignee'=>$r->assignee?->name])->values();
@endphp
<script>
window.INIT_DATA = {
    curUser:  @json($_curUser),
    tickets:  @json($_tickets),
    clients:  @json($_clients),
    itTeam:   @json($itTeam),
    config:   @json($config),
    aa:       @json($_aa),
    csrf:     document.querySelector('meta[name="csrf-token"]').content,
};
window.REVERB_DATA = {
    key:    '{{ env('REVERB_APP_KEY', 'gotiket-key') }}',
    host:   '{{ env('REVERB_HOST', 'localhost') }}',
    port:    {{ env('REVERB_PORT', 8080) }},
    scheme: '{{ env('REVERB_SCHEME', 'http') }}',
};
window.ROUTES = {
    ticketsStore:    '{{ route('tickets.store') }}',
    appData:         '{{ route('app.data') }}',
    autoassignStore: '{{ route('autoassign.store') }}',
    clientsStore:    '{{ route('clients.store') }}',
    usersData:       '{{ route('users.data') }}',
    usersStore:      '{{ route('users.store') }}',
    configSave:      '{{ route('config.save') }}',
    configReset:     '{{ route('config.reset') }}',
    passwordChange:  '{{ route('password.change') }}',
};
</script>
