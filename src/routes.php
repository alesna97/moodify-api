<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app) {
    $container = $app->getContainer();

    $app->get('/[{name}]', function (Request $request, Response $response, array $args) use ($container) {
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");

        // Render index view
        return $container->get('renderer')->render($response, 'index.phtml', $args);
    });

    $app->group('/api', function() use($app){

        //LOGIN USER
        $app->get('/login', function(Request $request, Response $response){
            $username = $request->getQueryParam('username');
            $sql = "SELECT * FROM user WHERE username=:username";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([":username" => $username]);
            $result = $stmt->fetch();
            try{
                if($result){
                    return $response->withJson(["status" => "success",
                                                "data" => $result, 200]);
                }  else{
                    return $response->withJson(["status" => "error",
                                                "error_message" =>"username tidak ditemukan"]);
                }
            }catch(Exception $e){   
                return $response->withJson(["status" => "error",
                                            "error_message" => $e->getMessage()]);
            }
        });

        //REGISTER
        $app->post('/register', function (Request $request, Response $response){
            $username = $request->getQueryParam('username');
            $email = $request->getQueryParam('email');
            $password = $request->getQueryParam('password');
            $tgl_lahir = $request->getQueryParam('tgl_lahir');
            $data = [
                    ":username" => $username,
                    ":email" => $email,
                    ":password" => md5($password),
                    ":tgl_lahir" => $tgl_lahir
                    ];
                    
            $sql = "INSERT INTO user (username,email,password,tgl_lahir,created_at,updated_at) VALUE (:username,:email,:password,:tgl_lahir,now(),now())";
            $stmt = $this->db->prepare($sql);
            try{
                if($stmt->execute($data)){
                    return $response->withJson(["status" => "success", "data" => $data, 200]);
                }
            }catch(Exception $e){
                return $response->withJson(["status" => $e->getMessage()]);
            }
        });

        //USER DATA
        $app->get('user/{username}', function($request,$response,$args){
            $user = $args['username'];

        });

    });
    
};
