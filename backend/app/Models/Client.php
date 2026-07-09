<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Sale;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_code',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'birthdate',
        'civil_status',
        'nationality',
        'tin',
        'email',
        'contact_number',
        'telephone_number',
        'address',
        'status',
    ];

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'client_id');
    }

}

