<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;

class AttachmentService
{
    /**
     * Simpan semua file lampiran dari request ke storage dan DB.
     */
    public function storeFromRequest(Request $request, Ticket $ticket, User $uploader): void
    {
        if (!$request->hasFile('attachments')) {
            return;
        }

        foreach ($request->file('attachments') as $file) {
            $stored = $file->store('attachments', 'local');
            Attachment::create([
                'ticket_id'     => $ticket->id,
                'user_id'       => $uploader->id,
                'original_name' => $file->getClientOriginalName(),
                'stored_name'   => $stored,
                'mime_type'     => $file->getMimeType(),
                'size'          => $file->getSize(),
            ]);
        }
    }
}
