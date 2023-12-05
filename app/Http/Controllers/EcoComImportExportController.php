<?php

namespace Muserpol\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Muserpol\Imports\EcoComImportSenasir;
use Muserpol\Models\EconomicComplement\EconomicComplement;
use Muserpol\Imports\EcoComImportAPS;
use Muserpol\Helpers\Util;
use Muserpol\Imports\EcoComImportPagoFuturo;
use Muserpol\Imports\EcoComUpdatePaidBank;
use Muserpol\Models\Affiliate;
use DB;
use Muserpol\Models\ObservationType;
use Muserpol\Models\DiscountType;
use Muserpol\User;
use Auth;
use Muserpol\Models\EconomicComplement\EcoComProcedure;
use Carbon\Carbon;

class EcoComImportExportController extends Controller
{
    public function importSenasir(Request $request)
    {
        if ($request->refresh != 'true') {
            $uploadedFile = $request->file('image');
            $filename = 'senasir.' . $uploadedFile->getClientOriginalExtension();
            Storage::disk('local')->putFileAs(
                'senasir/' . now()->year,
                $uploadedFile,
                $filename
            );
        }
        Excel::import(new EcoComImportSenasir, 'senasir/' . now()->year . '/senasir.xlsx');
        $eco_com_procedure_id = Util::getEcoComCurrentProcedure()->first();
        $no_import = EconomicComplement::with('eco_com_beneficiary')->select('economic_complements.*')
            ->leftJoin('affiliates', 'economic_complements.affiliate_id', '=', 'affiliates.id')
            ->where('eco_com_procedure_id', $eco_com_procedure_id)
            ->where('rent_type', '<>', 'Automatico')
            ->where('rent_type', '<>', 'Manual')
            ->where('affiliates.pension_entity_id', 5)
            ->get();
        return array_merge(session()->get('senasir_data'), ['not_found' => $no_import]);

        // return session()->get('senasir_data');
    }
    public function importAPS(Request $request)
    {
        $success = 0;
        $not_found = collect([]);
        $not_found_db = collect([]);
        $not_has_eco_com = collect([]);
        $sw_refresh = false;
        $eco_com_procedure_id = Util::getEcoComCurrentProcedure()->first();
        // $sw_override = false;
        if ($request->refresh == 'true') {
            $sw_refresh = true;
        }
        // if ($request->override == 'true') {
        //     $sw_override = true;
        // }
        switch ($request->type) {
            case 'vejez':
                if (!$sw_refresh) {
                    $uploadedFile = $request->file('vejez');
                    $filename = 'aps-vejez.' . $uploadedFile->getClientOriginalExtension();
                    Storage::disk('local')->putFileAs(
                        'aps/' . now()->year,
                        $uploadedFile,
                        $filename
                    );
                };
                Excel::import(new EcoComImportAPS, 'aps/' . now()->year . '/aps-vejez.csv');
                $data = session()->get('aps_data');
                $collect = collect([]);
                $process = collect([]);
                foreach ($data as $d1) {
                    $temp = $d1;
                    // [34] PTC_DERECHOHABIENTE
                    if ((is_null($d1[34]) || $d1[34] == 'C') && !$process->contains($d1[0])) {
                        foreach ($data as $d2) {
                            // if ($d1[3] == $d2[3] && $d1[10] == $d2[10] && ($d2[34] == 'C' || is_null($d2[34])) && $d1[0] != $d2[0]) {
                            if ($d1[3] == $d2[3] && ($d2[34] == 'C' || is_null($d2[34])) && $d1[0] != $d2[0]) {
                                $temp[13] =  Util::verifyAndParseNumber($temp[13]) + Util::verifyAndParseNumber($d2[13]); //TOTAL_CC
                                $temp[19] =  Util::verifyAndParseNumber($temp[19]) + Util::verifyAndParseNumber($d2[19]); //TOTAL_FSA
                                $temp[25] =  Util::verifyAndParseNumber($temp[25]) + Util::verifyAndParseNumber($d2[25]); //TOTAL_FS
                                $process->push($d2[0]);
                            }
                        }
                        $temp[13] = Util::verifyAndParseNumber($temp[13]);
                        $temp[19] = Util::verifyAndParseNumber($temp[19]);
                        $temp[25] = Util::verifyAndParseNumber($temp[25]);
                        $collect->push($temp);
                    }
                }
                $eco_coms = EconomicComplement::with('affiliate')
                    ->select('economic_complements.*')
                    ->leftJoin('affiliates', 'economic_complements.affiliate_id', '=', 'affiliates.id')
                    ->where('affiliates.pension_entity_id', '<>', 5)
                    ->where('eco_com_procedure_id', $eco_com_procedure_id)
                    ->NotHasEcoComState(1, 6)
                    ->get();
                foreach ($eco_coms as $e) {
                    foreach ($collect as $c) {
                        $affiliate_ci_eco_com = explode("-", ltrim($e->affiliate->identity_card, "0"))[0];
                        // $affiliate_ci_eco_com = ltrim($e->affiliate->identity_card, "0");
                        $ci_aps = explode("-", ltrim($c[10], "0"))[0];
                        // $ci_aps = ltrim($c[10], "0");
                        // if ($ci_aps == $affiliate_ci_eco_com && $c[3] == $e->affiliate->nua) {
                        if ($c[3] == $e->affiliate->nua) {
                            // if ($e->aps_total_cc <> round($c[13], 2) || $e->aps_total_fsa <> round($c[19], 2) || $e->aps_total_fs <> round($c[25], 2)) {
                            // if ($sw_override) {
                            $e->aps_total_cc = round($c[13], 2);
                            $e->aps_total_fsa = round($c[19], 2);
                            $e->aps_total_fs = round($c[25], 2);
                            $e->rent_type = 'Automatico';
                            $e->save();
                            $e->calculateTotalRentAps();
                            $success++;
                        }
                    }
                }
                foreach ($collect as $c) {
                    $ci_aps = explode("-", ltrim($c[10], "0"))[0];
                    $affiliate = Affiliate::whereRaw("split_part(ltrim(trim(affiliates.identity_card),'0'), '-', 1) ='" . ltrim(trim($ci_aps), '0') . "'")
                        ->where('nua', $c[3])->first();
                    if ($affiliate) {
                        if (!$affiliate->hasEconomicComplementWithProcedure($eco_com_procedure_id)) {
                            $not_has_eco_com->push($affiliate);
                        }
                    } else {
                        $not_found_db->push($c);
                    }
                }
                $not_found = EconomicComplement::with('eco_com_beneficiary')->select('economic_complements.*')
                    ->leftJoin('affiliates', 'economic_complements.affiliate_id', '=', 'affiliates.id')
                    ->where('eco_com_procedure_id', $eco_com_procedure_id)
                    ->where('affiliates.pension_entity_id', '<>', 5)
                    ->where('rent_type', '<>', 'Automatico')
                    ->where('rent_type', '<>', 'Manual')
                    ->where(function ($query) {
                        $query->whereNull('economic_complements.total_rent')
                            ->orWhere('economic_complements.total_rent', '=', 0);
                    })
                    ->get();

                break;
            case 'invalidez':
                if (!$sw_refresh) {
                    $uploadedFile = $request->file('invalidez');
                    $filename = 'aps-invalidez.' . $uploadedFile->getClientOriginalExtension();
                    Storage::disk('local')->putFileAs(
                        'aps/' . now()->year,
                        $uploadedFile,
                        $filename
                    );
                };
                Excel::import(new EcoComImportAPS, 'aps/' . now()->year . '/aps-invalidez.csv');
                $data = session()->get('aps_data');
                $collect = collect([]);
                $process = collect([]);
                foreach ($data as $d1) {
                    $temp = $d1;
                    if (!$process->contains($d1[0])) {
                        foreach ($data as $d2) {
                            if ($d1[3] == $d2[3] && $d1[0] != $d2[0]) {
                                $temp[16] =  Util::verifyAndParseNumber($temp[16]) + Util::verifyAndParseNumber($d2[16]);
                                $process->push($d2[0]);
                            }
                        }
                        $temp[16] = Util::verifyAndParseNumber($temp[16]);
                        $collect->push($temp);
                    }
                }
                $eco_coms = EconomicComplement::with('affiliate')->select('economic_complements.*')
                    ->leftJoin('affiliates', 'economic_complements.affiliate_id', '=', 'affiliates.id')
                    ->where('affiliates.pension_entity_id', '<>', 5)
                    ->where('eco_com_procedure_id', $eco_com_procedure_id)
                    ->NotHasEcoComState(1, 6)
                    ->get();
                $fails = collect([]);
                foreach ($eco_coms as $e) {
                    foreach ($collect as $c) {
                        $affiliate_ci_eco_com = explode("-", ltrim($e->affiliate->identity_card, "0"))[0];
                        // $affiliate_ci_eco_com = ltrim($e->affiliate->identity_card, "0");
                        $ci_aps = explode("-", ltrim($c[10], "0"))[0];
                        // $ci_aps = ltrim($c[10], "0");
                        // if ($ci_aps == $affiliate_ci_eco_com && $c[3] == $e->affiliate->nua) {
                        if ($c[3] == $e->affiliate->nua) {
                            // if ($e->aps_disability <> round($c[16], 2)) {
                            //     if ($sw_override) {
                            $e->aps_disability = round($c[16], 2);
                            $e->save();
                            $e->calculateTotalRentAps();
                            $success++;
                        }
                    }
                }
                $temp = 0;
                foreach ($collect as $c) {
                    if ($temp > 0) {
                        $ci_aps = explode("-", ltrim($c[10], "0"))[0];
                        $affiliate = Affiliate::whereRaw("split_part(ltrim(trim(affiliates.identity_card),'0'), '-', 1) ='" . ltrim(trim($ci_aps), '0') . "'")
                            ->where('nua', $c[3])->first();
                        if ($affiliate) {
                            if (!$affiliate->hasEconomicComplementWithProcedure($eco_com_procedure_id)) {
                                $not_has_eco_com->push($affiliate);
                            }
                        } else {
                            $not_found_db->push($c);
                        }
                    }
                    $temp++;
                }
                $not_found = EconomicComplement::with('eco_com_beneficiary')->select('economic_complements.*')
                    ->leftJoin('affiliates', 'economic_complements.affiliate_id', '=', 'affiliates.id')
                    ->where('eco_com_procedure_id', $eco_com_procedure_id)
                    ->where('affiliates.pension_entity_id', '<>', 5)
                    ->where('rent_type', '<>', 'Automatico')
                    ->where('rent_type', '<>', 'Manual')
                    ->where(function ($query) {
                        $query->whereNull('economic_complements.total_rent')
                            ->orWhere('economic_complements.total_rent', '=', 0);
                    })
                    ->get();
                break;

            case 'muerte':
                if (!$sw_refresh) {
                    $uploadedFile = $request->file('muerte');
                    $filename = 'aps-muerte.' . $uploadedFile->getClientOriginalExtension();
                    Storage::disk('local')->putFileAs(
                        'aps/' . now()->year,
                        $uploadedFile,
                        $filename
                    );
                };
                Excel::import(new EcoComImportAPS, 'aps/' . now()->year . '/aps-muerte.csv');
                $data = session()->get('aps_data');
                $collect = collect([]);
                $process = collect([]);
                foreach ($data as $d1) {
                    $temp = $d1;
                    if ((is_null($d1[27]) || $d1[27] == 'C') && !$process->contains($d1[0])) {
                        foreach ($data as $d2) {
                            // if ($d1[3] == $d2[3] && $d1[11] == $d2[11] && ($d2[27] == 'C' || is_null($d2[27])) && $d1[0] != $d2[0]) {
                            if ($d1[3] == $d2[3] && ($d2[27] == 'C' || is_null($d2[27])) && $d1[0] != $d2[0]) {
                                $temp[16] =  Util::verifyAndParseNumber($temp[16]) + Util::verifyAndParseNumber($d2[16]);
                                $process->push($d2[0]);
                            }
                        }
                        $temp[16] = Util::verifyAndParseNumber($temp[16]);
                        $collect->push($temp);
                    }
                }
                $eco_coms = EconomicComplement::with('affiliate')->select('economic_complements.*')
                    ->leftJoin('affiliates', 'economic_complements.affiliate_id', '=', 'affiliates.id')
                    ->where('affiliates.pension_entity_id', '<>', 5)
                    ->where('eco_com_procedure_id', $eco_com_procedure_id)
                    ->NotHasEcoComState(1, 6)
                    ->get();
                $fails = collect([]);
                foreach ($eco_coms as $e) {
                    foreach ($collect as $c) {
                        $affiliate_ci_eco_com = explode("-", ltrim($e->affiliate->identity_card, "0"))[0];
                        $ci_aps = explode("-", ltrim($c[11], "0"))[0];
                        // if ($ci_aps == $affiliate_ci_eco_com && $c[3] == $e->affiliate->nua) {
                        if ($c[3] == $e->affiliate->nua) {
                            $e->aps_total_death = round($c[17], 2);
                            $e->save();
                            $e->calculateTotalRentAps();
                            $success++;
                        }
                    }
                }
                $temp = 0;
                foreach ($collect as $c) {
                    if ($temp > 0) {
                        $ci_aps = ltrim($c[11], "0");
                        $affiliate = Affiliate::whereRaw("split_part(ltrim(trim(affiliates.identity_card),'0'), '-', 1) ='" . ltrim(trim($ci_aps), '0') . "'")
                            ->where('nua', $c[3])->first();
                        if ($affiliate) {
                            if (!$affiliate->hasEconomicComplementWithProcedure($eco_com_procedure_id)) {
                                $not_has_eco_com->push($affiliate);
                            }
                        } else {
                            $not_found_db->push($c);
                        }
                    }
                    $temp++;
                }
                $not_found = EconomicComplement::with('eco_com_beneficiary')->select('economic_complements.*')
                    ->leftJoin('affiliates', 'economic_complements.affiliate_id', '=', 'affiliates.id')
                    ->where('eco_com_procedure_id', $eco_com_procedure_id)
                    ->where('affiliates.pension_entity_id', '<>', 5)
                    ->where('rent_type', '<>', 'Automatico')
                    ->where('rent_type', '<>', 'Manual')
                    ->where(function ($query) {
                        $query->whereNull('economic_complements.total_rent')
                            ->orWhere('economic_complements.total_rent', '=', 0);
                    })
                    ->get();
                break;
            default:
                # code...
                break;
        }
        $data = [
            'success' => $success,
            'csvTotal' => $collect->count() - 1,
            'notHasEcoCom' => $not_has_eco_com,
            'notFoundDB' => $not_found_db,
            'notFound' => $not_found,
        ];
        return $data;
        // $no_import = EconomicComplement::with('eco_com_beneficiary')->select('economic_complements.*')
        //     ->leftJoin('affiliates', 'economic_complements.affiliate_id', '=', 'affiliates.id')
        //     ->where('eco_com_procedure_id', $eco_com_procedure_id)
        //     ->where('rent_type','<>','Automatico')
        //     ->where('affiliates.pension_entity_id',5)
        //     ->get();
        // return array_merge(session()->get('senasir_data'), ['not_found'=>$no_import]);
    }
    public function importPagoFuturo(Request $request)
    { DB::beginTransaction();
        $contribution_created = 0;
        $contribution_updated = 0;
        $tramit_number = 0;
        $total_contribution = 0;
        $data = [
            'tramit_number' => $tramit_number,
            'contribution_created'=>$contribution_created,
            'contribution_updated'=>$contribution_updated,
            'total_contribution'=>$total_contribution
        ];
        $current_procedures = $request->ecoComProcedureId;
        $pago_futuro_id = 31;
        $contribution_discontinued_id = 41;
        try{
          $affiliate_has_not_contributions = DB::table('observables')->select('observables.observable_id')->join('affiliates','observables.observable_id','affiliates.id')->join('economic_complements','affiliates.id','economic_complements.affiliate_id')->where('observable_type', 'affiliates')->where('observation_type_id', $contribution_discontinued_id)->whereNull('observables.deleted_at')->whereNull('economic_complements.deleted_at')->where('economic_complements.eco_com_procedure_id','=',$current_procedures)->distinct()->get();
          $observation_disc_con = ObservationType::find($contribution_discontinued_id);
          $eco_com_all = EconomicComplement::select('economic_complements.*')
          ->where('economic_complements.eco_com_procedure_id', $current_procedures)
          ->where('economic_complements.wf_current_state_id',3)
          ->where('economic_complements.eco_com_state_id',16)
          ->whereNotIn('economic_complements.eco_com_modality_id',[3,10,12,11])
          ->whereNull('economic_complements.deleted_at')->get();
          foreach($affiliate_has_not_contributions as $affiliate_discontinued){
          $eco_com_disc_con = $eco_com_all->where('affiliate_id', $affiliate_discontinued->observable_id)->first();
            if($eco_com_disc_con){
              if (!$eco_com_disc_con->hasObservationType($contribution_discontinued_id)) {
                  $eco_com_disc_con->observations()->save($observation_disc_con, [
                      'user_id' => Auth::user()->id,
                      'date' => now(),
                      'message' => "Observación generada desde el afiliado.",
                      'enabled' => true
                  ]);
              }
            }
          }
        $affiliates = DB::table('observables')->select('observables.observable_id')->join('affiliates','observables.observable_id','affiliates.id')->join('economic_complements','affiliates.id','economic_complements.affiliate_id')->where('observable_type', 'affiliates')->where('observation_type_id', $pago_futuro_id)->whereNull('observables.deleted_at')->whereNull('economic_complements.deleted_at')->where('economic_complements.eco_com_procedure_id','=',$current_procedures)->distinct()->get();
        $observation = ObservationType::find($pago_futuro_id);
        foreach ($affiliates as $affiliate) {
            $affiliate_id = $affiliate->observable_id;
            $eco_com = $eco_com_all->where('affiliate_id', $affiliate_id)->first();
            $pension_entity_id = Affiliate::find($affiliate_id)->pension_entity_id;
            if ($eco_com) {
                if (!($pension_entity_id == 5) && !($pension_entity_id == null)){
                         if (!$eco_com->hasObservationType($pago_futuro_id)) {
                             $eco_com->observations()->save($observation, [
                                 'user_id' => Auth::user()->id,
                                 'date' => now(),
                                 'message' => "Observación generada desde el afiliado.",
                                 'enabled' => true
                             ]);
                          }
                          $eco_com->calculateTotalRentAps();
                          $total_rent = $eco_com->total_rent;
                          if ($total_rent > 0){
                              $total = round($total_rent * 2.03 / 100, 2);
                              $aux = $total * 6;
                              $discount_type = DiscountType::findOrFail(7);
                              //registro o actualizacion del descuento
                                if ($eco_com->discount_types->contains($discount_type->id)) {
                                    $eco_com->discount_types()->updateExistingPivot($discount_type->id, ['amount' => $aux, 'date' => now()]);
                                } else {
                                    $eco_com->discount_types()->save($discount_type, ['amount' => $aux, 'date' => now()]);
                                }
                                //registro de aportes en la tabla contribution_passives
                                $user_id = Auth::user()->id;
                                $import_contribution = DB::select("select import_contribution_eco_com($user_id,$current_procedures,$eco_com->id)");
                                DB::commit();
                                if(!is_null($import_contribution[0]->import_contribution_eco_com)){
                                    $import_contribution = explode(',',$import_contribution[0]->import_contribution_eco_com);
                                    $tramit_number = $tramit_number + $import_contribution[0];
                                    $contribution_created = $contribution_created + $import_contribution[1];
                                    $contribution_updated = $contribution_updated + $import_contribution[2];
                                    $total_contribution = $contribution_created + $contribution_updated;
                                    $data = [
                                        'tramit_number' => $tramit_number,
                                        'contribution_created'=>$contribution_created,
                                        'contribution_updated'=>$contribution_updated,
                                        'total_contribution'=>$total_contribution
                                    ];
                                    if(filter_var($import_contribution[3], FILTER_VALIDATE_BOOLEAN)){
                                        $month = Carbon::parse($import_contribution[4]);
                                        $month = $month->formatLocalized('%B');
                                             return response()->json([
                                            'status' => 'error',
                                            'errors' => ['El afiliado con Nup:'.$affiliate_id.' tiene registro de aportes en el mes de '.$month.' con origen senasir.'],
                                            'data'=> $data
                                        ], 422);
                                    }
                                }
                          }
                }else{
                    return response()->json([
                        'status' => 'error',
                        'errors' => ['El afiliado con Nup:'.$affiliate_id.' tiene registrado como Ente Gestor Senasir ò no se tiene un registro.'],
                        'data'=> $data

                    ], 422);
                }
            }
        }
        session()->put('pago_futuro_data', $data);
        return session()->get('pago_futuro_data');
       }catch (\Exception $e) {
            DB::rollback();
            return $e;
       }
    }
    public function updatePaidBank(Request $request)
    {
        if ($request->refresh != 'true') {
            $uploadedFile = $request->file('image');
            $filename = 'pago_banco.' . $uploadedFile->getClientOriginalExtension();
            Storage::disk('local')->putFileAs(
                'pago_banco/' . now()->year,
                $uploadedFile,
                $filename
            );
        }
        Excel::import(new EcoComUpdatePaidBank, 'pago_banco/' . now()->year . '/pago_banco.csv');
        return session()->get('pago_banco_data');
    }
}
