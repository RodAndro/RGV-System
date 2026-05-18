<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private readonly bool $redactPii = true)
    {
    }

    public function query()
    {
        return User::query()->with('roles')->latest();
    }

    public function headings(): array
    {
        return ['Name', 'Email', 'Phone', 'Roles', 'Active', 'Created At'];
    }

    public function map($user): array
    {
        return [
            $user->name,
            $this->redactPii ? $this->redactEmail($user->email) : $user->email,
            $this->redactPii ? null : $user->phone,
            $user->roles->pluck('name')->implode(', '),
            $user->is_active ? 'Yes' : 'No',
            optional($user->created_at)->toDateTimeString(),
        ];
    }

    private function redactEmail(?string $email): ?string
    {
        if (! $email || ! str_contains($email, '@')) {
            return $email;
        }

        [$name, $domain] = explode('@', $email, 2);

        return substr($name, 0, 1) . '***@' . $domain;
    }
}
