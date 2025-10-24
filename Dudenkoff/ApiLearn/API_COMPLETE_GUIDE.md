# Magento 2 API Complete Guide - Dudenkoff ApiLearn Module

A comprehensive guide covering ACL (Access Control List), authentication, and REST API usage with practical examples.

**Base URL:** `http://localhost:8080/rest`

---

## Table of Contents

### Part 1: Understanding ACL & Security
1. [What is ACL?](#what-is-acl)
2. [How ACL Works](#how-acl-works)
3. [Resource Reference Types](#resource-reference-types)
4. [ACL Configuration](#acl-configuration)
5. [Authentication Methods](#authentication-methods)

### Part 2: Practical API Usage
6. [Anonymous Endpoints](#anonymous-endpoints)
7. [Protected Endpoints](#protected-endpoints)
8. [SearchCriteria Examples](#searchcriteria-examples)
9. [Testing & Utilities](#testing--utilities)

### Part 3: Advanced Topics
10. [Permission Levels & Roles](#permission-levels--roles)
11. [Error Handling](#error-responses)
12. [Best Practices](#best-practices)
13. [Quick Reference](#quick-reference)

---

# Part 1: Understanding ACL & Security

## What is ACL?

**ACL (Access Control List)** defines permission resources that control access to:
- Admin panel functionality
- API endpoints (REST/SOAP)
- Menu items
- Backend operations

### The Security Model

**ACL = Who Can Do What**

- **acl.xml**: Defines WHAT permissions exist
- **webapi.xml**: Defines WHO can access endpoints  
- **Admin Roles**: Assigns permissions to users
- **Tokens**: Authenticate API requests

---

## How ACL Works

```
┌─────────────┐
│  acl.xml    │ → Defines available permissions
└──────┬──────┘
       │
       ↓
┌─────────────┐
│ Admin Roles │ → Admin assigns permissions to roles
└──────┬──────┘
       │
       ↓
┌─────────────┐
│    Users    │ → Users inherit permissions from role
└──────┬──────┘
       │
       ↓
┌─────────────┐
│ webapi.xml  │ → API endpoints check permissions
└─────────────┘
```

---

## Resource Reference Types

### 1. anonymous
**Anyone** can access (no authentication required):

```xml
<resources>
    <resource ref="anonymous"/>
</resources>
```

**Use cases**: 
- Public APIs
- Product catalog
- Guest checkout
- Customer registration

**Example:**
```bash
curl "http://localhost:8080/rest/V1/dudenkoff/notes/1"
```

---

### 2. self
**Authenticated user** can only access **their own** resources:

```xml
<resources>
    <resource ref="self"/>
</resources>
<data>
    <parameter name="customerId" force="true">%customer_id%</parameter>
</data>
```

**Use cases**:
- Customer account (`/V1/customers/me`)
- My cart (`/V1/carts/mine`)
- My orders
- My wishlist

**How it works:**
- Requires customer token
- `%customer_id%` is auto-injected from token
- Customer A cannot access Customer B's data

**Real Magento Example:**
```xml
<!-- From Magento\Quote\etc\webapi.xml -->
<route url="/V1/carts/mine" method="GET">
    <service class="Magento\Quote\Api\CartManagementInterface" method="getCartForCustomer"/>
    <resources>
        <resource ref="self" />
    </resources>
    <data>
        <parameter name="customerId" force="true">%customer_id%</parameter>
    </data>
</route>
```

---

### 3. Custom ACL Resource
Requires **specific permission** defined in acl.xml:

```xml
<resources>
    <resource ref="Dudenkoff_ApiLearn::note_view"/>
</resources>
```

**Use cases**:
- Admin operations
- Protected endpoints
- Sensitive data access
- Role-based permissions

**Example:**
```bash
# Requires admin token + note_view permission
curl -H "Authorization: Bearer $TOKEN" \
  "http://localhost:8080/rest/V1/dudenkoff/notes/admin/1"
```

---

## ACL Configuration

### acl.xml - Defining Permissions

Location: `app/code/Dudenkoff/ApiLearn/etc/acl.xml`

```xml
<config>
    <acl>
        <resources>
            <resource id="Magento_Backend::admin">
                <resource id="Dudenkoff_ApiLearn::notes" title="Dudenkoff Notes">
                    <resource id="Dudenkoff_ApiLearn::note_view" title="View Notes" />
                    <resource id="Dudenkoff_ApiLearn::note_create" title="Create Notes" />
                    <resource id="Dudenkoff_ApiLearn::note_edit" title="Edit Notes" />
                    <resource id="Dudenkoff_ApiLearn::note_delete" title="Delete Notes" />
                    <resource id="Dudenkoff_ApiLearn::note_publish" title="Publish Notes" />
                </resource>
            </resource>
        </resources>
    </acl>
</config>
```

### ACL Resource Hierarchy

```
Magento_Backend::admin (root)
    └── Dudenkoff_ApiLearn::notes (module)
        ├── Dudenkoff_ApiLearn::note_view (view)
        ├── Dudenkoff_ApiLearn::note_create (create)
        ├── Dudenkoff_ApiLearn::note_edit (edit)
        ├── Dudenkoff_ApiLearn::note_delete (delete)
        └── Dudenkoff_ApiLearn::note_publish (publish)
```

**Inheritance**: If you grant `Dudenkoff_ApiLearn::notes`, user gets ALL child permissions.

---

### webapi.xml - Using ACL in Endpoints

Location: `app/code/Dudenkoff/ApiLearn/etc/webapi.xml`

**Public endpoint:**
```xml
<route url="/V1/dudenkoff/notes/:noteId" method="GET">
    <service class="Dudenkoff\ApiLearn\Api\NoteRepositoryInterface" method="getById"/>
    <resources>
        <resource ref="anonymous"/>
    </resources>
</route>
```

**Protected endpoint:**
```xml
<route url="/V1/dudenkoff/notes/admin/:noteId" method="GET">
    <service class="Dudenkoff\ApiLearn\Api\NoteRepositoryInterface" method="getById"/>
    <resources>
        <resource ref="Dudenkoff_ApiLearn::note_view"/>
    </resources>
</route>
```

---

## Authentication Methods

### 1. Admin Token (Backend Users)

Get token:
```bash
curl -X POST "http://localhost:8080/rest/V1/integration/admin/token" \
  -H "Content-Type: application/json" \
  -d '{"username": "admin", "password": "your_password"}'
```

Response:
```json
"abc123xyz456token789"
```

Store in variable:
```bash
TOKEN=$(curl -s -X POST "http://localhost:8080/rest/V1/integration/admin/token" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"your_password"}' | tr -d '"')
```

Use token:
```bash
curl -H "Authorization: Bearer $TOKEN" \
  "http://localhost:8080/rest/V1/dudenkoff/notes/admin/1"
```

---

### 2. Customer Token (Frontend Users)

Get token:
```bash
curl -X POST "http://localhost:8080/rest/V1/integration/customer/token" \
  -H "Content-Type: application/json" \
  -d '{"username": "customer@email.com", "password": "password"}'
```

Use with `ref="self"` endpoints:
```bash
curl -H "Authorization: Bearer $CUSTOMER_TOKEN" \
  "http://localhost:8080/rest/V1/carts/mine"
```

---

### 3. Integration Token (Third-party Apps)

**Setup:**
1. Admin → System → Integrations
2. Create new integration
3. Activate and get tokens

**Use:**
```bash
curl -H "Authorization: Bearer integration_access_token" \
  "http://localhost:8080/rest/V1/endpoint"
```

---

# Part 2: Practical API Usage

## Anonymous Endpoints
*No Authentication Required*

### GET - Retrieve a Single Note by ID

Get note with ID 1:
```bash
curl -X GET "http://localhost:8080/rest/V1/dudenkoff/notes/1"
```

Pretty print with jq:
```bash
curl -s "http://localhost:8080/rest/V1/dudenkoff/notes/1" | jq '.'
```

**Response:**
```json
{
  "note_id": 1,
  "title": "Welcome to Magento 2 API",
  "content": "This is a sample note...",
  "author": "System",
  "is_published": true,
  "created_at": "2025-10-24 11:52:32",
  "updated_at": "2025-10-24 11:52:32"
}
```

---

### GET - Search/List Notes

Get all notes:
```bash
curl -gs "http://localhost:8080/rest/V1/dudenkoff/notes/search?searchCriteria=" | jq '.'
```

Filter by author:
```bash
curl -g "http://localhost:8080/rest/V1/dudenkoff/notes/search?searchCriteria[filterGroups][0][filters][0][field]=author&searchCriteria[filterGroups][0][filters][0][value]=Dudenkoff&searchCriteria[filterGroups][0][filters][0][conditionType]=eq"
```

Filter published notes only:
```bash
curl -g "http://localhost:8080/rest/V1/dudenkoff/notes/search?searchCriteria[filterGroups][0][filters][0][field]=is_published&searchCriteria[filterGroups][0][filters][0][value]=1&searchCriteria[filterGroups][0][filters][0][conditionType]=eq"
```

Sort by title ascending:
```bash
curl -g "http://localhost:8080/rest/V1/dudenkoff/notes/search?searchCriteria[sortOrders][0][field]=title&searchCriteria[sortOrders][0][direction]=ASC"
```

Pagination - page 1 with 5 items:
```bash
curl -g "http://localhost:8080/rest/V1/dudenkoff/notes/search?searchCriteria[pageSize]=5&searchCriteria[currentPage]=1"
```

Complex query - Published notes by Dudenkoff, sorted by date:
```bash
curl -g "http://localhost:8080/rest/V1/dudenkoff/notes/search?searchCriteria[filterGroups][0][filters][0][field]=author&searchCriteria[filterGroups][0][filters][0][value]=Dudenkoff&searchCriteria[filterGroups][0][filters][0][conditionType]=eq&searchCriteria[filterGroups][1][filters][0][field]=is_published&searchCriteria[filterGroups][1][filters][0][value]=1&searchCriteria[filterGroups][1][filters][0][conditionType]=eq&searchCriteria[sortOrders][0][field]=created_at&searchCriteria[sortOrders][0][direction]=DESC"
```

---

### POST - Create a New Note

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

Create a draft note:
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

---

### PUT - Update an Existing Note

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

Partial update (only title and content):
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

---

### DELETE - Remove a Note

Delete note ID 11:
```bash
curl -X DELETE "http://localhost:8080/rest/V1/dudenkoff/notes/11"
```

Delete with verbose output:
```bash
curl -v -X DELETE "http://localhost:8080/rest/V1/dudenkoff/notes/12"
```

---

### POST - Publish a Note (Custom Business Logic)

Publish note ID 3:
```bash
curl -X POST "http://localhost:8080/rest/V1/dudenkoff/notes/publish/3"
```

With pretty output:
```bash
curl -s -X POST "http://localhost:8080/rest/V1/dudenkoff/notes/publish/6" | jq '.'
```

---

## Protected Endpoints
*Requires Admin Authentication + ACL Permission*

### Step 1: Get Admin Token

```bash
# Get token
TOKEN=$(curl -s -X POST "http://localhost:8080/rest/V1/integration/admin/token" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"your_password"}' | tr -d '"')

# Verify token is set
echo $TOKEN
```

**Alternative: Store in file**
```bash
curl -s -X POST "http://localhost:8080/rest/V1/integration/admin/token" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"your_password"}' | tr -d '"' > token.txt

TOKEN=$(cat token.txt)
```

---

### GET - View Note (requires `note_view`)

```bash
curl -X GET "http://localhost:8080/rest/V1/dudenkoff/notes/admin/1" \
  -H "Authorization: Bearer $TOKEN"
```

With pretty output:
```bash
curl -s -X GET "http://localhost:8080/rest/V1/dudenkoff/notes/admin/5" \
  -H "Authorization: Bearer $TOKEN" | jq '.'
```

---

### POST - Create Note (requires `note_create`)

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

### PUT - Update Note (requires `note_edit`)

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

### DELETE - Remove Note (requires `note_delete`)

```bash
curl -X DELETE "http://localhost:8080/rest/V1/dudenkoff/notes/admin/15" \
  -H "Authorization: Bearer $TOKEN"
```

---

### POST - Publish Note (requires `note_publish`)

```bash
curl -X POST "http://localhost:8080/rest/V1/dudenkoff/notes/admin/publish/8" \
  -H "Authorization: Bearer $TOKEN"
```

---

## SearchCriteria Examples

### Available Condition Types

| Type | Description | Example Value |
|------|-------------|---------------|
| `eq` | Equals | `"Dudenkoff"` |
| `neq` | Not equals | `"System"` |
| `like` | Contains | `"%API%"` |
| `nlike` | Not like | `"%draft%"` |
| `in` | In array | `"1,3,5"` |
| `nin` | Not in array | `"2,4,6"` |
| `gt` | Greater than | `5` |
| `lt` | Less than | `10` |
| `gteq` | Greater or equal | `5` |
| `lteq` | Less or equal | `10` |
| `null` | Is null | - |
| `notnull` | Is not null | - |

---

### Filter Examples

**Greater than:**
```bash
curl -g "http://localhost:8080/rest/V1/dudenkoff/notes/search?searchCriteria[filterGroups][0][filters][0][field]=note_id&searchCriteria[filterGroups][0][filters][0][value]=5&searchCriteria[filterGroups][0][filters][0][conditionType]=gt"
```

**Date filter:**
```bash
curl -g "http://localhost:8080/rest/V1/dudenkoff/notes/search?searchCriteria[filterGroups][0][filters][0][field]=created_at&searchCriteria[filterGroups][0][filters][0][value]=2025-10-20%2000:00:00&searchCriteria[filterGroups][0][filters][0][conditionType]=gt"
```

**IN filter (multiple IDs):**
```bash
curl -g "http://localhost:8080/rest/V1/dudenkoff/notes/search?searchCriteria[filterGroups][0][filters][0][field]=note_id&searchCriteria[filterGroups][0][filters][0][value]=1,3,5&searchCriteria[filterGroups][0][filters][0][conditionType]=in"
```

**Multiple filter groups (OR logic):**
```bash
# (Author = Dudenkoff) OR (is_published = 1)
curl -g "http://localhost:8080/rest/V1/dudenkoff/notes/search?searchCriteria[filterGroups][0][filters][0][field]=author&searchCriteria[filterGroups][0][filters][0][value]=Dudenkoff&searchCriteria[filterGroups][0][filters][0][conditionType]=eq&searchCriteria[filterGroups][1][filters][0][field]=is_published&searchCriteria[filterGroups][1][filters][0][value]=1&searchCriteria[filterGroups][1][filters][0][conditionType]=eq"
```

---

## Testing & Utilities

### Quick Test All Endpoints

```bash
echo "Testing GET single note..."
curl -s "http://localhost:8080/rest/V1/dudenkoff/notes/1" | jq '.title'

echo "Testing GET all notes..."
curl -gs "http://localhost:8080/rest/V1/dudenkoff/notes/search?searchCriteria=" | jq '.total_count'

echo "Testing POST create note..."
curl -s -X POST "http://localhost:8080/rest/V1/dudenkoff/notes" \
  -H "Content-Type: application/json" \
  -d '{"note":{"title":"Test","content":"Testing","author":"Tester","is_published":true}}' | jq '.note_id'

echo "Testing POST publish..."
curl -s -X POST "http://localhost:8080/rest/V1/dudenkoff/notes/publish/3" | jq '.is_published'
```

---

### Error Testing

**Test non-existent note (404):**
```bash
curl "http://localhost:8080/rest/V1/dudenkoff/notes/99999"
```

**Test invalid JSON (400):**
```bash
curl -X POST "http://localhost:8080/rest/V1/dudenkoff/notes" \
  -H "Content-Type: application/json" \
  -d 'invalid json'
```

**Test protected endpoint without token (401):**
```bash
curl "http://localhost:8080/rest/V1/dudenkoff/notes/admin/1"
```

---

### Response Handling

**Save to file:**
```bash
curl -s "http://localhost:8080/rest/V1/dudenkoff/notes/1" > note.json
```

**Extract specific field:**
```bash
curl -s "http://localhost:8080/rest/V1/dudenkoff/notes/1" | jq '.title'
```

**Count items:**
```bash
curl -gs "http://localhost:8080/rest/V1/dudenkoff/notes/search?searchCriteria=" | jq '.items | length'
```

**Show only titles:**
```bash
curl -gs "http://localhost:8080/rest/V1/dudenkoff/notes/search?searchCriteria=" | jq '.items[].title'
```

**Show HTTP status code:**
```bash
curl -s -o /dev/null -w "%{http_code}" "http://localhost:8080/rest/V1/dudenkoff/notes/1"
```

**Verbose mode (headers):**
```bash
curl -v "http://localhost:8080/rest/V1/dudenkoff/notes/1"
```

---

### Batch Operations

**Create multiple notes:**
```bash
for i in {1..5}; do
  curl -s -X POST "http://localhost:8080/rest/V1/dudenkoff/notes" \
    -H "Content-Type: application/json" \
    -d "{\"note\":{\"title\":\"Batch Note $i\",\"content\":\"Content $i\",\"author\":\"Batch\",\"is_published\":true}}" \
    | jq '.note_id'
done
```

**Get all note IDs:**
```bash
curl -gs "http://localhost:8080/rest/V1/dudenkoff/notes/search?searchCriteria=" | jq '.items[].note_id'
```

---

### Useful One-Liners

**Quick API health check:**
```bash
curl -gs "http://localhost:8080/rest/V1/dudenkoff/notes/search?searchCriteria=" | jq '.total_count'
```

**Create and get new ID:**
```bash
NEW_ID=$(curl -s -X POST "http://localhost:8080/rest/V1/dudenkoff/notes" \
  -H "Content-Type: application/json" \
  -d '{"note":{"title":"Quick Note","content":"Fast","author":"CLI","is_published":true}}' \
  | jq '.note_id') && echo "Created note ID: $NEW_ID"
```

**Count published notes:**
```bash
curl -gs "http://localhost:8080/rest/V1/dudenkoff/notes/search?searchCriteria[filterGroups][0][filters][0][field]=is_published&searchCriteria[filterGroups][0][filters][0][value]=1" | jq '.total_count'
```

**Count draft notes:**
```bash
curl -gs "http://localhost:8080/rest/V1/dudenkoff/notes/search?searchCriteria[filterGroups][0][filters][0][field]=is_published&searchCriteria[filterGroups][0][filters][0][value]=0" | jq '.total_count'
```

---

# Part 3: Advanced Topics

## Permission Levels & Roles

### Testing ACL in Admin Panel

**Step 1: View Permissions**

1. Login to admin: `http://localhost:8080/admin`
2. Go to: **System → Permissions → User Roles**
3. Edit a role (e.g., Administrators)
4. Click **Role Resources** tab
5. Find **"Dudenkoff Notes"** in the tree
6. See sub-permissions:
   - View Notes
   - Create Notes
   - Edit Notes
   - Delete Notes
   - Publish Notes

---

### Create Test Roles

**Role 1: View Only**
- Enable: `Dudenkoff_ApiLearn::note_view`
- Can: GET notes
- Cannot: Create, edit, delete, publish

**Role 2: Full Access**
- Enable: `Dudenkoff_ApiLearn::notes` (parent)
- Can: Everything (inherits all child permissions)

**Role 3: Custom Mix**
- Enable: `note_view` + `note_create`
- Can: View and create
- Cannot: Edit, delete, publish

---

### Test with Different Users

```bash
# User 1 (view only)
TOKEN1="..." # Get token for user 1
curl -H "Authorization: Bearer $TOKEN1" \
  "http://localhost:8080/rest/V1/dudenkoff/notes/admin/1"  # ✓ Works

curl -X DELETE -H "Authorization: Bearer $TOKEN1" \
  "http://localhost:8080/rest/V1/dudenkoff/notes/admin/1"  # ✗ Fails (403)

# User 2 (full access)
TOKEN2="..." # Get token for user 2
curl -X DELETE -H "Authorization: Bearer $TOKEN2" \
  "http://localhost:8080/rest/V1/dudenkoff/notes/admin/1"  # ✓ Works
```

---

## Error Responses

### 401 Unauthorized
**Cause:** No authentication token provided

```json
{
    "message": "Consumer is not authorized to access %resources"
}
```

**Solution:** Provide admin/customer/integration token

---

### 403 Forbidden
**Cause:** Token valid but user lacks permission

```json
{
    "message": "Consumer is not authorized to access Dudenkoff_ApiLearn::note_delete"
}
```

**Solution:** Grant permission to user's role in Admin panel

---

### 404 Not Found
**Cause:** Resource doesn't exist

```json
{
    "message": "The note with the \"%1\" ID doesn't exist."
}
```

**Solution:** Verify the ID exists

---

### 400 Bad Request
**Cause:** Invalid request data

```json
{
    "message": "Invalid request data"
}
```

**Solution:** Check JSON syntax and required fields

---

## Best Practices

### ✅ DO

**1. Use descriptive resource names**
```xml
<resource id="Vendor_Module::entity_action" title="Human Readable Name" />
```

**2. Create granular permissions**
```xml
<!-- Good: Separate permissions -->
<resource id="Module::view" title="View Items" />
<resource id="Module::edit" title="Edit Items" />

<!-- Bad: One permission for everything -->
<resource id="Module::all" title="Do Everything" />
```

**3. Use hierarchy for organization**
```xml
<resource id="Module::parent" title="Parent Resource">
    <resource id="Module::child1" title="Child 1" />
    <resource id="Module::child2" title="Child 2" />
</resource>
```

**4. Document your APIs**
- Add clear titles to ACL resources
- Explain what each permission allows
- Include examples in comments

**5. Use anonymous carefully**
- Only for truly public data
- Consider security implications
- Document why it's public

**6. Test different permission levels**
- Create test roles with varying permissions
- Test with different user accounts
- Verify error messages

**7. Use SearchCriteria effectively**
- Always use pagination for large datasets
- Add indexes to frequently filtered fields
- Cache results when possible

---

### ❌ DON'T

**1. Don't use anonymous for sensitive data**
```xml
<!-- Bad: Sensitive admin data without protection -->
<resource ref="anonymous"/>
```

**2. Don't create duplicate resource IDs**
```xml
<!-- Bad: Same ID twice -->
<resource id="Module::view" />
<resource id="Module::view" /> <!-- Error! -->
```

**3. Don't forget to flush cache**
```bash
# Always run after ACL changes
bin/magento cache:flush
```

**4. Don't hardcode credentials**
```bash
# Bad
curl -d '{"username":"admin","password":"admin123"}'

# Good - use environment variables
curl -d "{\"username\":\"$ADMIN_USER\",\"password\":\"$ADMIN_PASS\"}"
```

**5. Don't skip pagination**
```bash
# Bad: Could return thousands of records
curl "http://localhost:8080/rest/V1/notes/search?searchCriteria="

# Good: Limit results
curl "http://localhost:8080/rest/V1/notes/search?\
searchCriteria[pageSize]=20&searchCriteria[currentPage]=1"
```

---

## ACL Naming Conventions

### Format
```
Vendor_Module::resource_name
```

### Examples from Magento Core
```
Magento_Catalog::products           → Catalog products permission
Magento_Sales::sales_order          → Sales orders permission
Magento_Customer::manage            → Customer management
Magento_Cms::page                   → CMS pages permission
```

### Your Module
```
Dudenkoff_ApiLearn::notes           → Notes module (parent)
Dudenkoff_ApiLearn::note_view       → View notes
Dudenkoff_ApiLearn::note_create     → Create notes
Dudenkoff_ApiLearn::note_edit       → Edit notes
Dudenkoff_ApiLearn::note_delete     → Delete notes
Dudenkoff_ApiLearn::note_publish    → Publish notes
```

---

## Debugging ACL

### Check if ACL is loaded

```bash
bin/magento cache:flush
bin/magento setup:upgrade
```

### View in Admin
1. Login to admin
2. System → User Roles → Edit Role
3. Role Resources tab
4. Look for your module

### Check Logs
```bash
tail -f var/log/exception.log
tail -f var/log/system.log
```

### Test API with curl
```bash
# Get token
TOKEN=$(curl -s -X POST "http://localhost:8080/rest/V1/integration/admin/token" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password"}' | tr -d '"')

# Test endpoint
curl -v -H "Authorization: Bearer $TOKEN" \
  "http://localhost:8080/rest/V1/dudenkoff/notes/admin/1"
```

---

## Quick Reference

### Anonymous Endpoints (Public)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/V1/dudenkoff/notes/:noteId` | Get single note |
| GET | `/V1/dudenkoff/notes/search` | Search/list notes |
| POST | `/V1/dudenkoff/notes` | Create note |
| PUT | `/V1/dudenkoff/notes/:noteId` | Update note |
| DELETE | `/V1/dudenkoff/notes/:noteId` | Delete note |
| POST | `/V1/dudenkoff/notes/publish/:noteId` | Publish note |

---

### Protected Endpoints (ACL Required)

| Method | Endpoint | ACL Permission | Description |
|--------|----------|----------------|-------------|
| GET | `/V1/dudenkoff/notes/admin/:noteId` | `note_view` | View note |
| POST | `/V1/dudenkoff/notes/admin` | `note_create` | Create note |
| PUT | `/V1/dudenkoff/notes/admin/:noteId` | `note_edit` | Update note |
| DELETE | `/V1/dudenkoff/notes/admin/:noteId` | `note_delete` | Delete note |
| POST | `/V1/dudenkoff/notes/admin/publish/:noteId` | `note_publish` | Publish note |

---

### Authentication Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/V1/integration/admin/token` | Get admin token |
| POST | `/V1/integration/customer/token` | Get customer token |

---

### Testing Checklist

#### Setup
- [ ] acl.xml created with resources
- [ ] webapi.xml references ACL resources
- [ ] Module enabled: `bin/magento module:enable Dudenkoff_ApiLearn`
- [ ] Cache cleared: `bin/magento cache:flush`

#### In Admin Panel
- [ ] Login to admin panel
- [ ] Go to System → User Roles
- [ ] Find module permissions in tree
- [ ] Assign to test role
- [ ] Create user with that role

#### API Testing
- [ ] Get admin token
- [ ] Test anonymous endpoints (should work without token)
- [ ] Test protected endpoint WITH token (should work)
- [ ] Test protected endpoint WITHOUT token (should fail 401)
- [ ] Test with user lacking permission (should fail 403)

---

## Summary

### Key Takeaways

1. **Three Permission Types:**
   - `anonymous` - Public access
   - `self` - Own resources only (with customer token)
   - `Vendor_Module::resource` - Specific ACL permission required

2. **Configuration Flow:**
   ```
   acl.xml → webapi.xml → Admin Roles → User Access
   ```

3. **Authentication:**
   - Admin token for backend operations
   - Customer token for frontend operations
   - Integration token for third-party apps

4. **Security:**
   - Always use ACL for sensitive operations
   - Test with different permission levels
   - Use granular permissions for better control

5. **Best Practices:**
   - Create hierarchical permissions
   - Use descriptive names
   - Document your APIs
   - Test thoroughly

---

### Your ApiLearn Module Features

✅ **Public Endpoints:**
- 6 anonymous endpoints (GET, POST, PUT, DELETE, Publish)
- Full CRUD operations
- Custom business logic (publish)

✅ **Protected Endpoints:**
- 5 ACL-protected endpoints
- Granular permissions (view, create, edit, delete, publish)
- Production-ready security

✅ **SearchCriteria Support:**
- Filtering by any field
- Sorting (ASC/DESC)
- Pagination
- Multiple filter groups (OR logic)
- All condition types (eq, like, gt, in, etc.)

---

## Additional Resources

### Magento DevDocs
- [Web API Authentication](https://devdocs.magento.com/guides/v2.4/get-started/authentication/gs-authentication.html)
- [Web API Overview](https://devdocs.magento.com/guides/v2.4/rest/bk-rest.html)
- [Configure ACL](https://devdocs.magento.com/guides/v2.4/ext-best-practices/tutorials/create-access-control-list-rule.html)

### Tools
- [jq - JSON processor](https://stedolan.github.io/jq/)
- [Postman](https://www.postman.com/)
- [cURL Documentation](https://curl.se/docs/)

---

**🎉 Congratulations!**

You now have a complete understanding of:
- ACL and permissions
- Authentication methods
- REST API usage
- Best practices

**Test it out and build amazing APIs!** 🚀

