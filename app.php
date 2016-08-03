<?php

require __DIR__.'/functions.php';
require __DIR__.'/autoload.php';
require __DIR__.'/vendor/autoload.php';

define('ROOT_CATALOGUE', ''); // leave empty if site lays in the server root
define('BASE_DIR', __DIR__);
define('ENV', 'dev');
define('SALT', '$2y$11$q5MkhSBtlsJcNEVsYh64a.aCluzHnGog7TQAKVmQwO9C8xb.t89F.');

$connection_string = "mysql:host=localhost;dbname=tretyak_test_db";
$user = 'root';
$password = '';
$conn = new PDO($connection_string, $user, $password);

DbModel::setConnection($conn);

function app_run() {
    try
    {
        $route = get_route();

        if ($route = get_route())
        {
            require BASE_DIR.'/controllers/'.$route['file'];
            $handler = $route['namespace'].'\\'.$route['function'];
            $response = $handler();

            if (!is_array($response))
            {
                $response = ['code' => 200, 'body' => $response];
            }
        }
        else
        {
            throw new NotFoundException();
        }
    }
    catch (NotFoundException $e)
    {
        $response = ['code' => 404, 'body' => view('errors/404')];
    }
    catch (WrongInputException $e)
    {
        flash_set('old', $_POST);
        flash_set('errors', $e->errors);
        $response = redirect_back();
    }
    catch (NotAuthorizedException $e)
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == strtolower('xmlhttprequest'))) {
            $response = [
                'code' => 403,
                'body' => json_encode(['error' => 'You are not signed in. Please, sign in and refresh the page'])
            ];
        }
        else {
            flash_set('authorize_return_url', $_SERVER['REQUEST_URI']);
            $response = redirect(url_for('login'));
        }
    }

    return $response;
}