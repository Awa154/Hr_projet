<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contrat extends Model
{
    use HasFactory;
    protected $fillable = [
        'document_id', 'clause'
    ];
    protected  $table = 'contrat';

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
