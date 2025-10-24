# Magento 2 ACL (Access Control List) Guide

## What is ACL?

**ACL (Access Control List)** defines permission resources that control access to:
- Admin panel functionality
- API endpoints (REST/SOAP)
- Menu items
- Backend operations

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

## File Structure

### acl.xml
Defines the permission resources:

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

### webapi.xml
Uses ACL resources to protect endpoints:

```xml
<!-- Public endpoint - anyone can access -->
<route url="/V1/dudenkoff/notes/:id" method="GET">
    <service class="..." method="getById"/>
    <resources>
        <resource ref="anonymous"/>
    </resources>
</route>

<!-- Protected endpoint - requires permission -->
<route url="/V1/dudenkoff/notes/admin/:id" method="GET">
    <service class="..." method="getById"/>
    <resources>
        <resource ref="Dudenkoff_ApiLearn::note_view"/>
    </resources>
</route>
```

## ACL Resource Hierarchy

Resources can be nested to create permission groups:

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

## Resource Reference Types

### 1. anonymous
**Anyone** can access (no authentication required):

```xml
<resources>
    <resource ref="anonymous"/>
</resources>
```

**Use cases**: Public APIs, guest checkout, product catalog

### 2. self
User can only access **their own** resources:

```xml
<resources>
    <resource ref="self"/>
</resources>
```

**Use cases**: Customer account, my orders, my wishlist

### 3. Custom ACL Resource
Requires specific permission:

```xml
<resources>
    <resource ref="Dudenkoff_ApiLearn::note_view"/>
</resources>
```

**Use cases**: Admin operations, protected endpoints, sensitive data

## How to Test ACL

### Step 1: Enable Module with ACL

```bash
bin/magento module:enable Dudenkoff_ApiLearn
bin/magento setup:upgrade
bin/magento cache:flush
```

### Step 2: View Permissions in Admin

1. Login to admin panel: http://localhost:8080/admin
2. Go to: **System → Permissions → User Roles**
3. Edit a role (e.g., Administrators)
4. Click **Role Resources** tab
5. Find **"Dudenkoff Notes"** in the tree
6. See the sub-permissions:
   - View Notes
   - Create Notes
   - Edit Notes
   - Delete Notes
   - Publish Notes

### Step 3: Get Admin Token

```bash
curl -X POST http://localhost:8080/rest/V1/integration/admin/token \
  -H "Content-Type: application/json" \
  -d '{"username": "your_admin_username", "password": "your_admin_password"}'
```

Response:
```json
"abc123xyz456token789"
```

### Step 4: Test Protected Endpoint

```bash
# Without token - FAILS
curl http://localhost:8080/rest/V1/dudenkoff/notes/admin/1

# With token - SUCCEEDS (if user has permission)
curl -H "Authorization: Bearer abc123xyz456token789" \
  http://localhost:8080/rest/V1/dudenkoff/notes/admin/1
```

## API Endpoints - Public vs Protected

### Public Endpoints (anonymous)

Anyone can access without authentication:

```bash
# Get note (public)
curl http://localhost:8080/rest/V1/dudenkoff/notes/1

# Search notes (public)
curl http://localhost:8080/rest/V1/dudenkoff/notes/search

# Create note (public)
curl -X POST http://localhost:8080/rest/V1/dudenkoff/notes \
  -H "Content-Type: application/json" \
  -d '{"note":{"title":"Test","content":"Content"}}'
```

### Protected Endpoints (ACL required)

Require admin token + permission:

```bash
# Get admin token first
TOKEN=$(curl -X POST http://localhost:8080/rest/V1/integration/admin/token \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password"}' | tr -d '"')

# View note (requires note_view permission)
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8080/rest/V1/dudenkoff/notes/admin/1

# Create note (requires note_create permission)
curl -X POST http://localhost:8080/rest/V1/dudenkoff/notes/admin \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"note":{"title":"Admin Note","content":"Protected"}}'

# Delete note (requires note_delete permission)
curl -X DELETE http://localhost:8080/rest/V1/dudenkoff/notes/admin/1 \
  -H "Authorization: Bearer $TOKEN"

# Publish note (requires note_publish permission)
curl -X POST http://localhost:8080/rest/V1/dudenkoff/notes/admin/publish/1 \
  -H "Authorization: Bearer $TOKEN"
```

## ACL Naming Conventions

### Format:
```
Vendor_Module::resource_name
```

### Examples:
```
Magento_Catalog::products           → Catalog products permission
Magento_Sales::sales_order          → Sales orders permission
Magento_Customer::manage            → Customer management
Dudenkoff_ApiLearn::note_view       → View notes permission
Dudenkoff_ApiLearn::note_delete     → Delete notes permission
```

