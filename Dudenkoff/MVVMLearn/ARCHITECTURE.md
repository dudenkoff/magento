# Magento MVC/MVVM Architecture Explained

## 🏛️ Architecture Overview

Magento uses a **layered architecture** combining MVC and MVVM patterns:

```
┌──────────────────────────────────────────────────────────┐
│                    PRESENTATION LAYER                     │
│  ┌────────────┐  ┌──────────┐  ┌─────────────┐          │
│  │ Controller │→ │  Block   │→ │  Template   │          │
│  │   (HTTP)   │  │(ViewModel)│  │   (View)    │          │
│  └────────────┘  └──────────┘  └─────────────┘          │
│         ↓                                                  │
└─────────┼──────────────────────────────────────────────────┘
          ↓
┌─────────┼──────────────────────────────────────────────────┐
│         ↓        SERVICE LAYER                             │
│  ┌─────────────┐  ┌────────────────┐                      │
│  │ Repository  │→ │ SearchCriteria │                      │
│  │(Interface)  │  │   (Filters)    │                      │
│  └─────────────┘  └────────────────┘                      │
│         ↓                                                   │
└─────────┼───────────────────────────────────────────────────┘
          ↓
┌─────────┼───────────────────────────────────────────────────┐
│         ↓        DOMAIN/BUSINESS LAYER                      │
│  ┌──────────┐  ┌──────────────┐                            │
│  │  Model   │  │  Collection  │                            │
│  │ (Entity) │  │ (Multi-row)  │                            │
│  └──────────┘  └──────────────┘                            │
│         ↓              ↓                                     │
└─────────┼──────────────┼─────────────────────────────────────┘
          ↓              ↓
┌─────────┼──────────────┼─────────────────────────────────────┐
│         ↓              ↓    DATA ACCESS LAYER                │
│  ┌──────────────────────────────┐                           │
│  │    ResourceModel             │                           │
│  │  (Database Abstraction)      │                           │
│  └──────────────────────────────┘                           │
│                 ↓                                            │
└─────────────────┼────────────────────────────────────────────┘
                  ↓
            ┌──────────┐
            │ DATABASE │
            └──────────┘
```

---

## 🎯 Layer Breakdown

### 1. **DATABASE** (Bottom Layer)

**File:** `etc/db_schema.xml`

**Defines:**
- Table structure
- Columns and types
- Indexes
- Constraints

```xml
<table name="dudenkoff_book" resource="default" engine="innodb">
    <column xsi:type="int" name="book_id" nullable="false" identity="true"/>
    <column xsi:type="varchar" name="title" nullable="false" length="255"/>
    <!-- ... -->
</table>
```

**Purpose:**
- Physical data storage
- Schema definition
- Relationships

---

### 2. **RESOURCE MODEL** (Data Access Layer)

**File:** `Model/ResourceModel/Book.php`

**Responsibilities:**
- Direct SQL queries
- CRUD operations
- Complex database logic
- Transactions

**Example:**
```php
class Book extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('dudenkoff_book', 'book_id');
    }
    
    public function getBooksByAuthor(string $author): array
    {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable())
            ->where('author LIKE ?', "%{$author}%");
        return $this->getConnection()->fetchAll($select);
    }
}
```

**When to use:**
- Custom SQL queries
- Bulk operations
- Performance-critical code
- Complex joins

---

### 3. **MODEL** (Domain Layer)

**Files:**
- `Api/Data/BookInterface.php` (contract)
- `Model/Book.php` (implementation)

**Responsibilities:**
- Entity representation
- Business logic
- Data validation
- Getters/setters

**Example:**
```php
class Book extends AbstractModel implements BookInterface
{
    protected function _construct()
    {
        $this->_init(\Dudenkoff\MVVMLearn\Model\ResourceModel\Book::class);
    }
    
    public function isInStock(): bool
    {
        return $this->getStockQty() > 0;
    }
    
    public function getDiscountedPrice(float $percent): float
    {
        return $this->getPrice() * (1 - $percent / 100);
    }
}
```

**When to use:**
- Single entity operations
- Business logic needed
- Simple CRUD

---

### 4. **COLLECTION** (Domain Layer)

**File:** `Model/ResourceModel/Book/Collection.php`

**Responsibilities:**
- Multiple record operations
- Filtering
- Sorting
- Pagination
- Lazy loading

**Example:**
```php
$collection = $collectionFactory->create();
$collection->addStatusFilter(1)
           ->addInStockFilter()
           ->addPriceFilter(20, 50)
           ->setOrderByTitle('ASC')
           ->setPageSize(10)
           ->setCurPage(1);

foreach ($collection as $book) {
    echo $book->getTitle();
}
```

**When to use:**
- Lists/grids
- Filtering needed
- Multiple records
- Pagination

---

