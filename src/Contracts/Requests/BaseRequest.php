<?php

namespace Vinhdev\Travel\Contracts\Requests;


use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use MongoDB\BSON\ObjectId;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Vinhdev\Travel\Contracts\DTO\GetUserInformationDTOTrait;
use Vinhdev\Travel\Contracts\DTO\GetUserInformationDTOInterface;
use Vinhdev\Travel\Contracts\DTO\UserInformationDTO;

class BaseRequest extends Request
{
    use GetUserInformationDTOTrait;
    
    protected function failedValidation(Validator $validator)
    {
        $response = new JsonResponse([
            'status'  => ResponseAlias::HTTP_BAD_REQUEST,
            'message' => $validator->errors(),
        ], ResponseAlias::HTTP_BAD_REQUEST);

        throw new HttpResponseException($response);
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(new JsonResponse([
            'status'  => ResponseAlias::HTTP_FORBIDDEN,
            'message' => 'Không có quyền truy cập'
        ], ResponseAlias::HTTP_FORBIDDEN));
    }

    public function getDTO(): GetUserInformationDTOInterface
    {
        $user = Auth::user();
        $dto = new UserInformationDTO();
        if ($user) {
            $dto->setUserId(new ObjectId($user->getId()));
            $dto->setUserName($user->getName());
        }
        
        return $dto;
    }
}