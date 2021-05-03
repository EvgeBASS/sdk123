<?php

namespace App\Http\Controllers;

use App\Ref_obj;
use App\Ref_opo;
use Illuminate\Http\Request;

class Tb extends Controller
{
    public function view_elem_tb($id_opo, $id_obj, $id_tb)
    {
        $jas = OpoController::view_jas_15();     // Жас всех ОПО 15 записей
        $ver_opo =  Ref_opo::find($id_opo);  // Ссылка на ОПО
        $all_opo = Ref_opo::all(); //Сыслка на все ОПО
        $elems_opo = $ver_opo->opo_to_obj; // Перечень всех лементов ОПО
        $this_elem = Ref_obj::find($id_obj)->elem_to_tu->where('from_type_obor', '=', $id_tb);  // перечень элементов ТУ входящих в состав ТБ
        $this_elem_apk = Ref_obj::find($id_obj)->elem_to_APK->where('idOTO', '=', $id_tb);  // перечень элементов ТУ входящих в состав ТБ

      //  return $this_elem;
       return view('web.tb', compact('jas', 'ver_opo', 'elems_opo', 'this_elem', 'id_obj', 'this_elem_apk', 'all_opo'));
    }
}