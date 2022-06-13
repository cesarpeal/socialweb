<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use App\Services\JwtAuth;
use App\Entity\User;
use App\Entity\Post;
use App\Entity\FotoPost;
use App\Entity\Comment;
use App\Entity\Like;

class PostController extends AbstractController
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

    public function getPost(Request $request){
        $json = $request->get('json', null);
        $params = json_decode($json);
        $id = $params->id;

        $post = $this->getDoctrine()->getRepository(Post::class)->findOneBy(['id' => $id]);
        $fotos_post = $this->getDoctrine()->getRepository(FotoPost::class)->findBy(['post' => $post]);

        $data = [
            'status' => 'success',
            'code' => 200,
            'post' => $post,
            'fotos_post' => $fotos_post
        ];

        return $this->resjson($data);
    }

    public function getPostComments($idPost = null){
        $post_comments = $this->getDoctrine()->getRepository(Comment::class)->findBy(['post' => $idPost]);

        $data = [
            'status' => 'success',
            'code' => 200,
            'comments' => $post_comments
        ];

        return $this->resjson($data);
    }

    public function create(Request $request, JwtAuth $jwt_auth) {
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'No se ha creado el post'
        ];
        $json = $request->get('json', null);
        $params = json_decode($json);

        $token = $request->headers->get('Authorization', null);

        $authCheck = $jwt_auth->checkToken($token);

        if($authCheck){
            $identity = $jwt_auth->checkToken($token, true);

            if(!empty($json)){
                $user_id = (!empty($identity->sub)) ? $identity->sub : null;
                $titulo = (!empty($params->titulo)) ? $params->titulo : null;
                $contenido = (!empty($params->contenido)) ? $params->contenido : null;

                if(!empty($user_id) && !empty($titulo) && !empty($contenido)) {
                    $doctrine = $this->getDoctrine();
                    $em = $doctrine->getManager();

                    $user = $doctrine->getRepository(User::class)->findOneBy(['id' => $user_id]);

                    $post = new Post();
                    $post->setTitulo($titulo);
                    $post->setContenido($contenido);
                    $post->setUser($user);
                    $post->setCreatedAt(new \Datetime('now'));
                    $post->setUpdatedAt(new \Datetime('now'));

                    $em->persist($post);
                    $em->flush();

                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'El post se ha creado correctamente',
                        'post' => $post
                    ];
                }
            }
        }
        return $this->resjson($data);
    }

    public function edit(Request $request, JwtAuth $jwt_auth, $id = null) {
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'No se ha editado el post'
        ];

        $token = $request->headers->get('Authorization', null);

        $authCheck = $jwt_auth->checkToken($token);

        if($authCheck){
            $json = $request->get('json', null);
            $params = json_decode($json);

            $identity = $jwt_auth->checkToken($token, true);

            if(!empty($json)){
                $user_id = (!empty($identity->sub)) ? $identity->sub : null;
                $titulo = (!empty($params->titulo)) ? $params->titulo : null;
                $contenido = (!empty($params->contenido)) ? $params->contenido : null;

                if(!empty($user_id) && !empty($titulo) && !empty($contenido)) {
                    $doctrine = $this->getDoctrine();
                    $em = $doctrine->getManager();

                    $post = $doctrine->getRepository(Post::class)->findOneBy(['id' => $id]);
                    if($post->getUser()->getId() == $identity->sub){
                        $post->setTitulo($titulo);
                        $post->setContenido($contenido);
                        $post->setUpdatedAt(new \Datetime('now'));

                        $em->persist($post);
                        $em->flush();

                        $data = [
                            'status' => 'success',
                            'code' => 200,
                            'message' => 'El post se ha editado correctamente',
                            'post' => $post
                        ];
                    } else {
                        $data = [
                            'status' => 'error',
                            'code' => 400,
                            'message' => 'El usuario no es dueño del post'
                        ];  
                    }
                }
            }
        }
        return $this->resjson($data);
    }

    public function delete(Request $request, JwtAuth $jwt_auth) {
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'No se ha podido borrar el post'
        ];

        $token = $request->headers->get('Authorization', null);
        $json = $request->get('json', null);
        $params = json_decode($json);

        $authCheck = $jwt_auth->checkToken($token);

        if($authCheck) {
            $identity = $jwt_auth->checkToken($token, true);

            $idPost = $params->idPost;

            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();

            $post = $doctrine->getRepository(Post::class)->findOneBy(['id' => $idPost]);
            $comments_post = $doctrine->getRepository(Comment::class)->findBy(['post' => $idPost]);
            $fotos_post = $doctrine->getRepository(FotoPost::class)->findBy(['post' => $idPost]);
            $likes_post = $doctrine->getRepository(Like::class)->findBy(['post' => $idPost]);

            if($identity->sub == $post->getUser()->getId() && is_object($post)) {
                if(count($fotos_post) > 0){
                    for($c=0;$c<count($fotos_post);$c++){
                        $em->remove($fotos_post[$c]);
                    }
                }

                if(count($likes_post) > 0){
                    for($c=0;$c<count($likes_post);$c++){
                        $em->remove($likes_post[$c]);
                    }
                }

                if(count($comments_post) > 0){
                    for($c=0;$c<count($comments_post);$c++){
                        $em->remove($comments_post[$c]);
                    }
                }
                $em->remove($post);
                $em->flush();
                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Se ha borrado el post',
                    'post' => $post
                ];
            } else {
                $data = [
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'El usuario no es dueño del post'
                ];  
            }
        }
        return $this->resjson($data);
    }

    public function getUserPosts(Request $request, JwtAuth $jwt_auth){
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'No se ha podido borrar el post'
        ];

        $json = $request->get('json', null);
        $params = json_decode($json);

        $token = $request->headers->get('Authorization', null);

        $authCheck = $jwt_auth->checkToken($token);
        if($authCheck){
            $identity = $jwt_auth->checkToken($token, true);
            if($params->userId){
                $user_id = $params->userId;
            } else {
                $user_id = $identity->sub;
            }
            

            $limite = $params->limite;
            $posts = $this->getDoctrine()->getRepository(Post::class)->createQueryBuilder('p')
                                                                     ->andWhere('p.user = :identity')
                                                                     ->orderBy('p.createdAt', 'desc')
                                                                     ->setMaxResults($limite)
                                                                     ->setParameter('identity', $user_id)
                                                                     ->getQuery()
                                                                     ->getResult();

            $fotos_posts = array();

            for($i=0;$i<count($posts);$i++){
                $fotos_posts[$i] = $this->getDoctrine()->getRepository(FotoPost::class)->findBy(['post' => $posts[$i]]);
            }

            $data = [
                'status' => 'success',
                'code' => 200,
                'posts' => $posts,
                'fotos_posts' => $fotos_posts
            ];

            return $this->resjson($data);
        }
        return $this->resjson($data);
    }

    public function home(Request $request){
        $json = $request->get('json', null);
        $params = json_decode($json);

        $limite = $params->limite;
        $posts = $this->getDoctrine()->getRepository(Post::class)->createQueryBuilder('p')
                                                                 ->orderBy('p.createdAt', 'desc')
                                                                 ->setMaxResults($limite)
                                                                 ->getQuery()
                                                                 ->getResult();

        $fotos_posts = array();

        for($i=0;$i<count($posts);$i++){
            $fotos_posts[$i] = $this->getDoctrine()->getRepository(FotoPost::class)->findBy(['post' => $posts[$i]]);
        }

        $data = [
            'status' => 'success',
            'code' => 200,
            'posts' => $posts,
            'fotos_posts' => $fotos_posts
        ];

        return $this->resjson($data);
    }
}