### 5. **REPOSITORY** (Service Layer)

**Files:**
- `Api/BookRepositoryInterface.php` (contract)
- `Model/BookRepository.php` (implementation)

**Responsibilities:**
- Service contract implementation
- Type-safe operations
- API compatibility
- Search criteria handling

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
    ->setPageSize(10)
    ->create();
$results = $bookRepository->getList($searchCriteria);
```

**When to use:**
- API endpoints
- WebAPI
- GraphQL
- Service layer
- Modern best practice

---

### 6. **CONTROLLER** (Presentation Layer)

**Files:**
- `Controller/Index/Index.php` - List page
- `Controller/Book/View.php` - Detail page

**Responsibilities:**
- Handle HTTP requests
- Process input
- Call business logic
- Return response

**Types:**
- `HttpGetActionInterface` - GET requests
- `HttpPostActionInterface` - POST requests

**Example:**
```php
class View implements HttpGetActionInterface
{
    private $bookRepository;
    private $pageFactory;
    
    public function execute()
    {
        $id = $this->request->getParam('id');
        $book = $this->bookRepository->getById($id);
        $this->registry->register('current_book', $book);
        return $this->pageFactory->create();
    }
}
```

**URL Mapping:**
```
Route: books/book/view
URL: /books/book/view/id/1
File: Controller/Book/View.php
```

---

### 7. **BLOCK** (ViewModel - Presentation Layer)

**Files:**
- `Block/BookList.php`
- `Block/BookView.php`

**Responsibilities:**
- Prepare data for templates
- Presentation logic
- Format data
- Generate URLs
- Access collections

**Example:**
```php
class BookList extends Template
{
    public function getBookCollection()
    {
        return $this->collectionFactory->create()
            ->addStatusFilter(1)
            ->setOrderByTitle('ASC');
    }
    
    public function formatPrice(float $price): string
    {
        return '$' . number_format($price, 2);
    }
    
    public function getBookUrl(int $bookId): string
    {
        return $this->getUrl('books/book/view', ['id' => $bookId]);
    }
}
```

**When to use:**
- Prepare data for display
- Format values
- Generate URLs
- Access data sources

---

### 8. **TEMPLATE** (View - Presentation Layer)

**Files:**
- `view/frontend/templates/book/list.phtml`
- `view/frontend/templates/book/view.phtml`

**Responsibilities:**
- HTML output
- Display data from Block
- User interface

**Example:**
```php
<?php foreach ($block->getBookCollection() as $book): ?>
    <h2><?= $block->escapeHtml($book->getTitle()) ?></h2>
    <p>Price: <?= $block->formatPrice($book->getPrice()) ?></p>
    <a href="<?= $block->escapeUrl($block->getBookUrl($book->getBookId())) ?>">
        View Details
    </a>
<?php endforeach; ?>
```

**Always escape:**
- `escapeHtml()` - Text content
- `escapeUrl()` - URLs
- `escapeHtmlAttr()` - HTML attributes

---

## 🔄 Complete Request Flow

### Example: User visits `/books/book/view/id/1`

**Step 1: ROUTING**
```
routes.xml defines: frontName="books"
URL /books/book/view → Controller/Book/View.php
```

**Step 2: CONTROLLER**
```php
public function execute()
{
    $id = $this->request->getParam('id');  // Get ID from URL
    $book = $this->bookRepository->getById($id);  // Load book
    $this->registry->register('current_book', $book);  // Store for later
    return $this->pageFactory->create();  // Render page
}
```

**Step 3: REPOSITORY → MODEL → RESOURCE MODEL**
```php
// Repository
$book = $this->bookFactory->create();
$this->bookResource->load($book, $id);

// ResourceModel
SELECT * FROM dudenkoff_book WHERE book_id = 1

// Model loaded with data
$book->getTitle() // Returns "PHP for Beginners"
```

**Step 4: LAYOUT**
```xml
<block class="BookView" 
       name="book.view"
       template="book/view.phtml"/>
