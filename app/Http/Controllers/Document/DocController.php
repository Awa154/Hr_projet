<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Models\Contrat;
use App\Models\Document;
use App\Models\FichePaie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DocController extends Controller
{
    public function store(Request $request)
    {
        try {
            $input = $request->all();
            $validator = Validator::make($input, [
                "date_debut" => "required|date",
                "date_fin" => "required|date",
                "type_document" => "required|in:contrat,fiche_de_paie",
                "entreprise_id"=> "required|exists:entreprise,id",
                "employe_id"=> "required|exists:employe,id",
                "remuneration" => "required",
                "status" => "required|in:actualiter,terminer,payer,impayer",
                "clause" => "nullable|string",
                "tmp_hjour" => "nullable",
                "tmp_jmois" => "nullable",
                "tmp_hsup" => "nullable",
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => false,
                    "message" => "Erreur de validation",
                    "errors" => $validator->errors(),
                ], 422);
            }

            $document = Document::create($input);

            if ($document->type_document == 'contrat') {
                $contrat = new Contrat([
                    'entreprise_id' => $request->input('entreprise_id'),
                    'employe_id' => $request->input('employe_id'),
                    'clause' => $request->input('clause')
                ]);
                $document->contrat()->save($contrat);
            }

            if ($document->type_document == 'fiche_de_paie') {
                $fichepaie = new FichePaie([
                    'tmp_hjour' => $request->input('tmp_hjour'),
                    'tmp_jmois' => $request->input('tmp_jmois'),
                    'tmp_hsup' => $request->input('tmp_hsup'),
                ]);
                $document->fichepaie()->save($fichepaie);
            }

            return response()->json([
                "status" => true,
                "message" => "Document établi avec succès!",
                "data" => $document,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $input = $request->all();
            $validator = Validator::make($input, [
                "date_debut" => "required|date",
                "date_fin" => "required|date",
                "type_document" => "required|in:contrat,fiche_de_paie",
                "entreprise_id"=> "required|exists:entreprise,id",
                "employe_id"=> "required|exists:employe,id",
                "remuneration" => "required",
                "status" => "required|in:impayer,actualite,terminer,payer",
                "clause" => "nullable|string",
                "tmp_hjour" => "nullable",
                "tmp_jmois" => "nullable",
                "tmp_hsup" => "nullable",
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => false,
                    "message" => "Erreur de validation",
                    "errors" => $validator->errors(),
                ], 422);
            }

            $document = Document::findOrFail($id);
            $document->update($input);

            if ($document->type_document == 'contrat') {
                $contratData = [
                    'entreprise_id' => $request->input('entreprise_id'),
                    'employe_id' => $request->input('employe_id'),
                    'clause' => $request->input('clause')
                ];
                if ($document->contrat) {
                    $document->contrat->update($contratData);
                } else {
                    $contrat = new Contrat($contratData);
                    $document->contrat()->save($contrat);
                }
            }

            if ($document->type_document == 'fiche_de_paie') {
                $fichepaieData = [
                    'tmp_hjour' => $request->input('tmp_hjour'),
                    'tmp_jmois' => $request->input('tmp_jmois'),
                    'tmp_hsup' => $request->input('tmp_hsup'),
                ];
                if ($document->fichepaie) {
                    $document->fichepaie->update($fichepaieData);
                } else {
                    $fichepaie = new FichePaie($fichepaieData);
                    $document->fichepaie()->save($fichepaie);
                }
            }

            return response()->json([
                "status" => true,
                "message" => "Document mis à jour avec succès!",
                "data" => $document,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    //Affichage pour l'admin
    public function contratActu()
    {
        try {
            $documents = Document::select('d.date_debut',
                    'd.date_fin',
                    'en.nom_entreprise',
                    'user_entreprise.nom as nom_employeur',
                    'user_entreprise.prenom as prenom_employeur',
                    'user_entreprise.contact as contact_employeur',
                    'u.nom as nom_employe', 'u.prenom as prenom_employe',
                    'u.contact as contact_employe',
                    'e.domaine',
                    'e.competence as competence_employe',
                    'c.clause',
                    'd.remuneration',
                    'd.status')
                ->from('document as d')
                ->join('contrat as c', 'd.id', '=', 'c.document_id')
                ->where('type_document', 'contrat')
                ->where('d.status', 'actualiter')
                ->join('employe as e', 'c.employe_id', '=', 'e.id')
                ->join('users as u', 'e.user_id', '=', 'u.id')
                ->join('entreprise as en', 'c.entreprise_id', '=', 'en.id')
                ->join('users as user_entreprise', 'en.user_id', '=', 'user_entreprise.id')
                ->get();

            return response()->json([
                "status" => true,
                "message" => "Listes des contrats en cours",
                "data" => $documents,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    //Affichage pour utilisateur connecté (employé)
    public function contratActuEntreprise()
    {
        try {
            $iduser = Auth::id(); // Récupère l'utilisateur connecté
            $documents = Document::select('d.date_debut',
                    'd.date_fin',
                    'en.nom_entreprise',
                    'user_entreprise.nom as nom_employeur',
                    'user_entreprise.prenom as prenom_employeur',
                    'user_entreprise.contact as contact_employeur',
                    'u.nom as nom_employe', 'u.prenom as prenom_employe',
                    'u.contact as contact_employe',
                    'e.domaine',
                    'e.competence as competence_employe',
                    'c.clause',
                    'd.remuneration',
                    'd.status')
                ->from('document as d')
                ->join('contrat as c', 'd.id', '=', 'c.document_id')
                ->where('type_document', 'contrat')
                ->where('d.status', 'actualiter')
                ->join('employe as e', 'c.employe_id', '=', 'e.id')
                ->join('users as u', 'e.user_id', '=', 'u.id')
                ->join('entreprise as en', 'c.entreprise_id', '=', 'en.id')
                ->join('users as user_entreprise', 'en.user_id', '=', 'user_entreprise.id')
                ->where('user_entreprise.id', $iduser) // Filtre par l'ID de l'entreprise connecté
                ->get();

            return response()->json([
                "status" => true,
                "message" => "Listes des contrats en cours",
                "data" => $documents,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    //Affichage pour utilisateur connecté (employé)
    public function contratActuEmploye()
    {
        try {
            $iduser = Auth::id(); // Récupère l'utilisateur connecté
            $documents = Document::select('d.date_debut',
                    'd.date_fin',
                    'en.nom_entreprise',
                    'user_entreprise.nom as nom_employeur',
                    'user_entreprise.prenom as prenom_employeur',
                    'user_entreprise.contact as contact_employeur',
                    'u.nom as nom_employe', 'u.prenom as prenom_employe',
                    'u.contact as contact_employe',
                    'e.domaine',
                    'e.competence as competence_employe',
                    'c.clause',
                    'd.remuneration',
                    'd.status')
                ->from('document as d')
                ->join('contrat as c', 'd.id', '=', 'c.document_id')
                ->where('type_document', 'contrat')
                ->where('d.status', 'actualiter')
                ->join('employe as e', 'c.employe_id', '=', 'e.id')
                ->join('users as u', 'e.user_id', '=', 'u.id')
                ->join('entreprise as en', 'c.entreprise_id', '=', 'en.id')
                ->join('users as user_entreprise', 'en.user_id', '=', 'user_entreprise.id')
                ->where('u.id', $iduser) // Filtre par l'ID de l'employé connecté
                ->get();

            return response()->json([
                "status" => true,
                "message" => "Listes des contrats en cours",
                "data" => $documents,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    //Affichage pour l'admin
    public function contratTerminer()
    {
        try {
            $documents = Document::select('d.date_debut',
                    'd.date_fin',
                    'en.nom_entreprise',
                    'user_entreprise.nom as nom_employeur',
                    'user_entreprise.prenom as prenom_employeur',
                    'user_entreprise.contact as contact_employeur',
                    'u.nom as nom_employe', 'u.prenom as prenom_employe',
                    'u.contact as contact_employe',
                    'e.domaine',
                    'e.competence as competence_employe',
                    'c.clause',
                    'd.remuneration',
                    'd.status')
                ->from('document as d')
                ->join('contrat as c', 'd.id', '=', 'c.document_id')
                ->where('type_document', 'contrat')
                ->where('d.status', 'terminer')
                ->join('employe as e', 'c.employe_id', '=', 'e.id')
                ->join('users as u', 'e.user_id', '=', 'u.id')
                ->join('entreprise as en', 'c.entreprise_id', '=', 'en.id')
                ->join('users as user_entreprise', 'en.user_id', '=', 'user_entreprise.id')
            ->get();

            return response()->json([
                "status" => true,
                "message" => "Liste des contrats terminés",
                "data" => $documents,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    //Affichage pour l'utilisateur connecté (entreprise)
    public function contratTerminerEntreprise()
    {
        try {
            $iduser = Auth::id(); // Récupère l'utilisateur connecté
            $documents = Document::select('d.date_debut',
                    'd.date_fin',
                    'en.nom_entreprise',
                    'user_entreprise.nom as nom_employeur',
                    'user_entreprise.prenom as prenom_employeur',
                    'user_entreprise.contact as contact_employeur',
                    'u.nom as nom_employe', 'u.prenom as prenom_employe',
                    'u.contact as contact_employe',
                    'e.domaine',
                    'e.competence as competence_employe',
                    'c.clause',
                    'd.remuneration',
                    'd.status')
                ->from('document as d')
                ->join('contrat as c', 'd.id', '=', 'c.document_id')
                ->where('type_document', 'contrat')
                ->where('d.status', 'actualiter')
                ->join('employe as e', 'c.employe_id', '=', 'e.id')
                ->join('users as u', 'e.user_id', '=', 'u.id')
                ->join('entreprise as en', 'c.entreprise_id', '=', 'en.id')
                ->join('users as user_entreprise', 'en.user_id', '=', 'user_entreprise.id')
                ->where('user_entreprise.id', $iduser) // Filtre par l'ID de l'entreprise connecté
                ->get();

            return response()->json([
                "status" => true,
                "message" => "Liste des contrats terminés",
                "data" => $documents,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    //Affichage pour l'utilisateur connecté (employé)
    public function contratTerminerEmploye()
    {
        try {
            $iduser = Auth::id(); // Récupère l'utilisateur connecté
            $documents = Document::select('d.date_debut',
                    'd.date_fin',
                    'en.nom_entreprise',
                    'user_entreprise.nom as nom_employeur',
                    'user_entreprise.prenom as prenom_employeur',
                    'user_entreprise.contact as contact_employeur',
                    'u.nom as nom_employe', 'u.prenom as prenom_employe',
                    'u.contact as contact_employe',
                    'e.domaine',
                    'e.competence as competence_employe',
                    'c.clause',
                    'd.remuneration',
                    'd.status')
                ->from('document as d')
                ->join('contrat as c', 'd.id', '=', 'c.document_id')
                ->where('type_document', 'contrat')
                ->where('d.status', 'actualiter')
                ->join('employe as e', 'c.employe_id', '=', 'e.id')
                ->join('users as u', 'e.user_id', '=', 'u.id')
                ->join('entreprise as en', 'c.entreprise_id', '=', 'en.id')
                ->join('users as user_entreprise', 'en.user_id', '=', 'user_entreprise.id')
                ->where('u.id', $iduser) // Filtre par l'ID de l'employé connecté
                ->get();

            return response()->json([
                "status" => true,
                "message" => "Liste des contrats terminés",
                "data" => $documents,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    //Affichage pour l'admin
    public function fichepaieNonPayer()
    {
        try {
            $documents = Document::select('d.date_debut',
                    'd.date_fin',
                    'en.nom_entreprise',
                    'user_entreprise.nom as nom_employeur',
                    'user_entreprise.prenom as prenom_employeur',
                    'user_entreprise.contact as contact_employeur',
                    'u.nom as nom_employe', 'u.prenom as prenom_employe',
                    'u.contact as contact_employe',
                    'e.domaine',
                    'e.competence as competence_employe',
                    'f.tmp_hjour',
                    'f.tmp_jmois',
                    'f.tmp_hsup',
                    'd.remuneration',
                    'd.status')
                ->from('document as d')
                ->join('fichepaie as f', 'd.id', '=', 'f.document_id')
                ->where('type_document', 'fichepaie')
                ->where('d.status', 'impayer')
                ->join('employe as e', 'f.employe_id', '=', 'e.id')
                ->join('users as u', 'e.user_id', '=', 'u.id')
                ->join('entreprise as en', 'f.entreprise_id', '=', 'en.id')
                ->join('users as user_entreprise', 'en.user_id', '=', 'user_entreprise.id')
                ->get();

            return response()->json([
                "status" => true,
                "message" => "Liste des fiches de paies non payées",
                "data" => $documents,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    //Affichage pour l'utilisateur connecter (employé)
    public function fichepaieNonPayerEmploye()
    {
        try {
            $iduser = Auth::id(); // Récupère l'utilisateur connecté
            $documents = Document::select('d.date_debut',
                    'd.date_fin',
                    'en.nom_entreprise',
                    'user_entreprise.nom as nom_employeur',
                    'user_entreprise.prenom as prenom_employeur',
                    'user_entreprise.contact as contact_employeur',
                    'u.nom as nom_employe', 'u.prenom as prenom_employe',
                    'u.contact as contact_employe',
                    'e.domaine',
                    'e.competence as competence_employe',
                    'f.tmp_hjour',
                    'f.tmp_jmois',
                    'f.tmp_hsup',
                    'd.remuneration',
                    'd.status')
                ->from('document as d')
                ->join('fichepaie as f', 'd.id', '=', 'f.document_id')
                ->where('type_document', 'fichepaie')
                ->where('d.status', 'impayer')
                ->join('employe as e', 'f.employe_id', '=', 'e.id')
                ->join('users as u', 'e.user_id', '=', 'u.id')
                ->join('entreprise as en', 'f.entreprise_id', '=', 'en.id')
                ->join('users as user_entreprise', 'en.user_id', '=', 'user_entreprise.id')
                ->where('u.id', $iduser) // Filtre par l'ID de l'employé connecté
                ->get();

            return response()->json([
                "status" => true,
                "message" => "Liste des fiches de paies non payées",
                "data" => $documents,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    //Affichage pour l'utilisateur connecter (entreprise)
    public function fichepaieNonPayerEntreprise()
    {
        try {
            $iduser = Auth::id(); // Récupère l'utilisateur connecté
            $documents = Document::select('d.date_debut',
                    'd.date_fin',
                    'en.nom_entreprise',
                    'user_entreprise.nom as nom_employeur',
                    'user_entreprise.prenom as prenom_employeur',
                    'user_entreprise.contact as contact_employeur',
                    'u.nom as nom_employe', 'u.prenom as prenom_employe',
                    'u.contact as contact_employe',
                    'e.domaine',
                    'e.competence as competence_employe',
                    'f.tmp_hjour',
                    'f.tmp_jmois',
                    'f.tmp_hsup',
                    'd.remuneration',
                    'd.status')
                ->from('document as d')
                ->join('fichepaie as f', 'd.id', '=', 'f.document_id')
                ->where('type_document', 'fichepaie')
                ->where('d.status', 'impayer')
                ->join('employe as e', 'f.employe_id', '=', 'e.id')
                ->join('users as u', 'e.user_id', '=', 'u.id')
                ->join('entreprise as en', 'f.entreprise_id', '=', 'en.id')
                ->join('users as user_entreprise', 'en.user_id', '=', 'user_entreprise.id')
                ->where('user_entreprise.id', $iduser) // Filtre par l'ID de l'employé connecté
                ->get();

            return response()->json([
                "status" => true,
                "message" => "Liste des fiches de paies non payées",
                "data" => $documents,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    //Affichage pour l'admin
    public function fichepaiePayer()
    {
        try {
            $documents = Document::select('d.date_debut',
                    'd.date_fin',
                    'en.nom_entreprise',
                    'user_entreprise.nom as nom_employeur',
                    'user_entreprise.prenom as prenom_employeur',
                    'user_entreprise.contact as contact_employeur',
                    'u.nom as nom_employe', 'u.prenom as prenom_employe',
                    'u.contact as contact_employe',
                    'f.tmp_hjour',
                    'f.tmp_jmois',
                    'f.tmp_hsup',
                    'd.remuneration',
                    'd.status')
                ->from('document as d')
                ->join('fichepaie as f', 'd.id', '=', 'f.document_id')
                ->where('type_document', 'fichepaie')
                ->where('d.status', 'payer')
                ->join('employe as e', 'f.employe_id', '=', 'e.id')
                ->join('users as u', 'e.user_id', '=', 'u.id')
                ->join('entreprise as en', 'f.entreprise_id', '=', 'en.id')
                ->join('users as user_entreprise', 'en.user_id', '=', 'user_entreprise.id')
                ->get();

            return response()->json([
                "status" => true,
                "message" => "Liste des fiches de paies payées",
                "data" => $documents,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    //Affichage pour l'utilisateur connecter (employe)
    public function fichepaiePayerEmploye()
    {
        try {
            $iduser = Auth::id(); // Récupère l'utilisateur connecté
            $documents = Document::select('d.date_debut',
                    'd.date_fin',
                    'en.nom_entreprise',
                    'user_entreprise.nom as nom_employeur',
                    'user_entreprise.prenom as prenom_employeur',
                    'user_entreprise.contact as contact_employeur',
                    'u.nom as nom_employe', 'u.prenom as prenom_employe',
                    'u.contact as contact_employe',
                    'f.tmp_hjour',
                    'f.tmp_jmois',
                    'f.tmp_hsup',
                    'd.remuneration',
                    'd.status')
                ->from('document as d')
                ->join('fichepaie as f', 'd.id', '=', 'f.document_id')
                ->where('type_document', 'fichepaie')
                ->where('d.status', 'payer')
                ->join('employe as e', 'f.employe_id', '=', 'e.id')
                ->join('users as u', 'e.user_id', '=', 'u.id')
                ->join('entreprise as en', 'f.entreprise_id', '=', 'en.id')
                ->join('users as user_entreprise', 'en.user_id', '=', 'user_entreprise.id')
                ->where('u.id', $iduser) // Filtre par l'ID de l'employé connecté
                ->get();

            return response()->json([
                "status" => true,
                "message" => "Liste des fiches de paies payées",
                "data" => $documents,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    //Affichage pour l'utilisateur connecté (entreprise)
    public function fichepaiePayerEntreprise()
    {
        try {
            $iduser = Auth::id(); // Récupère l'utilisateur connecté
            $documents = Document::select('d.date_debut',
                    'd.date_fin',
                    'en.nom_entreprise',
                    'user_entreprise.nom as nom_employeur',
                    'user_entreprise.prenom as prenom_employeur',
                    'user_entreprise.contact as contact_employeur',
                    'u.nom as nom_employe', 'u.prenom as prenom_employe',
                    'u.contact as contact_employe',
                    'f.tmp_hjour',
                    'f.tmp_jmois',
                    'f.tmp_hsup',
                    'd.remuneration',
                    'd.status')
                ->from('document as d')
                ->join('fichepaie as f', 'd.id', '=', 'f.document_id')
                ->where('type_document', 'fichepaie')
                ->where('d.status', 'payer')
                ->join('employe as e', 'f.employe_id', '=', 'e.id')
                ->join('users as u', 'e.user_id', '=', 'u.id')
                ->join('entreprise as en', 'f.entreprise_id', '=', 'en.id')
                ->join('users as user_entreprise', 'en.user_id', '=', 'user_entreprise.id')
                ->where('user_entreprise.id', $iduser) // Filtre par l'ID de l'employé connecté
                ->get();

            return response()->json([
                "status" => true,
                "message" => "Liste des fiches de paies payées",
                "data" => $documents,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
            ], 500);
        }
    }

}