### Hierarchy Example:
```xml
<resource id="Magento_Catalog::catalog">                <!-- Top level -->
    <resource id="Magento_Catalog::products">           <!-- Category -->
        <resource id="Magento_Catalog::products_view" />    <!-- Specific -->
        <resource id="Magento_Catalog::products_edit" />    <!-- Specific -->
    </resource>
</resource>
```

## Permission Levels

### 1. No Permission (anonymous)
```xml
<resource ref="anonymous"/>
```
- No authentication required
- Public access
- Use for: Guest APIs, public data

### 2. Self Permission
```xml
<resource ref="self"/>
```
- User can access only their own resources
- Authenticated but limited
- Use for: Customer account, my orders

### 3. Custom ACL Resource
```xml
<resource ref="Vendor_Module::permission"/>
```
- Specific permission required
- Configured in acl.xml
- Use for: Admin operations, sensitive data

## Creating Role-Specific Permissions

### Step 1: Define in acl.xml

```xml
<resource id="Magento_Backend::admin">
    <resource id="MyCompany_CustomModule::manage" title="Custom Module">
        <resource id="MyCompany_CustomModule::view" title="View Items" />
        <resource id="MyCompany_CustomModule::edit" title="Edit Items" />
    </resource>
</resource>
```

### Step 2: Use in webapi.xml

```xml
<route url="/V1/custom/items/:id" method="GET">
    <service class="..." method="get"/>
    <resources>
        <resource ref="MyCompany_CustomModule::view"/>
    </resources>
</route>
```

### Step 3: Assign to Role in Admin

1. System → Permissions → User Roles
2. Edit role
3. Role Resources tab
4. Check the custom permissions
5. Save role

## Testing Different Permission Levels

### Create Test Roles

**Role 1: View Only**
- Enable: `Dudenkoff_ApiLearn::note_view`
- Can: GET notes
- Cannot: Create, edit, delete, publish

**Role 2: Full Access**
- Enable: `Dudenkoff_ApiLearn::notes` (parent - grants all)
- Can: Everything

**Role 3: Custom Mix**
- Enable: `note_view` + `note_create`
- Can: View and create
- Cannot: Edit, delete, publish

### Test with Different Users

```bash
# User 1 (view only)
TOKEN1="..." # Get token for user 1
curl -H "Authorization: Bearer $TOKEN1" \
  http://localhost:8080/rest/V1/dudenkoff/notes/admin/1  # ✓ Works

curl -X DELETE -H "Authorization: Bearer $TOKEN1" \
  http://localhost:8080/rest/V1/dudenkoff/notes/admin/1  # ✗ Fails (403)

# User 2 (full access)
TOKEN2="..." # Get token for user 2
curl -X DELETE -H "Authorization: Bearer $TOKEN2" \
  http://localhost:8080/rest/V1/dudenkoff/notes/admin/1  # ✓ Works
```

## Real-World Examples

### E-commerce Store - Order Management

```xml
<resource id="MyStore::orders" title="Orders">
    <resource id="MyStore::orders_view" title="View Orders" />
    <resource id="MyStore::orders_edit" title="Edit Orders" />
    <resource id="MyStore::orders_cancel" title="Cancel Orders" />
    <resource id="MyStore::orders_refund" title="Refund Orders" />
</resource>
```

**Roles**:
- **Sales Rep**: View only
- **Manager**: View + Edit
- **Admin**: All permissions

### Blog Module

```xml
<resource id="MyBlog::blog" title="Blog Management">
    <resource id="MyBlog::posts" title="Blog Posts">
        <resource id="MyBlog::posts_view" title="View Posts" />
        <resource id="MyBlog::posts_create" title="Create Posts" />
        <resource id="MyBlog::posts_publish" title="Publish Posts" />
    </resource>
    <resource id="MyBlog::comments" title="Comments">
        <resource id="MyBlog::comments_moderate" title="Moderate Comments" />
    </resource>
</resource>
```

## Error Responses

### 401 Unauthorized
No authentication token provided:

```json
{
    "message": "Consumer is not authorized to access %resources"
}
```

**Solution**: Provide admin/customer/integration token

### 403 Forbidden
Token valid but lacks permission:

```json
{
    "message": "Consumer is not authorized to access Dudenkoff_ApiLearn::note_delete"
}
```

**Solution**: Grant permission to user's role

## Best Practices

### ✅ DO:

1. **Use descriptive resource names**
   ```xml
   <resource id="Vendor_Module::entity_action" title="Human Readable" />
   ```

2. **Create granular permissions**
   ```xml
   <!-- Good: Separate view/edit/delete -->
   <resource id="Module::view" />
   <resource id="Module::edit" />
   
   <!-- Bad: One permission for everything -->
   <resource id="Module::all" />
   ```

