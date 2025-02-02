<?php

use Phalcon\Mvc\Micro;
use Phalcon\Loader;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as PdoMysql;
use Phalcon\Http\Response;

// Use Loader() to autoload our model
$loader = new Loader();
$loader->registerDirs(
    array(
        __DIR__ . '/models/'
    )
)->register();

$di = new FactoryDefault();

// Set up the database service
$di->set('db', function () {
    return new PdoMysql(
        array(
            "host"     => "127.0.0.1",
            "username" => "root",
            "password" => "Eezohng7",
            "dbname"   => "proj"
        )
    );
});

// Create and bind the DI to the application
$app = new Micro($di);


//—------------------------------------------------------------------------------
// Function Name: addCrumb
// Description: Add a crumb to the database
// URL: http://uaf132701.ddns.uark.edu/api/crumb/add
// Method: POST
// Payload: JSON Crumb Object: Fields are:
//    1. user_id
//    2. title
//    2. latitude
//    3. longitude
//    4. text
//-------------------------------------------------------------------------------
$app->post('/api/crumb/add', function () use ($app) {
    // Get the raw JSON content
    $item = $app->request->getJsonRawBody();

    $phql = "INSERT INTO Crumb (user_id, latitude, longitude, text, title) VALUES (:user_id:, :latitude:, :longitude:, :text:, :title:)";
    // Get a collection of users that meet the criteria
    $status = $app->modelsManager->executeQuery($phql, array(
        'user_id' => $item->user_id,
        'latitude' => $item->latitude,
        'longitude' => $item->longitude,
        'title' => $item->title,
        'text' => $item->text
    ));

    // Create a response
    $response = new Response();

    if ($status->success() == true) {
        // Change the HTTP status
        $response->setStatusCode(201, "Created");

        $item->note_id = $status->getModel()->note_id;

        $response->setJsonContent(
            array(
                'status' => 'OK',
                'data'   => $item
            )
        );

    } else {
        // Change the HTTP status
        $response->setStatusCode(409, "Conflict");

        // Send errors to the client
        $errors = array();
        foreach ($status->getMessages() as $message) {
            $errors[] = $message->getMessage();
        }

        $response->setJsonContent(
            array(
                'status'   => 'ERROR',
                'messages' => $errors
            )
        );
    }

    return $response;
});

//-------------------------------------------------------------------------------
// Function Name: getCrumbContent
// Description: Get the content of a single Crumb based on a note_id
// URL: http://uaf132701.ddns.uark.edu/api/crumb/<id:integer>
// Method: GET
// Returns: JSON Object:
//    if success:
//        {
//         “status”:”FOUND”,
//         “data”: “{crumb object}”
//        }
//    if not success:
//        {
//         “status”:”NOT-FOUND”
//        }
//-------------------------------------------------------------------------------
$app->get('/api/crumb/{id:[0-9]+}', function ($id) use ($app) {
	$phql = "SELECT * FROM Crumb WHERE note_id = :id:";

	//Get the list that matches the given list id
	$crumbs = $app->modelsManager->executeQuery($phql, array(
			'id' => $id
	));

	//Create a response to send back to the client
	$response = new Response();
	$crumb = $crumbs->getFirst();

	if($crumb == false) {
	    $response->setJsonContent(
	    array(
	        'status' => 'NOT-FOUND')
	    );
	}
	else {
        /* Here is where we would loop through the results and build
           the JSON objects*/
        $response->setJsonContent(
           array(
                'status' => 'FOUND',
                'data' => array(
                    'note_id' => $crumb->user_id,
                    'user_id' => $crumb->user_id,
                    'latitude' => $crumb->latitude,
                    'longitude' => $crumb->longitude,
                    'title' => $crumb->title,
                    'text' => $crumb->text
                    )
            )
        );
    }

    return $response;
});

