<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Models\Contrat;
use App\Models\Document;
use App\Models\FichePaie;
use Illuminate\Http\Request;
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
                "nom_employeur" => "required|string",
                "prenom_employeur" => "required|string",
                "contact_employeur" => "required",
                "nom_employe" => "required|string",
                "prenom_employe" => "required|string",
                "contact_employe" => "required",
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
                    'clause' => $request->input('clause'),
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
                "nom_employeur" => "required|string",
                "prenom_employeur" => "required|string",
                "contact_employeur" => "required",
                "nom_employe" => "required|string",
                "prenom_employe" => "required|string",
                "contact_employe" => "required",
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

    public function contratActu()
    {
        try {
            $documents = Document::select('d.date_debut', 'd.date_fin', 'd.nom_employeur', 'd.prenom_employeur', 'd.contact_employeur', 'd.nom_employe', 'd.prenom_employe', 'd.contact_employe', 'd.remuneration', 'd.status', 'c.clause')
                ->from('document as d')
                ->join('contrat as c', 'd.id', '=', 'c.document_id')
                ->where('d.type_document', 'contrat')
                ->where('d.status', 'actualiter')
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

    public function contratTerminer()
    {
        try {
            $documents = Document::select('d.date_debut', 'd.date_fin', 'd.nom_employeur', 'd.prenom_employeur', 'd.contact_employeur', 'd.nom_employe', 'd.prenom_employe', 'd.contact_employe', 'd.remuneration', 'd.status', 'c.clause')
                ->from('document as d')
                ->join('contrat as c', 'd.id', '=', 'c.document_id')
                ->where('d.type_document', 'contrat')
                ->where('d.status', 'terminer')
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

    public function fichepaieNonPayer()
    {
        try {
            $documents = Document::select('d.date_debut', 'd.date_fin', 'd.nom_employeur', 'd.prenom_employeur', 'd.contact_employeur', 'd.nom_employe', 'd.prenom_employe', 'd.contact_employe', 'd.remuneration', 'd.status', 'f.tmp_hjour','f.tmp_jmois', 'f.tmp_hsup')
                ->from('document as d')
                ->join('fichepaie as f', 'd.id', '=', 'f.document_id')
                ->where('d.type_document', 'fiche_de_paie')
                ->where('d.status', 'impayer')
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

    public function fichepaiePayer()
    {
        try {
            $documents = Document::select('d.date_debut', 'd.date_fin', 'd.nom_employeur', 'd.prenom_employeur', 'd.contact_employeur', 'd.nom_employe', 'd.prenom_employe', 'd.contact_employe', 'd.remuneration', 'd.status', 'f.tmp_hjour','f.tmp_jmois', 'f.tmp_hsup')
                ->from('document as d')
                ->join('fichepaie as f', 'd.id', '=', 'f.document_id')
                ->where('d.type_document', 'fiche_de_paie')
                ->where('d.status', 'payer')
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
