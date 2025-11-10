# MVVMLearn Module - Setup Guide

## 🎯 Quick Start

```bash
# 1. Enable the module
docker-compose exec magento bin/magento module:enable Dudenkoff_MVVMLearn

# 2. Run setup (creates tables and adds sample data)
docker-compose exec magento bin/magento setup:upgrade

# 3. Clear cache
docker-compose exec magento bin/magento cache:clean

# 4. Test CLI examples
docker-compose exec magento bin/magento dudenkoff:mvvm:book-examples --example=all

# 5. Visit frontend
# Open browser: http://localhost:8080/books
```

---

## 📋 What Gets Created

### Database Table: `dudenkoff_book`

| Column | Type | Description |
|--------|------|-------------|
| book_id | INT | Primary key (auto-increment) |
| title | VARCHAR(255) | Book title |
| author | VARCHAR(255) | Author name |
| isbn | VARCHAR(20) | ISBN (unique) |
| description | TEXT | Book description |
| price | DECIMAL(10,2) | Price |
| stock_qty | INT | Stock quantity |
| status | SMALLINT | Status (1=enabled, 0=disabled) |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Update timestamp |

### Sample Data (3 books)

1. **PHP for Beginners** by John Doe - $29.99 (50 in stock)
2. **Magento 2 Developer Guide** by Jane Smith - $49.99 (30 in stock)
3. **MySQL Performance Tuning** by Bob Johnson - $39.99 (Out of stock)

---

## 🧪 Testing Each Pattern

### 1. Model Pattern

```bash
docker-compose exec magento bin/magento dudenkoff:mvvm:book-examples -e model
```

**Output:**
```
1. MODEL Pattern (ORM)
   Purpose: Represents a single entity with business logic

   Loaded: PHP for Beginners by John Doe
   Price: $29.99
   In Stock: Yes
   20% Discount: $23.99
```

**What it demonstrates:**
- Loading a model
- Using getters
- Business logic methods (`isInStock()`, `getDiscountedPrice()`)

---

### 2. ResourceModel Pattern

```bash
docker-compose exec magento bin/magento dudenkoff:mvvm:book-examples -e resource
```

**Output:**
```
2. RESOURCE MODEL Pattern (Database Operations)
   Purpose: Direct database access with custom SQL

   Books by 'Jane': 1
   Total Inventory Value: $4999.00
```

**What it demonstrates:**
- Custom SQL queries
- Aggregate functions
- Direct database operations

---

### 3. Collection Pattern

```bash
docker-compose exec magento bin/magento dudenkoff:mvvm:book-examples -e collection
```

**Output:**
```
3. COLLECTION Pattern (Multiple Records)
   Purpose: Work with sets of records, filtering, sorting

   In-Stock Books (2):
   - Magento 2 Developer Guide ($49.99)
   - PHP for Beginners ($29.99)

   Books over $40 (1):
   - Magento 2 Developer Guide ($49.99)
```

**What it demonstrates:**
- Collection filtering
- Sorting
- Working with multiple records
- Custom filter methods

---

### 4. Repository Pattern

```bash
docker-compose exec magento bin/magento dudenkoff:mvvm:book-examples -e repository
```

**Output:**
```
4. REPOSITORY Pattern (Service Contract)
   Purpose: API-safe data access, best practice for services

   getById(1): PHP for Beginners
   getByIsbn('978-1234567890'): PHP for Beginners

   ✓ Repository provides type-safe, API-compatible access
```

**What it demonstrates:**
- Service contract usage
- Type-safe operations
- API-compatible methods

---

## 🌐 Frontend Testing

### Book List Page

**URL:** `http://localhost:8080/books`

**What you'll see:**
- List of all books
- Title, Author, ISBN, Price
- Stock status
- "View Details" button for each book

**Behind the scenes:**
1. Routes.xml maps `/books` to `Controller/Index/Index.php`
2. Controller returns page
3. Layout XML (`books_index_index.xml`) defines structure
4. Block (`BookList.php`) provides data
5. Template (`list.phtml`) renders HTML

### Book Detail Page

**URL:** `http://localhost:8080/books/book/view/id/1`

**What you'll see:**
- Full book details
- Description
- Stock status with styling
- Created/Updated timestamps
- Back to list link

