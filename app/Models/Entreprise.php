<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entreprise extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'nom_entreprise', 'description', 'site_web', 'logo'
    ];
    protected  $table = 'entreprise';

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
