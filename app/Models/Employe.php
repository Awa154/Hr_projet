<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employe extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'domaine', 'competence', 'annee_exp'
    ];
    protected  $table = 'employe';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contrat()
    {
        return $this->hasOne(Contrat::class);
    }

    public function fichepaie()
    {
        return $this->hasOne(FichePaie::class);
    }
}
