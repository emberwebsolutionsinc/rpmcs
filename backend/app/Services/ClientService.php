<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Support\Facades\DB;

class ClientService
{
   public function create(array $data): Client
    {
        return DB::transaction(function () use ($data) {
            if (empty($data['client_code'])) {
                $data['client_code'] = $this->generateClientCode();
            }

            return Client::create($data);
        });
    }

    private function generateClientCode(): string
    {
        $nextId = (Client::max('id') ?? 0) + 1;

        return 'CL-' . str_pad(
            (string) $nextId,
            6,
            '0',
            STR_PAD_LEFT
        );
    }

    public function update(Client $client, array $data): Client
    {
        return DB::transaction(function () use ($client, $data) {
            $client->update($data);

            return $client->fresh();
        });
    }

    public function delete(Client $client): bool
    {
        return DB::transaction(fn () => $client->delete());
    }
}
