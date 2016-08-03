<?php
    ///start route functions
        function all_routes()
        {
            return [
                //Openning page
                '/'                    => ['file' =>'main.php', 'namespace' => 'Controllers\Main', 'function' => 'index', 'alias'=>'main'],
                //Login/logout/register
                '/login'               => ['file' =>'auth.php', 'namespace' => 'Controllers\Auth', 'function' => 'login',  'alias'=>'login'],
                '/logout'              => ['file' =>'auth.php', 'namespace' => 'Controllers\Auth', 'function' => 'logout', 'alias'=>'logout'],
                '/register'            => ['file' =>'auth.php', 'namespace' => 'Controllers\Auth', 'function' => 'register', 'alias'=>'register'],
            ];
        }

        function get_route()
        {
            $url_without_params = strtok($_SERVER["REQUEST_URI"],'?');

            foreach(all_routes() as $url_template => $route_params)
            {
                if ($url_template == $url_without_params)
                {
                    return $route_params;
                }
            }

            return null;
        }
    ///end route functions

    ///start url functions
        function url($path)
        {
            return ROOT_CATALOGUE.$path;
        }

        function url_for($alias)
        {
            foreach (all_routes() as $url => $params)
            {
                if (isset($params['alias']) && $params['alias'] == $alias)
                {
                    return $url;
                }
            }

            throw new Exception('Wrong alias '+$alias);
        }
    ///end url functions

    ///start exception classes
        class NotFoundException extends Exception
        {
        }

        class WrongInputException extends Exception
        {
            public $errors;

            public function __construct($errors)
            {
                $this->errors = $errors;
            }
        }

        class NotAuthorizedException extends Exception
        {
        }
    ///end exception classes

    ///start redirect functions
        function redirect($url)
        {
            return ['code' => 302, 'headers' => ['Location' => $url]];
        }

        function redirect_back()
        {
            $request_url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

            $url = (isset($_SERVER['HTTP_REFERER']) && ($request_url != $_SERVER['HTTP_REFERER']))
                    ? $_SERVER['HTTP_REFERER']
                    : url_for('main');
            return redirect($url);
        }
    ///end redirect functions

    ///start flash messages
        function flash_set($param, $value)
        {
            $_SESSION['flash'][$param] = $value;
        }

        function flash_has($param)
        {
            return isset($_SESSION['flash'][$param]);
        }

        function flash_get($param)
        {
            if (flash_has($param))
            {
                $result = $_SESSION['flash'][$param];
                unset($_SESSION['flash'][$param]);
                return $result;
            }

            return null;
        }
    ///end flash messages

    ///start authentication function
        ///start User function
            function get_authorized_user()
            {
                if (!empty($_SESSION['user']))
                {
                    // get user data from DB by id
                    return $_SESSION['user'];
                }
                return null;
            }

            function get_auth_user()
            {
                if (!empty($_SESSION['user']))
                {
                    return \User::find($_SESSION['user']['id']);
                }
                return null;
            }

            function validate_authorized()
            {
                if (!get_authorized_user())
                {
                    throw new NotAuthorizedException();
                }
            }
        ///End user function

        ///Start validate input
            use Illuminate\Validation\Factory as ValidatorFactory;
            use Symfony\Component\Translation\Translator;
            use Symfony\Component\Translation\MessageSelector;

            function validate_input($data, $rules)
            {
                $translator = new Translator('en_US', new MessageSelector());
                $validatorFactory = new ValidatorFactory($translator);
                $validatorFactory->resolver(function($translator, $data, $rules, $messages, $customAttributes) {
                    return new DbValidator(DbModel::getConnection(), $translator, $data, $rules, $messages, $customAttributes);
                });

                $messages = array(
                    'email.required' => 'Is required',
                    'email.unique' => 'Must be unique',
                    'email.email' => 'Must be email',

                    'login.required' => 'Is required',
                    'login.unique' => 'Must be unique',
                    'login.min' => 'Must be at least :min characters',
                    'login.max' => 'Must be no more than :max characters',

                    'real_name.required' => 'Is required',
                    'real_name.min' => 'Must be at least :min characters',
                    'real_name.max' => 'Must be no more than :max characters',

                    'password.required' => 'Is required',
                    'password.min' => 'Must be at least :min characters',

                    'country_id.required' => 'Is required',
                    'country_id.exists'   => 'Is incorrect',

                    'birth_date.required' => 'Is required',
                    'birth_date.date_format' => 'Wrong date format',

                    'agree_cond.accepted' => 'Must be checked',
                );
                $validator = $validatorFactory->make($data, $rules, $messages);
                if ($validator->fails()) {
                    $errors = $validator->messages()->toArray();
                    throw new \WrongInputException($errors);
                }
            }
        ///End validate input

        function crypt_password($password)
        {
            return md5($password.SALT);
        }
    ///end authentication function

    ///start functions without categories
        function view($path, $data = null)
        {
            $data['message'] = flash_get('message');

            $data['authorized_user'] = get_authorized_user();
            $data['current_user'] = get_auth_user();

            $old = flash_get('old');
            $data['old'] = $old ? $old : [];

            $errors = flash_get('errors');
            $data['errors'] = $errors ? $errors : [];

            if ($data)
            {
                extract($data);
            }

            ob_start();
            $path = BASE_DIR.'/view/'.$path.'.view.php';
            require BASE_DIR.'/view/'.'/layout.php';
            return ob_get_clean();
        }

        function dd($var)
        {
            var_dump($var);
            die();
        }
    ///End functions without categories