<?php

namespace Webcore\Elogui\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Webcore\Elogui\Models\DataSource;

class UpdateDataSourceRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return DataSource::$rules;
    }
}
