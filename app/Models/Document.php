<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;
    protected $fillable = [
        'date_debut', 'date_fin', 'type_document', 'nom_employeur', 'prenom_employeur', 'contact_employeur', 'nom_employe', 'prenom_employe', 'contact_employe','remuneration','status'
    ];
    protected  $table = 'document';

    public function contrat()
    {
        return $this->hasOne(Contrat::class);
    }

    public function fichepaie()
    {
        return $this->hasOne(FichePaie::class);
    }
}
