<?php

namespace App\Http\Controllers;

use App\Events\AddLogs;

use App\Events\WasBanned;
use App\Events\WasUnbanned;
use App\Models\Logs;
use App\Models\Logs_safety;
use App\Models\Permission;
//use App\Models\Role;
use App\Ref_opo;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PDF;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use function Sodium\compare;

class AdminController extends Controller
{
    // Запись логов
    public static function log_record($message)
    {
        $ip = request()->ip();
        event(new WasUnbanned(Auth::user()->name,  $message, $ip));  //пишем в журнал
    }

    // Вывод логов
    public function log_view()
    {
       // AdminController::log_record('Открыл журнал ИБ для просмотра  ');//пишем в журнал
       // return view('admin.admin', ['logs' => Logs::orderBy('id', 'desc')->paginate(15)]);

        return view('web.admin.admin_main', ['logs' => Logs::orderBy('id', 'desc')->paginate(20), 'all_logs' => Logs::orderBy('id')->get()]);
    }

//    // Конфигурация безовасности
//    public function config_view()
//    {
//        $config = Logs_safety::get();
//        AdminController::log_record('Открыл для просмотра конфигурацию безопасности');
//        return view('web.config_safety.show', compact('config'));
//    }
    //редактирование настроек безопасности
    public function config_edit()
    {
        $text = "";
        $config = Logs_safety::first();
        AdminController::log_record('Открыл для просмотра конфигурацию безопасности');
        return view('web.config_safety.edit', compact('config', 'text'));
    }
    //обновление настроек
    public function config_update(Request $request)
    {
        $this->validate($request, [
            'num_znak' => 'required|numeric|min:1',
            'num_error' => 'required|numeric|min:1',
            'time_ban' => 'required|numeric|min:1',
            'num_password' => 'required|numeric|min:1',
            'time_session' => 'required|numeric|min:1',
            'time_password' => 'required|numeric|min:1',
        ]);
        $input = $request->all();
        $config = Logs_safety::first();
        $config->update($input);
        AdminController::log_record('Сохранил после изменения конфигурацию безопасности');
        $text = "Конфигурация безопасности успешно обновлена!";
        return view('web.config_safety.edit', compact('config', 'text'));
//        return redirect('/admin/config_safety', compact('text'));
    }

    // Вывод Пользователей
    public function user_view()
    {
        return view('admin.user_view', ['users' => User::all()]);
    }

// Вывод ролей
    public function role_view()
    {

        return view('admin.role_view', ['roles' => Role::orderBy('id')->get()]);
    }

    // Вывод привелегий
    public function perm_view()
    {
        return view('admin.perm_view', ['perms' => Permission::orderBy('id')->get()]);
    }

    // Выгрузка логов
    public function pdf_logs()
    {

        $data['title'] = 'Журнал событий';
        $data['logs'] = Logs::orderBy('id', 'desc')->get();
        $patch = 'logs' . Carbon::now() . '.pdf';
        $ip = request()->ip();
        event(new AddLogs(Auth::user()->name, $patch, $ip));  //пишем в журнал
        $pdf = PDF::loadView('admin.logs_pdf', $data);

        return $pdf->download($patch);
    }
    // Удаление логов
    public function clear_logs()
    {

        Logs::truncate();
        $this->log_record('Очистил журнал событий ИБ');//пишем в журнал

        return redirect('/admin');
    }

    // Добавить нового пользователя
    public function reg_user()
    {

        return view('admin.new_user', ['roles' => Role::all()]);
    }

    // Сохранить нового пользователя
    public function add_user(Request $request)
    {


        $this->validate($request, [
            'name' => 'required',
            'surname' => 'required',
            'middle_name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'required'
        ]);
        $input = $request->all();
        $input['password'] = Hash::make($input['password']);
        $user = User::create($input);
        $user->assignRole($request->input('role'));


        $this->log_record('Добавил пользователя '.$user->name. 'с ролью '.$request->input('role'));//пишем в журнал

        return redirect('/admin/users');
    }

