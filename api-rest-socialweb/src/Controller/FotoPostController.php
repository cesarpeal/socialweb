<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use App\Services\JwtAuth;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\FotoPost;

class FotoPostController extends AbstractController
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

    public function upload(Request $request, SluggerInterface $slugger, JwtAuth $jwt_auth, $post_id = null){

        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'Error al subir la imagen'
        ];
        $doctrine = $this->getDoctrine();
        $token = $request->headers->get('Authorization', null);
        $authCheck = $jwt_auth->checkToken($token);

        $json = $request->get('json', null);
        $params = json_decode($json);

        if($authCheck){
            //Sacamos el archivo
            $file = $request->files->get('filename', null);

            //Con esto renombramos el archivo, y seteamos el nombre para el objeto Foto
            $filename = $file->getClientOriginalName();
            $safeFilename = $slugger->slug($filename);
            $newFilename = uniqid().'-'.$safeFilename.'.'.$file->guessExtension();
            if(!empty($json)){
                $titulo = (!empty($params->titulo)) ? $params->titulo : null;
            }

            //Sacamos al usuario que est?? subiendo la imagen
            $identity = $jwt_auth->checkToken($token, true);
            if($identity){
                $user_id = (!empty($identity->sub)) ? $identity->sub : null;
                $post = $doctrine->getRepository(Post::class)->findOneBy(['id' => $post_id]);
                $post_user = $post->getUser()->getId();

                //Comprobamos que el que sube la foto es el creador del post
                if(!empty($file) && $user_id == $post_user){
                    //Y setteamos la imagen a subir a la bbdd
                    $foto = new FotoPost();
                    if(!empty($json)){
                        $foto->setTitulo($titulo);
                    }
                    $foto->setFilename($newFilename);
                    $foto->setPost($post);
                    $foto->setCreatedAt(new \Datetime('now'));
                    $foto->setUpdatedAt(new \Datetime('now'));

                    //Subimos la imagen al directorio
                    $file->move(
                        $this->getParameter('posts_images'),
                        $newFilename
                    );

                    //Y la metemos en la base de datos
                    $em = $doctrine->getManager();
                    $em->persist($foto);
                    $em->flush();

                    $data = [
                        'status' => 'success',
                        'code' => '200',
                        'image' => $foto
                    ];
                }
            } else {
                $data = [
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'La imagen no corresponde al usuario'
                ];
            }
        }
        return $this->resjson($data);
    }

    public function rename(Request $request, JwtAuth $jwt_auth, $id = null){
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'Error al encontrar la imagen'
        ];

        //Recoger los datos por post
        $json = $request->get('json', null);
        $params = json_decode($json);

        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $token = $request->headers->get('Authorization', null);
        $authCheck = $jwt_auth->checkToken($token);

        if($authCheck){
            $identity = $jwt_auth->checkToken($token, true);
            $foto = $doctrine->getRepository(FotoPost::class)->findOneBy(['id' => $id]);
            $post = $doctrine->getRepository(Post::class)->findOneBy(['id' => $foto->getPost()]);

            if(!empty($json) && $identity->sub == $post->getUser()->getId()){
                $titulo = (!empty($params->titulo)) ? $params->titulo : null;
                if(!empty($params->titulo)){
                    $foto->setTitulo($titulo);
                    $foto->setUpdatedAt(new \Datetime('now'));

                    $em->persist($foto);
                    $em->flush();

                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'foto' => $foto
                    ];
                }
            }
            return $this->resjson($data);
        }
    }

    public function delete(Request $request, JwtAuth $jwt_auth, $id = null) {

        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'Error al encontrar la imagen'
        ];

        $doctrine = $this->getDoctrine();
        $token = $request->headers->get('Authorization', null);
        $authCheck = $jwt_auth->checkToken($token);

        if($authCheck){
            //Sacamos al usuario que est?? subiendo la imagen
            $identity = $jwt_auth->checkToken($token, true);
            $foto = $doctrine->getRepository(FotoPost::class)->findOneBy(['id' => (int)$id]);
            $post = $doctrine->getRepository(Post::class)->findOneBy(['id' => $foto->getPost()->getId()]);

            if($id && $identity->sub == $post->getUser()->getId()) {
                $em = $doctrine->getManager();
                $em->remove($foto);
                $em->flush();

                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'image' => $foto
                ];
            }
        }
        return $this->resjson($data);
    }

    public function getFotosPost($id = null){
        $fotos_post = $this->getDoctrine()->getRepository(FotoPost::class)->findBy(['post' => $id]);

        if($fotos_post){
            $data = [
                'status' => 'success',
                'code' => 200,
                'fotos_post' => $fotos_post 
            ];  
        } else {
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'El post no tiene fotos' 
            ];
        }

        return $this->resjson($data);
    }

    public function getFotoPost($filename = null){
        $path = $this->getParameter('posts_images');

        return new BinaryFileResponse($path.'/'.$filename);
    }

}