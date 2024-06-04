<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contrat extends Model
{
    use HasFactory;
    protected $fillable = [
        'document_id', 'entreprise_id', 'employe_id','clause'
    ];
    protected  $table = 'contrat';

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }
}
