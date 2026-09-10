<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class restauranteRequest extends FormRequest
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
       

        if ($this->route('id')) 
        {  
            return [
                'nit'=>'integer|min:5|max:99999999999|unique:restaurantes,id,' . $this->route('id'),
                'nombre' => 'required|min:4|max:255|string',
                'id_estado'=>'required',
              
            ];
        } 
        else
        {
            return [
                'nit'=>'required|integer|min:5|max:99999999999|unique:restaurantes',
                'nombre' => 'required|min:4|max:255|string',
                'id_estado'=>'required' ,
                           
            ];
        }
    }
}
