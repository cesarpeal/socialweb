<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Foto;
use App\Entity\User;
use App\Entity\Comment;
use App\Entity\Post;
use App\Services\JwtAuth;

class CommentController extends AbstractController
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

    public function commentPost(Request $request, JwtAuth $jwt_auth, $post_id = null){
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'Error al publicar el comentario'
        ];

        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $token = $request->headers->get('Authorization', null);
        $authCheck = $jwt_auth->checkToken($token);

        if($authCheck) {
            $json = $request->get('json', null);
            $params = json_decode($json);
            $identity = $jwt_auth->checkToken($token, true);

            if(!empty($json)){
                $user_id = (!empty($identity->sub)) ? $identity->sub : null;
                $contenido = (!empty($params->contenido)) ? $params->contenido : null;

                if(!empty($contenido) && !empty($user_id)){
                    $user = $doctrine->getRepository(User::class)->findOneBy(['id' => $user_id]);
                    $post = $doctrine->getRepository(Post::class)->findOneBy(['id' => $post_id]);

                    $comment = new Comment();
                    $comment->setContenido($contenido);
                    $comment->setUser($user);
                    $comment->setCreatedAt(new \Datetime('now'));
                    $comment->setUpdatedAt(new \Datetime('now'));
                    $comment->setPost($post);


                    $em->persist($comment);
                    $em->flush();

                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'comment' => $comment
                    ];
                }
            }
        }
        return $this->resjson($data);
    }

    public function commentFoto(Request $request, JwtAuth $jwt_auth, $foto_id = null){
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'Error al publicar el comentario'
        ];

        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $token = $request->headers->get('Authorization', null);
        $authCheck = $jwt_auth->checkToken($token);

        if($authCheck) {
            $json = $request->get('json', null);
            $params = json_decode($json);
            $identity = $jwt_auth->checkToken($token, true);

            if(!empty($json)){
                $user_id = (!empty($identity->sub)) ? $identity->sub : null;
                $contenido = (!empty($params->contenido)) ? $params->contenido : null;

                if(!empty($contenido) && !empty($user_id)){
                    $user = $doctrine->getRepository(User::class)->findOneBy(['id' => $user_id]);
                    $foto = $doctrine->getRepository(Foto::class)->findOneBy(['id' => $foto_id]);

                    $comment = new Comment();
                    $comment->setContenido($contenido);
                    $comment->setUser($user);
                    $comment->setCreatedAt(new \Datetime('now'));
                    $comment->setUpdatedAt(new \Datetime('now'));
                    $comment->setFoto($foto);


                    $em->persist($comment);
                    $em->flush();

                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'comment' => $comment
                    ];
                }
            }
        }
        return $this->resjson($data);
    }

    public function edit(Request $request, JwtAuth $jwt_auth, $id = null){
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'Error al publicar el comentario'
        ];

        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $token = $request->headers->get('Authorization', null);
        $authCheck = $jwt_auth->checkToken($token);

        if($authCheck) {
            $json = $request->get('json', null);
            $params = json_decode($json);
            $identity = $jwt_auth->checkToken($token, true);
            $comment = $doctrine->getRepository(Comment::class)->findOneBy(['id' => $id]);

            if(!empty($json) && $comment->getUser()->getId() == $identity->sub){
                $contenido = (!empty($params->contenido)) ? $params->contenido : null;

                if(!empty($contenido)){
                    $comment->setContenido($contenido);
                    $comment->setUpdatedAt(new \Datetime('now'));

                    $em->persist($comment);
                    $em->flush();

                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'comment' => $comment
                    ];
                }
            }
        }
        return $this->resjson($data);
    }

    public function delete(Request $request, JwtAuth $jwt_auth, $id = null) {
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'No se ha podido borrar el post'
        ];

        $token = $request->headers->get('Authorization', null);

        $authCheck = $jwt_auth->checkToken($token);

        if($authCheck) {
            $identity = $jwt_auth->checkToken($token, true);

            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();

            $comment = $doctrine->getRepository(Comment::class)->findOneBy(['id' => $id]);

            if($identity->sub == $comment->getUser()->getId() && is_object($comment)) {
                $em->remove($comment);
                $em->flush();
            $data = [
                'status' => 'success',
                'code' => 200,
                'message' => 'Se ha borrado el comentario',
                'comment' => $comment
            ];
            }
        }
        return $this->resjson($data);
    }
}