**Behind the scenes:**
1. Controller loads book via Repository
2. Stores in Registry
3. Block retrieves from Registry
4. Template displays formatted data

---

## 📊 Architecture Visualization

```
REQUEST: http://localhost:8080/books/book/view/id/1

   ↓
   
[ROUTER] (routes.xml)
   ↓
   
[CONTROLLER] (Controller/Book/View.php)
   ├─ Get ID from request
   ├─ Call Repository
   │   ↓
   │  [REPOSITORY] (BookRepository.php)
   │   ├─ Create Model
   │   ├─ Call ResourceModel
   │   │   ↓
   │   │  [RESOURCE MODEL] (ResourceModel/Book.php)
   │   │   └─ Execute SQL: SELECT * FROM dudenkoff_book WHERE book_id = 1
   │   │      ↓
   │   │     [DATABASE]
   │   │      ↓
   │   │   Load data into Model
   │   │   
   │   └─ Return Model
   │   
   ├─ Store in Registry
   └─ Return Page
   
   ↓
   
[LAYOUT] (books_book_view.xml)
   └─ Define Block and Template
   
   ↓
   
[BLOCK] (Block/BookView.php)
   ├─ Get book from Registry
   ├─ Format price
   └─ Prepare data
   
   ↓
   
[TEMPLATE] (view.phtml)
   └─ Render HTML using Block methods
   
   ↓
   
HTML Response → Browser
```

---

## 🔍 File Relationships

### Model Group
```
Api/Data/BookInterface.php          ← Interface (contract)
        ↓
Model/Book.php                       ← Implementation
        ↓
Model/ResourceModel/Book.php         ← Database layer
```

### Collection Group
```
Model/ResourceModel/Book.php
        ↓
Model/ResourceModel/Book/Collection.php  ← Works with Book ResourceModel
```

### Repository Group
```
Api/BookRepositoryInterface.php     ← Interface (contract)
        ↓
Model/BookRepository.php             ← Implementation
        ↓
Uses: BookFactory, BookResource, CollectionFactory
```

### MVC Flow
```
routes.xml → Controller → Repository → Model → ResourceModel → DB
                ↓
             Layout.xml
                ↓
              Block
                ↓
             Template
```

---

## 🎓 Learning Path

1. **Start with CLI Examples**
   ```bash
   docker-compose exec magento bin/magento dudenkoff:mvvm:book-examples --example=all
   ```

2. **Read the Code** - Open files in this order:
   - `Api/Data/BookInterface.php` - Data contract
   - `Model/Book.php` - Model implementation
   - `Model/ResourceModel/Book.php` - Database layer
   - `Model/ResourceModel/Book/Collection.php` - Collection
   - `Model/BookRepository.php` - Repository
   - `Controller/Index/Index.php` - Controller
   - `Block/BookList.php` - Block/ViewModel
   - `view/frontend/templates/book/list.phtml` - Template

3. **Visit Frontend Pages**
   - List: http://localhost:8080/books
   - Detail: http://localhost:8080/books/book/view/id/1

4. **Experiment**
   - Modify templates
   - Add new methods to Block
   - Add filters to Collection
   - Create new controllers

---

## 🔧 Troubleshooting

### Issue: Module not showing in list

```bash
docker-compose exec magento bin/magento module:status
```

### Issue: Tables not created

```bash
docker-compose exec magento bin/magento setup:db:status
docker-compose exec magento bin/magento setup:upgrade
```

### Issue: Frontend pages show 404

```bash
# Clear cache
docker-compose exec magento bin/magento cache:clean

# Check routes
docker-compose exec magento bin/magento setup:upgrade
```

### Issue: No sample data

```bash
# Check database
docker-compose exec magento bin/magento db:query "SELECT * FROM dudenkoff_book;"

# If empty, run setup:upgrade again
```

---

## ✅ Verification Checklist

- [ ] Module enabled: `bin/magento module:status | grep MVVMLearn`
- [ ] Table created: Check database for `dudenkoff_book`
- [ ] Sample data: 3 books in table
- [ ] CLI works: `bin/magento dudenkoff:mvvm:book-examples`
- [ ] Frontend accessible: http://localhost:8080/books
- [ ] Book detail works: Click on any book

---

## 🎯 Success!

If all checks pass, you're ready to learn! Start exploring the code and running the examples.

**Happy Learning!** 🚀

