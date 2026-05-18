<?php

namespace App\Imports;

use App\Models\ImportLog;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Spatie\Permission\Models\Role;

class UsersImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    public function __construct(private readonly int $importLogId)
    {
    }

    public function collection(Collection $rows): void
    {
        $log = ImportLog::findOrFail($this->importLogId);
        $errors = $log->errors ?? [];
        $log->increment('total_rows', $rows->count());

        foreach ($rows as $index => $row) {
            $data = $row->toArray();
            $validator = Validator::make($data, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['nullable', 'string', 'min:8'],
                'role' => ['nullable', 'string', 'exists:roles,name'],
            ]);

            if ($validator->fails()) {
                $errors[] = [
                    'row' => $log->processed_rows + $index + 2,
                    'email' => $data['email'] ?? null,
                    'errors' => $validator->errors()->all(),
                ];
                $log->increment('failed_rows');
                $log->increment('processed_rows');
                continue;
            }

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password'] ?? str()->random(16)),
                'is_active' => true,
            ]);

            if (! empty($data['role']) && Role::where('name', $data['role'])->exists()) {
                $user->assignRole($data['role']);
            }

            $log->increment('successful_rows');
            $log->increment('processed_rows');
        }

        $log->update(['errors' => $errors]);
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
