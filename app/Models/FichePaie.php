<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FichePaie extends Model
{
    use HasFactory;
    protected $fillable = [
        'document_id','entreprise_id', 'employe_id', 'tmp_hjour', 'tmp_jmois', 'tmp_hsup'
    ];
    protected  $table = 'fichepaie';

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }
}
