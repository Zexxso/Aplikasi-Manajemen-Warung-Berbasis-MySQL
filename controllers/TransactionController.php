<?php

require_once __DIR__ . '/../helpers/helpers.php';

class TransactionController
{
    public function storeStockIn(): void
    {
        if (!auth_check()) {
            redirect('/login');
        }

        if (!csrf_verify()) {
            set_flash('error', 'Token keamanan tidak valid.');
            back();
        }

        $ownerId = warung_owner_id();
        $productId = (int) ($_POST['id_produk'] ?? $_POST['product_id'] ?? 0);
        $quantity = (int) ($_POST['jumlah'] ?? $_POST['quantity'] ?? 0);
        $buyPrice = (float) ($_POST['harga_beli'] ?? $_POST['buy_price'] ?? 0);
        $note = trim($_POST['catatan'] ?? $_POST['note'] ?? '');

        if (!$productId || $quantity <= 0) {
            set_flash('error', 'Pilih barang dan masukkan jumlah masuk yang valid.');
            back();
        }

        if ($buyPrice < 0) {
            set_flash('error', 'Harga beli tidak boleh negatif.');
            back();
        }

        $pdo = db();
        try {
            $pdo->beginTransaction();

            // Lock product
            $stmt = $pdo->prepare("SELECT * FROM produk WHERE id = :id AND id_pengguna = :id_pengguna AND dihapus_pada IS NULL FOR UPDATE");
            $stmt->execute(['id' => $productId, 'id_pengguna' => $ownerId]);
            $product = $stmt->fetch();

            if (!$product) {
                throw new Exception("Barang tidak ditemukan.");
            }

            // 1. Update product stock and buy_price
            $prodStock = (int) ($product['stok'] ?? $product['stock']);
            $prodBuyPrice = (float) ($product['harga_beli'] ?? $product['buy_price']);
            $prodName = $product['nama'] ?? $product['name'] ?? 'Barang';
            $prodUnit = $product['satuan'] ?? $product['unit'] ?? 'item';

            $newStock = $prodStock + $quantity;
            $newBuyPrice = $buyPrice > 0 ? $buyPrice : $prodBuyPrice;

            $upStmt = $pdo->prepare("UPDATE produk SET stok = :stock, harga_beli = :buy_price, diperbarui_pada = NOW() WHERE id = :id");
            $upStmt->execute([
                'stock' => $newStock,
                'buy_price' => $newBuyPrice,
                'id' => $productId,
            ]);

            // 2. Record stock movement
            $noteText = $note ?: "Kulakan {$prodName}";
            $movementDate = date('Y-m-d H:i:s');

            $movStmt = $pdo->prepare("
                INSERT INTO mutasi_stok (id_pengguna, id_produk, jenis, alasan, jumlah, harga_satuan, harga_modal, tanggal_mutasi, catatan, dibuat_pada, diperbarui_pada)
                VALUES (:id_pengguna, :id_produk, 'in', 'purchase', :quantity, :unit_price, :unit_cost, :movement_date, :note, NOW(), NOW())
            ");
            $movStmt->execute([
                'id_pengguna' => $ownerId,
                'id_produk' => $productId,
                'quantity' => $quantity,
                'unit_price' => $buyPrice,
                'unit_cost' => $buyPrice,
                'movement_date' => $movementDate,
                'note' => $noteText,
            ]);
            $movementId = (int) $pdo->lastInsertId();

            // 3. Auto record expense in cash_entries / catatan_kas
            $catStmt = $pdo->prepare("SELECT id FROM kategori_kas WHERE jenis = 'expense' AND nama = 'Kulakan' AND (id_pengguna = :id_pengguna OR id_pengguna IS NULL) LIMIT 1");
            $catStmt->execute(['id_pengguna' => $ownerId]);
            $categoryId = $catStmt->fetchColumn();

            if (!$categoryId) {
                $fallbackStmt = $pdo->prepare("SELECT id FROM kategori_kas WHERE jenis = 'expense' AND (id_pengguna = :id_pengguna OR id_pengguna IS NULL) LIMIT 1");
                $fallbackStmt->execute(['id_pengguna' => $ownerId]);
                $categoryId = $fallbackStmt->fetchColumn();
            }

            $totalAmount = $quantity * $buyPrice;

            $cashStmt = $pdo->prepare("
                INSERT INTO catatan_kas (id_pengguna, id_kategori_kas, id_mutasi_stok, jenis, nominal, tanggal_transaksi, catatan, dibuat_pada, diperbarui_pada)
                VALUES (:id_pengguna, :id_kategori_kas, :id_mutasi_stok, 'expense', :amount, :entry_date, :note, NOW(), NOW())
            ");
            $cashStmt->execute([
                'id_pengguna' => $ownerId,
                'id_kategori_kas' => $categoryId,
                'id_mutasi_stok' => $movementId,
                'amount' => $totalAmount,
                'entry_date' => $movementDate,
                'note' => $noteText,
            ]);

            $pdo->commit();

            set_flash('status', "Barang masuk berhasil dicatat! Stok {$prodName} bertambah {$quantity} {$prodUnit}.");
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            set_flash('error', $e->getMessage());
        }

        back();
    }

    public function storeStockOut(): void
    {
        if (!auth_check()) {
            redirect('/login');
        }

        if (!csrf_verify()) {
            set_flash('error', 'Token keamanan tidak valid.');
            back();
        }

        $ownerId = warung_owner_id();
        $productId = (int) ($_POST['id_produk'] ?? $_POST['product_id'] ?? 0);
        $quantity = (int) ($_POST['jumlah'] ?? $_POST['quantity'] ?? 0);
        $reason = $_POST['alasan'] ?? $_POST['reason'] ?? 'sale';
        $note = trim($_POST['catatan'] ?? $_POST['note'] ?? '');

        if (!in_array($reason, ['sale', 'personal', 'damaged', 'adjustment'])) {
            $reason = 'sale';
        }

        if (!$productId || $quantity <= 0) {
            set_flash('error', 'Pilih barang dan masukkan jumlah keluar yang valid.');
            back();
        }

        $pdo = db();
        try {
            $pdo->beginTransaction();

            // Lock product
            $stmt = $pdo->prepare("SELECT * FROM produk WHERE id = :id AND id_pengguna = :id_pengguna AND dihapus_pada IS NULL FOR UPDATE");
            $stmt->execute(['id' => $productId, 'id_pengguna' => $ownerId]);
            $product = $stmt->fetch();

            if (!$product) {
                throw new Exception("Barang tidak ditemukan.");
            }

            $prodStock = (int) ($product['stok'] ?? $product['stock']);
            $prodName = $product['nama'] ?? $product['name'] ?? 'Barang';
            $prodUnit = $product['satuan'] ?? $product['unit'] ?? 'item';

            // CRITICAL BUSINESS RULE: Stock cannot be negative
            if ($prodStock < $quantity) {
                throw new Exception("Stok tidak mencukupi! Stok saat ini untuk {$prodName} hanya {$prodStock} {$prodUnit}.");
            }

            // 1. Deduct stock
            $newStock = $prodStock - $quantity;
            $upStmt = $pdo->prepare("UPDATE produk SET stok = :stock, diperbarui_pada = NOW() WHERE id = :id");
            $upStmt->execute([
                'stock' => $newStock,
                'id' => $productId,
            ]);

            // 2. Resolve customer and payment status if reason is sale
            $customerId = null;
            $customerLabel = '';
            $paymentStatus = 'paid';
            $isKasbon = false;

            if ($reason === 'sale') {
                $paymentMethod = $_POST['payment_method'] ?? 'cash';
                $isKasbon = ($paymentMethod === 'kasbon') || !empty($_POST['is_kasbon']);

                if ($isKasbon) {
                    $customerInput = trim($_POST['customer_name'] ?? '');
                    if ($customerInput === '') {
                        throw new Exception("Nama pembeli wajib diisi untuk transaksi kasbon / hutang.");
                    }
                    $customerId = get_or_create_kasbon_customer($ownerId, $customerInput);
                    $paymentStatus = 'debt';

                    $custStmt = $pdo->prepare("SELECT kode, nama FROM pelanggan WHERE id = :id LIMIT 1");
                    $custStmt->execute(['id' => $customerId]);
                    $customerData = $custStmt->fetch();
                    $customerName = $customerData['nama'] ?? $customerData['name'] ?? $customerInput;
                    $customerLabel = " (Kasbon: {$customerName})";
                } else {
                    // Penjualan Tunai (Pembeli Umum / Anonim)
                    $customerId = null;
                    $paymentStatus = 'paid';
                    $customerLabel = " (Tunai)";
                }
            }

            // 3. Insert stock movement
            $defaultNote = match ($reason) {
                'sale' => $isKasbon ? "Penjualan Kasbon {$prodName}" : "Penjualan {$prodName}",
                'personal' => "Pemakaian pribadi {$prodName}",
                'damaged' => "Barang rusak/hilang {$prodName}",
                default => "Penyesuaian stok {$prodName}",
            };
            $noteText = $note ?: $defaultNote;
            $movementDate = date('Y-m-d H:i:s');
            $unitPrice = (float) ($product['harga_jual'] ?? $product['sell_price']);
            $unitCost = (float) ($product['harga_beli'] ?? $product['buy_price']);

            $movStmt = $pdo->prepare("
                INSERT INTO mutasi_stok (id_pengguna, id_pelanggan, no_faktur, id_produk, jenis, alasan, status_pembayaran, jumlah, harga_satuan, harga_modal, tanggal_mutasi, catatan, dibuat_pada, diperbarui_pada)
                VALUES (:id_pengguna, :id_pelanggan, 'TMP', :id_produk, 'out', :reason, :payment_status, :quantity, :unit_price, :unit_cost, :movement_date, :note, NOW(), NOW())
            ");
            $movStmt->execute([
                'id_pengguna' => $ownerId,
                'id_pelanggan' => $customerId,
                'id_produk' => $productId,
                'reason' => $reason,
                'payment_status' => $paymentStatus,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'unit_cost' => $unitCost,
                'movement_date' => $movementDate,
                'note' => $noteText,
            ]);
            $movementId = (int) $pdo->lastInsertId();

            // Generate clean readable invoice_no
            $invoiceNo = generate_invoice_no($movementId, $movementDate);
            $pdo->prepare("UPDATE mutasi_stok SET no_faktur = :inv WHERE id = :id")->execute([
                'inv' => $invoiceNo,
                'id' => $movementId,
            ]);

            // 4. If reason is sale AND payment is paid (TUNAI), record income in catatan_kas
            // (If KASBON, cash has not been received, so it is NOT recorded in catatan_kas yet!)
            if ($reason === 'sale' && $paymentStatus === 'paid') {
                $catStmt = $pdo->prepare("SELECT id FROM kategori_kas WHERE jenis = 'income' AND nama = 'Penjualan' AND (id_pengguna = :id_pengguna OR id_pengguna IS NULL) LIMIT 1");
                $catStmt->execute(['id_pengguna' => $ownerId]);
                $categoryId = $catStmt->fetchColumn();

                if (!$categoryId) {
                    $fallbackStmt = $pdo->prepare("SELECT id FROM kategori_kas WHERE jenis = 'income' AND (id_pengguna = :id_pengguna OR id_pengguna IS NULL) LIMIT 1");
                    $fallbackStmt->execute(['id_pengguna' => $ownerId]);
                    $categoryId = $fallbackStmt->fetchColumn();
                }

                $totalIncome = $quantity * $unitPrice;

                $cashStmt = $pdo->prepare("
                    INSERT INTO catatan_kas (id_pengguna, id_pelanggan, id_kategori_kas, id_mutasi_stok, jenis, nominal, tanggal_transaksi, catatan, dibuat_pada, diperbarui_pada)
                    VALUES (:id_pengguna, :id_pelanggan, :id_kategori_kas, :id_mutasi_stok, 'income', :amount, :entry_date, :note, NOW(), NOW())
                ");
                $cashStmt->execute([
                    'id_pengguna' => $ownerId,
                    'id_pelanggan' => $customerId,
                    'id_kategori_kas' => $categoryId,
                    'id_mutasi_stok' => $movementId,
                    'amount' => $totalIncome,
                    'entry_date' => $movementDate,
                    'note' => $noteText,
                ]);
            }

            $pdo->commit();

            if ($reason === 'sale') {
                if ($isKasbon) {
                    set_flash('status', "Penjualan KASBON atas nama {$customerName} ({$invoiceNo}) berhasil dicatat di Buku Kasbon!");
                } else {
                    set_flash('status', "Penjualan Tunai ({$invoiceNo}) berhasil dicatat! Kas warung bertambah " . format_rupiah($quantity * $unitPrice) . ".");
                }
            } else {
                $reasonText = match ($reason) {
                    'personal' => 'Pemakaian pribadi',
                    'damaged' => 'Barang rusak',
                    default => 'Barang keluar',
                };
                set_flash('status', "{$reasonText} berhasil dicatat! Stok {$prodName} berkurang {$quantity} {$prodUnit}.");
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            set_flash('error', $e->getMessage());
        }

        back();
    }

    public function storeCash(): void
    {
        if (!auth_check()) {
            redirect('/login');
        }

        if (!csrf_verify()) {
            set_flash('error', 'Token keamanan tidak valid.');
            back();
        }

        $ownerId = warung_owner_id();
        $type = (($_POST['jenis'] ?? $_POST['type'] ?? '') === 'income') ? 'income' : 'expense';
        $categoryId = (int) ($_POST['id_kategori_kas'] ?? $_POST['cash_category_id'] ?? 0);
        $rawAmount = (string) ($_POST['nominal'] ?? $_POST['amount'] ?? '');
        $cleanAmount = (float) preg_replace('/[^\d]/', '', $rawAmount);
        $dateInput = $_POST['tanggal_transaksi'] ?? $_POST['entry_date'] ?? null;
        $entryDate = !empty($dateInput) ? $dateInput . ' ' . date('H:i:s') : date('Y-m-d H:i:s');
        $note = trim($_POST['catatan'] ?? $_POST['note'] ?? '');

        if ($cleanAmount <= 0) {
            set_flash('error', 'Nominal uang harus lebih dari 0.');
            back();
        }

        if (!$categoryId) {
            set_flash('error', 'Pilih kategori kas terlebih dahulu.');
            back();
        }

        $catStmt = db()->prepare("SELECT nama FROM kategori_kas WHERE id = :id AND (id_pengguna = :id_pengguna OR id_pengguna IS NULL) LIMIT 1");
        $catStmt->execute(['id' => $categoryId, 'id_pengguna' => $ownerId]);
        $categoryName = $catStmt->fetchColumn();

        if (!$categoryName) {
            set_flash('error', 'Kategori kas tidak valid.');
            back();
        }

        $noteText = $note ?: $categoryName;

        $insStmt = db()->prepare("
            INSERT INTO catatan_kas (id_pengguna, id_kategori_kas, id_mutasi_stok, jenis, nominal, tanggal_transaksi, catatan, dibuat_pada, diperbarui_pada)
            VALUES (:id_pengguna, :id_kategori_kas, NULL, :type, :amount, :entry_date, :note, NOW(), NOW())
        ");
        $insStmt->execute([
            'id_pengguna' => $ownerId,
            'id_kategori_kas' => $categoryId,
            'type' => $type,
            'amount' => $cleanAmount,
            'entry_date' => $entryDate,
            'note' => $noteText,
        ]);

        $typeText = $type === 'income' ? 'Pemasukan kas' : 'Pengeluaran kas';
        set_flash('status', "{$typeText} sebesar " . format_rupiah($cleanAmount) . " berhasil dicatat!");

        back();
    }

    public function quickStoreProduct(): void
    {
        if (!auth_check()) {
            json_response(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $ownerId = warung_owner_id();

        // Handle JSON or Form-encoded input
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $name = trim($input['name'] ?? $input['nama'] ?? '');
        $categoryId = !empty($input['category_id'] ?? $input['id_kategori']) ? (int) ($input['category_id'] ?? $input['id_kategori']) : null;
        $unit = trim($input['unit'] ?? $input['satuan'] ?? 'pcs');
        $buyPrice = (float) ($input['buy_price'] ?? $input['harga_beli'] ?? 0);
        $sellPrice = (float) ($input['sell_price'] ?? $input['harga_jual'] ?? 0);
        $stock = (int) ($input['stock'] ?? $input['stok'] ?? 0);
        $minStock = (int) ($input['min_stock'] ?? $input['stok_minimum'] ?? 5);

        if (empty($name)) {
            json_response(['success' => false, 'message' => 'Nama barang wajib diisi.'], 422);
        }

        $insStmt = db()->prepare("
            INSERT INTO produk (id_pengguna, id_kategori, nama, satuan, harga_beli, harga_jual, stok, stok_minimum, status_aktif, dibuat_pada, diperbarui_pada)
            VALUES (:id_pengguna, :id_kategori, :name, :unit, :buy_price, :sell_price, :stock, :min_stock, 1, NOW(), NOW())
        ");
        $insStmt->execute([
            'id_pengguna' => $ownerId,
            'id_kategori' => $categoryId,
            'name' => $name,
            'unit' => $unit,
            'buy_price' => $buyPrice,
            'sell_price' => $sellPrice,
            'stock' => $stock,
            'min_stock' => $minStock,
        ]);
        $newId = (int) db()->lastInsertId();

        $productData = [
            'id' => $newId,
            'nama' => $name,
            'name' => $name,
            'satuan' => $unit,
            'unit' => $unit,
            'harga_beli' => $buyPrice,
            'buy_price' => $buyPrice,
            'harga_jual' => $sellPrice,
            'sell_price' => $sellPrice,
            'stok' => $stock,
            'stock' => $stock,
        ];

        json_response([
            'success' => true,
            'product' => $productData,
        ]);
    }

    public function settleKasbon(int $movementId): void
    {
        if (!auth_check()) {
            redirect('/login');
        }

        if (!csrf_verify()) {
            set_flash('error', 'Token keamanan tidak valid.');
            back();
        }

        $ownerId = warung_owner_id();
        $pdo = db();

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                SELECT sm.*, p.nama AS product_name, p.nama, c.nama AS customer_name, c.nama AS nama_pelanggan
                FROM mutasi_stok sm
                LEFT JOIN produk p ON p.id = sm.id_produk
                LEFT JOIN pelanggan c ON c.id = sm.id_pelanggan
                WHERE sm.id = :id AND sm.id_pengguna = :id_pengguna AND sm.status_pembayaran = 'debt' AND sm.dihapus_pada IS NULL
                FOR UPDATE
            ");
            $stmt->execute(['id' => $movementId, 'id_pengguna' => $ownerId]);
            $movement = $stmt->fetch();

            if (!$movement) {
                throw new Exception("Data transaksi kasbon tidak ditemukan atau sudah lunas.");
            }

            $now = date('Y-m-d H:i:s');
            $up = $pdo->prepare("UPDATE mutasi_stok SET status_pembayaran = 'paid', dibayar_pada = :paid_at, diperbarui_pada = NOW() WHERE id = :id");
            $up->execute(['paid_at' => $now, 'id' => $movementId]);

            // Insert into catatan_kas
            $catStmt = $pdo->prepare("SELECT id FROM kategori_kas WHERE jenis = 'income' AND nama = 'Penjualan' AND (id_pengguna = :id_pengguna OR id_pengguna IS NULL) LIMIT 1");
            $catStmt->execute(['id_pengguna' => $ownerId]);
            $categoryId = $catStmt->fetchColumn();

            if (!$categoryId) {
                $fallbackStmt = $pdo->prepare("SELECT id FROM kategori_kas WHERE jenis = 'income' AND (id_pengguna = :id_pengguna OR id_pengguna IS NULL) LIMIT 1");
                $fallbackStmt->execute(['id_pengguna' => $ownerId]);
                $categoryId = $fallbackStmt->fetchColumn();
            }

            $quantity = (int) ($movement['jumlah'] ?? $movement['quantity']);
            $unitPrice = (float) ($movement['harga_satuan'] ?? $movement['unit_price']);
            $totalAmount = $quantity * $unitPrice;
            $customerName = $movement['customer_name'] ?? $movement['nama_pelanggan'] ?? 'Pembeli';
            $invoiceNo = $movement['no_faktur'] ?? $movement['invoice_no'] ?? ('TRX-' . $movementId);
            $productName = $movement['product_name'] ?? $movement['nama'] ?? 'Barang';
            $note = "Pelunasan Kasbon {$customerName} ({$invoiceNo}) - {$productName}";

            $cashStmt = $pdo->prepare("
                INSERT INTO catatan_kas (id_pengguna, id_pelanggan, id_kategori_kas, id_mutasi_stok, jenis, nominal, tanggal_transaksi, catatan, dibuat_pada, diperbarui_pada)
                VALUES (:id_pengguna, :id_pelanggan, :id_kategori_kas, :id_mutasi_stok, 'income', :amount, :entry_date, :note, NOW(), NOW())
            ");
            $cashStmt->execute([
                'id_pengguna' => $ownerId,
                'id_pelanggan' => $movement['id_pelanggan'] ?? $movement['customer_id'] ?? null,
                'id_kategori_kas' => $categoryId,
                'id_mutasi_stok' => $movementId,
                'amount' => $totalAmount,
                'entry_date' => $now,
                'note' => $note,
            ]);

            $pdo->commit();
            set_flash('status', "Kasbon {$customerName} sebesar " . format_rupiah($totalAmount) . " berhasil dilunasi!");
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            set_flash('error', $e->getMessage());
        }

        back();
    }

    public function showDetail(int $movementId): void
    {
        if (!auth_check()) {
            redirect('/login');
        }

        $ownerId = warung_owner_id();
        $owner = get_warung_owner();
        $user = auth_user();

        $stmt = db()->prepare("
            SELECT 
                sm.id AS movement_id,
                sm.id AS id_mutasi_stok,
                sm.no_faktur AS invoice_no,
                sm.no_faktur,
                sm.jenis AS movement_type,
                sm.jenis,
                sm.alasan AS reason,
                sm.alasan,
                sm.status_pembayaran AS payment_status,
                sm.status_pembayaran,
                sm.jumlah AS quantity,
                sm.jumlah,
                sm.harga_satuan AS unit_price,
                sm.harga_satuan,
                sm.harga_modal AS unit_cost,
                sm.harga_modal,
                (sm.jumlah * sm.harga_satuan) AS subtotal,
                sm.tanggal_mutasi AS movement_date,
                sm.tanggal_mutasi,
                sm.dibayar_pada AS paid_at,
                sm.dibayar_pada,
                sm.catatan AS note,
                sm.catatan,
                sm.dibuat_pada AS created_at,
                sm.dibuat_pada,
                p.id AS product_id,
                p.id AS id_produk,
                p.nama AS product_name,
                p.nama,
                p.satuan AS unit,
                p.satuan,
                p.gambar AS product_image,
                p.gambar,
                c.id AS customer_id,
                c.id AS id_pelanggan,
                c.kode AS customer_code,
                c.kode,
                c.nama AS customer_name,
                c.nama,
                c.telepon AS customer_phone,
                c.telepon,
                c.alamat AS customer_address,
                c.alamat,
                ce.id AS cash_entry_id,
                ce.id AS id_catatan_kas,
                ce.nominal AS cash_amount,
                ce.nominal,
                u.nama AS creator_name,
                u.nama
            FROM mutasi_stok sm
            LEFT JOIN produk p ON p.id = sm.id_produk
            LEFT JOIN pelanggan c ON c.id = sm.id_pelanggan
            LEFT JOIN catatan_kas ce ON ce.id_mutasi_stok = sm.id AND ce.dihapus_pada IS NULL
            LEFT JOIN pengguna u ON u.id = sm.id_pengguna
            WHERE sm.id = :id AND sm.id_pengguna = :id_pengguna AND sm.dihapus_pada IS NULL
            LIMIT 1
        ");
        $stmt->execute(['id' => $movementId, 'id_pengguna' => $ownerId]);
        $tx = $stmt->fetch();

        if (!$tx) {
            set_flash('error', 'Data detail transaksi tidak ditemukan.');
            redirect('/history');
        }

        view('transactions.show', [
            'tx' => $tx,
            'owner' => $owner,
            'user' => $user,
            'title' => 'Detail Transaksi ' . ($tx['invoice_no'] ?: ('TRX-' . $tx['movement_id'])),
        ]);
    }

    public function showReceipt(int $movementId): void
    {
        if (!auth_check()) {
            redirect('/login');
        }

        $ownerId = warung_owner_id();
        $owner = get_warung_owner();
        $user = auth_user();

        $stmt = db()->prepare("
            SELECT 
                sm.id AS movement_id,
                sm.id AS id_mutasi_stok,
                sm.no_faktur AS invoice_no,
                sm.no_faktur,
                sm.jenis AS type,
                sm.jenis,
                sm.alasan AS reason,
                sm.alasan,
                sm.status_pembayaran AS payment_status,
                sm.status_pembayaran,
                sm.jumlah AS quantity,
                sm.jumlah,
                sm.harga_satuan AS unit_price,
                sm.harga_satuan,
                (sm.jumlah * sm.harga_satuan) AS subtotal,
                sm.tanggal_mutasi AS movement_date,
                sm.tanggal_mutasi,
                sm.dibayar_pada AS paid_at,
                sm.dibayar_pada,
                sm.catatan AS note,
                sm.catatan,
                p.nama AS product_name,
                p.nama,
                p.satuan AS unit,
                p.satuan,
                c.id AS customer_id,
                c.id AS id_pelanggan,
                c.kode AS customer_code,
                c.kode,
                c.nama AS customer_name,
                c.nama,
                c.telepon AS customer_phone,
                c.telepon
            FROM mutasi_stok sm
            LEFT JOIN produk p ON p.id = sm.id_produk
            LEFT JOIN pelanggan c ON c.id = sm.id_pelanggan
            WHERE sm.id = :id AND sm.id_pengguna = :id_pengguna AND sm.dihapus_pada IS NULL
            LIMIT 1
        ");
        $stmt->execute(['id' => $movementId, 'id_pengguna' => $ownerId]);
        $receipt = $stmt->fetch();

        if (!$receipt) {
            set_flash('error', 'Data struk tidak ditemukan.');
            redirect('/history');
        }

        view('receipt.show', [
            'receipt' => $receipt,
            'owner' => $owner,
            'user' => $user,
            'title' => 'Struk Penjualan #' . ($receipt['invoice_no'] ?: $receipt['movement_id']),
        ], 'guest');
    }
}

