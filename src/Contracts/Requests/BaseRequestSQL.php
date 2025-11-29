<?php

namespace Vinhdev\Travel\Contracts\Requests;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\ValidationException;
use MongoDB\BSON\ObjectId;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Vinhdev\Travel\Contracts\DTO\SQL\GetUserInformationDTOSQLInterface;
use Vinhdev\Travel\Contracts\DTO\SQL\GetUserInformationDTOSQLTrait;
use Vinhdev\Travel\Contracts\DTO\SQL\UserInformationDTOSQL;

abstract class BaseRequestSQL extends Request
{
    use GetUserInformationDTOSQLTrait;


    abstract protected function requiredRole(): string;

    abstract protected function requiredPermission(): string;

    /**
     * Constructor - automatically merge from global request if no data provided
     */
    public function __construct(
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
              $content = null
    ) {
        // Nếu không có data truyền vào, lấy từ global request
        if (empty($query) && empty($request) && empty($attributes) && app()->bound('request')) {
            $globalRequest = app('request');

            parent::__construct(
                $globalRequest->query->all(),
                $globalRequest->request->all(),
                $globalRequest->attributes->all(),
                $globalRequest->cookies->all(),
                $globalRequest->files->all(),
                $globalRequest->server->all(),
                $globalRequest->getContent()
            );

            // Copy thêm các thuộc tính quan trọng
            $this->headers = clone $globalRequest->headers;
            $this->setJson($globalRequest->json());

            if (method_exists($globalRequest, 'getRouteResolver')) {
                $this->setRouteResolver($globalRequest->getRouteResolver());
            }

            if (method_exists($globalRequest, 'getUserResolver')) {
                $this->setUserResolver($globalRequest->getUserResolver());
            }
        } else {
            parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     * @throws BindingResolutionException
     */
    public function authorize()
    {
        return app(Pipeline::class)
            ->send($this->user())
            ->through([
                new CheckRolePipe($this->requiredRole(), $this->get('roles')),
                new CheckPermissionPipe($this->requiredPermission(), $this->get('permissions')),
            ])
            ->thenReturn();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [];
    }

    protected function prepareForValidation(): void
    {
        // Override in child classes if needed
    }

    /**
     * Get the data to be validated from the request.
     * Override this method to merge route parameters or modify validation data
     */
    public function validationData(): array
    {
        return $this->all();
    }


    /**
     * Validate the request data
     * @throws ValidationException
     * @throws BindingResolutionException
     */
    public function validateRequest(): array
    {
        if (!$this->authorize()) {
            $this->failedAuthorization();
        }

        $this->prepareForValidation();

        $validator = ValidatorFacade::make(
            $this->validationData(),
            $this->rules(),
            $this->messages(),
            $this->attributes()
        );

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        return $validator->validated();
    }

    /**
     * Get validated data
     * @throws ValidationException
     * @throws BindingResolutionException
     */
    public function validated(): array
    {
        return $this->validateRequest();
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

    public function getDTO(): GetUserInformationDTOSQLInterface
    {
        $user = Auth::user();
        $dto  = new UserInformationDTOSQL();
        if ($user) {
            $dto->setUserId($user->getId());
            $dto->setUserName($user->getName());
        }

        return $dto;
    }
}