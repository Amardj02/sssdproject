<?php
require_once 'BaseService.php';
require_once __DIR__ . "/../dao/UserDao.php";
include '../vendor/autoload.php';
include (__DIR__ . '/validtld.php');
use OTPHP\TOTP;
use Firebase\JWT\JWT; 
use Firebase\JWT\Key;

class UserService extends BaseService
{
    private $userDao;

    public function __construct()
    {
        parent::__construct(new UserDao);
    }

    private function checkPassword($password)
    {
        $pawned = false;
        $sha1Password = strtoupper(sha1($password));
        $prefix = substr($sha1Password, 0, 5);
        $suffix = substr($sha1Password, 5);
        $ch = curl_init("https://api.pwnedpasswords.com/range/" . $prefix);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        if ($response === false) {
            Flight::json(['status' => 500, 'message' => 'Could not retrieve data from the API.'], 500);
            return false;  // Exit the function
        }

        if (str_contains($response, $suffix)) {
            $pawned = true;
        }

        return $pawned;
    }

    private function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    private function checkExistenceForEmail($username)
    {
        return Flight::userdao()->checkExistenceForEmail($username);
    }

    private function checkExistenceForUsername($username)
    {
        return Flight::userdao()->checkExistenceForUsername($username);
    }

    private function checkEmail($email)
    {
        global $tld_array;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $email_array = explode(".", $email);
        $email_tld = end($email_array);

        if (!in_array($email_tld, $tld_array)) {
            return false;
        }

        $domain_array = explode('@', $email);
        $domain = $domain_array[1];
        if (getmxrr($domain, $mx_details)) {
            return count($mx_details) > 0;
        }

        return false;
    }

    private function checkPhoneNumber($phone)
    {
        $phone_util = \libphonenumber\PhoneNumberUtil::getInstance();
        try {
            $number_proto = $phone_util->parse($phone, "BA");
            return $phone_util->getNumberType($number_proto) === \libphonenumber\PhoneNumberType::MOBILE;
        } catch (\libphonenumber\NumberParseException $e) {
            return false;
        }
    }

    private function checkPlusSign($phone)
    {
        return substr($phone, 0, 1) === '+';
    }

    public function register($data)
    {
        $fullname = $data['full_name'];
        $username = $data['username'];
        $password = $data['password'];
        $email = $data['email'];
        $phone = $data['phone_number'];
        $reserved_names = ["admin", "root", "system", "administrator", "user", "owner"];

        if (mb_strlen($username) <= 3) {
            Flight::json(['status' => 500, 'message' => 'The username should be longer than 3 characters.'], 500);
            return;
        }

        if (!ctype_alnum($username)) {
            Flight::json(['status' => 500, 'message' => 'The username can only contain letters and numbers, no special characters and spaces.'], 500);
            return;
        }

        foreach ($reserved_names as $reserved_name) {
            if (stripos($username, $reserved_name) !== false) {
                Flight::json(['status' => 500, 'message' => 'A reserved name can\'t be used as username.'], 500);
                return;
            }
        }

        if (mb_strlen($password) < 8) {
            Flight::json(['status' => 500, 'message' => 'The password should be at least 8 characters long.'], 500);
            return;
        }

        if (!$this->checkEmail($email)) {
            Flight::json(['status' => 500, 'message' => 'Email input invalid.'], 500);
            return;
        }

        if (!$this->checkPhoneNumber($phone)) {
            Flight::json(['status' => 500, 'message' => 'Phone number input invalid.'], 500);
            return;
        }

        if (!$this->checkPlusSign($phone)) {
            Flight::json(['status' => 500, 'message' => 'Please put a + sign in front of the phone number.'], 500);
            return;
        }

        $pawned = $this->checkPassword($password);

        if ($pawned) {
            Flight::json(['status' => 500, 'message' => 'Password is pawned. Use another password.'], 500);
            return;
        }

        $hashedPassword = $this->hashPassword($password);
        $data["password"] = $hashedPassword;
        $secret = $this->generateOTPassword();
        $data["secret"] = $secret;
        $data["login_count"] = 0;

        $daoResult = parent::add($data);
        if ($daoResult["status"] == 500) {
            Flight::json(['status' => 500, 'message' => $daoResult["message"]], 500);
            return;
        }

        Flight::json(['status' => $daoResult["status"], 'message' => $daoResult["message"]], $daoResult["status"]);
    }

