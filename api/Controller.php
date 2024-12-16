<?php


namespace Sssd;


use OpenApi\Annotations as OA;
use Flight as Flight;
use Firebase\JWT\JWT; 
use Firebase\JWT\Key;

require_once __DIR__ . '/../config_default.php';


class Controller
{
   /**
 * @OA\POST(
 * path="/register",
 * summary="Register User",
 * description="Register a new user by providing full details.",
 * tags={"Users"},
 * @OA\RequestBody(
 *    required=true,
 *    description="Provide the required information to register.",
 *    @OA\JsonContent(
 *       required={"full_name", "username", "password", "email", "phone_number"},
 *       @OA\Property(property="full_name", type="string", example="Jane Doe"),
 *       @OA\Property(property="username", type="string", example="user123"),
 *       @OA\Property(property="password", type="string", example="123456"),
 *       @OA\Property(property="email", type="string", format="email", example="janedoe@example.com"),
 *       @OA\Property(property="phone_number", type="string", example="+38761234567"),
 *    ),
 * ),
 * @OA\Response(
 *    response=200,
 *    description="User registered successfully.",
 *    @OA\JsonContent(
 *       @OA\Property(property="error", type="boolean", example=false),
 *       @OA\Property(property="message", type="string", example="User registered successfully.")
 *    )
 * ),
 * @OA\Response(
 *    response=500,
 *    description="Registration failed.",
 *    @OA\JsonContent(
 *       @OA\Property(property="error", type="boolean", example=true),
 *       @OA\Property(property="message", type="string", example="User registration failed.")
 *    )
 * )
 * )
 */
public function register()
    {
        $data = Flight::request()->data->getData();
        Flight::json(Flight::userservice()->register($data));

    }

   /**
 * @OA\POST(
 * path="/login",
 * summary="User Login",
 * description="Authenticate a user with their username/email and password.",
 * tags={"Users"},
 * @OA\RequestBody(
 *    required=true,
 *    description="Provide your username or email and password to log in.",
 *    @OA\JsonContent(
 *       required={"username", "password"},
 *       @OA\Property(property="username", type="string", example="user123"),
 *       @OA\Property(property="password", type="string", example="password123"),
 *    ),
 * ),
 * @OA\Response(
 *    response=200,
 *    description="Login successful.",
 *    @OA\JsonContent(
 *       @OA\Property(property="error", type="boolean", example=false),
 *       @OA\Property(property="message", type="string", example="User logged in successfully."),
 *       @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...")
 *    )
 * ),
 * @OA\Response(
 *    response=401,
 *    description="Invalid credentials.",
 *    @OA\JsonContent(
 *       @OA\Property(property="error", type="boolean", example=true),
 *       @OA\Property(property="message", type="string", example="Invalid username or password.")
 *    )
 * ),
 * @OA\Response(
 *    response=500,
 *    description="Login failed due to a server error.",
 *    @OA\JsonContent(
 *       @OA\Property(property="error", type="boolean", example=true),
 *       @OA\Property(property="message", type="string", example="An unexpected error occurred.")
 *    )
 * )
 * )
 */
    public function login()
    {

        $data = Flight::request()->data->getData();
        Flight::json(Flight::userservice()->login($data));

    }
/**
 * @OA\POST(
 * path="/entertwofactormethodcode",
 * summary="Verify 2FA Code",
 * description="Verify the 2FA code sent to the user.",
 * tags={"Users"},
 * security={{"bearerAuth": {}}},
 * @OA\RequestBody(
 *    required=true,
 *    description="Provide the 2FA code.",
 *    @OA\JsonContent(
 *       required={"otp_code"},
 *       @OA\Property(property="otp_code", type="string", example="789567"),
 *    ),
 * ),
 * @OA\Response(
 *    response=200,
 *    description="2FA verification successful.",
 *    @OA\JsonContent(
 *       @OA\Property(property="error", type="boolean", example=false),
 *       @OA\Property(property="message", type="string", example="2FA code is correct."),
 *       @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...")
 *    )
 * ),
 * @OA\Response(
 *    response=400,
 *    description="Invalid 2FA code.",
 *    @OA\JsonContent(
 *       @OA\Property(property="error", type="boolean", example=true),
 *       @OA\Property(property="message", type="string", example="The provided 2FA code is incorrect.")
 *    )
 * ),
 * @OA\Response(
 *    response=500,
 *    description="Server error during 2FA verification.",
 *    @OA\JsonContent(
 *       @OA\Property(property="error", type="boolean", example=true),
 *       @OA\Property(property="message", type="string", example="An unexpected error occurred.")
 *    )
 * )
 * )
 */

    public function entertwofactormethodcode()
    {

        $data = Flight::request()->data->getData();
        Flight::json(Flight::userservice()->entertwofactormethodcode($data));

    }
    }
    