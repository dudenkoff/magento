# MVVM/MVC Learning Module - Quick Start

## ✅ Module is Installed and Ready!

## 🚀 Three Ways to Learn

### 1. **CLI Examples** (Recommended Start)

```bash
# See all patterns in action
docker-compose exec magento bin/magento dudenkoff:mvvm:book-examples --example=all
```

**This will show you:**
- Model pattern (single entity)
- ResourceModel pattern (custom SQL)
- Collection pattern (multiple records with filtering)
- Repository pattern (service contract)

### 2. **Frontend Pages**

**Book List:**
```
http://localhost:8080/books
```
Shows all books with links to detail pages.

**Book Detail:**
```
http://localhost:8080/books/book/view/id/1
http://localhost:8080/books/book/view/id/2
http://localhost:8080/books/book/view/id/3
```

### 3. **Read the Code**

Start here (in order):
1. `README.md` - Module overview
2. `ARCHITECTURE.md` - Deep dive into patterns
3. `Model/Book.php` - Model example
4. `Model/ResourceModel/Book.php` - ResourceModel example
5. `Model/ResourceModel/Book/Collection.php` - Collection example
6. `Model/BookRepository.php` - Repository example
7. `Controller/Book/View.php` - Controller example
8. `Block/BookList.php` - Block/ViewModel example
9. `view/frontend/templates/book/list.phtml` - Template example

---

## 📋 Sample Data

The module includes 3 sample books:

| ID | Title | Author | Price | Stock |
|----|-------|--------|-------|-------|
| 1 | PHP for Beginners | John Doe | $29.99 | 50 |
| 2 | Magento 2 Developer Guide | Jane Smith | $49.99 | 30 |
| 3 | MySQL Performance Tuning | Bob Johnson | $39.99 | 0 |

---

## 🎯 Quick Command Reference

```bash
# Run all examples
docker-compose exec magento bin/magento dudenkoff:mvvm:book-examples -e all

# Individual patterns
docker-compose exec magento bin/magento dudenkoff:mvvm:book-examples -e model
docker-compose exec magento bin/magento dudenkoff:mvvm:book-examples -e resource
docker-compose exec magento bin/magento dudenkoff:mvvm:book-examples -e collection
docker-compose exec magento bin/magento dudenkoff:mvvm:book-examples -e repository
```

---

## 📚 What Each Pattern Demonstrates

### MODEL (-e model)
```
✓ Loading a model by ID
✓ Using getters (getTitle(), getAuthor(), etc.)
✓ Business logic (isInStock(), getDiscountedPrice())
```

### RESOURCE MODEL (-e resource)
```
✓ Custom SQL query (getBooksByAuthor)
✓ Aggregate function (getTotalInventoryValue)
✓ Direct database access
```

### COLLECTION (-e collection)
```
✓ Filtering (addStatusFilter, addInStockFilter)
✓ Price range filtering
✓ Sorting
✓ Iterating through results
```

### REPOSITORY (-e repository)
```
✓ getById() - Type-safe entity retrieval
✓ getByIsbn() - Custom field lookup
✓ Service contract pattern
✓ Exception handling
```

---

## 🌐 Frontend Pages

### List Page (/books/)

**Demonstrates:**
- Controller → Block → Template flow
- Collection usage in Block
- Template iteration
- URL generation
- Output escaping

**Try:**
- View source to see generated HTML
- Inspect Block methods used
- See how Collection is used

### Detail Page (/books/book/view/id/1)

**Demonstrates:**
- Controller with parameters
- Repository usage
- Registry pattern
- Block accessing stored data
- Conditional rendering

**Try:**
- Change book ID in URL
- View different books
- See how data flows from Controller → Block → Template

---

## 💡 Learning Tips

1. **Start with CLI** - Easiest to understand
2. **Read comments** - All code is documented
3. **Compare patterns** - See differences in approach
4. **Modify code** - Best way to learn
5. **Use Xdebug** - Set breakpoints and step through

---

## 🎓 Next Steps

1. ✅ Run CLI examples: `dudenkoff:mvvm:book-examples -e all`
2. ✅ Visit http://localhost:8080/books
3. ✅ Read `ARCHITECTURE.md` for deep understanding
4. ✅ Read `README.md` for complete guide
5. ✅ Experiment with the code!

---

## 🔍 Understanding MVC/MVVM

**MVC (Model-View-Controller):**
- **Model:** Book.php, BookRepository.php
- **View:** Templates (.phtml files)
- **Controller:** Controller/Book/View.php

**MVVM (Model-View-ViewModel):**
- **Model:** Book.php, BookRepository.php
- **View:** Templates (.phtml files)
- **ViewModel:** Block/BookList.php, Block/BookView.php

**Magento uses BOTH:**
- Controller handles requests (MVC)
- Block prepares data (MVVM)
- Template displays (View in both)

---

## ✨ You're Ready!

Everything is set up and working. Start exploring! 🚀

**Commands to try NOW:**
```bash
# See patterns
docker-compose exec magento bin/magento dudenkoff:mvvm:book-examples -e all

# Visit site
http://localhost:8080/books
```

