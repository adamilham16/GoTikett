<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Client;
use App\Models\AutoAssignRule;
use App\Models\AppConfig;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Users ─────────────────────────────────────────────────────────────
        $users = [
            // IT SIM
            ['username' => 'adam',   'name' => 'Adam Ilham Suwendi', 'type' => 'it',      'role' => 'IT Infra', 'dept' => 'IT',         'color' => '#fb923c', 'approver' => null],
            ['username' => 'puji',   'name' => 'Puji Rahmat',        'type' => 'it',      'role' => 'ALL',      'dept' => 'IT',         'color' => '#34d399', 'approver' => null],
            ['username' => 'rizky',  'name' => 'Rizky Yudha',        'type' => 'it',      'role' => 'IT BA',    'dept' => 'IT',         'color' => '#60a5fa', 'approver' => null],
            ['username' => 'saddam', 'name' => 'Saddam',             'type' => 'it',      'role' => 'IT BA',    'dept' => 'IT',         'color' => '#a78bfa', 'approver' => null],
            // Requester
            ['username' => 'icha',   'name' => 'Icha',               'type' => 'user',    'role' => 'Staff',    'dept' => 'CS Inbound', 'color' => '#f472b6', 'approver' => 'jovi'],
            ['username' => 'mutia',  'name' => 'Mutia',              'type' => 'user',    'role' => 'Staff',    'dept' => 'CS Inbound', 'color' => '#e879f9', 'approver' => 'jovi'],
            // Manager
            ['username' => 'jovi',   'name' => 'Jovi',               'type' => 'manager', 'role' => 'Manager',  'dept' => 'CS Inbound', 'color' => '#fbbf24', 'approver' => null],
        ];

        $createdUsers = [];
        foreach ($users as $u) {
            $createdUsers[$u['username']] = User::create([
                'username' => $u['username'],
                'name'     => $u['name'],
                'password' => bcrypt($u['username'] . '123'), // password default: username123
                'type'     => $u['type'],
                'role'     => $u['role'],
                'dept'     => $u['dept'],
                'color'    => $u['color'],
            ]);
        }

        // Set approver_id setelah semua user dibuat
        foreach ($users as $u) {
            if ($u['approver']) {
                $createdUsers[$u['username']]->update([
                    'approver_id' => $createdUsers[$u['approver']]->id,
                ]);
            }
        }

        // ── Clients ───────────────────────────────────────────────────────────
        $client = Client::create(['nama' => 'CS Inbound']);

        // ── Auto Assign Rules ─────────────────────────────────────────────────
        AutoAssignRule::insert([
            ['kategori' => 'Infra',  'client' => 'CS Inbound', 'assignee_id' => $createdUsers['adam']->id,   'created_at' => now(), 'updated_at' => now()],
            ['kategori' => 'Sistem', 'client' => 'CS Inbound', 'assignee_id' => $createdUsers['rizky']->id,  'created_at' => now(), 'updated_at' => now()],
            ['kategori' => 'Telko',  'client' => 'CS Inbound', 'assignee_id' => $createdUsers['saddam']->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ── App Config default ────────────────────────────────────────────────
        $defaults = [
            'appName'     => 'GoTiket',
            'appSubtitle' => 'Atur Kerja, Dukung Tim',
            'appIcon'     => '🗂️',
            'bgType'      => 'gradient',
            'bgColor'     => '#e0f2f7',
            'bgGradient'  => 'linear-gradient(135deg,#bae6fd 0%,#a5f3fc 40%,#99f6e4 100%)',
            'bgImage'     => '',
        ];
        foreach ($defaults as $key => $value) {
            AppConfig::create(['key' => $key, 'value' => $value]);
        }
    }
}