    // Удалить пользователя
    public function destroy_user($id)
    {
        $user = User::find($id);
        $this->log_record('Удалил пользователя '.$user->name);//пишем в журнал
        DB::delete('delete from users where id = ?', [$id]);

        return redirect('/admin/users');
    }

    // Редактировать пользователя
    public function edit_user($id)
    {
        $user = User::find($id);
        return view('admin.edit_user', ['users' => $user, 'roles' => Role::all()]);
    }

    // Сохранить редактирование пользователя
    public function update_user(Request $request)
    {

        $id = $request->input('id');
        $this->validate($request, [
            'name' => 'required',
            'surname' => 'required',
            'middle_name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'required'
        ]);

        $input = $request->all();
        if(!empty($input['password'])){
            $input['password'] = Hash::make($input['password']);
        }else{
            $input = Arr::except($input,array('password'));
        }

        $user = User::find($id);
        $user->update($input);
        DB::table('model_has_roles')->where('model_id',$id)->delete();

        $user->assignRole($request->input('role'));
        $this->log_record('Изменил данные пользователя '.$request->input('name'));//пишем в журнал
        return redirect('/admin/users');
    }

    //Блокировка пользователя
    public function ban1_user($id)
    {
        $user = User::find($id);
        $user->ban();
        $this->log_record('Заблокировал пользователя '.$user->name);//пишем в журнал
        return redirect('/users');
    }

    //Разблокировка пользователя

    public function unban_user($id)
    {
        $user = User::find($id);
        $user->unban();
        $this->log_record('Разблокировал пользователя '.$user->name);//пишем в журнал
        return redirect('/users');
    }

    // Добавить новую роль пользователя
    public function reg_role()
    {

        return view('admin.new_role', ['update' => 0, 'perms' => Permission::all()]);
    }

    // Сохранить новую роль пользователя
    public function add_role(Request $request)
    {
      $role = new Role(
            ['name' => $request->input('name'),
                'slug' => $request->input('slug'),
             ]);
     $role->save();
        $this->log_record('Добавил роль '.$role->name);//пишем в журнал
      return redirect('/admin/roles');
    }
    // Удалить пользователя
    public function destroy_role($id)
    {
        $role = Role::find($id);
        $this->log_record('Удалил роль '.$role->name);//пишем в журнал
        DB::delete('delete from roles where id = ?', [$id]);

        return redirect('/admin/roles');
    }

    // Редактировать пользователя
    public function edit_role($id)
    {
        $role = Role::find($id);
        return view('admin.new_role', ['roles' => $role, 'update' => 1]);
    }

    // Сохранить редактирование пользователя
    public function update_role(Request $request)
    {

        // $user = User::find($request->input('id'));

        Role::whereId($request->input('id'))->update([
            'name' => $request->input('name'),
            'slug' => $request->input('slug'),

        ]);

        $this->log_record('Изменил роль '. $request->input('name'));//пишем в журнал
        return redirect('/admin/roles');
    }

    public function xml_view ()
    {
        $ver_opo =  Ref_opo::find(1);
        $contents = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?> \n ";
        $contents = $contents."<do id = \"gda\">\n";
        $contents = $contents."<opo>\n";
        $contents = $contents."<name>".$ver_opo->fullDescOPO."</name>\n";
        $contents = $contents."<regnumder>".$ver_opo->regNumOPO."</regnumder>\n";
        $contents = $contents."<ip_reackt>".$ver_opo->opo_to_calc1->first()->ip_opo."</ip_reackt>\n";
        $contents = $contents."<status>".$ver_opo->opo_to_calc1->first()->calc_to_status->status."</status>\n";
        $contents = $contents."</opo>\n";
        $contents = $contents."<date>".date("m-d-y")."</date>\n";
        $contents = $contents."<time>".date("H:i:s")."</time>\n";
        $contents = $contents."</do>";


//       Storage::disk('remote-sftp')->put('15_min.xml', $contents, 'public');
       Storage::disk('remote-sftp')->put('15_min.xml', $contents, 'public');
     //  Storage::disk('local')->put('15_min.xml', $contents, 'public');

    }

}
