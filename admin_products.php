<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

require_once 'mysqli_connect.php';

$success_msg = '';
$error_msg = '';

$upload_dir = __DIR__ . '/assets/images';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_brand') {
        $name = trim($_POST['brand_name'] ?? '');
        $description = trim($_POST['brand_description'] ?? '');
        $logo_path = '';

        if (isset($_FILES['brand_logo']) && $_FILES['brand_logo']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['brand_logo']['name'], PATHINFO_EXTENSION);
            $filename = 'brand_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['brand_logo']['tmp_name'], $upload_dir . '/' . $filename)) {
                $logo_path = 'assets/images/' . $filename;
            }
        }

        if (!empty($name)) {
            $stmt = mysqli_prepare($dbc, "INSERT INTO brands (name, description, logo) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'sss', $name, $description, $logo_path);
            if (mysqli_stmt_execute($stmt)) {
                $success_msg = "Brand '$name' successfully added!";
            } else {
                $error_msg = "Error adding brand: " . mysqli_error($dbc);
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_msg = "Brand name cannot be empty.";
        }
    } 
    
    elseif ($action === 'update_brand') {
        $id = (int)$_POST['brand_id'];
        $name = trim($_POST['brand_name'] ?? '');
        $description = trim($_POST['brand_description'] ?? '');
        $logo_path = $_POST['existing_logo'] ?? '';

        if (isset($_FILES['brand_logo']) && $_FILES['brand_logo']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['brand_logo']['name'], PATHINFO_EXTENSION);
            $filename = 'brand_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['brand_logo']['tmp_name'], $upload_dir . '/' . $filename)) {
                $logo_path = 'assets/images/' . $filename;
            }
        }

        if ($id > 0 && !empty($name)) {
            $stmt = mysqli_prepare($dbc, "UPDATE brands SET name = ?, description = ?, logo = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'sssi', $name, $description, $logo_path, $id);
            if (mysqli_stmt_execute($stmt)) {
                $success_msg = "Brand successfully updated!";
            } else {
                $error_msg = "Error updating brand: " . mysqli_error($dbc);
            }
            mysqli_stmt_close($stmt);
        }
    } 
    
    elseif ($action === 'delete_brand') {
        $id = (int)$_POST['brand_id'];
        if ($id > 0) {
            $stmt = mysqli_prepare($dbc, "DELETE FROM brands WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            if (mysqli_stmt_execute($stmt)) {
                $success_msg = "Brand successfully deleted!";
            } else {
                $error_msg = "Error deleting brand: " . mysqli_error($dbc);
            }
            mysqli_stmt_close($stmt);
        }
    }

    elseif ($action === 'add_product') {
        $brand_id = !empty($_POST['brand_id']) ? (int)$_POST['brand_id'] : null;
        $name = trim($_POST['product_name'] ?? '');
        $description = trim($_POST['product_description'] ?? '');
        $price = (float)($_POST['product_price'] ?? 0.00);
        $stock = (int)($_POST['product_stock'] ?? 0);
        $category = trim($_POST['product_category'] ?? '');
        $badge = trim($_POST['product_badge'] ?? '');
        $image_path = 'assets/images/placeholder.png';

        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
            $filename = 'product_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_dir . '/' . $filename)) {
                $image_path = 'assets/images/' . $filename;
            }
        }

        if (!empty($name)) {
            $stmt = mysqli_prepare($dbc, "INSERT INTO products (brand_id, name, description, price, stock, category, badge, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'issdisss', $brand_id, $name, $description, $price, $stock, $category, $badge, $image_path);
            if (mysqli_stmt_execute($stmt)) {
                $success_msg = "Product '$name' successfully added!";
            } else {
                $error_msg = "Error adding product: " . mysqli_error($dbc);
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_msg = "Product name cannot be empty.";
        }
    } 
    
    elseif ($action === 'update_product') {
        $id = (int)$_POST['product_id'];
        $brand_id = !empty($_POST['brand_id']) ? (int)$_POST['brand_id'] : null;
        $name = trim($_POST['product_name'] ?? '');
        $description = trim($_POST['product_description'] ?? '');
        $price = (float)($_POST['product_price'] ?? 0.00);
        $stock = (int)($_POST['product_stock'] ?? 0);
        $category = trim($_POST['product_category'] ?? '');
        $badge = trim($_POST['product_badge'] ?? '');
        $image_path = $_POST['existing_image'] ?? 'assets/images/placeholder.png';

        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
            $filename = 'product_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_dir . '/' . $filename)) {
                $image_path = 'assets/images/' . $filename;
            }
        }

        if ($id > 0 && !empty($name)) {
            $stmt = mysqli_prepare($dbc, "UPDATE products SET brand_id = ?, name = ?, description = ?, price = ?, stock = ?, category = ?, badge = ?, image = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'issdisssi', $brand_id, $name, $description, $price, $stock, $category, $badge, $image_path, $id);
            if (mysqli_stmt_execute($stmt)) {
                $success_msg = "Product '$name' successfully updated!";
            } else {
                $error_msg = "Error updating product: " . mysqli_error($dbc);
            }
            mysqli_stmt_close($stmt);
        }
    } 
    
    elseif ($action === 'delete_product') {
        $id = (int)$_POST['product_id'];
        if ($id > 0) {
            $stmt = mysqli_prepare($dbc, "DELETE FROM products WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            if (mysqli_stmt_execute($stmt)) {
                $success_msg = "Product successfully deleted!";
            } else {
                $error_msg = "Error deleting product: " . mysqli_error($dbc);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

$brands = [];
$brands_res = mysqli_query($dbc, "SELECT * FROM brands ORDER BY name ASC");
if ($brands_res) {
    while ($row = mysqli_fetch_assoc($brands_res)) {
        $brands[] = $row;
    }
}

$products = [];
$products_res = mysqli_query($dbc, "SELECT p.*, b.name AS brand_name FROM products p LEFT JOIN brands b ON p.brand_id = b.id ORDER BY p.id DESC");
if ($products_res) {
    while ($row = mysqli_fetch_assoc($products_res)) {
        $products[] = $row;
    }
}

include 'header.php';
?>

<section class="products-section" style="padding: 150px 0 100px; min-height: 80vh;">
    <div class="container">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; flex-wrap: wrap; gap: 20px;">
            <div>
                <h1 style="font-size: 2.8rem; margin: 0; color: white;">Manage <span>Inventory</span></h1>
                <p style="color: var(--text-secondary); margin-top: 5px;">Add, edit, or remove store brands and products.</p>
            </div>
            <div style="display: flex; gap: 15px;">
                <a href="admin_dashboard.php" class="btn btn-outline">Back to Dashboard</a>
                <a href="admin_customers.php" class="btn btn-outline">Manage Customers</a>
            </div>
        </div>

        <?php if($success_msg): ?>
            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #10b981; padding: 15px; border-radius: 8px; margin-bottom: 35px;">
                🎉 <?php echo htmlspecialchars($success_msg); ?>
            </div>
        <?php endif; ?>

        <?php if($error_msg): ?>
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #ef4444; padding: 15px; border-radius: 8px; margin-bottom: 35px;">
                ⚠️ <?php echo htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 50px; align-items: start;">
            
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 30px;">
                <h2 style="color: white; font-size: 1.6rem; margin-bottom: 25px;">Brands Management</h2>
                
                <form id="brand-form" method="POST" action="admin_products.php" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 15px; margin-bottom: 40px; background: var(--bg-secondary); padding: 20px; border-radius: 12px; border: 1px solid var(--border-color);">
                    <input type="hidden" name="action" id="brand-action" value="add_brand">
                    <input type="hidden" name="brand_id" id="brand-id" value="">
                    <input type="hidden" name="existing_logo" id="existing-logo" value="">
                    
                    <h3 id="brand-form-title" style="font-size: 1.1rem; color: var(--accent-primary); margin: 0 0 10px 0;">Add New Brand</h3>
                    
                    <div>
                        <label style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 5px;">Brand Name</label>
                        <input type="text" name="brand_name" id="brand-name" required placeholder="e.g. InnovateTech" style="width: 100%; border-radius: 6px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 5px;">Description</label>
                        <textarea name="brand_description" id="brand-description" placeholder="Description of the brand..." style="width: 100%; height: 80px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 6px; color: white; padding: 10px; font-family: inherit; outline: none;"></textarea>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 5px;">Brand Logo Image</label>
                        <input type="file" name="brand_logo" style="width: 100%; background: transparent; border: none; padding: 0;">
                    </div>
                    
                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                        <button type="submit" id="brand-submit-btn" class="btn btn-primary" style="padding: 10px 20px;">Save Brand</button>
                        <button type="button" id="brand-cancel-btn" class="btn btn-outline" style="display: none; padding: 10px 20px;" onclick="resetBrandForm()">Cancel</button>
                    </div>
                </form>

                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--border-color); color: var(--text-secondary); font-size: 0.9rem;">
                                <th style="padding: 10px;">Logo</th>
                                <th style="padding: 10px;">Name</th>
                                <th style="padding: 10px; text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($brands)): ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; padding: 20px; color: var(--text-secondary);">No brands registered yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($brands as $b): ?>
                                    <tr style="border-bottom: 1px solid var(--border-color);">
                                        <td style="padding: 12px 10px;">
                                            <?php if ($b['logo']): ?>
                                                <img src="<?php echo htmlspecialchars($b['logo']); ?>" alt="logo" style="height: 30px; object-fit: contain; max-width: 60px; filter: drop-shadow(0 0 5px rgba(255,255,255,0.1));">
                                            <?php else: ?>
                                                <span style="color: var(--text-secondary); font-size: 0.8rem;">No Logo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 12px 10px; font-weight: 600; color: white;">
                                            <?php echo htmlspecialchars($b['name']); ?>
                                        </td>
                                        <td style="padding: 12px 10px; text-align: right;">
                                            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                                <button class="btn btn-outline btn-small" onclick='editBrand(<?php echo json_encode($b); ?>)' style="padding: 6px 12px; font-size: 0.8rem;">Edit</button>
                                                <form method="POST" action="admin_products.php" onsubmit="return confirm('Are you sure you want to delete this brand? All products of this brand will be unbranded.');" style="display: inline;">
                                                    <input type="hidden" name="action" value="delete_brand">
                                                    <input type="hidden" name="brand_id" value="<?php echo $b['id']; ?>">
                                                    <button type="submit" class="btn btn-outline btn-small" style="padding: 6px 12px; font-size: 0.8rem; border-color: #ef4444; color: #ef4444;">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 30px;">
                <h2 style="color: white; font-size: 1.6rem; margin-bottom: 25px;">Products Management</h2>
                
                <form id="product-form" method="POST" action="admin_products.php" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 15px; margin-bottom: 40px; background: var(--bg-secondary); padding: 20px; border-radius: 12px; border: 1px solid var(--border-color);">
                    <input type="hidden" name="action" id="product-action" value="add_product">
                    <input type="hidden" name="product_id" id="product-id" value="">
                    <input type="hidden" name="existing_image" id="existing-image" value="assets/images/placeholder.png">
                    
                    <h3 id="product-form-title" style="font-size: 1.1rem; color: var(--accent-primary); margin: 0 0 10px 0;">Add New Product</h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 5px;">Product Name</label>
                            <input type="text" name="product_name" id="product-name" required placeholder="Quantum Pro 2" style="width: 100%; border-radius: 6px;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 5px;">Brand</label>
                            <select name="brand_id" id="product-brand" style="width: 100%; background: var(--bg-card); color: white; border: 1px solid var(--border-color); border-radius: 6px; padding: 10px; outline: none; cursor: pointer;">
                                <option value="">-- No Brand --</option>
                                <?php foreach ($brands as $b): ?>
                                    <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                        <div>
                            <label style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 5px;">Price (₱)</label>
                            <input type="number" step="0.01" name="product_price" id="product-price" required placeholder="0.00" style="width: 100%; border-radius: 6px;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 5px;">Stock Level</label>
                            <input type="number" name="product_stock" id="product-stock" required placeholder="10" style="width: 100%; border-radius: 6px;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 5px;">Category</label>
                            <input type="text" name="product_category" id="product-category" placeholder="Computers" style="width: 100%; border-radius: 6px;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 5px;">Badge (Optional)</label>
                            <input type="text" name="product_badge" id="product-badge" placeholder="e.g. New, Bestseller" style="width: 100%; border-radius: 6px;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 5px;">Product Image</label>
                            <input type="file" name="product_image" style="width: 100%; background: transparent; border: none; padding: 0;">
                        </div>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 5px;">Description</label>
                        <textarea name="product_description" id="product-description" placeholder="Product details..." style="width: 100%; height: 80px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 6px; color: white; padding: 10px; font-family: inherit; outline: none;"></textarea>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                        <button type="submit" id="product-submit-btn" class="btn btn-primary" style="padding: 10px 20px;">Save Product</button>
                        <button type="button" id="product-cancel-btn" class="btn btn-outline" style="display: none; padding: 10px 20px;" onclick="resetProductForm()">Cancel</button>
                    </div>
                </form>

                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--border-color); color: var(--text-secondary); font-size: 0.9rem;">
                                <th style="padding: 10px;">Image</th>
                                <th style="padding: 10px;">Name</th>
                                <th style="padding: 10px;">Brand</th>
                                <th style="padding: 10px;">Price</th>
                                <th style="padding: 10px;">Stock</th>
                                <th style="padding: 10px; text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 20px; color: var(--text-secondary);">No products registered yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($products as $p): ?>
                                    <tr style="border-bottom: 1px solid var(--border-color);">
                                        <td style="padding: 12px 10px;">
                                            <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="product" style="height: 35px; width: 35px; object-fit: cover; border-radius: 6px;">
                                        </td>
                                        <td style="padding: 12px 10px; font-weight: 600; color: white;">
                                            <?php echo htmlspecialchars($p['name']); ?>
                                            <span style="display: block; font-size: 0.75rem; color: var(--text-secondary); font-weight: normal;"><?php echo htmlspecialchars($p['category']); ?></span>
                                        </td>
                                        <td style="padding: 12px 10px; color: var(--text-secondary);">
                                            <?php echo htmlspecialchars($p['brand_name'] ?? 'Unbranded'); ?>
                                        </td>
                                        <td style="padding: 12px 10px; font-weight: bold; color: var(--accent-primary);">
                                            ₱<?php echo number_format($p['price'], 2); ?>
                                        </td>
                                        <td style="padding: 12px 10px;">
                                            <span style="color: <?php echo $p['stock'] < 5 ? '#ef4444' : '#10b981'; ?>; font-weight: bold;">
                                                <?php echo $p['stock']; ?>
                                            </span>
                                        </td>
                                        <td style="padding: 12px 10px; text-align: right;">
                                            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                                <button class="btn btn-outline btn-small" onclick='editProduct(<?php echo json_encode($p); ?>)' style="padding: 6px 12px; font-size: 0.8rem;">Edit</button>
                                                <form method="POST" action="admin_products.php" onsubmit="return confirm('Are you sure you want to delete this product?');" style="display: inline;">
                                                    <input type="hidden" name="action" value="delete_product">
                                                    <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                                    <button type="submit" class="btn btn-outline btn-small" style="padding: 6px 12px; font-size: 0.8rem; border-color: #ef4444; color: #ef4444;">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
</section>

<script>
function editBrand(brand) {
    document.getElementById('brand-action').value = 'update_brand';
    document.getElementById('brand-id').value = brand.id;
    document.getElementById('brand-name').value = brand.name;
    document.getElementById('brand-description').value = brand.description;
    document.getElementById('existing-logo').value = brand.logo;
    
    document.getElementById('brand-form-title').innerText = 'Edit Brand: ' + brand.name;
    document.getElementById('brand-submit-btn').innerText = 'Update Brand';
    document.getElementById('brand-cancel-btn').style.display = 'inline-block';
    
    document.getElementById('brand-form').scrollIntoView({ behavior: 'smooth' });
}

function resetBrandForm() {
    document.getElementById('brand-action').value = 'add_brand';
    document.getElementById('brand-id').value = '';
    document.getElementById('brand-name').value = '';
    document.getElementById('brand-description').value = '';
    document.getElementById('existing-logo').value = '';
    
    document.getElementById('brand-form-title').innerText = 'Add New Brand';
    document.getElementById('brand-submit-btn').innerText = 'Save Brand';
    document.getElementById('brand-cancel-btn').style.display = 'none';
}

function editProduct(product) {
    document.getElementById('product-action').value = 'update_product';
    document.getElementById('product-id').value = product.id;
    document.getElementById('product-name').value = product.name;
    document.getElementById('product-brand').value = product.brand_id ? product.brand_id : '';
    document.getElementById('product-price').value = product.price;
    document.getElementById('product-stock').value = product.stock;
    document.getElementById('product-category').value = product.category;
    document.getElementById('product-badge').value = product.badge;
    document.getElementById('product-description').value = product.description;
    document.getElementById('existing-image').value = product.image;
    
    document.getElementById('product-form-title').innerText = 'Edit Product: ' + product.name;
    document.getElementById('product-submit-btn').innerText = 'Update Product';
    document.getElementById('product-cancel-btn').style.display = 'inline-block';
    
    document.getElementById('product-form').scrollIntoView({ behavior: 'smooth' });
}

function resetProductForm() {
    document.getElementById('product-action').value = 'add_product';
    document.getElementById('product-id').value = '';
    document.getElementById('product-name').value = '';
    document.getElementById('product-brand').value = '';
    document.getElementById('product-price').value = '';
    document.getElementById('product-stock').value = '';
    document.getElementById('product-category').value = '';
    document.getElementById('product-badge').value = '';
    document.getElementById('product-description').value = '';
    document.getElementById('existing-image').value = 'assets/images/placeholder.png';
    
    document.getElementById('product-form-title').innerText = 'Add New Product';
    document.getElementById('product-submit-btn').innerText = 'Save Product';
    document.getElementById('product-cancel-btn').style.display = 'none';
}
</script>

<style>
input[type="text"], input[type="number"], input[type="email"], input[type="password"] {
    background: var(--bg-card) !important;
    border: 1px solid var(--border-color) !important;
    color: white !important;
    padding: 10px 15px !important;
    outline: none !important;
    font-family: inherit !important;
}
input[type="text"]:focus, input[type="number"]:focus, input[type="email"]:focus, input[type="password"]:focus, textarea:focus, select:focus {
    border-color: var(--accent-primary) !important;
    box-shadow: 0 0 10px var(--accent-glow) !important;
}
@media (max-width: 992px) {
    div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php include 'footer.php'; ?>
