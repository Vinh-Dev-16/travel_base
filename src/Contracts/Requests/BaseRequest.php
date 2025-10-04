<?php

namespace Vinhdev\Travel\Contracts\Requests;


use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\ValidationException;
use MongoDB\BSON\ObjectId;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Vinhdev\Travel\Contracts\DTO\GetUserInformationDTOTrait;
use Vinhdev\Travel\Contracts\DTO\GetUserInformationDTOInterface;
use Vinhdev\Travel\Contracts\DTO\UserInformationDTO;

class BaseRequest extends Request
{
    use GetUserInformationDTOTrait;
    
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Mặc định cho phép tất cả, có thể override trong class con
    }
    
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return []; // Mặc định không có rules, có thể override trong class con
    }
    
    /**
     * Validate the request data
     * @throws ValidationException
     */
    public function validate(): array
    {
        if (!$this->authorize()) {
            $this->failedAuthorization();
        }
        
        $validator = ValidatorFacade::make($this->all(), $this->rules());
        
        if ($validator->fails()) {
            $this->failedValidation($validator);
        }
        
        return $validator->validated();
    }
    
    /**
     * Get validated data
     */
    public function validated(): array
    {
        return $this->validate();
    }
    
    /**
     * Get input data from both request body and query parameters
     */
    public function input($key = null, $default = null)
    {
        if ($key === null) {
            return array_merge(parent::input(), $this->query());
        }
        
        return parent::input($key) ?? $this->query($key) ?? $default;
    }
    
    /**
     * Get query parameters
     */
    public function query($key = null, $default = null)
    {
        return parent::query($key, $default);
    }
    
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