//-----------------------------------------------------------------------------
// Function Name: editCrumb
// Description: Edit crumb information. (Title, text)
// URL: http://uaf132701.ddns.uark.edu/api/crumb/edit
// Method: POST
//-----------------------------------------------------------------------------
$app->post('/api/crumb/edit', function () use ($app) {
    // Get the raw JSON content
    $crumb = $app->request->getJsonRawBody();

    $phql = "UPDATE Crumb SET title = :title:, text = :text: WHERE note_id = :id:";
    $status = $app->modelsManager->executeQuery($phql, array(
        'id' => $crumb->note_id,
        'title' => $crumb->title,
        'text' => $crumb->text
    ));

    // Create a response
    $response = new Response();

    if ($status->success() == true) {
        // Change the HTTP status
        $response->setStatusCode(201, "Created");

        $response->setJsonContent(
            array(
                'status' => 'OK',
                'data'   => $crumb
            )
        );

    } else {
        // Change the HTTP status
        $response->setStatusCode(409, "Conflict");

        // Send errors to the client
        $errors = array();
        foreach ($status->getMessages() as $message) {
            $errors[] = $message->getMessage();
        }

        $response->setJsonContent(
            array(
                'status'   => 'ERROR',
                'messages' => $errors
            )
        );
    }

    return $response;
});

//-------------------------------------------------------------------------------
// Function Name: addUser
// Description: Add a user
// URL: http://uaf132701.ddns.uark.edu/api/user/add
// Method: POST
// Payload: JSON User Object: Required Fields are:
//    1. user_name
//    2. first_name
//    3. last_name
//    4. email
//    5. password
// Returns: JSON Object:
//    if success:
//        {
//         “status”:”OK”,
//         “data”: “{User object}”
//        }
//    if not success:
//        {
//         “status”:”ERROR”
//        }
//-------------------------------------------------------------------------------
$app->post('/api/user/add', function () use ($app) {
    // Get the raw JSON content
    $user = $app->request->getJsonRawBody();

    $phql = "INSERT INTO User (user_name, first_name, last_name, email, password) VALUES (:user_name:, :first_name:, :last_name:, :email:, :password:)";
    // Get a collection of users that meet the criteria
    $status = $app->modelsManager->executeQuery($phql, array(
        'user_name' => $user->user_name,
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'email' => $user->email,
        'password' => $user->password
    ));

    // Create a response
    $response = new Response();

    if ($status->success() == true) {
        // Change the HTTP status
        $response->setStatusCode(201, "Created");

        $user->user_id = $status->getModel()->user_id;

        $response->setJsonContent(
            array(
                'status' => 'OK',
                'data'   => $user
            )
        );

    } else {
        // Change the HTTP status
        $response->setStatusCode(409, "Conflict");

        // Send errors to the client
        $errors = array();
        foreach ($status->getMessages() as $message) {
            $errors[] = $message->getMessage();
        }

        $response->setJsonContent(
            array(
                'status'   => 'ERROR',
                'messages' => $errors
            )
        );
    }

    return $response;
});

//-------------------------------------------------------------------------------
// Function Name: getAllCrumbs
// Description: Returns all crumbs in the database, but only their basic
//     information such as, title, bites, rating, etc.
// URL: http://uaf132701.ddns.uark.edu/api/crumb/all
// Method: GET
// Returns: JSON Object:
//    if success:
//        {
//         “status”:”FOUND”,
//         “data[]”: “{crumb objects}”
//        }
//    if not success:
//        {
//         “status”:”NOT-FOUND”
//        }
//
// Notes
// -----
// The crumbs returned are only partial crumbs that do not include user_id or
// text fields.
//-------------------------------------------------------------------------------
$app->get('/api/crumb/all', function () use ($app) {
	$phql = "SELECT * FROM Crumb";

	//Get the list that matches the given list id
	$crumbs = $app->modelsManager->executeQuery($phql);
    
	//Create a response to send back to the client
	$response = new Response();

	if($crumbs == false) {
	    $response->setJsonContent(
	    array(
	        'status' => 'NOT-FOUND')
	    );
	}
	else {
        /* Here is where we would loop through the results and build
           the JSON objects*/
        $data = array();
        foreach ($crumbs as $crumb) {
               $data[] = array(
                   'note_id' => $crumb->note_id,
                   'latitude' => $crumb->latitude,
                   'longitude' => $crumb->longitude,
                   'title' => $crumb->title
               ); 
        }
        $response->setJsonContent(
            array(
	            'status' => 'FOUND',
	            'data' => $data
            )
        );
    }

    return $response;
});



/* Handle a request when the file/function they requested is not 
available.*/
$app->notFound(function () use ($app) {
    $app->response->setStatusCode(404, "Not Found")->sendHeaders();
    echo 'This is crazy, but this page was not found!';
    echo 'I know this made your night you poor developer...';
});

$app->handle();

$app->get('/', function () {
    throw new \Exception("An error");
});

$app->error(
    function ($exception) {
        echo "An error has occurred";
    }
);


