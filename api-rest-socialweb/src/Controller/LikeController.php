<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Foto;
use App\Services\JwtAuth;
use App\Entity\User;
use App\Entity\Post;
use App\Entity\Like;

class LikeController extends AbstractController
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

    public function like(Request $request, JwtAuth $jwt_auth){
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'error'
        ];
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $token = $request->headers->get('Authorization', null);
        $authCheck = $jwt_auth->checkToken($token);
        $json = $request->get('json', null);
        $params = json_decode($json);


        if($authCheck){
            $identity = $jwt_auth->checkToken($token, true);
            $user = $doctrine->getRepository(User::class)->findOneBy(['id' => $identity->sub]);
            $post_id = (!empty($params->post)) ? $params->post : null;
            $foto_id = (!empty($params->foto)) ? $params->foto : null;
            $post = null;
            $foto = null;

            if($post_id != null){
                $post = $doctrine->getRepository(Post::class)->findOneBy(['id' => $post_id]);
                $duplicates = $doctrine->getRepository(Like::class)->findOneBy(['post' => $post_id, 'user' => $user]);
            } else if($foto_id != null){
                $foto = $doctrine->getRepository(Foto::class)->findOneBy(['id' => $foto_id]);
                $duplicates = $doctrine->getRepository(Like::class)->findOneBy(['foto' => $foto_id, 'user' => $user]);
            }

            $type = (!empty($params->type)) ? $params->type : null;

            if(!empty($json)){
                if($duplicates == null){

                    if(!empty($type)){
                        $like = new LiKe();
                        $like->setPost($post);
                        $like->setLikeType($type);
                        $like->setUser($user);
                        if($post_id != null){
                            $like->setPost($post);
                        } else if($foto_id != null){
                            $like->setFoto($foto);
                        }

                        $em->persist($like);
                        $em->flush();

                        $data = [
                            'status' => 'success',
                            'code' => 200,
                            'like' => $like->getLikeType()
                        ];
                    }
                } else if($duplicates) {
                    if($duplicates->getLikeType() == $type){
                        $em->remove($duplicates);
                        $em->flush();
                        $data = [
                            'status' => 'success',
                            'code' => 200,
                            'like' => $duplicates->getLikeType(),
                            'message' => 'Ya no me gusta'
                        ];
                    } else {
                        $duplicates->setLikeType($type);
                        $em->persist($duplicates);
                        $em->flush();

                        $data = [
                            'status' => 'success',
                            'code' => 200,
                            'like' => $duplicates->getLikeType()
                        ];
                    }
                }
            }
        }
        return $this->resjson($data);
    }

    public function count_post_likes($post_id = null){
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'error'
        ];

        $count = null;
        $doctrine = $this->getDoctrine();
        $likes = $doctrine->getRepository(Like::class)->findBy(['post' => $post_id]);

        $like = $doctrine->getRepository(Like::class)->findBy(['post' => $post_id, 'likeType' => 'like']);
        $like_love = $doctrine->getRepository(Like::class)->findBy(['post' => $post_id, 'likeType' => 'love']);
        $like_fun = $doctrine->getRepository(Like::class)->findBy(['post' => $post_id, 'likeType' => 'fun']);
        $dislike = $doctrine->getRepository(Like::class)->findBy(['post' => $post_id, 'likeType' => 'dislike']);

        $dislike_count = count($dislike);
        $count = count($likes) - $dislike_count;
        $like_count = count($like);
        $love_count = count($like_love);
        $fun_count = count($like_fun);
        if($count != null){
            $data = [
                'status' => 'success',
                'code' => 200,
                'total likes' => $count,
                'likes' => $like_count,
                'love likes' => $love_count,
                'fun likes' => $fun_count,
                'dislikes' => $dislike_count
            ];
        }


        return $this->resjson($data);
    }

    public function count_foto_likes($foto_id = null){
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'error'
        ];

        $count = null;
        $doctrine = $this->getDoctrine();
        $likes = $doctrine->getRepository(Like::class)->findBy(['foto' => $foto_id]);

        $like = $doctrine->getRepository(Like::class)->findBy(['foto' => $foto_id, 'likeType' => 'like']);
        $like_love = $doctrine->getRepository(Like::class)->findBy(['foto' => $foto_id, 'likeType' => 'love']);
        $like_fun = $doctrine->getRepository(Like::class)->findBy(['foto' => $foto_id, 'likeType' => 'fun']);
        $dislike = $doctrine->getRepository(Like::class)->findBy(['foto' => $foto_id, 'likeType' => 'dislike']);

        $dislike_count = count($dislike);
        $count = count($likes) - $dislike_count;
        $like_count = count($like);
        $love_count = count($like_love);
        $fun_count = count($like_fun);
        $dislike_count = count($dislike);
        if($count != null){
            $data = [
                'status' => 'success',
                'code' => 200,
                'total likes' => $count,
                'likes' => $like_count,
                'love likes' => $love_count,
                'fun likes' => $fun_count,
                'dislikes' => $dislike_count
            ];
        }


        return $this->resjson($data);
    }
}
