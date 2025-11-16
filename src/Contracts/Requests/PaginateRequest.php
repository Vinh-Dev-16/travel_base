<?php

namespace Vinhdev\Travel\Contracts\Requests;



use Vinhdev\Travel\Contracts\Rules\LimitRule;
use Vinhdev\Travel\Contracts\Rules\PageRule;

class PaginateRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'page'    => ['nullable', 'integer', new PageRule()],
            'limit'   => ['nullable', 'integer', new LimitRule()],
            'keyWord' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'page.integer'   => 'Số trang phải là số nguyên',
            'limit.integer'  => 'Số lượng phải là số nguyên',
            'keyWord.string' => 'Từ khóa phải là chuỗi',
        ];
    }

    protected function requiredRole(): string
    {
        return '';
    }

    protected function requiredPermission(): string
    {
        return '';
    }
}