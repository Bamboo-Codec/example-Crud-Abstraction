<?php

namespace App\Services\Crud;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

Class CrudService {

    //obtiene todos los recursos del usuario
    public function index(Request $request, string $relationName): array {
        $user = $request->user();

        $relation = $user->$relationName();

        return [$relationName => $relation->get()];
    }

    //crea un nuevo recurso
    public function store(Request $request, string $relationName, array $validationRules,$uniqueField = null): array {
        
        $validated = $request->validate($validationRules);
        $user = $request->user();

        $existing = false;
        //verificar si ya existe (basado en el campo unico)
        if(isset($uniqueField)) {
            $existing = $user->$relationName()->where($uniqueField, $validated[$uniqueField])->first();
        }

        if ($existing) {
            throw new \Exception("El {$relationName} ya existe");
        }

        //crear el recurso
        $resource = $user->$relationName()->create($validated);

        return ['message' => ucfirst($relationName) . ' creado correctamente', $relationName => $resource];

    }   

    //muestra un recurso especifico
    public function show(Request $request, string $relationName, int $id, string $idField = 'id'): Model{
        $user = $request->user();
        
        $resource = $user->$relationName()
            ->where($idField, $id)
            ->first();

        if (!$resource) {
            throw new \Exception("Error {$relationName} inexistente", 404);
        }

        return $resource;
    }

    //actualiza un recurso
    public function update(Request $request,string $relationName,int $id,array $validationRules = ['nombre' => 'string|required'],string $idField = 'id'): array {
        
        $validated = $request->validate($validationRules);
        $user = $request->user();

        $resource = $user->$relationName()
            ->where($idField, $id)
            ->first();

        if (!$resource) {
            throw new \Exception("Error {$relationName} inexistente", 404);
        }

        // Actualizar campos
        foreach ($validated as $field => $value) {
            $resource->$field = $value;
        }
        
        $resource->save();

        return [
            'message' => ucfirst($relationName) . ' actualizado correctamente',
            $relationName => $resource
        ];
    }

    //Elimina un recurso
    public function destroy(Request $request,string $relationName,int $id,string $idField = 'id'): array {
        $user = $request->user();

        $resource = $user->$relationName()
            ->where($idField, $id)
            ->first();

        if (!$resource) {
            throw new ModelNotFoundException("{$relationName} inexistente");
        }

        $resource->delete();

        return [
            'message' => ucfirst($relationName) . ' eliminado correctamente'
        ];
    }
}