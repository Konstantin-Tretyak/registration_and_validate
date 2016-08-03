<?php

namespace Controllers\Auth;

function login()
{
    if ($_SERVER['REQUEST_METHOD'] == 'GET')
    {
        if (get_authorized_user())
        {
            return redirect_back();
        }

        return view('auth/login');
    }
    elseif ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        validate_input($_POST, ['login'=>['required'], 'password'=>['required']]);
        $user = \User::query()->where("(login = :login AND password = :password) OR (email = :login AND password = :password)",
                                      ['login' => $_POST['login'],
                                       'password' => crypt_password($_POST['password'])
                                      ])
                                      ->first();

        if ($user)
        {
            $_SESSION['user'] = $user->toArray();
            return redirect(url_for('main'));
        }
        else
            throw new \WrongInputException(['login' => ['Wrong login-password pair']]);
    }
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

        $_POST['password'] = crypt_password($_POST['password']);
        $user = \User::create($_POST, array('time_created'=>date('Y-m-d H:i:s')));

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
