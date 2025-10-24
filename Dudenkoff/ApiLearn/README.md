# Magento 2 Web API Learning Module

## Overview

This module teaches you **Magento 2 Web API, Service Contracts, and Repository Pattern** through practical examples.

## What You'll Learn

1. **Web API Configuration** (`webapi.xml`)
   - REST endpoint mapping
   - HTTP methods (GET, POST, PUT, DELETE)
   - URL routing with parameters
   - ACL/permissions

2. **Service Contracts** (API Interfaces)
   - Data interfaces (`NoteInterface`)
   - Repository interfaces (`NoteRepositoryInterface`)
   - Management interfaces (`NoteManagementInterface`)
   - Search results (`NoteSearchResultsInterface`)

3. **Repository Pattern**
   - CRUD operations
   - Search criteria
   - Filtering and pagination
   - Best practices

4. **Model Implementation**
   - Data models
   - Resource models
   - Interface implementation

## Quick Start

### 1. Enable Module

```bash
cd /home/dudenkoff/Projects/magento
bin/magento module:enable Dudenkoff_ApiLearn
bin/magento setup:upgrade
bin/magento cache:flush
```

### 2. Study the Code

**Start with these files** (in order):

1. **etc/webapi.xml** - See how API endpoints are configured
2. **Api/Data/NoteInterface.php** - Data contract (what fields a Note has)
3. **Api/NoteRepositoryInterface.php** - Repository contract (CRUD operations)
4. **Api/NoteManagementInterface.php** - Custom service contract
5. **Model/Note.php** - How to implement a data interface
6. **etc/di.xml** - How interfaces map to implementations

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/V1/dudenkoff/notes/:id` | Get note by ID |
| `GET` | `/V1/dudenkoff/notes/search` | Search/list notes |
| `POST` | `/V1/dudenkoff/notes` | Create new note |
| `PUT` | `/V1/dudenkoff/notes/:id` | Update note |
| `DELETE` | `/V1/dudenkoff/notes/:id` | Delete note |
| `POST` | `/V1/dudenkoff/notes/publish/:id` | Publish note (custom action) |

## Key Concepts

###  1. Service Contracts (API Interfaces)

**What**: Interfaces that define how modules communicate.

**Why**: 
- Stable API (implementation can change, interface stays same)
- Multiple implementations possible
- Clear contracts
- Auto-generates API documentation

**Example**:
```php
// Api/Data/NoteInterface.php
interface NoteInterface
{
    const TITLE = 'title';
    
    public function getTitle();
    public function setTitle($title);
}
```

### 2. Repository Pattern

**What**: Gateway for data persistence (CRUD).

**Standard Methods**:
- `save($entity)` - Create or update
- `getById($id)` - Retrieve by ID
- `getList($searchCriteria)` - Search/filter
- `delete($entity)` - Delete
- `deleteById($id)` - Delete by ID

**Example**:
```php
// Api/NoteRepositoryInterface.php
interface NoteRepositoryInterface
{
    public function save(NoteInterface $note);
    public function getById($noteId);
    public function getList(SearchCriteriaInterface $criteria);
    public function delete(NoteInterface $note);
    public function deleteById($noteId);
}
```

### 3. webapi.xml Configuration

Maps HTTP endpoints to PHP methods:

```xml
<!-- GET /V1/dudenkoff/notes/:noteId -->
<route url="/V1/dudenkoff/notes/:noteId" method="GET">
    <service class="Dudenkoff\ApiLearn\Api\NoteRepositoryInterface" 
             method="getById"/>
    <resources>
        <resource ref="anonymous"/>
    </resources>
</route>
```

**Components**:
- **url**: The REST endpoint path
- **method**: HTTP method (GET, POST, PUT, DELETE)
- **service**: Interface and method to call
- **resources**: ACL permissions required

### 4. Data Interfaces

Define entity structure:

```php
interface NoteInterface
{
    // Constants for field names
    const NOTE_ID = 'note_id';
    const TITLE = 'title';
    
    // Getters
    public function getNoteId();
    public function getTitle();
    
