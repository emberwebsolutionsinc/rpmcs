<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'document_type',
        'document_title',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'expires_at',
        'verification_status',
        'remarks',
        'uploaded_by',
        'preview_path',
    ];

    protected $casts = [
        'expires_at' => 'date',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
