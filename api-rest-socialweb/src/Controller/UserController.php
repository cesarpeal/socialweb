<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Email;

use App\Entity\User;
use App\Entity\Friend;
use App\Entity\Page;
use App\Entity\Foto;
use App\Services\JwtAuth;

class UserController extends AbstractController
{
    private function resjson($data){
        // Serializar datos con servicio de serializer
        $json = $this->get('serializer')->serialize($data, 'json');

        // Response con httpfoundation
        $response = new Response();

        // Asignar contenido a la respuesta
        $response->setContent($json);

        //Indicar formato de respuesta
        $response->headers->set('Content-Type', 'application/json');

        //Devolver la respuesta
        return $response;
    }

    public function register(Request $request) {
        //Recogemos el json por post
        $json = $request->get('json', null);

        $params = json_decode($json);

        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'El usuario no se ha creado'
        ];

        if($json != null){
            $nombre = (!empty($params->nombre)) ? $params->nombre : null;
            $apellidos = (!empty($params->apellidos)) ? $params->apellidos : null;
            $direccion = (!empty($params->direccion)) ? $params->direccion : null;
            $telefono = (!empty($params->telefono)) ? $params->telefono : null;
            $email = (!empty($params->email)) ? $params->email : null;
            $password = (!empty($params->password)) ? $params->password : null;

            $validator = Validation::createValidator();
            $validate_email = $validator->validate($email, [
                new Email()
            ]);
        }

