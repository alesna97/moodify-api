<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
date_default_timezone_set('Asia/Jakarta');

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
                                                "id_user" => $result['id_user'],
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
                    } else if ((empty($email))) {
                        return $response->withJson(["success" => "0",
                                                    "message" => "email tidak boleh kosong"]);
                                                    
                     } else if ((empty($password))) {
                        return $response->withJson(["success" => "0",
                                                    "message" => "password lahir tidak boleh kosong"]);
                     }else if ((empty($tgl_lahir))) {
                        return $response->withJson(["success" => "0",
                                                    "message" => "tanggal lahir tidak boleh kosong"]);
                     }else if ((empty($confirm_password)) || $password != $confirm_password) {
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
        $app->post('/user', function($request,$response){
            $id = $_POST['id'];
            //$id = $request->getQueryParam("id_user");
            
            if (empty($id)){
                return $response->withJson(["success" => "0",
                                            "message" => "id masih kosong"]);
            }
            
            $sql = "SELECT * FROM user WHERE id_user =:id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([":id" => $id]);
            $result = $stmt->fetch();
            
            try{
                if($result){
                return $response->withJson($result, 200);
                }else{
                    return $response->withJson(["status" => "failed",
                                                "message" => "user not found"], 201);
                }
            }catch(Exception $e){
                 return $response->withJson(["status" => "error",
                                            "message" => $e->getMessage()]);
            }
            
        });
        
        //POST HISTORY MOOD
        $app->post('/history-mood', function( $request, $response){
            
            //$id = $request->getQueryParam("id_user");
            //$id_mood = $request->getQueryParam("id_mood");
            //$heart_rate = $request->getQueryParam("heart_rate");
            
            $id = $_POST['id'];
            $id_mood = $_POST['id_mood'];
            $heart_rate = $_POST['heart_rate'];
            $data = [
                    ":id" => $id,
                    ":id_mood" => $id_mood,
                    ":heart_rate" => $heart_rate
                    ];
            if(empty($id)){
                 return $response->withJson(["success" => "0",
                                            "message" => "id masih kosong"]);
            }
            $time = date("h:i a");
            $sql = "INSERT INTO histori_mood (id_user,id_mood,heart_rate,tanggal_mood,waktu_mood)
                    VALUE (:id,:id_mood,:heart_rate,DATE_FORMAT(CURRENT_DATE(), '%W, %M %e'),'".$time."')";
            $stmt = $this->db->prepare($sql);
            try{
                if($stmt->execute($data)){
                    return $response->withJson(["success" => "1", "message" => "saved"], 200);
                }
            }catch(Exception $e){
                return $response->withJson(["success" => "0", "message" => $e], 201);
            }
        });
        
        //GET HISTORY MOOD
        $app->post('/get-history-mood', function( $request, $response){
            //$id = $request->getQueryParam("id_user");
            //$id_mood = $request->getQueryParam("id_mood");
            //$heart_rate = $request->getQueryParam("heart_rate");
            
           $id = $_POST['id'];
            if(empty($id)){
                 return $response->withJson(["success" => "0",
                                            "message" => "id masih kosong"]);
            }
            $sql = "SELECT * FROM histori_mood where id_user = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([":id" => $id]);
            $result = $stmt->fetchAll();
            try{
                if($result != null){
                    return $response->withJson(["message" => "success",
                                                "historylist" => $result], 200);
                }else{
                    return $response->withJson(["message" => "not found"], 201);
                }
            }catch(Exception $e){
                return $response->withJson(["success" => "0", "message" => $e], 401);
            }
        });
        
        $app->post('/send-fcm-notification', function($request,$response){
            $url = "https://fcm.googleapis.com/fcm/send";
            //$token = $request->getQueryParam("token");
            //$heart_rate = $request->getQueryParam("heart_rate");
            
            $token = $_POST['token'];
            $heart_rate = $_POST['heart_rate'];
            $id_mood = $_POST['id_mood'];
            
            $serverKey = "AAAAEOWz5FA:APA91bG2eMbK0diqsCUt94EUrHDEwFXzWtLDCi2jcLAKjDKRsxnwhNaYySFwzJmnnO9lsctk1pnoCZ2D5cbIYzmlKNqcoaXJgjevFAQ_AHrvrVftou0Yf-C-89ljsbElSBQhYOurdfJ9";
            $title = "Mood Detected !";
            $body = "Your heart rate is ".$heart_rate." Bpm, Are you feeling ".$id_mood." today, this is some music for you, Go check it out !";
            $notification = array('title' =>$title , 'body' => $body, 'sound' => 'default', 'badge' => '1');
            $arrayToSend = array('to' => $token, 'notification' => $notification,'priority'=>'high');
            $json = json_encode($arrayToSend);
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Authorization: key='. $serverKey;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
            //Send the request
            $response = curl_exec($ch);
            //Close request
            if ($response === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
            }
            curl_close($ch);
        });
        
    });
    
};
