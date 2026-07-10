<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientDocument extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'document_type',
        'document_name',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'remarks',
        'uploaded_by',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