        if(!empty($email) && count($validate_email) == 0 && !empty($password) && !empty($nombre) && !empty($apellidos)) {
            $user = new User();
            $user->setNombre($nombre);
            $user->setApellidos($apellidos);
            $user->setEmail($email);
            $user->setDireccion($direccion);
            $user->setTelefono($telefono);
            $user->setCreatedAt(new \Datetime('now'));

            //Ciframos la contraseña
            $pwd = hash('sha256', $password);
            $user->setPassword($pwd);

            //Comprobamos que el usuario no existe
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $user_repo = $doctrine->getRepository(User::class);
            
            $isset_user = $user_repo->findBy(['email' => $email]);

            if(count($isset_user) == 0){
                //Persistimos el usuario
                $em->persist($user);
                //Insertamos lo persistido en la bbdd
                $em->flush();

                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Se ha creado el usuario',
                    'user' => $user
                ];

            } else {
                $data = [
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'El usuario ya existe'
                ];
            }
        }

        return $this->resjson($data);
    }

    public function login(Request $request, JwtAuth $jwt_auth) {
        $json = $request->get('json', null);
        $params = json_decode($json);

        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'Error al identificarse'
        ];

        if(!empty($json)){
            $email = (!empty($params->email)) ? $params->email : null;
            $password = (!empty($params->password)) ? $params->password : null;
            $gettoken = (!empty($params->gettoken)) ? $params->gettoken : null;

            $validator = Validation::createValidator();
            $validate_email = $validator->validate($email, [
                new Email()
               
            ]);
        }

        if(!empty($email) && !empty($password) && count($validate_email) == 0){
            $pwd = hash('sha256', $password);

            if($gettoken){
                $signup = $jwt_auth->signup($email, $pwd, $gettoken);
            }else{
                $signup = $jwt_auth->signup($email, $pwd);
            }

            return new JsonResponse($signup);
        }
    }

    public function edit(Request $request, JwtAuth $jwt_auth){
        //Recogemos el token de la cabecera de autenticación
        $token = $request->headers->get('Authorization');

        //Comprobar si el token es correcto
        $authCheck = $jwt_auth->checkToken($token);

        //Respuesta por defecto
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'Usuario no actualizado'
        ];

        //Si el token es correcto entonces se procede a la edición de usuario
        if($authCheck) {
            $em = $this->getDoctrine()->getManager();

            //Conseguir al usuario identificado
            $identity = $jwt_auth->checkToken($token, true);

            //Conseguir al usuario a actualizar completo
            $user_repo = $this->getDoctrine()->getRepository(User::class);
            $user = $user_repo->findOneBy(['id' => $identity->sub]);

            //Recoger los datos por post
            $json = $request->get('json', null);
            $params = json_decode($json);

            if(!empty($json)){
                $nombre = (!empty($params->nombre)) ? $params->nombre : null;
                $apellidos = (!empty($params->apellidos)) ? $params->apellidos : null;
                $email = (!empty($params->email)) ? $params->email : null;
                $direccion = (!empty($params->direccion)) ? $params->direccion : null;
                $telefono = (!empty($params->telefono)) ? $params->telefono : null;

                $validator = Validation::createValidator();
                $validate_email = $validator->validate($email, [
                    new Email()
                ]);

                if(!empty($params->email) && count($validate_email) == 0 && !empty($params->nombre) && !empty($params->apellidos) && !empty($params->direccion) && !empty($params->telefono)){
                    $user->setNombre($nombre);
                    $user->setApellidos($apellidos);
                    $user->setEmail($email);
                    $user->setDireccion($direccion);
                    $user->setTelefono($telefono);
                        
                    //Guardar cambios en la base de datos
                    $em->persist($user);
                    $em->flush();

                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Usuario actualizado',
                        'user' => $user
                    ];
                }
            }
        }
        return $this->resjson($data);
    }

    public function searchUsers($search = null){
        $busqueda = (!empty($search)) ? $search : null;

        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'Error en la búsqueda'
        ];
        
        if($busqueda != null){
            $resultado_users = $this->getDoctrine()->getRepository(User::class)->createQueryBuilder('u')
                                             ->where('u.nombre LIKE :busqueda')
                                             ->orWhere('u.apellidos LIKE :busqueda')
                                             ->setParameter('busqueda', '%'.$busqueda.'%')
                                             ->getQuery()
                                             ->getResult();

            $resultado_pages = $this->getDoctrine()->getRepository(Page::class)->createQueryBuilder('p')
                                             ->where('p.titulo LIKE :busqueda')
                                             ->orWhere('p.descripcion LIKE :busqueda')
                                             ->setParameter('busqueda', '%'.$busqueda.'%')
                                             ->getQuery()
                                             ->getResult();

            $data = [
                'status' => 'success',
                'code' => 200,
                'users' => $resultado_users,
                'pages' => $resultado_pages
            ];
        }
        return $this->resjson($data);
    }

    public function getIdentity(Request $request, JwtAuth $jwt_auth){
        $token = $request->headers->get('Authorization', null);

        $token = str_replace("'", "", $token);
        $identity = $jwt_auth->checkToken($token, true);
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => $identity->sub]);

        $data = [
            'status' => 'success',
            'code' => 200,
            'user' => $user
        ];
        return $this->resjson($data);
    }

    public function getUser(Request $request){
        $json = $request->get('json', null);
        $params = json_decode($json);
        $idUser = $params->idUser;

        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => $idUser]);

        $data = [
            'status' => 'success',
            'code' => 200,
            'user' => $user
        ];

        return $this->resjson($data);
    }

    public function beFriend(Request $request, JwtAuth $jwt_auth){
        $token = $request->headers->get('Authorization', null);
        $json = $request->get('json', null);
        $params = json_decode($json);

        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'error' 
        ];

        $authCheck = $jwt_auth->checkToken($token);

        if($authCheck){
            $identity = $jwt_auth->checkToken($token, true);
            $em = $this->getDoctrine()->getManager();

            $user1 = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => $identity->sub]);
            $user2 = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => $params->target_user]);

            $friend = new Friend();
            $friend->setUser1($user1);
            $friend->setUser2($user2);
            $friend->setBefriends(0);

            $em->persist($friend);
            $em->flush();

            $data = [
                'status' => 'success',
                'code' => 200,
                'friend' => $friend
            ];
        }
        return $this->resjson($data);
    }

    public function getBeFriendPetitions(Request $request, JwtAuth $jwt_auth){
        $token = $request->headers->get('Authorization', null);
        $identity = $jwt_auth->checkToken($token, true);

        $petitions = $this->getDoctrine()->getRepository(Friend::class)->findBy(['user2' => $identity->sub, 'befriends' => 0]);

        $data = [
            'status' => 'success',
            'code' => 200,
            'petitions' => $petitions,
            'number_petitions' => count($petitions)
        ];
        return $this->resjson($data);
    }

    public function manageFriendshipPetition(Request $request, JwtAuth $jwt_auth){
        $token = $request->headers->get('Authorization', null);
        $identity = $jwt_auth->checkToken($token, true);

        $json = $request->get('json', null);
        $params = json_decode($json);
        $idPetition = $params->idPetition;

        $authCheck = $jwt_auth->checkToken($token);
        $petition = $this->getDoctrine()->getRepository(Friend::class)->findOneBy(['id' => $idPetition]);

        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'error'
        ];

        if($authCheck && $petition->getUser2()->getId() == $identity->sub) {
            $em = $this->getDoctrine()->getManager();
            $status = (int)$params->status;

            if($status == 1){
                $petition->setBefriends($status);
                $em->flush();
            } else if($status == 0){
                $em->remove($petition);
                $em->flush();
            }

            $data = [
                'status' => 'success',
                'code' => 200,
                'petition' => $petition
            ];
        }
        return $this->resjson($data);
    }

    public function getPetition(Request $request, JwtAuth $jwt_auth){
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'error'
        ];

        $json = $request->get('json', null);
        $params = json_decode($json);

        $token = $request->headers->get('Authorization', null);
        $identity = $jwt_auth->checkToken($token, true);

        $petition = $this->getDoctrine()->getRepository(Friend::class)->findOneBy(['user1' => $identity->sub, 'user2' => $params->user2]);
        $petition->getBefriends();

        if($petition->getBefriends() == 1){
            $data = [
                'status' => 'success',
                'code' => 200,
                'befriended' => 1
            ]; 
        } else if($petition){
            $data = [
                'status' => 'success',
                'code' => 200,
                'befriended' => 2
            ]; 
        } else {
            $data = [
                'status' => 'success',
                'code' => 200,
                'befriended' => 3
            ];   
        }
        return $this->resjson($data);
    }

    public function getFriends(Request $request, JwtAuth $jwt_auth){
        $json = $request->get('json', null);
        $params = json_decode($json);
        $token = $request->headers->get('Authorization', null);
        $identity = $jwt_auth->checkToken($token, true);

        $user = (!empty($params->userId)) ? $params->userId : $identity->sub;

        $friends1 = $this->getDoctrine()->getRepository(Friend::class)->findBy(['user1' => $user, 'befriends' => 1]);
        $friends2 = $this->getDoctrine()->getRepository(Friend::class)->findBy(['user2' => $user, 'befriends' => 1]);

        $friends = array();

        for($c=0;$c<count($friends1);$c++){
            $friends[$c] = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => $friends1[$c]->getUser2()->getId()]);
        }

        $count_friends = count($friends);
        for($c=0;$c<count($friends2);$c++){
            $friends[$count_friends] = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => $friends2[$c]->getUser1()->getId()]);
            $count_friends++;
        }

        $data = [
            'status' => 'success',
            'code' => 200,
            'friends' => $friends
        ];

        return $this->resjson($data);
    }

    public function getUserImages(Request $request, JwtAuth $jwt_auth){
        $json = $request->get('json', null);
        $params = json_decode($json);
        $token = $request->headers->get('Authorization', null);
        $identity = $jwt_auth->checkToken($token, true);

        $user = (!empty($params->idUser)) ? $params->idUser : $identity->sub;

        $images = $this->getDoctrine()->getRepository(Foto::class)->findBy(['user' => $user]);

        $data = [
            'status' => 'success',
            'code' => 200,
            'images' => $images
        ];

        return $this->resjson($data);
    }

    public function changeProfileImage(Request $request, JwtAuth $jwt_auth){
        $json = $request->get('json', null);
        $params = json_decode($json);
        $token = $request->headers->get('Authorization', null);
        $identity = $jwt_auth->checkToken($token, true);
        $em = $this->getDoctrine()->getManager();

        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => $identity->sub]);
        $image = $params->image;

        $user->setImage($image);
        $em->flush();

        $data = [
            'status' => 'success',
            'code' => 200,
            'user' => $user
        ];

        return $this->resjson($data);
    }
}