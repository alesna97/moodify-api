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
        $app->post('/login', function(Request $request, Response $response){
            $username = $_POST['username'];
            $password = md5($_POST['password']);
            //$password = md5($request->getQueryParam('password'));
            if ((empty($username)) || (empty($password))){
                return $response->withJson(["success" => "0",
                                            "message" => "username masih kosong"]);
            }

            $sql = "SELECT * FROM user WHERE username=:username AND password=:password";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([":username" => $username,
                            ":password" => $password]);
            $result = $stmt->fetch();
            try{
                if($result){
                    return $response->withJson(["success" => "1",
                                                "message" => "Selamat Datang ".$username."", 
                                                "id" => $result['id'],
                                                "username" => $result['username'],200]);
                }  else{
                    return $response->withJson(["success" => "0",
                                                "message" =>"username atau password tidak ditemukan"]);
                }
            }catch(Exception $e){   
                return $response->withJson(["status" => "error",
                                            "message" => $e->getMessage()]);
            }
        });

        //REGISTER
        $app->post('/register', function (Request $request, Response $response){
            $username = $_POST['username'];
            $password = md5($_POST['password']);
            $email = $_POST['email'];
            $confirm_password = md5($_POST['confirm_password']);
            $tgl_lahir = $_POST['tgl_lahir'];
            $data = [
                    ":username" => $username,
                    ":email" => $email,
                    ":password" => $password,
                    ":tgl_lahir" => $tgl_lahir
                    ];
                    if ((empty($username))) {
                        return $response->withJson(["success" => "0",
                                                    "message" => "username tidak boleh kosong"]);
                    } else if ((empty($password))) {
                        return $response->withJson(["success" => "0",
                                                    "message" => "password tidak boleh kosong"]);
                         die(json_encode($response));
                     } else if ((empty($confirm_password)) || $password != $confirm_password) {
                        return $response->withJson(["success" => "0",
                                                    "message" => "confirm password tidak sesuai"]);
                     }  else {
                        if (!empty($username) && $password == $confirm_password){
                            $sql = "SELECT * FROM user WHERE username='".$username."'";
                            $stmt = $this->db->prepare($sql);
                            $result = $stmt->fetch();
                            if($result == 0){
                                $sql = "INSERT INTO user (username,email,password,tgl_lahir,created_at,updated_at) VALUE (:username,:email,:password,:tgl_lahir,now(),now())";
                                $stmt = $this->db->prepare($sql);
                                try{
                                    if($stmt->execute($data)){
                                        return $response->withJson(["success" => "1", "message" => "Pendaftaran berhasil", 200]);
                                    }
                                }catch(Exception $e){
                                    return $response->withJson(["success"=>"0",
                                                                "message" => $e->getMessage()]);
                                }
                            }else{
                                return $response->withJson(["success" => "0",
                                                            "message" => "username sudah terdaftar"]);
                            }
                     }
                    }
        });

        //USER DATA
        $app->get('user/{username}', function($request,$response,$args){
            $user = $args['username'];

        });

    });
    
};
