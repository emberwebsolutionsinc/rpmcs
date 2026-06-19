<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Support\Facades\DB;

class ClientService
{
    public function create(array $data): Client
    {
        return DB::transaction(fn () => Client::create($data));
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
