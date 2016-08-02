<?php
    use Illuminate\Validation\Factory as ValidatorFactory;
    use Symfony\Component\Translation\Translator;
    use Symfony\Component\Translation\MessageSelector;
    use Symfony\Component\HttpFoundation\Request;
    require('../vendor/autoload.php');

    class DbValidator extends \Illuminate\Validation\Validator
    {
        public function __construct($connection, $translator, $data, $rules, $messages, $customAttributes)
        {
            $this->connection = $connection;
            return parent::__construct($translator, $data, $rules, $messages, $customAttributes);
        }

        public function validateExists($attribute, $value, $parameters)
        {
            $this->requireParameterCount(2, $parameters, 'exists');
            $table = $parameters[0];
            $column = $parameters[1];

            $result = $this->_query("select count(*) as value_count from `$table` where `$column` = ?", [$value]);
            return ($result['value_count'] > 0);
        }

        public function validateUnique($attribute, $value, $parameters)
        {
            $this->requireParameterCount(2, $parameters, 'exists');
            $table = $parameters[0];
            $column = $parameters[1];

            $result = $this->_query("select count(*) as value_count from `$table` where `$column` = ?", [$value]);
            return ($result['value_count'] == 0);
        }

        public function _query($query, $bindings)
        {
            $statement = $this->connection->prepare($query);
            $statement->execute($bindings);
            return $statement->fetch();
        }
    }

    function all_routes() {
        return [

            // TODO: move methods to classes and write like
            //       => ['class' => '\App\HomeController',  'method' => 'home'],
            //Openning pages
            '/'                    => ['file' =>'main.php', 'namespace' => 'Controllers\Main', 'function' => 'index', 'alias'=>'main'],
            //Login/logout
            '/login'               => ['file' =>'auth.php', 'namespace' => 'Controllers\Auth', 'function' => 'login',  'alias'=>'login'],
            '/logout'              => ['file' =>'auth.php', 'namespace' => 'Controllers\Auth', 'function' => 'logout', 'alias'=>'logout'],
            '/register'            => ['file' =>'auth.php', 'namespace' => 'Controllers\Auth', 'function' => 'register', 'alias'=>'register'],
        ];
    }

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

    function url($path)
    {
        return ROOT_CATALOGUE.$path;
    }

    class NotFoundException extends Exception
    {
    }

    class InternalServerException extends Exception
    {
    }

    class NotAllowedException extends Exception
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


    function validate_input($data, $rules)
    {
        $translator = new Translator('en_US', new MessageSelector());
        $validatorFactory = new ValidatorFactory($translator);
        $validatorFactory->resolver(function($translator, $data, $rules, $messages, $customAttributes) {
            return new DbValidator(DbModel::getConnection(), $translator, $data, $rules, $messages, $customAttributes);
        });

        $messages = array(
            'email.required' => 'Is required.',
            'email.unique' => 'Must be unique',
            'email.email' => 'Must be email.',

            'login.required' => 'Is required.',
            'login.unique' => 'Must be unique',
            'login.min' => 'Must be at least :min characters.',
            'login.max' => 'Must be no more than :max characters.',

            'real_name.required' => 'Is required.',
            'real_name.min' => 'Must be at least :min characters.',
            'real_name.max' => 'Must be no more than :max characters.',

            'password.required' => 'Is required.',
            'password.min' => 'Must be at least :min characters.',

            'country_id.required' => 'Is required.',
            'country_id.exists'   => 'Is incorect'
        );
        $validator = $validatorFactory->make($data, $rules, $messages);
        if ($validator->fails()) {
            $errors = $validator->messages()->toArray();
            throw new \WrongInputException($errors);
        }
    }

    // TODO: return url($url) => no need to write url(url_for('admin_edit')) in templates
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

    function flash_set($param, $value) {
        $_SESSION['flash'][$param] = $value;
    }

    function flash_has($param) {
        return isset($_SESSION['flash'][$param]);
    }

    function flash_get($param) {
        if (flash_has($param)) {
            $result = $_SESSION['flash'][$param];
            unset($_SESSION['flash'][$param]);
            return $result;
        }
        return null;
    }

    function get_authorized_user() {
        if (!empty($_SESSION['user'])) {
            // get user data from DB by id
            return $_SESSION['user'];
        }
        return null;
    }

    // TODO: use this instead of get_authorized_user
    function get_auth_user() {
        if (!empty($_SESSION['user'])) {
            return \User::find($_SESSION['user']['id']);
        }
        return null;
    }

    /* middlewares begin */
        function validate_authorized()
        {
            if (!get_authorized_user())
            {
                throw new NotAuthorizedException();
            }
        }

    function dd($var) {
        var_dump($var);die();
    }
    /* middlewares end */