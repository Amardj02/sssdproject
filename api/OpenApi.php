<?php
namespace Sssd;
use OpenApi\Annotations as OA;
/**
* @OA\Info(
* description="SSSD project API",
* version="1.0.0",
* title="My first API",
* @OA\Contact(
* email="amar.durovic@stu.ibu.edu.ba"
* )
* )
* @OA\Server(
* description="API Mocking",
* url="http://localhost:8080/sssdproject/api"
* )
*
* @OA\SecurityScheme(
*     securityScheme="bearerAuth",
*     type="http",
*     scheme="bearer",
*     bearerFormat="JWT"
* )
*/
class OpenApi
{
    //http://127.0.0.1/sssd-2024-21002935/doc.php
}