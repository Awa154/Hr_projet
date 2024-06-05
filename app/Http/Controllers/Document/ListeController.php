<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Models\Employe;
use App\Models\Entreprise;
use Illuminate\Http\Request;

class ListeController extends Controller
{
    //
    //Affichage de la liste des employés
    public function listeEmploye()
    {
        try {
            $employe = Employe::select('u.email as email_employe',
                    'u.nom as nom_employe',
                    'u.prenom as prenom_employe',
                    'u.contact as contact_employe',
                    'u.adresse as adresse_employe',
                    'e.domaine',
                    'e.annee_exp as annee_experience',
                    'e.competence as competence_employe',
                    )
                ->from('employe as e')
                ->join('users as u', 'e.user_id', '=', 'u.id')
                ->get();

            return response()->json([
                "status" => true,
                "message" => "Listes des employés",
                "data" => $employe,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    //Affichage de la liste des entreprises
    public function listeEntreprise()
    {
        try {
            $entreprise = Entreprise::select('user_entreprise.email as email_responsable',
                    'en.logo',
                    'en.nom_entreprise',
                    'en.site_web',
                    'en.description as detail_entreprise',
                    'user_entreprise.nom as nom_responsable',
                    'user_entreprise.prenom as prenom_responsable',
                    'user_entreprise.contact as contact_responsable',
                    'user_entreprise.adresse as adresse_entreprise',
                    )
                ->from('entreprise as en')
                ->join('users as user_entreprise', 'en.user_id', '=', 'user_entreprise.id')
                ->get();

            return response()->json([
                "status" => true,
                "message" => "Listes des entreprises partenaires",
                "data" => $entreprise,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
            ], 500);
        }
    }
}
