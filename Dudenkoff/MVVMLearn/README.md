# Magento MVC/MVVM Learning Module

## 📚 Overview

This module is a **complete educational resource** for understanding Magento's MVC/MVVM architecture patterns.

### What You'll Learn:

1. ✅ **Model** - Entity representation and business logic
2. ✅ **ResourceModel** - Database operations and complex queries
3. ✅ **Collection** - Working with multiple records
4. ✅ **Repository** - Service contract pattern (best practice)
5. ✅ **Controller** - Handle HTTP requests
6. ✅ **Block** - ViewModel (prepare data for views)
7. ✅ **View** - Templates (presentation layer)
8. ✅ **API/Service Contracts** - Type-safe interfaces

---

## 🏗️ Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                     MAGENTO ARCHITECTURE                     │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  HTTP Request → Controller → Block → Template → Response    │
│                      ↓                                       │
│                  Repository                                  │
│                      ↓                                       │
│                   Model                                      │
│                      ↓                                       │
│               ResourceModel                                  │
│                      ↓                                       │
│                   Database                                   │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## 📁 Module Structure

```
Dudenkoff/MVVMLearn/
├── Api/
│   ├── Data/
│   │   ├── BookInterface.php              # Data contract
│   │   └── BookSearchResultsInterface.php # Search results contract
│   └── BookRepositoryInterface.php        # Repository contract
│
├── Model/
│   ├── Book.php                           # Model (entity)
│   ├── BookRepository.php                 # Repository implementation
│   ├── BookSearchResults.php              # Search results
│   └── ResourceModel/
│       ├── Book.php                       # ResourceModel (DB operations)
│       └── Book/
│           └── Collection.php             # Collection (multiple records)
│
├── Controller/
│   ├── Index/
│   │   └── Index.php                      # List page controller
│   └── Book/
│       └── View.php                       # Detail page controller
│
├── Block/
│   ├── BookList.php                       # ViewModel for list page
│   └── BookView.php                       # ViewModel for detail page
│
├── view/frontend/
│   ├── layout/
│   │   ├── books_index_index.xml         # List page layout
│   │   └── books_book_view.xml           # Detail page layout
│   └── templates/book/
│       ├── list.phtml                     # List template
│       └── view.phtml                     # Detail template
│
├── Console/Command/
│   └── BookExamplesCommand.php            # CLI examples
│
├── Setup/Patch/Data/
│   └── AddSampleBooks.php                 # Sample data
│
└── etc/
    ├── module.xml                         # Module declaration
    ├── di.xml                             # Dependency injection
    ├── db_schema.xml                      # Database schema
    └── frontend/
        └── routes.xml                     # Frontend routes
```

---

## 🚀 Installation

```bash
# Enable module
docker-compose exec magento bin/magento module:enable Dudenkoff_MVVMLearn

# Run setup
docker-compose exec magento bin/magento setup:upgrade

# Deploy static content (dev mode - optional)
docker-compose exec magento bin/magento setup:static-content:deploy -f

# Clear cache
docker-compose exec magento bin/magento cache:clean
```

---

## 🧪 Testing the Module

### 1. **CLI Examples** (Best Way to Learn)

```bash
# See all patterns
docker-compose exec magento bin/magento dudenkoff:mvvm:book-examples --example=all

# Individual patterns
docker-compose exec magento bin/magento dudenkoff:mvvm:book-examples -e model
docker-compose exec magento bin/magento dudenkoff:mvvm:book-examples -e resource
docker-compose exec magento bin/magento dudenkoff:mvvm:book-examples -e collection
docker-compose exec magento bin/magento dudenkoff:mvvm:book-examples -e repository
```

### 2. **Frontend Pages**

Visit in browser:
- **Book List:** http://localhost:8080/books
- **Book Detail:** http://localhost:8080/books/book/view/id/1

---

## 🎓 Pattern Explanations

### 1. **MODEL** (`Model/Book.php`)

**What:** Represents a single entity (one book)  
**Extends:** `AbstractModel`  
**Purpose:**
- ORM functionality (get/set data)
- Business logic
- Data validation

**Example:**
```php
$book = $bookFactory->create();
$bookResource->load($book, 1);
echo $book->getTitle();              // PHP for Beginners
echo $book->getAuthor();             // John Doe
echo $book->isInStock();             // true/false (business logic)
```

**When to use:**
- Working with single records
- Need business logic
- Simple CRUD operations

---

### 2. **RESOURCE MODEL** (`Model/ResourceModel/Book.php`)

**What:** Handles database operations  
**Extends:** `AbstractDb`  
**Purpose:**
- Direct SQL queries
- Complex database operations
- Transactions

**Example:**
```php
// Custom query
$books = $bookResource->getBooksByAuthor('Jane Smith');

// Update with SQL expression
$bookResource->updateStockQty($bookId, -5);  // Decrement

// Aggregate query
$totalValue = $bookResource->getTotalInventoryValue();
```

