<?php


require __DIR__.'/functions.php';
require __DIR__.'/autoload.php';


define('ROOT_CATALOGUE', ''); // leave empty if site lays in the server root
define('BASE_DIR', __DIR__);
define('ENV', 'dev');

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
    catch (NotAllowedException $e)
    {
        $response = ['code' => 403, 'body' => view('errors/403')];
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

    catch (Exception $e)
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == strtolower('xmlhttprequest'))) {
            $error = (ENV == 'dev') ? $e->getMessage()."\n".$e->getTraceAsString() : 'Sorry, some error occured';
            $response = [
                'code' => 500,
                'body' => json_encode(['error' => $error])
            ];
        }
        else {
            $body = (ENV == 'dev') ? "<pre>".$e->getMessage()."\n".$e->getTraceAsString()."</pre>" : view('errors/500');
            $response = ['code' => 500, 'body' => $body];
        }
    }

    return $response;
}