```

**Step 5: BLOCK**
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

**Step 6: TEMPLATE**
```php
$book = $block->getBook();
<h1><?= $block->escapeHtml($book->getTitle()) ?></h1>
<p>Price: <?= $block->formatPrice($book->getPrice()) ?></p>
```

**Step 7: RESPONSE**
```
HTML sent to browser
```

---

## 📊 Pattern Comparison Matrix

| Feature | Model | ResourceModel | Collection | Repository |
|---------|-------|---------------|------------|------------|
| **Records** | Single | Single/Multiple | Multiple | Single/Multiple |
| **SQL Access** | No | Yes (direct) | Yes (query builder) | No (abstracted) |
| **Business Logic** | Yes | No | No | No |
| **API Safe** | No | No | No | Yes |
| **Performance** | Medium | High | Medium | Medium |
| **Type Safety** | Partial | No | No | Full |
| **Best For** | Simple CRUD | Complex queries | Lists/Grids | APIs/Services |

---

## 🎯 When to Use What

### Use **MODEL** when:
- ✅ Working with single record
- ✅ Need business logic
- ✅ Simple get/set operations
- ✅ Internal code only

### Use **RESOURCE MODEL** when:
- ✅ Complex SQL queries
- ✅ Bulk operations
- ✅ Performance critical
- ✅ Custom database logic
- ✅ Aggregations (SUM, COUNT, etc.)

### Use **COLLECTION** when:
- ✅ Multiple records
- ✅ Filtering/sorting needed
- ✅ Pagination required
- ✅ Admin grids
- ✅ Lists/catalogs

### Use **REPOSITORY** when:
- ✅ API endpoints
- ✅ WebAPI/REST/GraphQL
- ✅ Service layer
- ✅ **Modern best practice** (always prefer for new code)
- ✅ Type safety needed

---

## 💡 Best Practices

### ✅ DO:

1. **Always use Repository in new code**
   ```php
   // ✅ GOOD - Repository
   $book = $this->bookRepository->getById($id);
   
   // ❌ OLD - Direct model loading
   $book = $this->bookFactory->create();
   $this->bookResource->load($book, $id);
   ```

2. **Use Collections for lists**
   ```php
   // ✅ GOOD
   $collection = $this->collectionFactory->create()
       ->addStatusFilter(1)
       ->setPageSize(10);
   ```

3. **Escape all output in templates**
   ```php
   // ✅ GOOD
   <?= $block->escapeHtml($book->getTitle()) ?>
   
   // ❌ DANGEROUS (XSS vulnerability)
   <?= $book->getTitle() ?>
   ```

4. **Use interfaces for type hints**
   ```php
   // ✅ GOOD
   public function __construct(BookRepositoryInterface $repo)
   
   // ❌ BAD
   public function __construct(BookRepository $repo)
   ```

5. **Keep controllers thin**
   ```php
   // ✅ GOOD - Controller just coordinates
   public function execute()
   {
       $book = $this->bookRepository->getById($id);
       $this->registry->register('current_book', $book);
       return $this->pageFactory->create();
   }
   
   // ❌ BAD - Business logic in controller
   public function execute()
   {
       $book = $this->bookFactory->create();
       $this->bookResource->load($book, $id);
       if ($book->getStockQty() > 0) {
           $book->setPrice($book->getPrice() * 0.9);
           // ... lots of logic ...
       }
   }
   ```

### ❌ DON'T:

1. Don't put business logic in templates
2. Don't put SQL in controllers
3. Don't use `ObjectManager` directly
4. Don't forget to clear cache after changes
5. Don't mix layers (e.g., Controller accessing DB directly)

---

## 🎓 Code Organization

### Naming Conventions

**Model:**
```
Interface:      Api/Data/BookInterface.php
Implementation: Model/Book.php
```

**Repository:**
```
Interface:      Api/BookRepositoryInterface.php
Implementation: Model/BookRepository.php
```

**ResourceModel:**
```
Model:          Model/ResourceModel/Book.php
Collection:     Model/ResourceModel/Book/Collection.php
```

**Controller:**
```
Route:    books/book/view
File:     Controller/Book/View.php
Class:    Dudenkoff\MVVMLearn\Controller\Book\View
```

**Block:**
```
Class:    Block/BookList.php
Template: view/frontend/templates/book/list.phtml
Layout:   view/frontend/layout/books_index_index.xml
```

---

## 🔍 Understanding Each File Type

### Model (`Model/Book.php`)

```php
class Book extends AbstractModel implements BookInterface
{
    // Links to ResourceModel
    protected function _construct()
    {
        $this->_init(ResourceModel\Book::class);
    }
    
    // Getters/Setters
    public function getTitle() { return $this->getData('title'); }
    public function setTitle($title) { return $this->setData('title', $title); }
    
    // Business Logic
    public function isInStock(): bool
    {
        return $this->getStockQty() > 0;
    }
}
```

**Key methods:**
- `getData($key)` - Get field value
- `setData($key, $value)` - Set field value
- `getId()` - Get primary key
- `save()` - Save to database
- `delete()` - Delete from database

---

### ResourceModel (`Model/ResourceModel/Book.php`)

```php
class Book extends AbstractDb
{
    // Define table and primary key
    protected function _construct()
    {
        $this->_init('dudenkoff_book', 'book_id');
    }
    
