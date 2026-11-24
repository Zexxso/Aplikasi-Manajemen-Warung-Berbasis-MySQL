<?php

require_once __DIR__ . '/../helpers/helpers.php';

class ProductController
{
    public function index(): void
    {
        if (!auth_check()) {
            redirect('/login');
        }

        $ownerId = warung_owner_id();

        // Get categories (Single-store: Warung Bu Sari)
        $catStmt = db()->prepare("SELECT id, nama, nama AS name FROM kategori ORDER BY nama ASC");
        $catStmt->execute();
        $categories = $catStmt->fetchAll();

        // Get products with category name
        $prodStmt = db()->prepare("
            SELECT 
                p.id,
                p.nama,
                p.id_kategori,
                p.satuan,
                p.gambar,
                p.harga_beli,
                p.harga_jual,
                p.stok,
                p.stok_minimum,
                c.nama AS nama_kategori,
                p.nama AS name,
                p.id_kategori AS category_id,
                p.satuan AS unit,
                p.gambar AS image,
                p.harga_beli AS buy_price,
                p.harga_jual AS sell_price,
                p.stok AS stock,
                p.stok_minimum AS min_stock,
                c.nama AS category_name
            FROM produk p
            LEFT JOIN kategori c ON c.id = p.id_kategori
            WHERE p.dihapus_pada IS NULL
            ORDER BY p.nama ASC
        ");
        $prodStmt->execute();
        $products = $prodStmt->fetchAll();

        $initialFilter = $_GET['filter'] ?? '';
        $initialCategory = $_GET['category'] ?? '';
        $initialSearch = $_GET['search'] ?? '';

        view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'initialFilter' => $initialFilter,
            'initialCategory' => $initialCategory,
            'initialSearch' => $initialSearch,
            'title' => 'Daftar Barang - Warungku',
        ]);
    }

    public function store(): void
    {
        if (!auth_check()) {
            redirect('/login');
        }

        if (!csrf_verify()) {
            set_flash('error', 'Token keamanan tidak valid.');
            back();
        }

        $ownerId = warung_owner_id();

        $name = trim($_POST['nama'] ?? $_POST['name'] ?? '');
        $categoryId = !empty($_POST['id_kategori'] ?? $_POST['category_id']) ? (int) ($_POST['id_kategori'] ?? $_POST['category_id']) : null;
        $unit = trim($_POST['satuan'] ?? $_POST['unit'] ?? 'pcs');

        // Robust currency/number sanitization (strip dots, commas, Rp, whitespace)
        $rawBuy = preg_replace('/[^\d]/', '', (string)($_POST['harga_beli'] ?? $_POST['buy_price'] ?? '0'));
        $rawSell = preg_replace('/[^\d]/', '', (string)($_POST['harga_jual'] ?? $_POST['sell_price'] ?? '0'));
        $rawStock = preg_replace('/[^\d]/', '', (string)($_POST['stok'] ?? $_POST['stock'] ?? '0'));
        $rawMinStock = preg_replace('/[^\d]/', '', (string)($_POST['stok_minimum'] ?? $_POST['min_stock'] ?? '5'));

        $buyPrice = (float) ($rawBuy !== '' ? $rawBuy : 0);
        $sellPrice = (float) ($rawSell !== '' ? $rawSell : 0);
        $stock = (int) ($rawStock !== '' ? $rawStock : 0);
        $minStock = (int) ($rawMinStock !== '' ? $rawMinStock : 5);

        if (empty($name) || empty($unit)) {
            set_old($_POST);
            set_flash('error', 'Nama barang dan satuan wajib diisi.');
            back();
        }

        if ($buyPrice < 0 || $sellPrice < 0 || $stock < 0 || $minStock < 0) {
            set_old($_POST);
            set_flash('error', 'Nilai harga dan stok tidak boleh bernilai negatif.');
            back();
        }

        // Validate category if selected
        if ($categoryId) {
            $catCheck = db()->prepare("SELECT id FROM kategori WHERE id = :id");
            $catCheck->execute(['id' => $categoryId]);
            if (!$catCheck->fetch()) {
                $categoryId = null;
            }
        }

        try {
            $imageName = $this->handleImageUpload($_FILES['gambar'] ?? $_FILES['image'] ?? null);
        } catch (Exception $e) {
            set_old($_POST);
            set_flash('error', $e->getMessage());
            back();
        }

        try {
            $insStmt = db()->prepare("
                INSERT INTO produk (id_kategori, nama, satuan, gambar, harga_beli, harga_jual, stok, stok_minimum, status_aktif, dibuat_pada, diperbarui_pada)
                VALUES (:id_kategori, :nama, :satuan, :gambar, :harga_beli, :harga_jual, :stok, :stok_minimum, 1, NOW(), NOW())
            ");
            $insStmt->execute([
                'id_kategori' => $categoryId,
                'nama' => $name,
                'satuan' => $unit,
                'gambar' => $imageName,
                'harga_beli' => $buyPrice,
                'harga_jual' => $sellPrice,
                'stok' => $stock,
                'stok_minimum' => $minStock,
            ]);
        } catch (PDOException $e) {
            set_old($_POST);
            set_flash('error', 'Gagal menyimpan barang ke database: ' . $e->getMessage());
            back();
        }

        clear_old();
        set_flash('status', "Barang {$name} berhasil ditambahkan.");
        redirect('/products');
    }

    public function update(int $id): void
    {
        if (!auth_check()) {
            redirect('/login');
        }

        if (!csrf_verify()) {
            set_flash('error', 'Token keamanan tidak valid.');
            back();
        }

        $ownerId = warung_owner_id();

        // Check product existence
        $stmt = db()->prepare("SELECT * FROM produk WHERE id = :id AND dihapus_pada IS NULL LIMIT 1");
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch();

        if (!$product) {
            set_flash('error', 'Barang tidak ditemukan.');
            redirect('/products');
        }

        $name = trim($_POST['nama'] ?? $_POST['name'] ?? '');
        $categoryId = !empty($_POST['id_kategori'] ?? $_POST['category_id']) ? (int) ($_POST['id_kategori'] ?? $_POST['category_id']) : null;
        $unit = trim($_POST['satuan'] ?? $_POST['unit'] ?? 'pcs');

        // Robust currency/number sanitization
        $rawBuy = preg_replace('/[^\d]/', '', (string)($_POST['harga_beli'] ?? $_POST['buy_price'] ?? '0'));
        $rawSell = preg_replace('/[^\d]/', '', (string)($_POST['harga_jual'] ?? $_POST['sell_price'] ?? '0'));
        $rawStock = preg_replace('/[^\d]/', '', (string)($_POST['stok'] ?? $_POST['stock'] ?? '0'));
        $rawMinStock = preg_replace('/[^\d]/', '', (string)($_POST['stok_minimum'] ?? $_POST['min_stock'] ?? '5'));

        $buyPrice = (float) ($rawBuy !== '' ? $rawBuy : 0);
        $sellPrice = (float) ($rawSell !== '' ? $rawSell : 0);
        $stock = (int) ($rawStock !== '' ? $rawStock : 0);
        $minStock = (int) ($rawMinStock !== '' ? $rawMinStock : 5);

        if (empty($name) || empty($unit)) {
            set_old($_POST);
            set_flash('error', 'Nama barang dan satuan wajib diisi.');
            back();
        }

        if ($categoryId) {
            $catCheck = db()->prepare("SELECT id FROM kategori WHERE id = :id");
            $catCheck->execute(['id' => $categoryId]);
            if (!$catCheck->fetch()) {
                $categoryId = null;
            }
        }

        // Support removing existing image
        $existingImage = $product['gambar'] ?? $product['image'] ?? null;
        $imageFile = !empty($_FILES['gambar']['name']) ? $_FILES['gambar'] : ($_FILES['image'] ?? null);
        if (!empty($_POST['remove_image']) && empty($imageFile['name'])) {
            if (!empty($existingImage)) {
                $uploadDir = __DIR__ . '/../uploads/products/';
                $oldFilePath = $uploadDir . $existingImage;
                if (is_file($oldFilePath)) {
                    @unlink($oldFilePath);
                }
            }
            $imageName = null;
        } else {
            try {
                $imageName = $this->handleImageUpload($imageFile, $existingImage);
            } catch (Exception $e) {
                set_old($_POST);
                set_flash('error', $e->getMessage());
                back();
            }
        }

        try {
            $upStmt = db()->prepare("
                UPDATE produk 
                SET nama = :nama,
                    id_kategori = :id_kategori,
                    satuan = :satuan,
                    gambar = :gambar,
                    harga_beli = :harga_beli,
                    harga_jual = :harga_jual,
                    stok = :stok,
                    stok_minimum = :stok_minimum,
                    diperbarui_pada = NOW()
                WHERE id = :id AND dihapus_pada IS NULL
            ");
            $upStmt->execute([
                'nama' => $name,
                'id_kategori' => $categoryId,
                'satuan' => $unit,
                'gambar' => $imageName,
                'harga_beli' => $buyPrice,
                'harga_jual' => $sellPrice,
                'stok' => $stock,
                'stok_minimum' => $minStock,
                'id' => $id,
            ]);
        } catch (PDOException $e) {
            set_old($_POST);
            set_flash('error', 'Gagal memperbarui barang: ' . $e->getMessage());
            back();
        }

        clear_old();
        set_flash('status', "Barang {$name} berhasil diperbarui.");

        // Notifikasi: perubahan harga jual
        $oldSellPrice = (float) ($product['harga_jual'] ?? $product['sell_price'] ?? 0);
        if ($oldSellPrice > 0 && abs($sellPrice - $oldSellPrice) > 0.01) {
            notif_perubahan_harga($id, $name, $oldSellPrice, $sellPrice);
        }

        redirect('/products');
    }

    public function destroy(int $id): void
    {
        if (!auth_check()) {
            redirect('/login');
        }

        if (!csrf_verify()) {
            set_flash('error', 'Token keamanan tidak valid.');
            back();
        }

        $stmt = db()->prepare("SELECT * FROM produk WHERE id = :id AND dihapus_pada IS NULL LIMIT 1");
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch();

        if ($product) {
            $delStmt = db()->prepare("UPDATE produk SET dihapus_pada = NOW(), diperbarui_pada = NOW() WHERE id = :id");
            $delStmt->execute(['id' => $id]);
            $prodName = $product['nama'] ?? $product['name'] ?? 'Barang';
            set_flash('status', "Barang {$prodName} berhasil dihapus.");
        } else {
            set_flash('error', 'Barang tidak ditemukan.');
        }

        redirect('/products');
    }

    private function handleImageUpload(?array $file, ?string $oldImage = null): ?string
    {
        return upload_product_image($file, $oldImage);
    }
}