3. **Use hierarchy**
   ```xml
   <resource id="Module::parent">
       <resource id="Module::child1" />
       <resource id="Module::child2" />
   </resource>
   ```

4. **Document permissions**
   - Add clear titles
   - Explain what each permission allows

5. **Use anonymous carefully**
   - Only for truly public data
   - Consider security implications

### ❌ DON'T:

1. **Don't use anonymous for sensitive data**
   ```xml
   <!-- Bad: Sensitive admin endpoint without protection -->
   <resource ref="anonymous"/>
   ```

2. **Don't create duplicate resource IDs**
   ```xml
   <!-- Bad: Same ID twice -->
   <resource id="Module::view" />
   <resource id="Module::view" /> ← Error!
   ```

3. **Don't forget to update ACL**
   - When adding new endpoints
   - When changing permissions

## Integration with Other Files

### acl.xml → webapi.xml → Admin Roles

```
1. acl.xml defines permissions
   ↓
2. webapi.xml uses permissions on endpoints
   ↓
3. Admin assigns permissions to roles
   ↓
4. Users get API access based on role
```

## Testing Checklist

### Setup:
- [ ] acl.xml created with resources
- [ ] webapi.xml references ACL resources
- [ ] Module enabled and cache cleared

### In Admin:
- [ ] Login to admin panel
- [ ] Go to System → User Roles
- [ ] Find your module's permissions in tree
- [ ] Assign to test role
- [ ] Create user with that role

### API Testing:
- [ ] Get admin token
- [ ] Test endpoint WITH token (should work)
- [ ] Test endpoint WITHOUT token (should fail 401)
- [ ] Test with user lacking permission (should fail 403)

## Quick Reference

### Define Permission:
```xml
<!-- etc/acl.xml -->
<resource id="Vendor_Module::permission_name" title="Display Name" />
```

### Use in API:
```xml
<!-- etc/webapi.xml -->
<route url="/V1/endpoint" method="GET">
    <service class="..." method="..."/>
    <resources>
        <resource ref="Vendor_Module::permission_name"/>
    </resources>
</route>
```

### Get Admin Token:
```bash
curl -X POST http://localhost:8080/rest/V1/integration/admin/token \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password"}'
```

### Use Token:
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8080/rest/V1/protected/endpoint
```

## Common ACL Resources

### Magento Core:

```
Magento_Backend::admin                  → Admin access (root)
Magento_Catalog::catalog                → Catalog
Magento_Catalog::products               → Products
Magento_Sales::sales                    → Sales
Magento_Sales::sales_order              → Orders
Magento_Customer::customer              → Customers
Magento_Cms::page                       → CMS Pages
Magento_Reports::report                 → Reports
```

## Debugging ACL

### Check if ACL is loaded:
```bash
bin/magento cache:flush
bin/magento setup:upgrade
```

### View ACL in admin:
1. Login to admin
2. System → User Roles → Edit Role
3. Role Resources tab
4. Look for your module

### Test API with token:
```bash
# Get token
TOKEN=$(curl -s -X POST http://localhost:8080/rest/V1/integration/admin/token \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password"}' | tr -d '"')

# Test endpoint
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8080/rest/V1/dudenkoff/notes/admin/1
```

### Check user permissions:
1. Admin → System → User Roles
2. View role assigned to user
3. Check Role Resources
4. Verify permission is checked

## API Authentication Methods

### 1. Admin Token (Backend Users)
```bash
POST /V1/integration/admin/token
Body: {"username": "admin", "password": "password"}
```

### 2. Customer Token (Frontend Users)
```bash
POST /V1/integration/customer/token
Body: {"username": "customer@email.com", "password": "password"}
```

### 3. Integration Token (Third-party Apps)
Generated in: Admin → System → Integrations

```bash
curl -H "Authorization: Bearer integration_token" ...
```

## Summary

**ACL = Who Can Do What**

- **acl.xml**: Defines WHAT permissions exist
- **webapi.xml**: Defines WHO can access endpoints
- **Admin Roles**: Assigns permissions to users
- **Tokens**: Authenticate API requests

### Key Points:

1. ✅ Define granular permissions in acl.xml
2. ✅ Reference in webapi.xml
3. ✅ Assign via Admin → User Roles
4. ✅ Use tokens for authentication
5. ✅ Test with different permission levels

---

**Your ApiLearn module now has:**
- ✅ 6 public endpoints (anonymous)
- ✅ 5 protected endpoints (ACL)
- ✅ Complete permission hierarchy
- ✅ Production-ready security

**Test it out in the admin panel!** 🔒

