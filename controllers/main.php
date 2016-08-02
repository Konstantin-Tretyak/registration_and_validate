<?php

    namespace Controllers\Main;

    function index()
    {
        validate_authorized();

        $user = get_auth_user();

        return view('main', compact('user'));
    }