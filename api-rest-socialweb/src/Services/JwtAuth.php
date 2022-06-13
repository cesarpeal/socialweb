<?php
namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Entity\User;

class JwtAuth {
	public $manager;
	public $key;

	public function __construct($manager) {
		$this->manager = $manager;
		$this->key = 'ola esta es una api de red social 3241714 viva el vino';
	}

	public function signup($email, $password, $gettoken = null){
		//Comprobar si el usuario existe
		$user = $this->manager->getRepository(User::class)->findOneBy([
			'email' => $email,
			'password' => $password
		]);

		$signup = false;
		if(is_object($user)){
			$signup = true;
		}

		//Si existe, generar el token de jwt
		if($signup){
			$token = [
				'sub' => $user->getId(),
				'nombre' => $user->getNombre(),
				'apellidos' => $user->getApellidos(),
				'email' => $user->getEmail(),
				'iat' => time(),
				'exp' => time()+ (7*24*60*60)
			];

			//Comprobar el flag del gettoken, condición
			$jwt = JWT::encode($token, $this->key, 'HS256');

			if(!empty($gettoken)){
				$data = $jwt;
			}else{
				$decoded = JWT::decode($jwt, new Key($this->key, 'HS256'));
				$data = $decoded;
			}
		}else{
			$data = [
				'status' => 'error',
				'message' => 'Login incorrecto'
			];
		}

		//Devolver datos
		return $data;
	}

	public function checkToken($jwt, $identity = false) {
		$auth = false;

		try{
		$decoded = JWT::decode($jwt, new Key($this->key, 'HS256'));
		}catch(\UnexpectedValueException $e){
			$auth = false;
		}catch(\DomainException $e){
			$auth = false;
		}

		if(isset($decoded) && !empty($decoded) && is_object($decoded) && isset($decoded->sub)){
			//Si el token contiene algo nos devolverá true
			$auth = true;
		} else {
			$auth = false;
		}

		if($identity != false){
			//Aquí sacamos al usuario identificado si le pasamos algo en identity que no sea false
			return $decoded;
		} else {
			//Con esto damos el visto bueno (o no) a la autenticación
			return $auth;
		}
	}
}