    // Setters (return $this for chaining)
    public function setNoteId($id);
    public function setTitle($title);
}
```

## File Structure

```
app/code/Dudenkoff/ApiLearn/
├── registration.php
├── etc/
│   ├── module.xml
│   ├── di.xml                    ← Maps interfaces to classes
│   └── webapi.xml                ← ⭐ API endpoint configuration
├── Api/
│   ├── Data/
│   │   ├── NoteInterface.php             ← Data contract
│   │   └── NoteSearchResultsInterface.php
│   ├── NoteRepositoryInterface.php       ← Repository contract
│   └── NoteManagementInterface.php       ← Service contract
├── Model/
│   ├── Note.php                  ← Data model
│   ├── NoteSearchResults.php
│   └── ResourceModel/
│       └── Note.php              ← Resource model
└── README.md                     ← This file
```

## Best Practices

### ✅ DO:

1. **Always use interfaces** in service contracts
2. **Type-hint properly** in method signatures
3. **Return interface types** not concrete classes
4. **Use constants** for field names
5. **Document exceptions** in PHPDoc
6. **Follow naming conventions**:
   - Data: `{Entity}Interface`
   - Repository: `{Entity}RepositoryInterface`
   - Search Results: `{Entity}SearchResultsInterface`

### ❌ DON'T:

1. **Don't expose models** directly in API
2. **Don't skip interfaces** (always use contracts)
3. **Don't put business logic** in models
4. **Don't use concrete types** in service contracts
5. **Don't forget ACL resources** for secured endpoints

## Real-World Usage

### Creating an Entity via API

```bash
curl -X POST http://localhost:8080/rest/V1/dudenkoff/notes \
  -H "Content-Type: application/json" \
  -d '{
    "note": {
      "title": "My First Note",
      "content": "Learning Magento 2 Web API",
      "author": "Developer",
      "is_published": false
    }
  }'
```

### Retrieving an Entity

```bash
curl -X GET http://localhost:8080/rest/V1/dudenkoff/notes/1
```

### Searching with Criteria

```bash
curl -X GET "http://localhost:8080/rest/V1/dudenkoff/notes/search?\
searchCriteria[filterGroups][0][filters][0][field]=is_published&\
searchCriteria[filterGroups][0][filters][0][value]=1&\
searchCriteria[filterGroups][0][filters][0][conditionType]=eq"
```

## Learning Path

### Beginner (2-3 hours)
1. Read this README
2. Study `etc/webapi.xml`
3. Study `Api/Data/NoteInterface.php`
4. Study `Api/NoteRepositoryInterface.php`
5. Understand the repository pattern

### Intermediate (2-3 hours)
6. Study `Model/Note.php` implementation
7. Study `etc/di.xml` mappings
8. Understand service contracts
9. Learn search criteria

### Advanced (2-3 hours)
10. Create your own entity
11. Add custom endpoints
12. Implement management service
13. Add authentication/ACL

## Testing the API

### Using cURL

```bash
# Get note
curl -X GET http://localhost:8080/rest/V1/dudenkoff/notes/1

# Create note  
curl -X POST http://localhost:8080/rest/V1/dudenkoff/notes \
  -H "Content-Type: application/json" \
  -d '{"note": {"title": "Test", "content": "Content"}}'

# Update note
curl -X PUT http://localhost:8080/rest/V1/dudenkoff/notes/1 \
  -H "Content-Type: application/json" \
  -d '{"note": {"note_id": 1, "title": "Updated"}}'

# Delete note
curl -X DELETE http://localhost:8080/rest/V1/dudenkoff/notes/1
```

### Using Postman

1. Import Magento 2 REST API collection
2. Set base URL: `http://localhost:8080/rest`
3. Test endpoints from webapi.xml

## Key Takeaways

1. **Service Contracts** = Stable API interfaces
2. **Repository Pattern** = Data persistence gateway
3. **webapi.xml** = Maps URLs to PHP methods
4. **Data Interfaces** = Define entity structure
5. **DI Configuration** = Maps interfaces to implementations

## Further Reading

- [Magento DevDocs: Service Contracts](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/service-contracts/service-contracts.html)
- [Magento DevDocs: Web APIs](https://devdocs.magento.com/guides/v2.4/get-started/bk-get-started-api.html)
- [Magento DevDocs: Repository Pattern](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/searching-with-repositories.html)

## What's Next?

After studying this module, you can:
- Create your own Web APIs
- Build RESTful services
- Implement repository pattern
- Design service contracts
- Integrate with external systems

**Happy Learning! 🚀**