    public function login($data)
    {
        $username = $data['username'];
        $password = $data['password'];

        if ($username == '' || $password == '') {
            Flight::json(['status' => 500, 'message' => 'All fields have to be filled in.'], 500);
            return;
        }

        $emailVal = filter_var($username, FILTER_VALIDATE_EMAIL);

        if ($emailVal) {
            $user = $this->checkExistenceForEmail($username);
            if (!$user) {
                Flight::json(['status' => 500, 'message' => 'No user found with this email.'], 500);
                return;
            }

            if (!isset($user['password']) || !password_verify($data["password"], $user['password'])) {
                Flight::json(['status' => 500, 'message' => 'Invalid password.'], 500);
                return;
            }
        } else {
            $user = $this->checkExistenceForUsername($username);
            if (!$user) {
                Flight::json(['status' => 500, 'message' => 'No user found with this username.'], 500);
                return;
            }

            if (!isset($user['password']) || !password_verify($data["password"], $user['password'])) {
                Flight::json(['status' => 500, 'message' => 'Invalid password.'], 500);
                return;
            }
        }

        $secret = $user['secret'];
        $coded_user = [$user['full_name'], $user['username'], $user['email'], $user['phone_number'], $user['secret'], $user['login_count']];
        $token = JWT::encode($coded_user, JWT_SECRET, 'HS256');

        if ($user['login_count'] == 0) {
            $qrLink = $this->generateQrCode($secret, $user['username']);
            Flight::json(['status' => 200, 'message' => 'Scan the QR code and enter the OTP.', 'link' => $qrLink, 'token' => $token], 200);
            return;
        } else {
            Flight::json(['status' => 200, 'message' => 'Successful login.', 'token' => $token], 200);
        }
    }

    private function generateOTPassword()
    {
        $otp = TOTP::generate();
        return $otp->getSecret();
    }

    public function generateQrCode($secret, $username)
    {
        $otp = TOTP::createFromSecret($secret);
        $otp->setLabel($username . 'Amar@SSSD-PROJECT');
        return $otp->getQrCodeUri(
            'https://api.qrserver.com/v1/create-qr-code/?data=[DATA]&size=300x300&ecc=M',
            '[DATA]'
        );
    }

    public function createOTPCode($secret)
    {
        $otp_code = TOTP::createFromSecret($secret);
        return $otp_code->now();
    }

    private function updateLoginCount($email)
    {
        Flight::userdao()->updateLoginCount($email);
    }

    public function enterotp($jwt, $passcode)
    {
        $decoded = (array) JWT::decode($jwt, new Key(JWT_SECRET, 'HS256'));
        $user = $this->checkExistenceForEmail($decoded[2]);

        $secret = $user['secret'];
        $otp = TOTP::createFromSecret($secret);

        if ($otp->verify($passcode)) {
            $this->updateLoginCount($user['email']);
            Flight::json(['status' => 200, 'message' => '2FA Check is successful.'], 200);
        } else {
            Flight::json(['status' => 500, 'message' => 'Invalid OTP. Please try again.'], 500);
        }
    }

    public function entertwofactormethodcode($data)
    {
        $headers = getallheaders();
        $token = isset($headers['Authorization']) ? $headers['Authorization'] : null;

        if (empty($token)) {
            Flight::json(['status' => 401, 'message' => 'Authorization header not found.'], 401);
            return;
        }

        if (preg_match('/Bearer\s(\S+)/', $token, $matches)) {
            $token = $matches[1];
        } else {
            Flight::json(['status' => 401, 'message' => 'Invalid Authorization header format.'], 401);
            return;
        }

        try {
            $decoded = (array) JWT::decode($token, new Key(JWT_SECRET, 'HS256'));
            $email = $decoded[2];
            $user = $this->checkExistenceForEmail($email);
            $secret = $user['secret'];

            $otp = TOTP::createFromSecret($secret);
            $isValidOtp = $otp->verify($data["otp_code"]);

            if ($isValidOtp) {
                $this->updateLoginCount($email);
                Flight::json(['status' => 200, 'message' => '2FA Check is successful.'], 200);
            } else {
                Flight::json(['status' => 500, 'message' => '2FA Check is not successful.'], 500);
            }
        } catch (Exception $e) {
            Flight::json(['status' => 401, 'message' => 'Invalid token.'], 401);
        }
    }
}
