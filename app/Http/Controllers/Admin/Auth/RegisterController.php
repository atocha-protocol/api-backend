<?php


namespace App\Http\Controllers\Admin\Auth;

//use \Illuminate\Contracts\Validation\Validator;
use Backpack\CRUD\app\Http\Controllers\Auth\RegisterController as BackpackRegisterController;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Routing\Controller;
use Validator;

class RegisterController extends BackpackRegisterController {

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        //
        echo 'hello';
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $user_model_fqn = config('backpack.base.user_model_fqn');
        $user = new $user_model_fqn();
        $users_table = $user->getTable();
        $email_validation = backpack_authentication_column() == 'email' ? 'email|' : '';

        return Validator::make($data, [
            'name'                             => 'required|max:255',
            backpack_authentication_column()   => 'required|'.$email_validation.'max:255|unique:'.$users_table,
            'password'                         => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     *
     * @return User
     */
    protected function create(array $data)
    {
        $user_model_fqn = config('backpack.base.user_model_fqn');
        $user = new $user_model_fqn();


        // Check 0c9e8dc8f3b44562e2804ab8ba87c68d
        if('0c9e8dc8f3b44562e2804ab8ba87c68d' != md5($data['authorization_code'])) {
            echo 'authorization_code error';
            exit();
        }

        return $user->create([
            'name'                             => $data['name'],
            backpack_authentication_column()   => $data[backpack_authentication_column()],
            'password'                         => bcrypt($data['password']),
        ]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {

        // if registration is closed, deny access
        if (! config('backpack.base.registration_open')) {
            abort(403, trans('backpack::base.registration_closed'));
        }

        try{
            $this->validator($request->all())->validate();
        }catch (\Exception $e){
            print_r($e->getMessage() );
        }

        $user = $this->create($request->all());

        event(new Registered($user));
        $this->guard()->login($user);

        return redirect($this->redirectPath());
    }

}
