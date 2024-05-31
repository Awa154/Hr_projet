<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Employe;
use App\Models\Entreprise;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    //
    /**
     * Login user
     * @param Request $request
     * @return User
     */

    public function login(Request $request){
        try {
            //code...
            $input = $request->all();
            $validator = Validator::make($input, [
                "email"=> "required|email",
                "password"=> "required"
                ]);
                if ($validator->fails()) {
                    return response()->json([
                        "status"=> false,
                        "message"=> "Erreur de validation",
                        "errors"=> $validator->errors(),
                        ],422);
                }
                if(Auth::attempt($request->only("email","password"))){
                    return response()->json([
                        "status"=> false,
                        "message"=> "Email ou mot de passe incorrect",
                        "errors"=> $validator->errors(),
                        ],401);
                }

                $user=User::where("email", $request->email)->first();
                return response()->json([
                    "status"=> true,
                    "message"=> "Vous etes maintenant connecté!",
                    "date"=> [
                        "token"=> $user->createToken("auth_user")->plainTextToken,
                        "token_type"=>"Bearer",
                    ],
                    ],200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                "status"=> false,
                "message"=> $th->getMessage()],500);
        }
    }

    public function register(Request $request){
        try {
            //code...
            $input = $request->all();
            $validator = Validator::make($input, [
                "nom"=> "required",
                "prenom"=> "required",
                "email"=> "required|email|unique:users,email",
                "adresse"=> "required",
                "contact"=> "required|unique:users,contact",
                'usertype' => 'required|in:admin,employe,entreprise',
                //doit contenir au moins une lettre minuscule, une lettre majuscule, un chiffre et un caractère spécial (regex)
                "password"=> "required|confirmed|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$&])[A-Za-z\d@$&]{8,}$/",
                "password_confirmation"=> "required",
                "domaine"=> "nullable|string",
                'competence' => 'nullable|string',
                'annee_exp' => 'nullable|integer',
                'nom_entreprise'=> 'nullable|string',
                'description'=> 'nullable|string',
                'site_web'=> 'nullable|string',
                'logo'=> 'nullable|string',
                'poste_occupe'=> 'nullable|string',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        "status"=> false,
                        "message"=> "Erreur de validation",
                        "errors"=> $validator->errors(),
                        ],422);
                }

                $input["password"] = Hash::make($request->password);

                $user=User::create($input);

                if ($user->usertype == 'employe') {
                    $employe = new Employe([
                        'domaine' => $request->input('domaine'),
                        'competence' => $request->input('competence'),
                        'annee_exp' => $request->input('annee_exp'),
                    ]);
                    $user->employe()->save($employe);
                }

                if ($user->usertype == 'entreprise') {
                    $entreprise = new Entreprise([
                        'nom_entreprise'=> $request->input('nom_entreprise'),
                        'description' => $request->input('description'),
                        'site_web' => $request->input('site_web'),
                        'logo' => $request->input('logo'),
                    ]);
                    $user->entreprise()->save($entreprise);
                }
                
                if ($user->usertype == 'admin') {
                    $admin = new Admin([
                        'poste_occupe' => $request->input('poste_occupe'),
                    ]);
                    $user->admin()->save($admin);
                }
                return response()->json([
                    "status"=> true,
                    "message"=> "Compte créer avec succès!",
                    "date"=> [
                        "token"=> $user->createToken("auth_user")->plainTextToken,
                        "token_type"=>"Bearer",
                    ],
                    ],200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                "status"=> false,
                "message"=> $th->getMessage()],500);
        }
    }

    public function profile(Request $request){
        return response()->json([
            "status"=> true,
            "message"=> "Vous etes maintenant connecté!",
            "date"=>$request->user(),
            ],200);
    }
}
