# Betterfly Web Application

## Project Overview
Betterfly is a modern web application built using Laravel (backend) and Vue.js (frontend), designed to provide a robust and scalable solution with a focus on modularity and performance.

## Architecture

### Technology Stack
- **Backend**: Laravel 9.x
- **Frontend**: Vue.js 3.x
- **Database**: MySQL
- **Package Management**: 
  - Composer (PHP dependencies)
  - npm (JavaScript dependencies)

### Project Structure
```
betterfly/
├── app/                # Laravel application core
├── Modules/            # Modular application components
├── resources/          # Frontend resources
│   ├── js/             # Vue.js components
│   └── views/          # Vue templates
├── routes/             # Application routing
├── config/             # Configuration files
└── database/           # Database migrations and seeds
```

## Backend Architecture (Laravel)

### Routing
- Located in `routes/` directory
- Defines API endpoints and web routes
- Supports RESTful resource controllers
- Middleware for authentication and authorization

### Models and Database
- Eloquent ORM for database interactions
- Migrations for schema management
- Located in `app/Models/` and `database/migrations/`

### Controllers
- Handle request processing
- Located in `app/Http/Controllers/`
- Implement business logic
- Return JSON responses for API endpoints

## Frontend Architecture (Vue.js)

### Component Structure
- Single File Components (SFC)
- Modular design in `resources/js/`
- State management using Vuex/Pinia
- Vue Router for client-side routing

### Data Flow
1. User interaction triggers Vue component method
2. API call made via Axios
3. Laravel backend processes request
4. Database query executed
5. Response sent back to frontend
6. Vue updates component state

## Component Interaction Workflow: EmailTemplate Example

### 1. Model: `app/Models/EmailTemplate.php`
```php
class EmailTemplate extends BaseModel
{
    // Database table mapping
    protected $table = 'email_templates';

    // Fillable attributes
    protected $default = ['xid', 'name', 'subject', 'body'];

    // Global scopes and casts
    protected $casts = [
        'company_id' => Hash::class . ':hash',
        'status' => 'integer',
        'sharable' => 'integer',
    ];
}
```

### 2. Controller: `app/Http/Controllers/Api/EmailTemplateController.php`
```php
class EmailTemplateController extends ApiBaseController
{
    // Model and request class bindings
    protected $model = EmailTemplate::class;

    // Custom index query modification
    protected function modifyIndex($query)
    {
        $user = user();
        // Apply user-specific filtering
        if (!$user->ability('admin', 'email_templates_view_all')) {
            $query = $query->where('created_by', $user->id);
        }
        return $query;
    }

    // Lifecycle hooks for creating and updating
    public function storing($emailTemplate)
    {
        $loggedUser = user();
        $emailTemplate->created_by = $loggedUser->id;
        return $emailTemplate;
    }

    // Endpoint for retrieving all email templates
    public function allEmailTemplates()
    {
        $user = user();
        $emailTemplates = EmailTemplate::select('id', 'name', 'body', 'subject')
            ->where('status', 1)
            ->where(function ($query) use ($user) {
                $query->where('created_by', $user->id)
                    ->orWhere('sharable', 1);
            })
            ->get();

        return ApiResponse::make('Success', [
            'email_templates' => $emailTemplates
        ]);
    }
}
```

### 3. API Routes (Typical Configuration)
```php
Route::group(['prefix' => 'email-templates'], function () {
    Route::get('/', [EmailTemplateController::class, 'index']);
    Route::post('/', [EmailTemplateController::class, 'store']);
    Route::get('/all', [EmailTemplateController::class, 'allEmailTemplates']);
    Route::put('/{id}', [EmailTemplateController::class, 'update']);
    Route::delete('/{id}', [EmailTemplateController::class, 'destroy']);
});
```

### 4. Frontend Interaction (Typical Vue.js Axios Call)
```javascript
// Fetch email templates
async fetchEmailTemplates() {
    try {
        const response = await axios.get('/api/email-templates/all');
        this.emailTemplates = response.data.email_templates;
    } catch (error) {
        // Error handling
        console.error('Failed to fetch email templates', error);
    }
}
```

### Key Interaction Points
- **Model**: Defines database schema and relationships
- **Controller**: 
  - Handles business logic
  - Applies user permissions
  - Transforms data
- **Routes**: Map HTTP methods to controller methods
- **Frontend**: Makes API calls, renders data

### Data Flow
1. Frontend sends HTTP request
2. Laravel routes request to specific controller method
3. Controller interacts with Model
4. Model performs database operations
5. Controller transforms and returns data
6. Frontend receives and renders data

### Best Practices
- Use request validation classes
- Implement user permissions in controllers
- Use global scopes for multi-tenant applications
- Leverage Laravel's Eloquent relationships
- Handle errors gracefully in frontend

## Development Workflow

### Setup
1. Clone the repository
2. Install PHP dependencies: `composer install`
3. Install JavaScript dependencies: `npm install`
4. Configure `.env` file
5. Generate application key: `php artisan key:generate`
6. Run database migrations: `php artisan migrate`

### Running the Application
- Backend: `php artisan serve`
- Frontend: `npm run dev`

## Database Interaction

### Query Workflow
1. Controller receives request
2. Eloquent model defines relationship
3. Query builder constructs database query
4. Results returned and transformed

### Caching Strategy
- Redis/Memcached for query result caching
- Reduces database load
- Configurable cache lifetimes

## Security Features
- Laravel Sanctum for authentication
- CSRF protection
- Input validation
- Eloquent model security constraints

## Performance Optimization
- Eager loading of relationships
- Query optimization
- Caching mechanisms
- Minimal database queries

## Deployment
- CI/CD pipeline configuration
- Docker containerization support
- Environment-specific configurations

## Monitoring and Logging
- Laravel Log facade
- Error tracking
- Performance monitoring

## Contributing
1. Fork the repository
2. Create feature branch
3. Commit changes
4. Push and create Pull Request

## License
[Specify your project's license]

## Contact
[Your contact information]
