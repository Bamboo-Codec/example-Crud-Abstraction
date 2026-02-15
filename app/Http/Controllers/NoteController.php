<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NoteController extends AbstractCrudController
{
    protected string $relationName = 'notes';

    protected array $validationRules = [
        'title' => 'required|string|max:255',
        'content' => 'required|string',
    ];
}
