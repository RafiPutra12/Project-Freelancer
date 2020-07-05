<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
	//fungsi untuk register
	public function register(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'firstname'       => 'required|string|max:255',
			'lastname'        => 'required|string|max:255',
			'email'           => 'required|string|email|max:255|unique:users',
			'password'        => 'required|string|min:6',
			'is_freelancer'   => 'required|integer',
			'is_productowner' => 'required|integer',
			'address'         => 'required|string|max:255',
		]);

		if ($validator->fails()) {
			return response()->json([
				'status'	=> 0,
				'message'	=> $validator->errors()->toJson()
			]);
		}

		$user = new User();
		$user->firstname 	    = $request->firstname;
		$user->lastname 	    = $request->lastname;
		$user->email 	        = $request->email;
		$user->password         = Hash::make($request->password);
		$user->is_freelancer 	= $request->is_freelancer;
		$user->is_productowner 	= $request->is_productowner;
		$user->address 	        = $request->address;
		$user->save();

		$token = JWTAuth::fromUser($user);

		return response()->json([
			'status'	=> '1',
			'message'	=> 'User berhasil ter-registrasi'
		], 201);
	}

	//fungsi untuk login
	public function login(Request $request)
	{
		$credentials = $request->only('email', 'password');

		try {
			if (!$token = JWTAuth::attempt($credentials)) {
				return response()->json([
					'logged' 	=>  false,
					'message' 	=> 'Invalid email or password'
				]);
			}
		} catch (JWTException $e) {
			return response()->json([
				'logged' 	=> false,
				'message' 	=> 'Generate Token Failed'
			]);
		}

		return response()->json([
			"logged"    => True,
			"token"     => $token,
			"message" 	=> 'Login berhasil'
		]);
	}

	public function index($limit = 10, $offset = 0)
	{
		$data["count"] = User::count();
		$user = array();
		foreach (User::take($limit)->skip($offset)->get() as $p) {
			$item = [
				"id"             => $p->id,
				"firstname"      => $p->firstname,
				"lastname"  	 => $p->lastname,
				"email"          => $p->email,
				"is_freelancer"  => $p->is_freelancer,
				"is_productowner" => $p->is_productowner,
				"address"        => $p->address,
				"created_at"  => $p->created_at,
				"updated_at"     => $p->updated_at,
			];

			array_push($user, $item);
		}
		$data["User"] = $user;
		$data["status"] = 1;
		return response($data);
	}
	public function update(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'firstname' => 'required|string|max:255',
			'lastname' => 'required|string|max:255',
			'email' => 'required|string|email|max:255|unique:users',
			'password' => 'required|string|min:6',
			'is_freelancer' => 'required|string|max:255',
			'is_productowner' => 'required|string|max:255',
			'address' => 'required|string|max:255',
		]);

		if ($validator->fails()) {
			return response()->json([
				'status'	=> 0,
				'message'	=> $validator->errors()->toJson()
			]);
		}

		$user = new User();
		$user->firstname 	= $request->firstname;
		$user->lastname 	= $request->lastname;
		$user->email 	= $request->email;
		$user->password = Hash::make($request->password);
		$user->is_freelancer 	= $request->is_freelancer;
		$user->is_productowner 	= $request->is_productowner;
		$user->address 	= $request->address;
		$user->save();

		$token = JWTAuth::fromUser($user);

		return response()->json([
			'status'	=> '1',
			'message'	=> 'User berhasil terregistrasi'
		], 201);
	}

	public function getAuthenticatedUser()
	{
		try {
			if (!$user = JWTAuth::parseToken()->authenticate()) {
				return response()->json([
					'auth' 		=> false,
					'message'	=> 'Invalid token'
				]);
			}
		} catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
			return response()->json([
				'auth' 		=> false,
				'message'	=> 'Token expired'
			], $e->getStatusCode());
		} catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
			return response()->json([
				'auth' 		=> false,
				'message'	=> 'Invalid token'
			], $e->getStatusCode());
		} catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
			return response()->json([
				'auth' 		=> false,
				'message'	=> 'Token absent'
			], $e->getStatusCode());
		}

		return response()->json([
			"auth"      => True,
			"user"    	=> $user
		], 201);
	}
	public function delete($id)
	{
		try {

			User::where("id", $id)->delete();

			return response([
				"status"	=> 1,
				"message"   => "Data berhasil dihapus."
			]);
		} catch (\Exception $e) {
			return response([
				"status"	=> 0,
				"message"   => $e->getMessage()
			]);
		}
	}
}
