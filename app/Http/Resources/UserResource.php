<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request); // Aqui retornaria os dados do que recebeu do UserController
        return [
            'id' => $this->id,
            'name' => $this->name, //  aqui eu já poderia um método para altrar os nomes e deixar em caixa alta: 'name' => strtoupper($this->name),
            'descriptin' => $this->description,
        ];
    }
}

 /**
     * Aqui a gente padroniza ou personaliza todos os retornos de usuários.
     */
