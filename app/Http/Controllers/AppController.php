<?php

namespace App\Http\Controllers;

use App\Conseil;
use Barryvdh\DomPDF\Facade as PDF;
use App\Eleve;
use App\Matiere;
use App\Session;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class AppController extends Controller
{
    //

    public function printBulettinOfEleve($eleveId,$sessionId,$see){
//        if ($request->ajax())return "Ha" ;else return "777";
        $z=0;
        $eleve = Eleve::with('classe.prof')->find($eleveId);
        $conseil = Conseil::where('eleve_id',$eleveId)->where('session_id',$sessionId)->first();
//        return $eleve->nom_complet;
        $classe = $eleve->classe;
        $prof = $classe->prof;
//        dd($prof);
        $classeId = $classe->id;
        $niveauId = $classe->niveau_id;

        $MATIERES = Matiere::with([
                'dispenses'=>function($q) use ($niveauId) { $q->where('niveau_id',$niveauId); },
                'appreciations'=>function($q) use ($eleveId,$sessionId){
                    $q->where('eleve_id',$eleveId)->where('session_id',$sessionId);
                },
                /*'evaluations'=>function($q) use ($classeId,$sessionId){
//            $q->where('classe_id',$classeId)->where('take',1);
                    $q->where('classe_id',$classeId)->where('session_id',$sessionId);
                },*/
                /*'evaluations.notes'=>function($q) use ($eleveId){
                    $q->where('eleve_id',$eleveId);
                },*/
//                'interros'=>function($q){ $q->where('take',1);},
                // apparamment inutile=> filtre depuis evaluations
                'interros'=>function($q) use ($classeId, $sessionId){ $q->where('classe_id',$classeId)->where('session_id',$sessionId); },
                'ds'=>function($q) use ($classeId, $sessionId){ $q->where('classe_id',$classeId)->where('session_id',$sessionId); },
                'compos'=>function($q) use ($classeId, $sessionId){ $q->where('classe_id',$classeId)->where('session_id',$sessionId); },
                'compos.notes' => function($q) use ($eleveId){ $q->where('eleve_id',$eleveId); },
                'interros.notes' => function($q) use ($eleveId){ $q->where('eleve_id',$eleveId); },
                'ds.notes' => function($q) use ($eleveId){ $q->where('eleve_id',$eleveId); },
                'interventions',
                "interventions.prof"
            ]
        )->whereHas('niveaux',function($q) use ($niveauId){
            $q->where('niveau_id',$niveauId);
        })->orderBy('id')->get();







//        dd($MATIERES[0]->dispenses);

        $eleves = Eleve::where('classe_id',$classeId)->get();
        $gen_moys_of_eleves = [];
        $gen_moys_of_eleves_s1 = [];
        $gen_moys_of_eleves_s2 = [];
        $moyennes_de_l_eleves_dans_chaque_matiere = [];

        foreach ($eleves as $elv){
            $moy_gen_of_eleve = 0;
//            $moys_of_eleve = [];
            $eId = $elv->id;
//            dd($eId);
            $eMatieres = Matiere::with([
                    'dispenses'=>function($q) use ($niveauId) { $q->where('niveau_id',$niveauId); },
                    /*'evaluations'=>function($q) use ($classeId,$sessionId){
//            $q->where('classe_id',$classeId)->where('take',1);
                        $q->where('classe_id',$classeId)->where('session_id',$sessionId);
                    },
                    'evaluations.notes'=>function($q) use ($eId){
                        $q->where('eleve_id',$eId);
                    },*/
//                'interros'=>function($q){ $q->where('take',1);},
                    // apparamment inutile=> filtre depuis evaluations
                    'interros'=>function($q) use ($classeId, $sessionId){ $q->where('classe_id',$classeId)->where('session_id',$sessionId); },
                    'ds'=>function($q) use ($classeId, $sessionId){ $q->where('classe_id',$classeId)->where('session_id',$sessionId); },
                    'compos'=>function($q) use ($classeId, $sessionId){ $q->where('classe_id',$classeId)->where('session_id',$sessionId); },
//                    'interros'=>function($q) use ($classeId){ $q->where('classe_id',$classeId); },
//                    'ds'=>function($q) use ($classeId){ $q->where('classe_id',$classeId); },
//                    'compos'=>function($q) use ($classeId){ $q->where('classe_id',$classeId); },
                    'compos.notes' => function($q) use ($eId){ $q->where('eleve_id',$eId); },
                    'interros.notes' => function($q) use ($eId){ $q->where('eleve_id',$eId); },
                    'ds.notes' => function($q) use ($eId){ $q->where('eleve_id',$eId); },
                ]
            )->whereHas('niveaux',function($q) use ($niveauId){
                $q->where('niveau_id',$niveauId);
            })->orderBy('id')->get();

            $moys = [];
            $moys_obs = [];
            $moys_facs = [];
            foreach ($eMatieres as $i=>$matiere){
                $matiere->coef = (is_numeric($matiere->dispenses[0]->coef))?$matiere->dispenses[0]->coef:$matiere->coef;
                $interros_notes=[];
                $ds_notes=[];
                $compos_notes=[];
                // récupération des notes d'interrogations
                foreach ($matiere->interros as $interro){
                    if (count($interro->notes)>0){
                        if(is_numeric($interro->notes[0]->note)){
                            $interros_notes[] = $interro->notes[0]->note;
                        }
                    }
                }
                if(count($interros_notes)>0){
//                dd(array_sum($interros_notes));
                    $matiere["note_interro"] = number_format(array_sum($interros_notes)/count($interros_notes),2);
                }else{
                    $matiere["note_interro"] = "";
                }

                //récupétration des notes de devoirs sureillés
                foreach ($matiere->ds as $devoir){
                    if (count($devoir->notes)>0){
                        if(is_numeric($devoir->notes[0]->note)){
                            $ds_notes[] = $devoir->notes[0]->note;
                        }
                    }
                }
                if(count($ds_notes)>0){
                    $matiere["note_ds"] = number_format(array_sum($ds_notes)/count($ds_notes),2);
                }else{
                    $matiere["note_ds"] = "";
                }

                //calcul moyenne classe
                if(is_numeric($matiere["note_interro"]) && is_numeric($matiere["note_ds"])){
                    $matiere["note_classe"] = number_format((($matiere["note_interro"] + $matiere["note_ds"])/2),2);
                }
                elseif (!is_numeric($matiere["note_interro"]) && !is_numeric($matiere["note_ds"])){
                    $matiere->coef = "";
                }
                elseif (!is_numeric($matiere["note_ds"])) $matiere["note_classe"] = $matiere["note_interro"];
                elseif (!is_numeric($matiere["note_interro"])) $matiere["note_classe"] = $matiere["note_ds"];
                /*elseif (!is_numeric($matiere["note_interro"]) && !is_numeric($matiere["note_ds"])){
                    $matiere["note_classe"] = null;
                }*/
//            $matiere["moy_classe"] = $matiere["note_classe"];


                if(count($matiere->compos)>0){
                    if(count($matiere->compos[0]->notes)>0){
                        if(is_numeric($matiere->compos[0]->notes[0]->note)) $matiere["note_compo"] = $matiere->compos[0]->notes[0]->note;
                        else $matiere["note_compo"] = "";
                    }
                }

                    else
                    {
                        $matiere["note_compo"] = null;
                    }

                if(is_numeric($matiere["note_classe"]) && is_numeric($matiere["note_compo"]))
                    $matiere["moy_gen"] = number_format((($matiere["note_classe"]+$matiere["note_compo"])/2),2);
                else{
                    $matiere["moy_gen"] = "";
//                    $matiere->coef = 0;
                }
//                echo($matiere["moy_gen"]."->");
                if (is_numeric($matiere["moy_gen"])){
//                    dd($matiere->intitule);
                    array_push($moys,[
                        'eleve_id'=>$eId,
                        'matiere_id'=>$matiere->id,
                        "moy"=>$matiere["moy_gen"]
                    ]);

                    if ($matiere["obligatoire"]==1){
                        array_push($moys_obs,$matiere["moy_gen"]*$matiere->coef);
                    }else{
                        array_push($moys_facs,$matiere["moy_gen"]*$matiere->coef);
                    }
                }
//                $MATIERES[$i]->moys[] = $moys;

            }
//            dd($this->total($eMatieres,"coef"));
            $obligatoires = $this->filter_by_value($eMatieres,"obligatoire",1);
            $facultatives = $this->filter_by_value($eMatieres,"obligatoire",0);

            $coef_obl = $this->total($obligatoires,"coef");
            $coef_fac = $this->total($facultatives,"coef");
            $moy_fac = number_format((array_sum($moys_facs)/$this->total($facultatives,"coef")),2);
            $all_moys = $moys_obs;
            array_push($all_moys,$moy_fac);
            $all_coef = $coef_obl+1;
            $moy_of_eleve = number_format(array_sum($all_moys)/$all_coef,2);
//            dd($moy_of_eleve);

//            $moy_of_eleve = number_format((((array_sum($moys_obs)/$this->total($obligatoires,"coef"))+(array_sum($moys_facs)/$this->total($facultatives,"coef")))/2),2);
            array_push($gen_moys_of_eleves,$moy_of_eleve);
            array_push($moyennes_de_l_eleves_dans_chaque_matiere,$moys);
//            dd($moys);
        }

        if (count($gen_moys_of_eleves)>0)
            $moy_of_classe = number_format(array_sum($gen_moys_of_eleves)/count($gen_moys_of_eleves),2);
        $smallest_moy = $this->getSmallest($gen_moys_of_eleves);
        $biggest_moy = $this->getGreatest($gen_moys_of_eleves);

        //pour le calcul de la moyenn generale du 1er trim
        foreach ($eleves as $elv){
            $moy_gen_of_eleve = 0;
//            $moys_of_eleve = [];
            $eId = $elv->id;
//            dd($eId);
            $eMatieres = Matiere::with([
                    'dispenses'=>function($q) use ($niveauId) { $q->where('niveau_id',$niveauId); },
                    'interros'=>function($q) use ($classeId, $sessionId){ $q->where('classe_id',$classeId)->where('session_id',1); },
                    'ds'=>function($q) use ($classeId, $sessionId){ $q->where('classe_id',$classeId)->where('session_id',1); },
                    'compos'=>function($q) use ($classeId, $sessionId){ $q->where('classe_id',$classeId)->where('session_id',1); },
//                    'interros'=>function($q) use ($classeId){ $q->where('classe_id',$classeId); },
//                    'ds'=>function($q) use ($classeId){ $q->where('classe_id',$classeId); },
//                    'compos'=>function($q) use ($classeId){ $q->where('classe_id',$classeId); },
                    'compos.notes' => function($q) use ($eId){ $q->where('eleve_id',$eId); },
                    'interros.notes' => function($q) use ($eId){ $q->where('eleve_id',$eId); },
                    'ds.notes' => function($q) use ($eId){ $q->where('eleve_id',$eId); },
                ]
            )->whereHas('niveaux',function($q) use ($niveauId){
                $q->where('niveau_id',$niveauId);
            })->orderBy('id')->get();

            $moys = [];
            $moys_obs = [];
            $moys_facs = [];
            foreach ($eMatieres as $i=>$matiere){
                $matiere->coef = (is_numeric($matiere->dispenses[0]->coef))?$matiere->dispenses[0]->coef:$matiere->coef;
                $interros_notes=[];
                $ds_notes=[];
                $compos_notes=[];
                // récupération des notes d'interrogations
                foreach ($matiere->interros as $interro){
                    if (count($interro->notes)>0){
                        if(is_numeric($interro->notes[0]->note)){
                            $interros_notes[] = $interro->notes[0]->note;
                        }
                    }
                }
                if(count($interros_notes)>0){
//                dd(array_sum($interros_notes));
                    $matiere["note_interro"] = number_format(array_sum($interros_notes)/count($interros_notes),2);
                }else{
                    $matiere["note_interro"] = "";
                }

                //récupétration des notes de devoirs sureillés
                foreach ($matiere->ds as $devoir){
                    if (count($devoir->notes)>0){
                        if(is_numeric($devoir->notes[0]->note)){
                            $ds_notes[] = $devoir->notes[0]->note;
                        }
                    }
                }
                if(count($ds_notes)>0){
                    $matiere["note_ds"] = number_format(array_sum($ds_notes)/count($ds_notes),2);
                }else{
                    $matiere["note_ds"] = "";
                }

                //calcul moyenne classe
                if(is_numeric($matiere["note_interro"]) && is_numeric($matiere["note_ds"])){
                    $matiere["note_classe"] = ($matiere["note_interro"] + $matiere["note_ds"])/2;
                }
                elseif (!is_numeric($matiere["note_interro"]) && !is_numeric($matiere["note_ds"])){
                    $matiere->coef = "";
                }
                elseif (is_numeric($matiere["note_ds"])) $matiere["note_classe"] = $matiere["note_interro"];
                elseif (is_numeric($matiere["note_interro"])) $matiere["note_classe"] = $matiere["note_ds"];
                elseif (!is_numeric($matiere["note_interro"]) && !is_numeric($matiere["note_ds"])){
                    $matiere["note_classe"] = null;
                }
//            $matiere["moy_classe"] = $matiere["note_classe"];


                if(count($matiere->compos)>0){
                    if(count($matiere->compos[0]->notes)>0){
                        if(is_numeric($matiere->compos[0]->notes[0]->note)) $matiere["note_compo"] = $matiere->compos[0]->notes[0]->note;
                        else $matiere["note_compo"] = null;
                    }
                }

                else
                {
                    $matiere["note_compo"] = null;
                }

                if(is_numeric($matiere["note_classe"]) && is_numeric($matiere["note_compo"])){
                    $matiere["moy_gen"] = number_format((($matiere["note_classe"]+$matiere["note_compo"])/2),2);

                }
                else{
                    $matiere["moy_gen"] = "";
                    $matiere->coef = null;
                }
//                echo($matiere["moy_gen"]."->");

//                $MATIERES[$i]->moys[] = $moys;

            }
//            dd($this->total($eMatieres,"coef"));
            $obligatoires = $this->filter_by_value($eMatieres,"obligatoire",1);
            $facultatives = $this->filter_by_value($eMatieres,"obligatoire",0);

            $coef_obl = $this->total($obligatoires,"coef");
            $coef_fac = $this->total($facultatives,"coef");
            $moy_fac = number_format((array_sum($moys_facs)/$this->total($facultatives,"coef")),2);
            $all_moys = $moys_obs;
            array_push($all_moys,$moy_fac);
            $all_coef = $coef_obl+1;
            $moy_of_eleve = number_format(array_sum($all_moys)/$all_coef,2);
//            dd($moy_of_eleve);
//            dd($moys);

//            $moy_of_eleve = number_format((((array_sum($moys_obs)/$this->total($obligatoires,"coef"))+(array_sum($moys_facs)/$this->total($facultatives,"coef")))/2),2);
            array_push($gen_moys_of_eleves_s1,$moy_of_eleve);
            array_push($moyennes_de_l_eleves_dans_chaque_matiere,$moys);
//            dd($moys);
        }

        //pour le calcul de la moy gen du 2em trim
        if($sessionId==3){
            foreach ($eleves as $elv){
                $moy_gen_of_eleve = 0;
//            $moys_of_eleve = [];
                $eId = $elv->id;
//            dd($eId);
                $eMatieres = Matiere::with([
                        'dispenses'=>function($q) use ($niveauId) { $q->where('niveau_id',$niveauId); },
                        'interros'=>function($q) use ($classeId, $sessionId){ $q->where('classe_id',$classeId)->where('session_id',2); },
                        'ds'=>function($q) use ($classeId, $sessionId){ $q->where('classe_id',$classeId)->where('session_id',2); },
                        'compos'=>function($q) use ($classeId, $sessionId){ $q->where('classe_id',$classeId)->where('session_id',2); },
//                    'interros'=>function($q) use ($classeId){ $q->where('classe_id',$classeId); },
//                    'ds'=>function($q) use ($classeId){ $q->where('classe_id',$classeId); },
//                    'compos'=>function($q) use ($classeId){ $q->where('classe_id',$classeId); },
                        'compos.notes' => function($q) use ($eId){ $q->where('eleve_id',$eId); },
                        'interros.notes' => function($q) use ($eId){ $q->where('eleve_id',$eId); },
                        'ds.notes' => function($q) use ($eId){ $q->where('eleve_id',$eId); },
                    ]
                )->whereHas('niveaux',function($q) use ($niveauId){
                    $q->where('niveau_id',$niveauId);
                })->orderBy('id')->get();

                $moys = [];
                $moys_obs = [];
                $moys_facs = [];
                foreach ($eMatieres as $i=>$matiere){
                    $matiere->coef = (is_numeric($matiere->dispenses[0]->coef))?$matiere->dispenses[0]->coef:$matiere->coef;
                    $interros_notes=[];
                    $ds_notes=[];
                    $compos_notes=[];
                    // récupération des notes d'interrogations
                    foreach ($matiere->interros as $interro){
                        if (count($interro->notes)>0){
                            if(is_numeric($interro->notes[0]->note)){
                                $interros_notes[] = $interro->notes[0]->note;
                            }
                        }
                    }
                    if(count($interros_notes)>0){
//                dd(array_sum($interros_notes));
                        $matiere["note_interro"] = number_format(array_sum($interros_notes)/count($interros_notes),2);
                    }else{
                        $matiere["note_interro"] = "";
                    }

                    //récupétration des notes de devoirs sureillés
                    foreach ($matiere->ds as $devoir){
                        if (count($devoir->notes)>0){
                            if(is_numeric($devoir->notes[0]->note)){
                                $ds_notes[] = $devoir->notes[0]->note;
                            }
                        }
                    }
                    if(count($ds_notes)>0){
                        $matiere["note_ds"] = number_format(array_sum($ds_notes)/count($ds_notes),2);
                    }else{
                        $matiere["note_ds"] = "";
                    }

                    //calcul moyenne classe
                    if(is_numeric($matiere["note_interro"]) && is_numeric($matiere["note_ds"])){
                        $matiere["note_classe"] = ($matiere["note_interro"] + $matiere["note_ds"])/2;
                    }
                    elseif (!is_numeric($matiere["note_interro"]) && !is_numeric($matiere["note_ds"])){
                        $matiere->coef = "";
                    }
                    elseif (is_numeric($matiere["note_ds"])) $matiere["note_classe"] = $matiere["note_interro"];
                    elseif (is_numeric($matiere["note_interro"])) $matiere["note_classe"] = $matiere["note_ds"];
                    elseif (!is_numeric($matiere["note_interro"]) && !is_numeric($matiere["note_ds"])){
                        $matiere["note_classe"] = null;
                    }
//            $matiere["moy_classe"] = $matiere["note_classe"];


                    if(count($matiere->compos)>0){
                        if(count($matiere->compos[0]->notes)>0){
                            if(is_numeric($matiere->compos[0]->notes[0]->note)) $matiere["note_compo"] = $matiere->compos[0]->notes[0]->note;
                            else $matiere["note_compo"] = null;
                        }
                    }

                    else
                    {
                        $matiere["note_compo"] = null;
                    }

                    if(is_numeric($matiere["note_classe"]) && is_numeric($matiere["note_compo"]))
                        $matiere["moy_gen"] = number_format((($matiere["note_classe"]+$matiere["note_compo"])/2),2);
                    else{
                        $matiere["moy_gen"] = "";
                        $matiere->coef = null;
                    }
//                echo($matiere["moy_gen"]."->");
                    if (is_numeric($matiere["moy_gen"])){
//                    dd($matiere->intitule);
                        array_push($moys,[
                            'eleve_id'=>$eId,
                            'matiere_id'=>$matiere->id,
                            "moy"=>$matiere["moy_gen"]
                        ]);

                        if ($matiere["obligatoire"]==1){
                            array_push($moys_obs,$matiere["moy_gen"]*$matiere->coef);
                        }else{
                            array_push($moys_facs,$matiere["moy_gen"]*$matiere->coef);
                        }
                    }
//                $MATIERES[$i]->moys[] = $moys;

                }
//            dd($this->total($eMatieres,"coef"));
                $obligatoires = $this->filter_by_value($eMatieres,"obligatoire",1);
                $facultatives = $this->filter_by_value($eMatieres,"obligatoire",0);

                $coef_obl = $this->total($obligatoires,"coef");
                $coef_fac = $this->total($facultatives,"coef");
                $moy_fac = number_format((array_sum($moys_facs)/$this->total($facultatives,"coef")),2);
                $all_moys = $moys_obs;
                array_push($all_moys,$moy_fac);
                $all_coef = $coef_obl+1;
                $moy_of_eleve = number_format(array_sum($all_moys)/$all_coef,2);
//            dd($moy_of_eleve);

//            $moy_of_eleve = number_format((((array_sum($moys_obs)/$this->total($obligatoires,"coef"))+(array_sum($moys_facs)/$this->total($facultatives,"coef")))/2),2);
                array_push($gen_moys_of_eleves_s2,$moy_of_eleve);
                array_push($moyennes_de_l_eleves_dans_chaque_matiere,$moys);
//            dd($moys);
            }
        }


//        dd($gen_moys_of_eleves);
//        dd($moyennes_de_l_eleves_dans_chaque_matiere);


        foreach ($MATIERES as $MATIERE){

            $MATIERE["moys"] = $this->getMoysInMatieres($moyennes_de_l_eleves_dans_chaque_matiere,$MATIERE->id);
            if (count($MATIERE["moys"])>0)
                $MATIERE["moy_classe"] = number_format((array_sum($MATIERE["moys"]))/count($MATIERE["moys"]),2);
        }

//        dd($MATIERES);

//        $matieres = $matieres::with('interros')->find(4);


        foreach ($MATIERES as $i=>$Mmatiere){
//            dd($Mmatiere->intitule,$Mmatiere->compos);
            $interros_notes=[];
            $ds_notes=[];
            $compos_notes=[];

//            dd($Mmatiere);




            // récupération des notes d'interrogations
            $Mmatiere->coef = (is_numeric($Mmatiere->dispenses[0]->coef))?$Mmatiere->dispenses[0]->coef:$Mmatiere->coef;
            foreach ($Mmatiere->interros as $interro){
                if (count($interro->notes)>0){
                    if(is_numeric($interro->notes[0]->note)) $interros_notes[] = $interro->notes[0]->note;
                }
            }
            if(count($interros_notes)>0){
//                dd(array_sum($interros_notes));
                $Mmatiere["note_interro"] = number_format(array_sum($interros_notes)/count($interros_notes),2);
            }else{
                $Mmatiere["note_interro"] = "";
            }

            //récupétration des notes de devoirs sureillés
            foreach ($Mmatiere->ds as $devoir){
                if(count($devoir->notes)>0){
                    if(is_numeric($devoir->notes[0]->note)) $ds_notes[] = $devoir->notes[0]->note;
                }
            }
            if(count($ds_notes)>0){
                $Mmatiere["note_ds"] = number_format(array_sum($ds_notes)/count($ds_notes),2);
            }else{
                $Mmatiere["note_ds"] = "";
            }

            //calcul moyenne classe
            if(is_numeric($Mmatiere["note_interro"]) && is_numeric($Mmatiere["note_ds"])){
                $Mmatiere["note_classe"] = number_format((($Mmatiere["note_interro"] + $Mmatiere["note_ds"])/2),2);
            }
            elseif (!is_numeric($Mmatiere["note_interro"]) && !is_numeric($Mmatiere["note_ds"])){
//                dd($Mmatiere);
                $Mmatiere->coef = "";
            }
            elseif (!is_numeric($Mmatiere["note_ds"])){
//                dd($matiere->intitule);
                 $Mmatiere["note_classe"] = $Mmatiere["note_interro"];
            }
            elseif (!is_numeric($Mmatiere["note_interro"])){
//                dd($matiere->intitule);
                 $Mmatiere["note_classe"] = $Mmatiere["note_ds"];
            }


            /*if($Mmatiere->intitule=="EPS"){
                dd(is_numeric($Mmatiere["note_interro"]) && is_numeric($Mmatiere["note_ds"]));
            }*/
//            $Mmatiere["moy_classe"] = $Mmatiere["note_classe"];

//            dd($MATIERES);

            if(count($Mmatiere->compos)>0){
                if (count($Mmatiere->compos[0]->notes)>0){
                    if(is_numeric($Mmatiere->compos[0]->notes[0]->note)){
//                        dd($Mmatiere->compos[0]->notes[0]->note);
                        $Mmatiere["note_compo"] = $Mmatiere->compos[0]->notes[0]->note;
                    }
                }
            }

            else
                $Mmatiere["note_compo"] = "";

            if(is_numeric($Mmatiere["note_classe"]) && is_numeric($Mmatiere["note_compo"])){
                $Mmatiere["moy_gen"] = number_format((($Mmatiere["note_classe"]+$Mmatiere["note_compo"])/2),2);
            }
            else{
                $Mmatiere["moy_gen"] = "";
//                $Mmatiere->coef = 0;
            }


            $Mmatiere["rang"] = $this->getRangInMatiere($Mmatiere->moys,$Mmatiere->moy_gen,$Mmatiere->id);
        }

        $moys_tab = $gen_moys_of_eleves;

        $obligatoires = $this->filter_by_value($MATIERES,"obligatoire",1);
        $facultatives = $this->filter_by_value($MATIERES,"obligatoire",0);

        $S1MATS = Matiere::with([
                'dispenses'=>function($q) use ($niveauId) { $q->where('niveau_id',$niveauId); },
//                'interros'=>function($q){ $q->where('take',1);},
                // apparamment inutile=> filtre depuis evaluations
                'interros'=>function($q) use ($classeId, $sessionId){ $q->where('classe_id',$classeId)->where('session_id',1); },
                'ds'=>function($q) use ($classeId, $sessionId){ $q->where('classe_id',$classeId)->where('session_id',1); },
                'compos'=>function($q) use ($classeId, $sessionId){ $q->where('classe_id',$classeId)->where('session_id',1); },
                'compos.notes' => function($q) use ($eleveId){ $q->where('eleve_id',$eleveId); },
                'interros.notes' => function($q) use ($eleveId){ $q->where('eleve_id',$eleveId); },
                'ds.notes' => function($q) use ($eleveId){ $q->where('eleve_id',$eleveId); },
            ]
        )->whereHas('niveaux',function($q) use ($niveauId){
            $q->where('niveau_id',$niveauId);
        })->orderBy('id')->get();

        $S2MATS = Matiere::with([
                'dispenses'=>function($q) use ($niveauId) { $q->where('niveau_id',$niveauId); },
                'interros'=>function($q) use ($classeId, $sessionId){ $q->where('classe_id',$classeId)->where('session_id',2); },
                'ds'=>function($q) use ($classeId, $sessionId){ $q->where('classe_id',$classeId)->where('session_id',2); },
                'compos'=>function($q) use ($classeId, $sessionId){ $q->where('classe_id',$classeId)->where('session_id',2); },
                'compos.notes' => function($q) use ($eleveId){ $q->where('eleve_id',$eleveId); },
                'interros.notes' => function($q) use ($eleveId){ $q->where('eleve_id',$eleveId); },
                'ds.notes' => function($q) use ($eleveId){ $q->where('eleve_id',$eleveId); },
            ]
        )->whereHas('niveaux',function($q) use ($niveauId){
            $q->where('niveau_id',$niveauId);
        })->orderBy('id')->get();

        foreach ($S1MATS as $i=>$Mmatiere){
//            dd($Mmatiere->intitule,$Mmatiere->compos);
            $interros_notes=[];
            $ds_notes=[];
            $compos_notes=[];// récupération des notes d'interrogations
            $Mmatiere->coef = (is_numeric($Mmatiere->dispenses[0]->coef))?$Mmatiere->dispenses[0]->coef:$Mmatiere->coef;
            foreach ($Mmatiere->interros as $interro){
                if (count($interro->notes)>0){
                    if(is_numeric($interro->notes[0]->note)) $interros_notes[] = $interro->notes[0]->note;
                }
            }
            if(count($interros_notes)>0){
//                dd(array_sum($interros_notes));
                $Mmatiere["note_interro"] = number_format(array_sum($interros_notes)/count($interros_notes),2);
            }else{
                $Mmatiere["note_interro"] = "";
            }

            //récupétration des notes de devoirs sureillés
            foreach ($Mmatiere->ds as $devoir){
                if(count($devoir->notes)>0){if(is_numeric($devoir->notes[0]->note)) $ds_notes[] = $devoir->notes[0]->note;}
            }
            if(count($ds_notes)>0){
                $Mmatiere["note_ds"] = number_format(array_sum($ds_notes)/count($ds_notes),2);
            }else{
                $Mmatiere["note_ds"] = "";
            }

            //calcul moyenne classe
            if(is_numeric($Mmatiere["note_interro"]) && is_numeric($Mmatiere["note_ds"])){

                $Mmatiere["note_classe"] = number_format((($Mmatiere["note_interro"] + $Mmatiere["note_ds"])/2),2);
            }
            elseif (!is_numeric($Mmatiere["note_interro"]) && !is_numeric($Mmatiere["note_ds"])){
//                dd($Mmatiere);
                $Mmatiere->coef = 0;
            }
            elseif (!is_numeric($Mmatiere["note_ds"])){
//                dd($matiere->intitule);
                $Mmatiere["note_classe"] = $Mmatiere["note_interro"];
            }
            elseif (!is_numeric($Mmatiere["note_interro"])){
//                dd($matiere->intitule);
                $Mmatiere["note_classe"] = $Mmatiere["note_ds"];
            }

            /*if($Mmatiere->intitule=="EPS"){
                dd(is_numeric($Mmatiere["note_interro"]) && is_numeric($Mmatiere["note_ds"]));
            }*/
//            $Mmatiere["moy_classe"] = $Mmatiere["note_classe"];


            if(count($Mmatiere->compos)>0){
                if (count($Mmatiere->compos[0]->notes)>0)
                    if(is_numeric($Mmatiere->compos[0]->notes[0]->note)){
                        $Mmatiere["note_compo"] = $Mmatiere->compos[0]->notes[0]->note;
                    }
            }

            else
                $Mmatiere["note_compo"] = "";

            if(is_numeric($Mmatiere["note_classe"]) && is_numeric($Mmatiere["note_compo"])){
                $Mmatiere["moy_gen"] = number_format((($Mmatiere["note_classe"]+$Mmatiere["note_compo"])/2),2);
                if(is_numeric($Mmatiere->coef)){
                    $Mmatiere["moy_gen_coef"] = $Mmatiere["moy_gen"] * $Mmatiere->coef;
                }
            }
            else{
                $Mmatiere["moy_gen"] = "";
                $Mmatiere["moy_gen_coef"] = "";
            }


//            $Mmatiere["rang"] = $this->getRangInMatiere($Mmatiere->moys,$Mmatiere->moy_gen,$Mmatiere->id);
        }

        foreach ($S2MATS as $i=>$Mmatiere){
//            dd($Mmatiere->intitule,$Mmatiere->compos);
            $interros_notes=[];
            $ds_notes=[];
            $compos_notes=[];// récupération des notes d'interrogations
            $Mmatiere->coef = (is_numeric($Mmatiere->dispenses[0]->coef))?$Mmatiere->dispenses[0]->coef:$Mmatiere->coef;
            foreach ($Mmatiere->interros as $interro){
                if (count($interro->notes)>0){
                    if(is_numeric($interro->notes[0]->note)) $interros_notes[] = $interro->notes[0]->note;
                }
            }
            if(count($interros_notes)>0){
//                dd(array_sum($interros_notes));
                $Mmatiere["note_interro"] = number_format(array_sum($interros_notes)/count($interros_notes),2);
            }else{
                $Mmatiere["note_interro"] = "";
            }

            //récupétration des notes de devoirs sureillés
            foreach ($Mmatiere->ds as $devoir){
                if(count($devoir->notes)>0){if(is_numeric($devoir->notes[0]->note)) $ds_notes[] = $devoir->notes[0]->note;}
            }
            if(count($ds_notes)>0){
                $Mmatiere["note_ds"] = number_format(array_sum($ds_notes)/count($ds_notes),2);
            }else{
                $Mmatiere["note_ds"] = "";
            }

            //calcul moyenne classe
            if(is_numeric($Mmatiere["note_interro"]) && is_numeric($Mmatiere["note_ds"])){

                $Mmatiere["note_classe"] = number_format((($Mmatiere["note_interro"] + $Mmatiere["note_ds"])/2),2);
            }
            elseif (!is_numeric($Mmatiere["note_interro"]) && !is_numeric($Mmatiere["note_ds"])){
//                dd($Mmatiere);
                $Mmatiere->coef = 0;
            }
            elseif (!is_numeric($Mmatiere["note_ds"])){
//                dd($matiere->intitule);
                $Mmatiere["note_classe"] = $Mmatiere["note_interro"];
            }
            elseif (!is_numeric($Mmatiere["note_interro"])){
//                dd($matiere->intitule);
                $Mmatiere["note_classe"] = $Mmatiere["note_ds"];
            }

            /*if($Mmatiere->intitule=="EPS"){
                dd(is_numeric($Mmatiere["note_interro"]) && is_numeric($Mmatiere["note_ds"]));
            }*/
//            $Mmatiere["moy_classe"] = $Mmatiere["note_classe"];


            if(count($Mmatiere->compos)>0){
                if (count($Mmatiere->compos[0]->notes)>0)
                    if(is_numeric($Mmatiere->compos[0]->notes[0]->note)){
                        $Mmatiere["note_compo"] = $Mmatiere->compos[0]->notes[0]->note;
                    }
            }

            else
                $Mmatiere["note_compo"] = "";

            if(is_numeric($Mmatiere["note_classe"]) && is_numeric($Mmatiere["note_compo"])){
                $Mmatiere["moy_gen"] = number_format((($Mmatiere["note_classe"]+$Mmatiere["note_compo"])/2),2);
                if(is_numeric($Mmatiere->coef)){
                    $Mmatiere["moy_gen_coef"] = $Mmatiere["moy_gen"] * $Mmatiere->coef;
                }
            }
            else{
                $Mmatiere["moy_gen"] = "";
                $Mmatiere["moy_gen_coef"] = "";
            }


//            $Mmatiere["rang"] = $this->getRangInMatiere($Mmatiere->moys,$Mmatiere->moy_gen,$Mmatiere->id);
        }

        $s1_obls = $this->filter_by_value($S1MATS,"obligatoire",1);
        $s1_facs = $this->filter_by_value($S1MATS,"obligatoire",0);
        $s1_coef_fac = $this->total($s1_facs,"coef");
        $s1_coef_obl = $this->total($s1_obls,"coef");
        $s1_moys_facs = $this->total($s1_facs,"moy_gen_coef");
        $s1_moys_obs = $this->total($s1_obls,"moy_gen_coef");
//        dd($s1_moys_obs);
        $s1_moy_fac = number_format(($s1_moys_facs/$s1_coef_fac),2);
        $s1_all_moys = $s1_moys_obs;
        $s1_all_moys+=$s1_moy_fac;
        $s1_all_coef = $s1_coef_obl+1;
        $s1_moyenne = number_format(($s1_all_moys/$s1_all_coef),2);
        $s1_rang = $this->getRang($gen_moys_of_eleves_s1,$s1_moyenne);

//        dd($s1_moyenne);
//        dd($this->getRang($gen_moys_of_eleves_s1,$s1_moyenne));


        $s2_obls = $this->filter_by_value($S2MATS,"obligatoire",1);
        $s2_facs = $this->filter_by_value($S2MATS,"obligatoire",0);
        $s2_coef_fac = $this->total($s2_facs,"coef");
        $s2_coef_obl = $this->total($s2_obls,"coef");
        $s2_moys_facs = $this->total($s2_facs,"moy_gen_coef");
        $s2_moys_obs = $this->total($s2_obls,"moy_gen_coef");
//        dd($s2_moys_obs);
        $s2_moy_fac = number_format(($s2_moys_facs/$s2_coef_fac),2);
        $s2_all_moys = $s2_moys_obs;
        $s2_all_moys+=$s2_moy_fac;
        $s2_all_coef = $s2_coef_obl+1;
        $s2_moyenne = number_format(($s2_all_moys/$s2_all_coef),2);
        $s2_rang = "";

        if($sessionId==3){
            $s2_rang = $this->getRang($gen_moys_of_eleves_s2,$s1_moyenne);
        }


        /*$total_matieres_obs = $this->total($obligatoires);
        $total_matieres_facs = $this->total($facultatives);
        $moy_matieres_obs = number_format(($total_matieres_obs/count($obligatoires)),2);
        $moy_matieres_facs = number_format(($total_matieres_facs/count($facultatives)),2);*/

        $session = Session::find($sessionId);
        if ($see==1){
            return view('templates.bulletins.modele_0',compact('s1_moyenne','s2_moyenne','conseil','session','prof','eleve','classe','obligatoires','facultatives','moys_tab',"smallest_moy","biggest_moy","moy_of_classe",'s1_rang','s2_rang'));
        }else{

            /*$pdf = PDF::loadView('templates.bulletins.modele_0', compact('session','prof','eleve','classe','obligatoires','facultatives','moys_tab',"smallest_moy","biggest_moy","moy_of_classe"));
//            $pdf->setOption('margin-bottom', '0mm');
            return $pdf->download('invoice.pdf');*/

            $pdf = App::make('dompdf.wrapper');
//            PDF::loadView('templates.bulletins.modele_0',['data'=>compact('session','prof','eleve','classe','obligatoires','facultatives','moys_tab',"smallest_moy","biggest_moy","moy_of_classe")]);
//            $pdf->setPaper('A4', 'landscape');
//        $pdf->loadHTML('<h1>Test</h1>');
            $pdf = $pdf->loadView('templates.bulletins.modele_0', compact('s1_moyenne','s2_moyenne','conseil','session','prof','eleve','classe','obligatoires','facultatives','moys_tab',"smallest_moy","biggest_moy","moy_of_classe",'s1_rang','s2_rang'))
//                ->setOption('margin-bottom', '0mm')
            ;
            return $pdf->download($eleve->nom_complet.'.pdf');

        }

    }

    function filter_by_value ($array, $index, $value){
        $newarray = [];
        foreach ($array as $item){
            if($item[$index]==$value) array_push($newarray,$item);
        }
        return $newarray;
    }

    function getMoysInMatieres($eleves,$id){
        $moys = [];
        foreach ($eleves as $notes){
//            dd($eleves);
            foreach ($notes as $note){
//                dd($note["matiere_id"],$id);
                if ($note["matiere_id"]==$id) array_push($moys,$note['moy']);
            }
        }
        return $moys;
    }

    function getRangInMatiere($moys,$moy,$id){
//        return 0;
        /*if ($id==5){
            rsort($moys);
//            dd($moys);
        }*/
        if (is_numeric($moy)){
            $alls = [];
            rsort($moys);

            if (in_array($moy,$moys)){
//                return sizeof($moys);
                $key = array_search($molocqly,$moys);
                return $key+1;
            }else{
                return 0;
//                dd("no",$id);
//                $moy = number_format($moy,1);
//                dd($moy);
//                $moy = number_format($moy,2);
                $closest = $this->getClosest($moy,$moys);
//                dd($closest);
                $key = array_search($closest,$moys,true);
//                dd($key);
                return $key+1;
            }
//            dd($moys);

//        if ($moy == 6.5)
//        dd($moys);
//        dd($key);

        }

    }

    function getClosest($search, $arr) {
        $closest = 0;
        foreach ($arr as $item) {
            if ($closest == 0 || abs($search - $closest) > abs($item - $search)) {
                $closest = $item;
            }
        }

        return $closest;
    }

    function total($array,$key){
        $sum = 0;
        foreach ($array as $item){
            if (is_numeric($item[$key]))
                $sum+=$item[$key];
        }
        return $sum;
    }

    public function getSmallest($array){
        sort($array);
        return $array[0];
    }

    public function getGreatest($array){
        rsort($array);
        return $array[0];
    }

    public function getRang($moys,$moy){

//        return 0;
        if (is_numeric($moy)){
            //$moy = number_format($moy,1);
            //dd(number_format($moy,1));

            $alls = [];
            rsort($moys);
            //dd($moys);
            if (in_array($moy,$moys)){
                $key = array_search($moy,$moys);
                return $key+1;

            }else{
                //dd($moy);
                //$moy = number_format($moy,1);
                $closest = $this->getClosest($moy,$moys);
                //dd($closest);
                $key = array_search($closest,$moys);
                return $key+1;
            }

//        if ($moy == 6.5)
            //dd($moys);
//        dd($key);

        }


    }


}
