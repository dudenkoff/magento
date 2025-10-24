# Dudenkoff ApiLearn - cURL Commands Reference

**Base URL:** `http://localhost:8080/rest`  
All endpoints return JSON responses.

---

## Table of Contents

1. [Anonymous Endpoints](#section-1-anonymous-endpoints)
2. [Protected Endpoints](#section-2-protected-endpoints)
3. [Testing & Utilities](#section-3-testing--utilities)
4. [SearchCriteria Examples](#section-4-searchcriteria-examples)
5. [Useful One-Liners](#section-5-useful-one-liners)
6. [Quick Reference](#quick-reference)

---

## Section 1: Anonymous Endpoints
*No Authentication Required*

### 1.1 GET - Retrieve a Single Note by ID

Get note with ID 1:
```bash
curl -X GET "http://localhost:8080/rest/V1/dudenkoff/notes/1"
```

Get note with ID 5:
```bash
curl -X GET "http://localhost:8080/rest/V1/dudenkoff/notes/5"
```

Pretty print with jq:
```bash
curl -s "http://localhost:8080/rest/V1/dudenkoff/notes/1" | jq '.'
```

---

### 1.2 GET - Search/List Notes (with SearchCriteria)

Get all notes (empty search criteria):
```bash
curl -X GET "http://localhost:8080/rest/V1/dudenkoff/notes/search?searchCriteria="
```

Get all notes with pretty JSON:
```bash
curl -s "http://localhost:8080/rest/V1/dudenkoff/notes/search?searchCriteria=" | jq '.'
```

Filter by title (exact match):
```bash
curl -X GET "http://localhost:8080/rest/V1/dudenkoff/notes/search?\
searchCriteria[filterGroups][0][filters][0][field]=title&\
searchCriteria[filterGroups][0][filters][0][value]=Welcome%20to%20Magento%202%20API&\
searchCriteria[filterGroups][0][filters][0][conditionType]=eq"
```

Filter by author (like/contains):
```bash
curl -X GET "http://localhost:8080/rest/V1/dudenkoff/notes/search?\
searchCriteria[filterGroups][0][filters][0][field]=author&\
searchCriteria[filterGroups][0][filters][0][value]=Dudenkoff&\
searchCriteria[filterGroups][0][filters][0][conditionType]=like"
```

Filter published notes only:
```bash
curl -X GET "http://localhost:8080/rest/V1/dudenkoff/notes/search?\
searchCriteria[filterGroups][0][filters][0][field]=is_published&\
searchCriteria[filterGroups][0][filters][0][value]=1&\
searchCriteria[filterGroups][0][filters][0][conditionType]=eq"
```

Filter unpublished (draft) notes:
```bash
curl -X GET "http://localhost:8080/rest/V1/dudenkoff/notes/search?\
searchCriteria[filterGroups][0][filters][0][field]=is_published&\
searchCriteria[filterGroups][0][filters][0][value]=0&\
searchCriteria[filterGroups][0][filters][0][conditionType]=eq"
```

Sort by title ascending:
```bash
curl -X GET "http://localhost:8080/rest/V1/dudenkoff/notes/search?\
searchCriteria[sortOrders][0][field]=title&\
searchCriteria[sortOrders][0][direction]=ASC"
```

Sort by created_at descending (newest first):
```bash
curl -X GET "http://localhost:8080/rest/V1/dudenkoff/notes/search?\
searchCriteria[sortOrders][0][field]=created_at&\
searchCriteria[sortOrders][0][direction]=DESC"
```

Pagination - page 1 with 5 items per page:
```bash
curl -X GET "http://localhost:8080/rest/V1/dudenkoff/notes/search?\
searchCriteria[pageSize]=5&\
searchCriteria[currentPage]=1"
```

Pagination - page 2 with 5 items per page:
```bash
curl -X GET "http://localhost:8080/rest/V1/dudenkoff/notes/search?\
searchCriteria[pageSize]=5&\
searchCriteria[currentPage]=2"
```

Complex query: Published notes by Dudenkoff, sorted by date:
```bash
curl -X GET "http://localhost:8080/rest/V1/dudenkoff/notes/search?\
searchCriteria[filterGroups][0][filters][0][field]=author&\
searchCriteria[filterGroups][0][filters][0][value]=Dudenkoff&\
searchCriteria[filterGroups][0][filters][0][conditionType]=eq&\
searchCriteria[filterGroups][1][filters][0][field]=is_published&\
searchCriteria[filterGroups][1][filters][0][value]=1&\
searchCriteria[filterGroups][1][filters][0][conditionType]=eq&\
searchCriteria[sortOrders][0][field]=created_at&\
searchCriteria[sortOrders][0][direction]=DESC"
```

---

### 1.3 POST - Create a New Note

Create a simple note:
```bash
curl -X POST "http://localhost:8080/rest/V1/dudenkoff/notes" \
  -H "Content-Type: application/json" \
  -d '{
    "note": {
      "title": "My First API Note",
      "content": "This note was created via REST API",
      "author": "Developer",
      "is_published": true
    }
  }'
```

Create a draft note (unpublished):
```bash
curl -X POST "http://localhost:8080/rest/V1/dudenkoff/notes" \
  -H "Content-Type: application/json" \
  -d '{
    "note": {
      "title": "Draft Note",
      "content": "This is a draft that is not published yet",
      "author": "Editor",
      "is_published": false
    }
  }'
```

Create with pretty response:
```bash
curl -s -X POST "http://localhost:8080/rest/V1/dudenkoff/notes" \
  -H "Content-Type: application/json" \
  -d '{
    "note": {
      "title": "Testing Note",
      "content": "Just testing the API",
      "author": "Tester",
      "is_published": true
    }
  }' | jq '.'
```

---

### 1.4 PUT - Update an Existing Note

Update note ID 3:
```bash
curl -X PUT "http://localhost:8080/rest/V1/dudenkoff/notes/3" \
  -H "Content-Type: application/json" \
  -d '{
    "note": {
      "note_id": 3,
      "title": "Updated Title",
      "content": "Updated content here",
      "author": "Updated Author",
      "is_published": true
    }
  }'
```

Update only title and content (partial update):
```bash
curl -X PUT "http://localhost:8080/rest/V1/dudenkoff/notes/5" \
  -H "Content-Type: application/json" \
  -d '{
    "note": {
      "note_id": 5,
      "title": "New Title Only",
      "content": "New content only"
    }
  }'
```

Publish a draft note by updating is_published:
```bash
curl -X PUT "http://localhost:8080/rest/V1/dudenkoff/notes/6" \
  -H "Content-Type: application/json" \
  -d '{
    "note": {
      "note_id": 6,
      "is_published": true
    }
  }'
```

---

### 1.5 DELETE - Remove a Note

Delete note ID 11:
```bash
curl -X DELETE "http://localhost:8080/rest/V1/dudenkoff/notes/11"
```

Delete with verbose output:
```bash
curl -v -X DELETE "http://localhost:8080/rest/V1/dudenkoff/notes/12"
```

Delete and show response:
```bash
curl -s -X DELETE "http://localhost:8080/rest/V1/dudenkoff/notes/13" | jq '.'
```

---

### 1.6 POST - Publish a Note (Custom Business Logic)

Publish note ID 3:
```bash
curl -X POST "http://localhost:8080/rest/V1/dudenkoff/notes/publish/3"
```

Publish note ID 6 with pretty output:
```bash
curl -s -X POST "http://localhost:8080/rest/V1/dudenkoff/notes/publish/6" | jq '.'
```

Publish note ID 8:
```bash
curl -X POST "http://localhost:8080/rest/V1/dudenkoff/notes/publish/8"
```

---

## Section 2: Protected Endpoints
*Requires Admin Authentication*

### 2.1 GET ADMIN TOKEN (Authentication)

Get admin token:
```bash
curl -X POST "http://localhost:8080/rest/V1/integration/admin/token" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin",
    "password": "your_admin_password"
  }'
```

Store token in variable (Linux/Mac):
```bash
TOKEN=$(curl -s -X POST "http://localhost:8080/rest/V1/integration/admin/token" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"your_password"}' | tr -d '"')
```

Verify token is set:
```bash
echo $TOKEN
```

Alternative: Store in file:
```bash
curl -s -X POST "http://localhost:8080/rest/V1/integration/admin/token" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"your_password"}' | tr -d '"' > token.txt
```

Read token from file:
```bash
TOKEN=$(cat token.txt)
```

---

### 2.2 PROTECTED GET - View Note
*Requires `note_view` permission*

Get note with admin authentication:
```bash
curl -X GET "http://localhost:8080/rest/V1/dudenkoff/notes/admin/1" \
  -H "Authorization: Bearer $TOKEN"
```

Get note with token and pretty output:
```bash
curl -s -X GET "http://localhost:8080/rest/V1/dudenkoff/notes/admin/5" \
  -H "Authorization: Bearer $TOKEN" | jq '.'
```

---

### 2.3 PROTECTED POST - Create Note
*Requires `note_create` permission*

Create note with admin authentication:
```bash
curl -X POST "http://localhost:8080/rest/V1/dudenkoff/notes/admin" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "note": {
      "title": "Admin Created Note",
      "content": "This note was created by an admin user",
      "author": "Admin",
      "is_published": true
    }
  }'
```

---

### 2.4 PROTECTED PUT - Update Note
*Requires `note_edit` permission*

Update note with admin authentication:
```bash
curl -X PUT "http://localhost:8080/rest/V1/dudenkoff/notes/admin/7" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "note": {
      "note_id": 7,
      "title": "Admin Updated",
      "content": "Updated by admin",
      "author": "Admin Editor",
      "is_published": true
    }
  }'
```

---

### 2.5 PROTECTED DELETE - Remove Note
*Requires `note_delete` permission*

Delete note with admin authentication:
```bash
curl -X DELETE "http://localhost:8080/rest/V1/dudenkoff/notes/admin/15" \
  -H "Authorization: Bearer $TOKEN"
```

---

### 2.6 PROTECTED POST - Publish Note
*Requires `note_publish` permission*

Publish note with admin authentication:
```bash
curl -X POST "http://localhost:8080/rest/V1/dudenkoff/notes/admin/publish/8" \
  -H "Authorization: Bearer $TOKEN"
```

---

## Section 3: Testing & Utilities

### 3.1 Test All Endpoints Quickly

Test anonymous endpoints:
```bash
echo "Testing GET single note..."
curl -s "http://localhost:8080/rest/V1/dudenkoff/notes/1" | jq '.title'

echo "Testing GET all notes..."
curl -s "http://localhost:8080/rest/V1/dudenkoff/notes/search?searchCriteria=" | jq '.total_count'

echo "Testing POST create note..."
curl -s -X POST "http://localhost:8080/rest/V1/dudenkoff/notes" \
  -H "Content-Type: application/json" \
  -d '{"note":{"title":"Test","content":"Testing","author":"Tester","is_published":true}}' | jq '.note_id'

echo "Testing POST publish..."
curl -s -X POST "http://localhost:8080/rest/V1/dudenkoff/notes/publish/3" | jq '.is_published'
```

---

### 3.2 Error Testing

Test non-existent note (should return 404):
```bash
curl -X GET "http://localhost:8080/rest/V1/dudenkoff/notes/99999"
```

Test invalid JSON (should return 400):
```bash
curl -X POST "http://localhost:8080/rest/V1/dudenkoff/notes" \
  -H "Content-Type: application/json" \
  -d 'invalid json'
```

Test missing required fields:
```bash
curl -X POST "http://localhost:8080/rest/V1/dudenkoff/notes" \
  -H "Content-Type: application/json" \
  -d '{
    "note": {
      "title": "Only Title"
    }
  }'
```

Test protected endpoint without token (should return 401):
```bash
curl -X GET "http://localhost:8080/rest/V1/dudenkoff/notes/admin/1"
```

---

### 3.3 Response Handling

Save response to file:
```bash
curl -s "http://localhost:8080/rest/V1/dudenkoff/notes/1" > note.json
```

Extract specific field:
```bash
curl -s "http://localhost:8080/rest/V1/dudenkoff/notes/1" | jq '.title'
```

Count items in search:
```bash
curl -s "http://localhost:8080/rest/V1/dudenkoff/notes/search?searchCriteria=" | jq '.items | length'
```

Show only titles from search:
```bash
curl -s "http://localhost:8080/rest/V1/dudenkoff/notes/search?searchCriteria=" | jq '.items[].title'
```

Show HTTP status code:
```bash
curl -s -o /dev/null -w "%{http_code}" "http://localhost:8080/rest/V1/dudenkoff/notes/1"
```

Verbose mode (show headers):
```bash
curl -v "http://localhost:8080/rest/V1/dudenkoff/notes/1"
```

---

### 3.4 Batch Operations

Create multiple notes in sequence:
```bash
for i in {1..5}; do
  curl -s -X POST "http://localhost:8080/rest/V1/dudenkoff/notes" \
    -H "Content-Type: application/json" \
    -d "{\"note\":{\"title\":\"Batch Note $i\",\"content\":\"Content $i\",\"author\":\"Batch\",\"is_published\":true}}" \
    | jq '.note_id'
done
```

Get all notes and save IDs:
```bash
curl -s "http://localhost:8080/rest/V1/dudenkoff/notes/search?searchCriteria=" | jq '.items[].note_id'
```

---

## Section 4: SearchCriteria Examples

### Condition Types Available

- `eq` - equals
- `neq` - not equals
- `like` - contains (use % wildcards)
- `nlike` - not like
- `in` - in array
- `nin` - not in array
- `gt` - greater than
- `lt` - less than
- `gteq` - greater than or equal
- `lteq` - less than or equal
- `null` - is null
- `notnull` - is not null

---

### Greater than / Less than

Notes with ID greater than 5:
```bash
curl -X GET "http://localhost:8080/rest/V1/dudenkoff/notes/search?\
searchCriteria[filterGroups][0][filters][0][field]=note_id&\
searchCriteria[filterGroups][0][filters][0][value]=5&\
searchCriteria[filterGroups][0][filters][0][conditionType]=gt"
```

Notes created after specific date:
```bash
curl -X GET "http://localhost:8080/rest/V1/dudenkoff/notes/search?\
searchCriteria[filterGroups][0][filters][0][field]=created_at&\
searchCriteria[filterGroups][0][filters][0][value]=2025-10-20%2000:00:00&\
searchCriteria[filterGroups][0][filters][0][conditionType]=gt"
```

---

### IN / NOT IN

Get notes with IDs 1, 3, 5:
```bash
curl -X GET "http://localhost:8080/rest/V1/dudenkoff/notes/search?\
searchCriteria[filterGroups][0][filters][0][field]=note_id&\
searchCriteria[filterGroups][0][filters][0][value]=1,3,5&\
searchCriteria[filterGroups][0][filters][0][conditionType]=in"
```

---

### Multiple Filter Groups (OR logic between groups)

Get notes by Dudenkoff OR notes that are published:  
*(Author = Dudenkoff) OR (is_published = 1)*

```bash
curl -X GET "http://localhost:8080/rest/V1/dudenkoff/notes/search?\
searchCriteria[filterGroups][0][filters][0][field]=author&\
searchCriteria[filterGroups][0][filters][0][value]=Dudenkoff&\
searchCriteria[filterGroups][0][filters][0][conditionType]=eq&\
searchCriteria[filterGroups][1][filters][0][field]=is_published&\
searchCriteria[filterGroups][1][filters][0][value]=1&\
searchCriteria[filterGroups][1][filters][0][conditionType]=eq"
```

---

## Section 5: Useful One-Liners

Quick check if API is working:
```bash
curl -s "http://localhost:8080/rest/V1/dudenkoff/notes/search?searchCriteria=" | jq '.total_count'
```

Create and immediately get the new note ID:
```bash
NEW_ID=$(curl -s -X POST "http://localhost:8080/rest/V1/dudenkoff/notes" \
  -H "Content-Type: application/json" \
  -d '{"note":{"title":"Quick Note","content":"Fast","author":"CLI","is_published":true}}' \
  | jq '.note_id') && echo "Created note ID: $NEW_ID"
```

Get all published notes count:
```bash
curl -s "http://localhost:8080/rest/V1/dudenkoff/notes/search?\
searchCriteria[filterGroups][0][filters][0][field]=is_published&\
searchCriteria[filterGroups][0][filters][0][value]=1" | jq '.total_count'
```

Get all draft notes count:
```bash
curl -s "http://localhost:8080/rest/V1/dudenkoff/notes/search?\
searchCriteria[filterGroups][0][filters][0][field]=is_published&\
searchCriteria[filterGroups][0][filters][0][value]=0" | jq '.total_count'
```

---

## Important Notes

1. Replace `your_admin_password` with your actual admin password
2. All anonymous endpoints are accessible without authentication
3. Protected endpoints require admin token in Authorization header
4. Use `jq` for pretty JSON formatting (install with: `sudo apt install jq`)
5. Base URL is `http://localhost:8080/rest` - adjust if your setup differs
6. ACL permissions must be enabled in Admin panel for protected endpoints

---

## Quick Reference

### Anonymous Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/V1/dudenkoff/notes/:noteId` | Get single note |
| GET | `/V1/dudenkoff/notes/search` | Search/list notes |
| POST | `/V1/dudenkoff/notes` | Create note |
| PUT | `/V1/dudenkoff/notes/:noteId` | Update note |
| DELETE | `/V1/dudenkoff/notes/:noteId` | Delete note |
| POST | `/V1/dudenkoff/notes/publish/:noteId` | Publish note |

### Protected Endpoints (require admin token)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/V1/dudenkoff/notes/admin/:noteId` | Get note (ACL protected) |
| POST | `/V1/dudenkoff/notes/admin` | Create note (ACL protected) |
| PUT | `/V1/dudenkoff/notes/admin/:noteId` | Update note (ACL protected) |
| DELETE | `/V1/dudenkoff/notes/admin/:noteId` | Delete note (ACL protected) |
| POST | `/V1/dudenkoff/notes/admin/publish/:noteId` | Publish note (ACL protected) |

### Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/V1/integration/admin/token` | Get admin token |

---

**End of cURL Commands Reference**