    // Custom queries
    public function getBooksByAuthor(string $author): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where('author LIKE ?', "%{$author}%");
        return $connection->fetchAll($select);
    }
}
```

**Key methods:**
- `getConnection()` - Get DB connection
- `getMainTable()` - Get table name
- `load($model, $id)` - Load data into model
- `save($model)` - Save model to DB
- `delete($model)` - Delete from DB

---

### Collection (`Model/ResourceModel/Book/Collection.php`)

```php
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(BookModel::class, BookResource::class);
    }
    
    // Custom filters
    public function addStatusFilter(int $status = 1)
    {
        $this->addFieldToFilter('status', $status);
        return $this;
    }
    
    public function addPriceFilter(float $min, ?float $max = null)
    {
        $this->addFieldToFilter('price', ['gteq' => $min]);
        if ($max) {
            $this->addFieldToFilter('price', ['lteq' => $max]);
        }
        return $this;
    }
}
```

**Key methods:**
- `addFieldToFilter($field, $condition)` - Add WHERE clause
- `setOrder($field, $direction)` - Add ORDER BY
- `setPageSize($size)` - Set LIMIT
- `setCurPage($page)` - Set OFFSET
- `getSize()` - Get total count
- `getItems()` - Get array of models

---

### Repository (`Model/BookRepository.php`)

```php
class BookRepository implements BookRepositoryInterface
{
    public function getById(int $bookId): BookInterface
    {
        $book = $this->bookFactory->create();
        $this->bookResource->load($book, $bookId);
        
        if (!$book->getBookId()) {
            throw new NoSuchEntityException(__('Book not found'));
        }
        
        return $book;
    }
    
    public function save(BookInterface $book): BookInterface
    {
        try {
            $this->bookResource->save($book);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }
        return $book;
    }
    
    public function getList(SearchCriteriaInterface $criteria)
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        
        return $searchResults;
    }
}
```

**Key features:**
- Type-safe (uses interfaces)
- Exception handling
- SearchCriteria support
- API compatible

---

### Controller (`Controller/Book/View.php`)

```php
class View implements HttpGetActionInterface
{
    private $pageFactory;
    private $bookRepository;
    private $registry;
    
    public function execute()
    {
        // 1. Get input
        $bookId = $this->request->getParam('id');
        
        // 2. Load data (via Repository)
        $book = $this->bookRepository->getById($bookId);
        
        // 3. Store for Block access
        $this->registry->register('current_book', $book);
        
        // 4. Return response
        return $this->pageFactory->create();
    }
}
```

**Return types:**
- `PageFactory` - Full page
- `RedirectFactory` - Redirect
- `JsonFactory` - JSON response
- `RawFactory` - Raw output

---

### Block (`Block/BookList.php`)

```php
class BookList extends Template
{
    private $collectionFactory;
    
    public function getBookCollection()
    {
        return $this->collectionFactory->create()
            ->addStatusFilter(1);
    }
    
    public function formatPrice(float $price): string
    {
        return '$' . number_format($price, 2);
    }
    
    public function getBookUrl(int $bookId): string
    {
        return $this->getUrl('books/book/view', ['id' => $bookId]);
    }
}
```

**Used in template:**
```php
<?php foreach ($block->getBookCollection() as $book): ?>
    <p>Price: <?= $block->formatPrice($book->getPrice()) ?></p>
    <a href="<?= $block->getBookUrl($book->getBookId()) ?>">View</a>
<?php endforeach; ?>
```

---

### Template (`view/frontend/templates/book/list.phtml`)

```php
<?php
/**
 * @var $block \Dudenkoff\MVVMLearn\Block\BookList
 */
?>
<div class="book-list">
    <?php foreach ($block->getBookCollection() as $book): ?>
        <h2><?= $block->escapeHtml($book->getTitle()) ?></h2>
        <p><?= $block->formatPrice($book->getPrice()) ?></p>
    <?php endforeach; ?>
</div>
```

**Template has access to:**
- `$block` - Block instance
- All public methods in Block
- Can call: `$block->methodName()`

---

## 🎯 Summary

### The Layers (Top to Bottom):

1. **HTTP Request** → User clicks/types URL
2. **Router** → Maps URL to Controller
3. **Controller** → Handles request, calls Repository
4. **Repository** → Service contract, loads Model
5. **Model** → Entity with business logic
6. **ResourceModel** → Database operations
7. **Database** → Physical storage

### For Display (Parallel):

1. **Layout XML** → Defines page structure
2. **Block** → Prepares data
3. **Template** → Renders HTML
4. **HTTP Response** → Sent to browser

---

## ✨ Key Takeaways

1. **Separation of Concerns** - Each layer has specific responsibility
2. **Repository is Modern Standard** - Use for all new code
3. **Collections for Lists** - Efficient for multiple records
4. **ResourceModel for Complex SQL** - When you need performance
5. **Blocks = ViewModels** - Prepare data for templates
6. **Templates = Pure View** - Only display, no logic
7. **Controllers = Thin** - Just coordinate, don't contain logic

---

**You now have a complete understanding of Magento's MVC/MVVM architecture!** 🚀