**When to use:**
- Complex SQL queries
- Bulk operations
- Performance-critical operations
- Custom database logic

---

### 3. **COLLECTION** (`Model/ResourceModel/Book/Collection.php`)

**What:** Works with multiple records  
**Extends:** `AbstractCollection`  
**Purpose:**
- Filtering
- Sorting
- Pagination
- Lazy loading

**Example:**
```php
$collection = $collectionFactory->create();
$collection->addStatusFilter(1)           // WHERE status = 1
           ->addInStockFilter()           // WHERE stock_qty > 0
           ->addPriceFilter(20, 50)       // WHERE price BETWEEN 20 AND 50
           ->setOrderByTitle('ASC')       // ORDER BY title ASC
           ->setPageSize(10)              // LIMIT 10
           ->setCurPage(1);               // OFFSET 0

foreach ($collection as $book) {
    echo $book->getTitle();
}
```

**When to use:**
- Multiple records
- Filtering/sorting needed
- Pagination
- Grid displays

---

### 4. **REPOSITORY** (`Model/BookRepository.php`)

**What:** Service contract for data access  
**Implements:** `BookRepositoryInterface`  
**Purpose:**
- API-safe operations
- Type safety
- Best practice for modern Magento
- Abstraction layer

**Example:**
```php
// Get by ID
$book = $bookRepository->getById(1);

// Get by custom field
$book = $bookRepository->getByIsbn('978-1234567890');

// Save
$book->setPrice(39.99);
$bookRepository->save($book);

// Delete
$bookRepository->deleteById(1);

// Search with criteria
$searchCriteria = $searchCriteriaBuilder
    ->addFilter('author', 'Jane%', 'like')
    ->addFilter('status', 1)
    ->setPageSize(10)
    ->create();
$results = $bookRepository->getList($searchCriteria);
```

**When to use:**
- API endpoints
- Service layer
- WebAPI
- GraphQL
- Modern best practices

---

### 5. **CONTROLLER** (`Controller/Index/Index.php`)

**What:** Handles HTTP requests  
**Implements:** `HttpGetActionInterface` or `HttpPostActionInterface`  
**Purpose:**
- Route handling
- Request processing
- Return response

**Example:**
```php
class Index implements HttpGetActionInterface
{
    private $pageFactory;
    
    public function execute()
    {
        // Return a page
        return $this->pageFactory->create();
        
        // Or redirect
        // return $this->redirectFactory->create()->setPath('other/url');
        
        // Or JSON
        // return $this->jsonFactory->create()->setData($data);
    }
}
```

**Routes:**
- `books/index/index` → `/books/`
- `books/book/view` → `/books/book/view`

---

### 6. **BLOCK** (`Block/BookList.php`)

**What:** ViewModel - prepares data for templates  
**Extends:** `Template`  
**Purpose:**
- Business logic for presentation
- Format data for display
- Generate URLs
- Access collections/repositories

**Example:**
```php
class BookList extends Template
{
    public function getBookCollection()
    {
        return $this->collectionFactory->create()
            ->addStatusFilter(1);
    }
    
    public function formatPrice($price)
    {
        return '$' . number_format($price, 2);
    }
}
```

Used in template:
```php
<?php foreach ($block->getBookCollection() as $book): ?>
    <h2><?= $block->escapeHtml($book->getTitle()) ?></h2>
    <p><?= $block->formatPrice($book->getPrice()) ?></p>
<?php endforeach; ?>
```

---

### 7. **VIEW/TEMPLATE** (`view/frontend/templates/book/list.phtml`)

**What:** Presentation layer (HTML)  
**Purpose:**
- Display data
- User interface
- Use data from Block

**Available variables:**
- `$block` - The Block instance
- `$this` - Also the Block instance

**Always escape output:**
```php
<?= $block->escapeHtml($book->getTitle()) ?>     <!-- Text -->
<?= $block->escapeUrl($url) ?>                   <!-- URL -->
<?= $block->escapeHtmlAttr($attr) ?>             <!-- HTML attribute -->
```

---

## 🔄 Complete Flow Example

### User visits: `/books/book/view/id/1`

**Step 1: ROUTING**
```
routes.xml → frontName="books"
Controller: books/book/view
File: Controller/Book/View.php
```

**Step 2: CONTROLLER**
```php
public function execute()
{
    $bookId = $this->request->getParam('id');
    $book = $this->bookRepository->getById($bookId);  // ← Uses Repository
    $this->registry->register('current_book', $book);
    return $this->pageFactory->create();
}
```

**Step 3: REPOSITORY**
```php
public function getById($bookId)
{
    $book = $this->bookFactory->create();           // ← Creates Model
    $this->bookResource->load($book, $bookId);      // ← Uses ResourceModel
    return $book;
}
```

**Step 4: RESOURCE MODEL**
```php
public function _load($book, $id)
{
    $connection = $this->getConnection();
    // SELECT * FROM dudenkoff_book WHERE book_id = 1
    // Loads data into model
}
```

