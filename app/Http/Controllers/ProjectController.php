<?php

namespace App\Http\Controllers;

use App\Absence;
use App\Appreciation;
use App\CahierTexte;
use App\Classe;
use App\ClasseIntrevention;
use App\Conseil;
use App\Cours;
use App\Dispense;
use App\Eleve;
use App\Enseigne;
use App\Evaluation;
use App\EvaluationType;
use App\Examen;
use App\Exercice;
use App\Intervention;
use App\LienParente;
use App\Login;
use App\Matiere;
use App\MatiereGroupe;
use App\MatiereType;
use App\Note;
use App\Personnel;
use App\Responsable;
use App\Retard;
use App\Salle;
use App\Seance;
use App\Session;
use App\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    //
    public function initializeProject(){
//        User::query()->truncate();

        Login::query()->truncate();
        Salle::query()->truncate();
        Classe::query()->truncate();
        Eleve::query()->truncate();
        Responsable::query()->truncate();
        LienParente::query()->truncate();
        Personnel::query()->truncate();

        Matiere::query()->truncate();
        Cours::query()->truncate();
        Examen::query()->truncate();
        Note::query()->truncate();

        Intervention::query()->truncate();
        Dispense::query()->truncate();
        Absence::query()->truncate();
        CahierTexte::query()->truncate();
        Enseigne::query()->truncate();
        ClasseIntrevention::query()->truncate();
        MatiereGroupe::query()->truncate();
        MatiereType::query()->truncate();
        Seance::query()->truncate();
        Appreciation::query()->truncate();

        return "Projet Reinitialisé avec success";
    }

    public function newAcademic(){
        Note::query()->truncate();

        /*Intervention::query()->truncate();
        Dispense::query()->truncate();
        Absence::query()->truncate();
        CahierTexte::query()->truncate();
        Enseigne::query()->truncate();
        ClasseIntrevention::query()->truncate();
        MatiereGroupe::query()->truncate();
        MatiereType::query()->truncate();*/
        Seance::query()->truncate();
        Appreciation::query()->truncate();
        Absence::truncate();
        Appreciation::truncate();
        CahierTexte::truncate();
        ClasseIntrevention::truncate();
        Conseil::truncate();
        Cours::truncate();
        Dispense::truncate();
        Enseigne::truncate();
        Evaluation::truncate();
//        EvaluationType::truncate();
        Examen::truncate();
        Exercice::truncate();
        Intervention::truncate();
        Login::truncate();
        Note::truncate();
        Retard::truncate();
        Absence::truncate();
    }

    public function simulateS3(){
        $evs = Evaluation::with('notes')->where('session_id',1)->get();
        foreach ($evs as $ev){
//            $e = new Evaluation();
//            $e = $ev;
//            $e->session_id = 3;
//            $e->save();
            $ev->session_id = 3;
            $e = Evaluation::create($ev->getAttributes());
            //randomize notes in EValuation model
//            dd($e->id,$ev->id);
        }

        return "Done";
    }
}
