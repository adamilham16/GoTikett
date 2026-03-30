<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketExportService
{
    /**
     * Buat dan stream file Excel berisi semua tiket beserta ringkasan.
     */
    public function export(): StreamedResponse
    {
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
            ->select(
                'assignee_id',
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

        $headers = [
            'ID', 'Judul', 'Deskripsi', 'Tipe', 'Status', 'Approval', 'Kategori', 'Client',
            'Assignee', 'Creator', 'Disetujui Oleh', 'Tanggal Buat', 'Due Date',
            'Tanggal Close', 'Lead Time', 'SLA Status', 'Progress (%)',
        ];

        $filename    = 'GoTiket_Export_' . now()->format('Y-m-d') . '.xlsx';
        $spreadsheet = new Spreadsheet();

        // Sheet 1: Semua Tiket
        $sheet1 = $spreadsheet->getActiveSheet()->setTitle('Semua Tiket');
        $sheet1->fromArray([$headers], null, 'A1');
        $sheet1->getStyle('A1:Q1')->getFont()->setBold(true);

        $rowIdx = 2;
        Ticket::with(['creator', 'assignee', 'approver', 'tasks'])
            ->orderByDesc('created_at')
            ->chunk(200, function ($chunk) use ($sheet1, &$rowIdx) {
                foreach ($chunk as $t) {
                    $sla    = $t->sla;
                    $closed = (bool) $t->closed_at;
                    $sheet1->fromArray([[
                        $t->ticket_id,
                        $t->title,
                        $t->desc ?? '—',
                        match ($t->type) {
                            'incident'   => 'Incident',
                            'newproject' => 'New Project',
                            default      => 'Open Request',
                        },
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
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