**Step 5: LAYOUT**
```xml
<!-- books_book_view.xml -->
<block class="Dudenkoff\MVVMLearn\Block\BookView"  ← Block/ViewModel
       template="Dudenkoff_MVVMLearn::book/view.phtml"/>  ← Template
```

**Step 6: BLOCK (ViewModel)**
```php
public function getBook()
{
    return $this->registry->registry('current_book');
}

public function formatPrice($price)
{
    return '$' . number_format($price, 2);
}
```

**Step 7: TEMPLATE (View)**
```php
$book = $block->getBook();
echo $block->formatPrice($book->getPrice());  // $29.99
```

**Step 8: RESPONSE**
```
HTML page sent to browser
```

---

## 💡 Pattern Comparison

| Pattern | Purpose | When to Use |
|---------|---------|-------------|
| **Model** | Single entity | Simple CRUD, business logic |
| **ResourceModel** | DB operations | Complex SQL, performance |
| **Collection** | Multiple records | Filtering, sorting, pagination |
| **Repository** | Service contract | API, services, best practice |
| **Controller** | Request handling | Routes, HTTP logic |
| **Block** | Presentation logic | Format data for templates |
| **Template** | Display | HTML output |

---

## 🎯 Best Practices

### ✅ DO:

1. **Use Repository for new code** - Modern Magento standard
2. **Use Collection for lists** - Efficient for multiple records
3. **Escape output in templates** - Security!
4. **Type-hint interfaces** - Not concrete classes
5. **Use service contracts** - For APIs

### ❌ DON'T:

1. Don't put business logic in templates
2. Don't use Model directly in controllers (use Repository)
3. Don't use direct SQL in controllers
4. Don't forget to escape output
5. Don't mix presentation and business logic

---

## 🧪 Learning Exercises

### Exercise 1: Use Model

```bash
docker-compose exec magento bin/magento dudenkoff:mvvm:book-examples -e model
```

### Exercise 2: Use Collection

```bash
docker-compose exec magento bin/magento dudenkoff:mvvm:book-examples -e collection
```

### Exercise 3: Visit Frontend

```
http://localhost:8080/books
```

### Exercise 4: View Book Detail

```
http://localhost:8080/books/book/view/id/1
```

---

## 📖 Key Concepts

### Model vs ResourceModel

**Model:**
- Represents entity
- Contains business logic
- Has getters/setters
- Example: `$book->getTitle()`

**ResourceModel:**
- Database layer
- Complex SQL
- Transactions
- Example: `$bookResource->updateStockQty()`

### Collection vs Repository

**Collection:**
- Works with multiple records
- SQL-like filtering
- Direct database query building
- Example: `$collection->addPriceFilter(20, 50)`

**Repository:**
- Service contract (interface)
- API-safe
- Uses SearchCriteria
- Modern best practice
- Example: `$repository->getList($searchCriteria)`

### Controller vs Block

**Controller:**
- Handles HTTP request
- Processes input
- Returns response type
- Minimal logic

**Block:**
- Prepares data for view
- Presentation logic
- Formats data
- Called from template

---

## 🎓 Code Examples

See `Console/Command/BookExamplesCommand.php` for detailed examples of:

1. Loading models
2. Using business logic methods
3. Custom ResourceModel queries
4. Collection filtering and sorting
5. Repository CRUD operations
6. SearchCriteria usage

---

## 🔍 Understanding the Layers

### **Data Layer** (Bottom)
- Database tables
- Defined in: `etc/db_schema.xml`

### **Resource Layer**
- `ResourceModel/Book.php`
- Direct SQL queries
- Database abstraction

### **Model Layer**
- `Model/Book.php`
- Business logic
- Entity representation

### **Service Layer** (Repository Pattern)
- `Api/BookRepositoryInterface.php`
- `Model/BookRepository.php`
- Service contracts
- Best practice for APIs

### **Controller Layer**
- `Controller/*/`
- Request handling
- Route mapping

### **Presentation Layer** (Top)
- `Block/` - ViewModels
- `view/frontend/templates/` - Views
- User interface

---

## 🚀 Next Steps

1. **Read the code** - All files are heavily commented
2. **Run CLI examples** - See patterns in action
3. **Visit frontend pages** - See MVC flow
4. **Modify templates** - Learn view layer
5. **Add new methods** - Practice

---

## 📚 Additional Resources

- Official Magento DevDocs
- This module's commented source code
- CLI command output (`dudenkoff:mvvm:book-examples`)

---

## ✨ Module Features

- ✅ Complete Model-View-Controller example
- ✅ Repository pattern (service contracts)
- ✅ Collection with custom filters
- ✅ Frontend controllers and pages
- ✅ Block/Template examples
- ✅ Fully commented code
- ✅ CLI demonstrations
- ✅ Sample data included

**This is your complete guide to understanding Magento's architecture!** 🎓

