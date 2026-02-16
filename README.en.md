#### English | [Español](./README.md)

# User-Scoped Generic CRUD API

- [User-Scoped Generic CRUD API](#user-scoped-generic-crud-api)
  - [Introduction](#introduction)
  - [Technologies Used](#technologies-used)
  - [Installation & Initialization](#installation--initialization)
    - [1. Clone repository](#1-clone-repository)
    - [2. Install dependencies](#2-install-dependencies)
    - [3. Configure environment](#3-configure-environment)
      - [Copy environment file](#copy-environment-file)
      - [Generate app key](#generate-app-key)
    - [4. Run migrations](#4-run-migrations)
    - [5. Run seeders (optional)](#5-run-seeders-optional)
    - [6. Start server](#6-start-server)
  - [Project Objective](#project-objective)
  - [Project Motivation](#project-motivation)
  - [General Architecture](#general-architecture)
  - [Key Components](#key-components)
    - [1. AbstractCrudController](#1-abstractcrudcontroller)
    - [2. CrudService](#2-crudservice)
  - [Class Diagram](#class-diagram)
  - [AbstractCrudController](#abstractcrudcontroller)
    - [Objective](#objective)
    - [Key Properties](#key-properties)
    - [Example](#example)
  - [CrudService](#crudservice)
    - [Objective](#objective-1)
    - [Characteristics](#characteristics)
  - [Client Request Flow](#client-request-flow)
  - [Security Model](#security-model)
    - [Eloquent Relationships](#eloquent-relationships)
  - [Method Overriding (Special Cases)](#method-overriding-special-cases)
    - [Example: Batch Creation](#example-batch-creation)
  - [Advantages of This Architecture](#advantages-of-this-architecture)
  - [How to Extend the System](#how-to-extend-the-system)
  - [Testing](#testing)
  - [Conclusion](#conclusion)
  - [Author](#author)

---

## Introduction

This documentation aims to explain the architecture, technical decisions, and internal behavior of the project.

It is intended for developers who already have basic knowledge of Laravel, including:

- Controllers  
- Eloquent Models  
- Routes  
- Migrations  
- Services  
- Dependency Injection  

Basic framework concepts are not covered. Instead, this documentation focuses on the specific implementation of this reusable generic CRUD architecture.

---

## Technologies Used

| Technology | Purpose |
|------------|----------|
| Laravel | Base framework |
| Sanctum | Token-based API authentication |
| Composer | Dependency management |
| SQLite | Relational database |
| PHP 8.4+ | Main language |
| PHPUnit | Testing |
| Eloquent ORM | Database abstraction |
| Postman / Curl | Manual endpoint testing |

---

## Installation & Initialization

### 1. Clone repository

```bash
git clone https://github.com/Bamboo-Codec/example-Crud-Abstraction.git
cd api-crud-base
```

### 2. Install dependencies

```bash
composer install
```

### 3. Configure environment

#### Copy environment file

```bash
cp .env.example .env
```

#### Generate app key

```bash
php artisan key:generate
```

### 4. Run migrations

```bash
php artisan migrate
```

### 5. Run seeders (optional)

```bash
php artisan db:seed
```

Or a specific seeder:

```bash
php artisan db:seed --class=DemoSeeder
```

### 6. Start server

```bash
php artisan serve
```

---

## Project Objective

This project implements a REST CRUD API designed to manage resources associated with a single authenticated user.

The main goals are:

> Clear separation of responsibilities  
> Reusable CRUD logic  
> Extensibility through controlled method overriding  
> Clean architecture based on services  

The architecture avoids code duplication by using:

- `AbstractCrudController`
- `CrudService`

The objective is to follow the DRY (Don't Repeat Yourself) principle and centralize common user-scoped resource logic.

---

## Project Motivation

In many applications, resources belong exclusively to the authenticated user.

Examples:

- Personal notes  
- Journal entries  
- Tasks  
- Private events  
- Individual settings  

In these cases:

- A user can only access their own data  
- There is no need for global record access  
- Complex multi-user logic is unnecessary  

This project solves that need with a reusable architecture.

---

## General Architecture

The architecture is structured in three main layers:

- **Controller**
- **Service**
- **Model**

The API relies on:

- **Eloquent Relationships**
- **Abstract Controllers**

Typical relationship:

_One_ **User** has _many_ **Resources**

Example implemented in this project:

_One_ **User** has _many_ **Notes**

---

## Key Components

### 1. AbstractCrudController

Abstract class that implements generic CRUD behavior:

- index()
- show()
- store()
- update()
- destroy()

Responsibilities:

- Receive request  
- Validate data (if applicable)  
- Delegate logic to the service  
- Return standardized JSON response  

---

### 2. CrudService

Contains reusable generic CRUD logic.

Responsibilities:

- Model interaction  
- Standard operations  
- Business logic separation from controller  

---

## Class Diagram

![Class Diagram](./diagrams/out/DiagramClass.png)

![Execution Flow](./diagrams/out/ExecutionFlow.png)

---

## AbstractCrudController

### Objective

Centralize common CRUD logic for any resource related to the authenticated user.

### Key Properties

```php
protected string $relationName;
protected array $validationRules;
```

Each child controller defines:

- The relation name in the User model  
- Specific validation rules  

### Example

```php
class NoteController extends AbstractCrudController
{
    protected string $relationName = 'notes';

    protected array $validationRules = [
        'title' => 'required|string|max:255',
        'content' => 'required|string',
    ];
}
```

No manual CRUD methods are required.

---

## CrudService

### Objective

Encapsulate generic logic that interacts with the user model and its relationships.

Simplified example of index():

```php
public function index(Request $request, string $relationName): array
{
    $user = $request->user();

    return [
        $relationName => $user->$relationName()->get()
    ];
}
```

### Characteristics

- Always scoped to the authenticated user  
- Never exposes global data  
- Uses dynamic relation names  
- Reusable across multiple resources  

---

## Client Request Flow

![RequestFlow](./diagrams/out/RequestFlow.png)

---

## Security Model

Security is based on `auth:sanctum` to simulate a real-world system.

### Eloquent Relationships

Access is always scoped to:

```php
$request->user()
```

Never directly querying the global model:

```php
Note::all(); // ❌ Not allowed
```

Instead:

```php
$request->user()->notes()->get(); // ✅ Allowed
```

This guarantees full isolation between users.

---

## Method Overriding (Special Cases)

Although the architecture is generic, certain cases may require customization.

### Example: Batch Creation

Suppose we want to create multiple notes in a single request.

We can override `store()` in NoteController:

```php
public function store(Request $request): JsonResponse
{
    $validated = $request->validate([
        'notes' => 'required|array',
        'notes.*.title' => 'required|string|max:255',
        'notes.*.content' => 'required|string',
    ]);

    $user = $request->user();

    foreach ($validated['notes'] as $noteData) {
        $user->notes()->create($noteData);
    }

    return response()->json([
        'notes' => $user->notes()->latest()->take(count($validated['notes']))->get()
    ], 201);
}
```

What happens here?

- The user-scoped philosophy remains intact  
- The relationship is reused  
- Behavior is extended without breaking base architecture  

---

## Advantages of This Architecture

1. DRY: Eliminates duplicated code  
2. Maintainable: CRUD logic changes happen in one place  
3. Flexible: Validation rules can vary per controller  
4. Consistent: All controllers behave the same  
5. Testable: Services can be tested independently  

You can create new CRUD controllers in seconds by defining only:

- The relation name  
- Validation rules  

---

## How to Extend the System

To add a new resource:

1. Create model with `belongsTo(User)`  
2. Add `hasMany` relation in User model  
3. Create controller extending AbstractCrudController  
4. Define `$relationName`  
5. Define `$validationRules`  
6. Register `apiResource` route  

No additional CRUD logic is required.

---

## Testing

### Get Token

```bash
curl -X POST http://localhost:8000/api/login \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -d '{"email":"admin@example.com","password":"password"}'
```

### Create Record

```bash
curl -X POST http://localhost:8000/api/notes \
     -H "Authorization: Bearer 1|xxxxxxxxxxxx" \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -d '{
           "title": "Test Note",
           "content": "This is test content"
         }'
```

### List Records

```bash
curl -X GET http://localhost:8000/api/notes \
     -H "Authorization: Bearer 1|xxxxxxxxxxxx" \
     -H "Accept: application/json"
```

### Show Specific Record

```bash
curl -X GET http://localhost:8000/api/notes/6 \
     -H "Authorization: Bearer 1|xxxxxxxxxxxx" \
     -H "Accept: application/json"
```

### Update Record

```bash
curl -X PUT http://localhost:8000/api/notes/6 \
     -H "Authorization: Bearer 1|xxxxxxxxxxxx" \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -d '{
            "title": "Updated Note",
            "content": "This content has been updated"
        }'
```

### Delete Record

```bash
curl -X DELETE http://localhost:8000/api/notes/6 \
     -H "Authorization: Bearer 1|xxxxxxxxxxxx" \
     -H "Accept: application/json"
```

---

## Conclusion

This project demonstrates a clean implementation of a generic user-scoped CRUD.

It is especially useful in systems where:

- Each user manages their own data  
- No complex global administration is required  
- A maintainable and extensible architecture is desired  

The combination of:

`AbstractCrudController` + `CrudService` + `Eloquent Relationships` + `User-based Authentication`

Allows building consistent, secure, and scalable APIs.

---

## Author

If you have feedback about this architecture or would like to connect professionally, find me on **[LinkedIn](www.linkedin.com/in/gabriel-da-silva-dev?follow_check=true)**.
