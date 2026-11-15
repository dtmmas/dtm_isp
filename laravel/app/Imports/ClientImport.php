<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\DB;

use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\WithValidation;


class ClientImport implements ToCollection
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection  $rows)
    {
        if(session()->has('validacionExcel')){
            $this->importarUser($rows);
        }else{
            $correos = User::select('email')->pluck('email')->toArray();
            $correos_excel = [];
            foreach ($rows as $row) 
            {
                if(trim($row[2])!=''){
                    if(trim($row[0])=='' || trim($row[1])=='' || trim($row[3])=='' || trim($row[5])=='' || trim($row[6])=='' || trim($row[9])==''){
                        return session()->put('validacionExcel', ['rps'=>false,'msj'=>'Todos los campos son obligatorios.']);
                    }

                    //validad correo
                    if(!in_array(convertir_minusculas($row[2]), array_map("convertir_minusculas", $correos))){
                        if(!in_array($row[2], $correos_excel)){
                            array_push($correos_excel, $row[2]);
                        }else{
                            return session()->put('validacionExcel', ['rps'=>false,'msj'=>'El correo '.$row[2].' esta repetido dentro del excel']);
                        }
                    }else{
                        return session()->put('validacionExcel', ['rps'=>false,'msj'=>'El correo '.$row[2].' ya esta siendo utilizado por otro usuario']);
                    }
                }
            }
           return session()->put('validacionExcel', ['rps'=>true,'msj'=>'1. Validar Información']);
        }
    }

    public function importarUser(Collection  $rows)
    {   $contador=0;
        $item = false;
        foreach ($rows as $row){
            if($item && $row[2]!=''){
                $user = User::create([
                    'name' => $row[0],
                    'lastname' => $row[1],
                    'email' => $row[2],
                    'dni' => $row[3],
                    'address' => $row[4],
                    'phone' => $row[5],
                    'password' => Hash::make($row[6])
                ]);
                
                $user->client()->create([
                    'ip' => $row[7],
                    'clave_wifi' => $row[8],
                    'reference' => $row[9],
                ]);
                
                $user->assignRole('Cliente');
                $user->syncLegacyCollectorFlag();
                $contador++;
            }

            if(trim($row[0])=='' && trim($row[2])=='' && trim($row[1])=='' && trim($row[3])=='' && trim($row[5])=='' ){
                session()->forget('validacionExcel');
                return session()->put('importarUser', ['rps'=>true,'msj'=>'2. Data cargada correctamente. Total registros: '.$contador]);
            }
            $item=true;
        }
        session()->forget('validacionExcel');
        return session()->put('importarUser', ['rps'=>true,'msj'=>'2. Data cargada correctamente. Total registros: '.$contador]);
    }
}
