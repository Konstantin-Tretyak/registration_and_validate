<?php

namespace Controllers\Auth;

function login()
{
    if ( get_authorized_user() )
    {
        return redirect_back();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
            validate_input($_POST, ['login', 'password']);

            if ( isset( $_POST['action'] ) && $_POST['action'] == "register" )
            {
                $user = \User::create($_POST);
            }
            else {
                $user = \User::query()->where("login = ? AND password = ?", [$_POST['login'],$_POST['password']])->first();
            }
            if ($user)
            {
                $_SESSION['user'] = $user->toArray();

                $redirect_url = flash_has('authorize_return_url') ? flash_get('authorize_return_url') : url_for('main');
                return redirect($redirect_url);
            }
            else {
                throw new \WrongInputException(['login' => 'Wrong login-password pair']);
            }

        // return redirect_back();
    }

    $countries = \Country::query()->all();

    return view('auth/login',  compact('countries'));
}

function register()
{
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $countries = \Country::query()->all();
        return view('auth/register',  compact('countries'));
    }
    elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
        validate_input($_POST, array(
            'email' => ['required', 'email', 'unique:users,login'],
            'login' => ['required', 'min:3', 'max:20', 'unique:users,login'],
            'real_name' => ['required', 'min:3', 'max:20'],
            'password' => ['required', 'min:5'],
            'birth_date' => ['required','date_format:Y-m-d'],
            'country_id' => ['required','exists:countries,id'],
            'agree_cond' => ['accepted']));

        $user = \User::create($_POST);

        $_SESSION['user'] = $user->toArray();

        return redirect(url_for('main'));
    }
}


function logout()
{
    unset($_SESSION['user']);
    $_SESSION['message'] = 'You have logged out';
    return redirect_back();
}
