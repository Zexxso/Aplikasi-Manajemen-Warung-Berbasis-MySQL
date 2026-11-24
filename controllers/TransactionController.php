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

        $user = auth_user();
        $userId = (int) $user['id'];
        $productId = (int) ($_POST['id_produk'] ?? $_POST['product_id'] ?? 0);
        $quantity = (int) ($_POST['jumlah'] ?? $_POST['quantity'] ?? 0);
        $buyPrice = (float) ($_POST['harga_beli'] ?? $_POST['buy_price'] ?? 0);
        $supplierId = (int) ($_POST['id_pemasok'] ?? $_POST['supplier_id'] ?? 0);
        $quickSupplierName = trim($_POST['quick_supplier_name'] ?? '');
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
            $stmt = $pdo->prepare("SELECT * FROM produk WHERE id = :id AND dihapus_pada IS NULL FOR UPDATE");
            $stmt->execute(['id' => $productId]);
            $product = $stmt->fetch();

            if (!$product) {
                throw new Exception("Barang tidak ditemukan.");
            }

            // 1. Resolve Supplier
            if ($supplierId <= 0 && $quickSupplierName !== '') {
                $chkSup = $pdo->prepare("SELECT id_pemasok FROM pemasok WHERE LOWER(nama_pemasok) = LOWER(?) AND dihapus_pada IS NULL LIMIT 1");
                $chkSup->execute([$quickSupplierName]);
                $supplierId = (int) $chkSup->fetchColumn();

                if (!$supplierId) {
                    $insSup = $pdo->prepare("INSERT INTO pemasok (nama_pemasok, dibuat_pada, diperbarui_pada) VALUES (?, NOW(), NOW())");
                    $insSup->execute([$quickSupplierName]);
                    $supplierId = (int) $pdo->lastInsertId();
                }
            }

            if ($supplierId <= 0) {
                $defSup = $pdo->query("SELECT id_pemasok FROM pemasok WHERE dihapus_pada IS NULL ORDER BY id_pemasok ASC LIMIT 1")->fetchColumn();
                $supplierId = (int) ($defSup ?: 1);
            }

            // 2. Update product stock and buy_price
            $prodStock = (int) ($product['stok'] ?? $product['stock'] ?? 0);
            $prodBuyPrice = (float) ($product['harga_beli'] ?? $product['buy_price'] ?? 0);
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

            // 3. Record stock movement
            $noteText = $note ?: "Kulakan {$prodName}";
            $movementDate = date('Y-m-d H:i:s');
            $totalAmount = $quantity * $buyPrice;

            $movStmt = $pdo->prepare("
                INSERT INTO mutasi_stok (id_pengguna, id_produk, jenis, alasan, jumlah, harga_satuan, harga_modal, tanggal_mutasi, catatan, dibuat_pada, diperbarui_pada)
                VALUES (:id_pengguna, :id_produk, 'in', 'purchase', :quantity, :unit_price, :unit_cost, :movement_date, :note, NOW(), NOW())
            ");
            $movStmt->execute([
                'id_pengguna' => $userId,
                'id_produk' => $productId,
                'quantity' => $quantity,
                'unit_price' => $buyPrice,
                'unit_cost' => $buyPrice,
                'movement_date' => $movementDate,
                'note' => $noteText,
            ]);
            $movementId = (int) $pdo->lastInsertId();

            // 4. Record into suplai table
            $supStmt = $pdo->prepare("
                INSERT INTO suplai (id_pemasok, id_produk, id_pengguna, id_mutasi_stok, tanggal_suplai, jumlah, harga_beli, total_harga, catatan, dibuat_pada, diperbarui_pada)
                VALUES (:id_pemasok, :id_produk, :id_pengguna, :id_mutasi_stok, :tanggal, :jumlah, :harga, :total, :catatan, NOW(), NOW())
            ");
            $supStmt->execute([
                'id_pemasok' => $supplierId,
                'id_produk' => $productId,
                'id_pengguna' => $userId,
                'id_mutasi_stok' => $movementId,
                'tanggal' => $movementDate,
                'jumlah' => $quantity,
                'harga' => $buyPrice,
                'total' => $totalAmount,
                'catatan' => $noteText,
            ]);

            // 5. Auto record expense in catatan_kas
            $catStmt = $pdo->query("SELECT id FROM kategori_kas WHERE jenis = 'expense' AND nama = 'Kulakan' LIMIT 1");
            $categoryId = $catStmt->fetchColumn();

            if (!$categoryId) {
                $categoryId = $pdo->query("SELECT id FROM kategori_kas WHERE jenis = 'expense' LIMIT 1")->fetchColumn();
            }

            $cashStmt = $pdo->prepare("
                INSERT INTO catatan_kas (id_pengguna, id_kategori_kas, id_mutasi_stok, jenis, nominal, tanggal_transaksi, catatan, dibuat_pada, diperbarui_pada)
                VALUES (:id_pengguna, :id_kategori_kas, :id_mutasi_stok, 'expense', :amount, :entry_date, :note, NOW(), NOW())
            ");
            $cashStmt->execute([
                'id_pengguna' => $userId,
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

        $user = auth_user();
        $userId = (int) $user['id'];
        $reason = $_POST['alasan'] ?? $_POST['reason'] ?? 'sale';
        $note = trim($_POST['catatan'] ?? $_POST['note'] ?? '');

        if (!in_array($reason, ['sale', 'personal', 'damaged', 'adjustment'])) {
            $reason = 'sale';
        }

        $pdo = db();
        try {
            $pdo->beginTransaction();

            // Prepare items array (supporting Multi-Item cart or Single-item)
            $items = [];
            $rawCart = $_POST['cart_items'] ?? null;
            if (!empty($rawCart)) {
                $decoded = is_string($rawCart) ? json_decode($rawCart, true) : $rawCart;
                if (is_array($decoded)) {
                    foreach ($decoded as $ci) {
                        $pId = (int) ($ci['id_produk'] ?? $ci['product_id'] ?? ($ci['product']['id'] ?? 0));
                        $qty = (int) ($ci['jumlah'] ?? $ci['quantity'] ?? 0);
                        if ($pId > 0 && $qty > 0) {
                            $items[] = ['product_id' => $pId, 'quantity' => $qty];
                        }
                    }
                }
            }

            // Fallback to single item if cart is empty
            if (empty($items)) {
                $productId = (int) ($_POST['id_produk'] ?? $_POST['product_id'] ?? 0);
                $quantity = (int) ($_POST['jumlah'] ?? $_POST['quantity'] ?? 0);
                if ($productId > 0 && $quantity > 0) {
                    $items[] = ['product_id' => $productId, 'quantity' => $quantity];
                }
            }

            if (empty($items)) {
                throw new Exception("Pilih setidaknya satu barang dan masukkan kuantitas yang valid.");
            }

            // If reason is not sale, only single item is processed
            if ($reason !== 'sale') {
                $item = $items[0];
                $productId = $item['product_id'];
                $quantity = $item['quantity'];

                $stmt = $pdo->prepare("SELECT * FROM produk WHERE id = :id AND dihapus_pada IS NULL FOR UPDATE");
                $stmt->execute(['id' => $productId]);
                $product = $stmt->fetch();
                if (!$product) throw new Exception("Barang tidak ditemukan.");

                $prodStock = (int) ($product['stok'] ?? 0);
                $prodName = $product['nama'] ?? 'Barang';
                $prodUnit = $product['satuan'] ?? 'item';

                if ($prodStock < $quantity) {
                    throw new Exception("Stok {$prodName} tidak mencukupi! Sisa stok: {$prodStock} {$prodUnit}.");
                }

                $pdo->prepare("UPDATE produk SET stok = stok - :qty, diperbarui_pada = NOW() WHERE id = :id")
                    ->execute(['qty' => $quantity, 'id' => $productId]);

                $movementDate = date('Y-m-d H:i:s');
                $defaultNote = match ($reason) {
                    'personal' => "Pemakaian pribadi {$prodName}",
                    'damaged' => "Barang rusak/hilang {$prodName}",
                    default => "Penyesuaian stok {$prodName}",
                };
                $noteText = $note ?: $defaultNote;

                $movStmt = $pdo->prepare("
                    INSERT INTO mutasi_stok (id_pengguna, no_faktur, id_produk, jenis, alasan, status_pembayaran, jumlah, harga_satuan, harga_modal, tanggal_mutasi, catatan, dibuat_pada, diperbarui_pada)
                    VALUES (:uid, 'TMP', :pid, 'out', :reason, 'paid', :qty, :price, :cost, :dt, :note, NOW(), NOW())
                ");
                $movStmt->execute([
                    'uid' => $userId,
                    'pid' => $productId,
                    'reason' => $reason,
                    'qty' => $quantity,
                    'price' => (float)$product['harga_jual'],
                    'cost' => (float)$product['harga_beli'],
                    'dt' => $movementDate,
                    'note' => $noteText,
                ]);
                $movId = (int)$pdo->lastInsertId();
                $invoiceNo = generate_invoice_no($movId, $movementDate);
                $pdo->prepare("UPDATE mutasi_stok SET no_faktur = ? WHERE id = ?")->execute([$invoiceNo, $movId]);

                $pdo->commit();
                set_flash('status', "Barang keluar ({$prodName} - {$quantity} {$prodUnit}) berhasil dicatat!");
                back();
                return;
            }

            // === REASON IS SALE ===
            // Resolve payment method & customer
            $paymentMethod = $_POST['payment_method'] ?? 'cash';
            $isKasbon = ($paymentMethod === 'kasbon') || !empty($_POST['is_kasbon']);
            $customerId = null;
            $customerName = '';
            $paymentStatus = 'paid';

            if ($isKasbon) {
                $customerInput = trim($_POST['customer_name'] ?? '');
                if ($customerInput === '') {
                    throw new Exception("Nama pembeli wajib diisi untuk transaksi kasbon / hutang.");
                }
                $customerId = get_or_create_kasbon_customer(null, $customerInput);
                $paymentStatus = 'debt';

                $custStmt = $pdo->prepare("SELECT nama FROM pelanggan WHERE id = :id LIMIT 1");
                $custStmt->execute(['id' => $customerId]);
                $customerName = $custStmt->fetchColumn() ?: $customerInput;
            }

            // Lock and validate all items
            $validatedItems = [];
            $totalHarga = 0.00;

            foreach ($items as $item) {
                $pId = $item['product_id'];
                $qty = $item['quantity'];

                $stmt = $pdo->prepare("SELECT * FROM produk WHERE id = :id AND dihapus_pada IS NULL FOR UPDATE");
                $stmt->execute(['id' => $pId]);
                $prod = $stmt->fetch();
                if (!$prod) throw new Exception("Barang ID #{$pId} tidak ditemukan.");

                $stock = (int)$prod['stok'];
                $pName = $prod['nama'];
                $pUnit = $prod['satuan'] ?: 'item';

                if ($stock < $qty) {
                    throw new Exception("Stok untuk '{$pName}' tidak mencukupi! Stok saat ini: {$stock} {$pUnit}, jumlah diminta: {$qty} {$pUnit}.");
                }

                $sellPrice = (float)$prod['harga_jual'];
                $buyPrice = (float)$prod['harga_beli'];
                $subtotal = $qty * $sellPrice;
                $totalHarga += $subtotal;

                $validatedItems[] = [
                    'product' => $prod,
                    'quantity' => $qty,
                    'sell_price' => $sellPrice,
                    'buy_price' => $buyPrice,
                    'subtotal' => $subtotal,
                ];
            }

            // Payment calculations
            $movementDate = date('Y-m-d H:i:s');
            $rawCash = trim((string)($_POST['cash_received'] ?? $_POST['jumlah_bayar'] ?? ''));
            $cleanCash = (float) preg_replace('/[^\d.]/', '', $rawCash);

            if ($isKasbon) {
                $jumlahBayar = 0.00;
                $kembalian = 0.00;
            } else {
                $jumlahBayar = ($cleanCash >= $totalHarga) ? $cleanCash : $totalHarga;
                $kembalian = max(0.00, $jumlahBayar - $totalHarga);
            }

            // Generate clean invoice number
            $invPrefix = 'TRX-' . date('ymd', strtotime($movementDate)) . '-';
            $randSuffix = str_pad((string)random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
            $invoiceNo = $invPrefix . $randSuffix;
            while ($pdo->query("SELECT id_pesanan FROM pesanan WHERE no_faktur = '{$invoiceNo}' LIMIT 1")->fetch()) {
                $randSuffix = str_pad((string)random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
                $invoiceNo = $invPrefix . $randSuffix;
            }

            // 1. Insert header pesanan
            $insPesanan = $pdo->prepare("
                INSERT INTO pesanan (
                    no_faktur, id_pelanggan, id_pengguna, tanggal_pesanan,
                    total_harga, jumlah_bayar, kembalian, metode_pembayaran,
                    status_pembayaran, catatan, dibuat_pada, diperbarui_pada
                ) VALUES (
                    :faktur, :pelanggan, :pengguna, :tanggal,
                    :total, :bayar, :kembalian, :metode,
                    :status, :catatan, NOW(), NOW()
                )
            ");
            $insPesanan->execute([
                'faktur' => $invoiceNo,
                'pelanggan' => $customerId,
                'pengguna' => $userId,
                'tanggal' => $movementDate,
                'total' => $totalHarga,
                'bayar' => $jumlahBayar,
                'kembalian' => $kembalian,
                'metode' => $isKasbon ? 'kasbon' : 'cash',
                'status' => $paymentStatus,
                'catatan' => $note ?: null,
            ]);
            $pesananId = (int)$pdo->lastInsertId();

            // 2. Insert detail_pesanan & mutasi_stok, deduct stock
            $firstMovementId = null;
            foreach ($validatedItems as $vi) {
                $p = $vi['product'];
                $qty = $vi['quantity'];
                $sub = $vi['subtotal'];
                $pName = $p['nama'];

                // Deduct stock
                $pdo->prepare("UPDATE produk SET stok = stok - :qty, diperbarui_pada = NOW() WHERE id = :id")
                    ->execute(['qty' => $qty, 'id' => $p['id']]);

                // Insert detail_pesanan
                $insDetail = $pdo->prepare("
                    INSERT INTO detail_pesanan (
                        id_pesanan, id_produk, jumlah, harga_satuan,
                        harga_modal, subtotal, dibuat_pada, diperbarui_pada
                    ) VALUES (
                        :pesanan, :produk, :jumlah, :harga,
                        :modal, :subtotal, NOW(), NOW()
                    )
                ");
                $insDetail->execute([
                    'pesanan' => $pesananId,
                    'produk' => $p['id'],
                    'jumlah' => $qty,
                    'harga' => $vi['sell_price'],
                    'modal' => $vi['buy_price'],
                    'subtotal' => $sub,
                ]);

                // Insert mutasi_stok
                $movNote = $isKasbon ? "Penjualan Kasbon {$pName}" : "Penjualan {$pName}";
                $movStmt = $pdo->prepare("
                    INSERT INTO mutasi_stok (
                        id_pengguna, id_pelanggan, id_pesanan, no_faktur, id_produk,
                        jenis, alasan, status_pembayaran, jumlah, harga_satuan,
                        harga_modal, tanggal_mutasi, catatan, dibuat_pada, diperbarui_pada
                    ) VALUES (
                        :uid, :cid, :pid, :faktur, :prod_id,
                        'out', 'sale', :status, :qty, :price,
                        :modal, :tgl, :note, NOW(), NOW()
                    )
                ");
                $movStmt->execute([
                    'uid' => $userId,
                    'cid' => $customerId,
                    'pid' => $pesananId,
                    'faktur' => $invoiceNo,
                    'prod_id' => $p['id'],
                    'status' => $paymentStatus,
                    'qty' => $qty,
                    'price' => $vi['sell_price'],
                    'modal' => $vi['buy_price'],
                    'tgl' => $movementDate,
                    'note' => $movNote,
                ]);
                if ($firstMovementId === null) {
                    $firstMovementId = (int)$pdo->lastInsertId();
                }
            }

            // 3. If paid (cash), record in catatan_kas
            if ($paymentStatus === 'paid') {
                $catStmt = $pdo->query("SELECT id FROM kategori_kas WHERE jenis = 'income' AND nama = 'Penjualan' LIMIT 1");
                $categoryId = $catStmt->fetchColumn();
                if (!$categoryId) {
                    $categoryId = $pdo->query("SELECT id FROM kategori_kas WHERE jenis = 'income' LIMIT 1")->fetchColumn();
                }

                $itemSummary = count($validatedItems) === 1 ? $validatedItems[0]['product']['nama'] : (count($validatedItems) . " macam barang");
                $cashNote = "Penjualan {$invoiceNo} ({$itemSummary})";

                $cashStmt = $pdo->prepare("
                    INSERT INTO catatan_kas (
                        id_pengguna, id_pelanggan, id_kategori_kas, id_pesanan, id_mutasi_stok,
                        jenis, nominal, tanggal_transaksi, catatan, dibuat_pada, diperbarui_pada
                    ) VALUES (
                        :uid, :cid, :cat_id, :pesanan, :mutasi,
                        'income', :amount, :tgl, :note, NOW(), NOW()
                    )
                ");
                $cashStmt->execute([
                    'uid' => $userId,
                    'cid' => $customerId,
                    'cat_id' => $categoryId,
                    'pesanan' => $pesananId,
                    'mutasi' => $firstMovementId,
                    'amount' => $totalHarga,
                    'tgl' => $movementDate,
                    'note' => $cashNote,
                ]);
            }

            $pdo->commit();

            if ($isKasbon) {
                set_flash('status', "Penjualan KASBON atas nama {$customerName} ({$invoiceNo}) berhasil dicatat di Buku Kasbon!");
            } else {
                $changeText = $kembalian > 0 ? " · Kembalian: " . format_rupiah($kembalian) : "";
                set_flash('status', "Penjualan Tunai ({$invoiceNo}) berhasil dicatat! Total: " . format_rupiah($totalHarga) . $changeText . " · <a href='" . url('/receipt/' . $firstMovementId) . "' class='underline font-bold text-white'>Lihat Struk</a>");
            }

            // Notifikasi: cek stok menipis setelah setiap item terjual
            foreach ($validatedItems as $vi) {
                notif_stok_setelah_keluar((int) $vi['product']['id']);
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

        $userId = (int) auth_user()['id'];
        $type = (($_POST['jenis'] ?? $_POST['type'] ?? '') === 'income') ? 'income' : 'expense';
        $categoryId = (int) ($_POST['id_kategori_kas'] ?? $_POST['cash_category_id'] ?? 0);
        $rawAmount = (string) ($_POST['nominal'] ?? $_POST['amount'] ?? '');
        $cleanAmount = (float) preg_replace('/[^\d]/', '', $rawAmount);
        $dateInput = $_POST['tanggal_transaksi'] ?? $_POST['entry_date'] ?? null;
        $entryDate = !empty($dateInput) ? $dateInput . ' ' . date('H:i:s') : date('Y-m-d H:i:s');
        $note = trim($_POST['catatan'] ?? $_POST['note'] ?? '');

        if ($cleanAmount <= 0) {
            set_flash('error', 'Masukkan jumlah nominal uang yang valid.');
            back();
        }

        if (!$categoryId) {
            set_flash('error', 'Pilih kategori kas.');
            back();
        }

        $pdo = db();
        try {
            $catStmt = $pdo->prepare("SELECT id, nama FROM kategori_kas WHERE id = :id AND jenis = :type LIMIT 1");
            $catStmt->execute(['id' => $categoryId, 'type' => $type]);
            $category = $catStmt->fetch();

            if (!$category) {
                throw new Exception("Kategori kas tidak valid.");
            }

            $catName = $category['nama'] ?? $category['name'] ?? 'Kas';
            $defaultNote = ($type === 'income') ? "Pemasukan: {$catName}" : "Pengeluaran: {$catName}";
            $noteText = $note ?: $defaultNote;

            $cashStmt = $pdo->prepare("
                INSERT INTO catatan_kas (id_pengguna, id_kategori_kas, jenis, nominal, tanggal_transaksi, catatan, dibuat_pada, diperbarui_pada)
                VALUES (:id_pengguna, :id_kategori_kas, :type, :amount, :entry_date, :note, NOW(), NOW())
            ");
            $cashStmt->execute([
                'id_pengguna' => $userId,
                'id_kategori_kas' => $categoryId,
                'type' => $type,
                'amount' => $cleanAmount,
                'entry_date' => $entryDate,
                'note' => $noteText,
            ]);

            $typeLabel = ($type === 'income') ? 'Pemasukan' : 'Pengeluaran';
            set_flash('status', "{$typeLabel} kas sebesar " . format_rupiah($cleanAmount) . " berhasil dicatat!");

            // Notifikasi: pengeluaran besar
            if ($type === 'expense') {
                $kasId = (int) db()->lastInsertId();
                notif_pengeluaran_besar($cleanAmount, $kasId, $noteText);
            }
        } catch (Exception $e) {
            set_flash('error', $e->getMessage());
        }

        back();
    }

    public function quickStoreProduct(): void
    {
        if (!auth_check()) {
            json_response(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        if (!csrf_verify()) {
            json_response(['success' => false, 'message' => 'Token keamanan tidak valid.'], 400);
        }

        $userId = (int) auth_user()['id'];
        $name = trim($_POST['nama'] ?? $_POST['name'] ?? '');
        $categoryId = !empty($_POST['id_kategori'] ?? $_POST['category_id']) ? (int) ($_POST['id_kategori'] ?? $_POST['category_id']) : null;
        $unit = trim($_POST['satuan'] ?? $_POST['unit'] ?? 'pcs') ?: 'pcs';
        $buyPrice = (float) ($_POST['harga_beli'] ?? $_POST['buy_price'] ?? 0);
        $sellPrice = (float) ($_POST['harga_jual'] ?? $_POST['sell_price'] ?? 0);
        $stock = (int) ($_POST['stok'] ?? $_POST['stock'] ?? 0);

        if ($name === '') {
            json_response(['success' => false, 'message' => 'Nama barang wajib diisi.'], 422);
        }

        if ($buyPrice < 0 || $sellPrice < 0 || $stock < 0) {
            json_response(['success' => false, 'message' => 'Harga dan stok tidak boleh negatif.'], 422);
        }

        $pdo = db();
        try {
            $stmt = $pdo->prepare("
                INSERT INTO produk (id_kategori, nama, satuan, harga_beli, harga_jual, stok, status_aktif, dibuat_pada, diperbarui_pada)
                VALUES (:id_kategori, :name, :unit, :buy_price, :sell_price, :stock, 1, NOW(), NOW())
            ");
            $stmt->execute([
                'id_kategori' => $categoryId,
                'name' => $name,
                'unit' => $unit,
                'buy_price' => $buyPrice,
                'sell_price' => $sellPrice,
                'stock' => $stock,
            ]);

            $id = (int) $pdo->lastInsertId();

            json_response([
                'success' => true,
                'message' => "Barang {$name} berhasil ditambahkan!",
                'product' => [
                    'id' => $id,
                    'name' => $name,
                    'nama' => $name,
                    'unit' => $unit,
                    'satuan' => $unit,
                    'buy_price' => $buyPrice,
                    'harga_beli' => $buyPrice,
                    'sell_price' => $sellPrice,
                    'harga_jual' => $sellPrice,
                    'stock' => $stock,
                    'stok' => $stock,
                ],
            ]);
        } catch (Exception $e) {
            json_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
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

        $userId = (int) auth_user()['id'];
        $pdo = db();

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                SELECT sm.*, p.nama AS product_name, p.nama, c.nama AS customer_name, c.nama AS nama_pelanggan
                FROM mutasi_stok sm
                LEFT JOIN produk p ON p.id = sm.id_produk
                LEFT JOIN pelanggan c ON c.id = sm.id_pelanggan
                WHERE sm.id = :id AND sm.status_pembayaran = 'debt' AND sm.dihapus_pada IS NULL
                FOR UPDATE
            ");
            $stmt->execute(['id' => $movementId]);
            $movement = $stmt->fetch();

            if (!$movement) {
                throw new Exception("Data transaksi kasbon tidak ditemukan atau sudah lunas.");
            }

            $now = date('Y-m-d H:i:s');
            $up = $pdo->prepare("UPDATE mutasi_stok SET status_pembayaran = 'paid', dibayar_pada = :paid_at, diperbarui_pada = NOW() WHERE id = :id");
            $up->execute(['paid_at' => $now, 'id' => $movementId]);

            // If linked to pesanan header, update pesanan as well
            if (!empty($movement['id_pesanan'])) {
                $upPesanan = $pdo->prepare("
                    UPDATE pesanan 
                    SET status_pembayaran = 'paid', jumlah_bayar = total_harga, kembalian = 0.00, diperbarui_pada = NOW() 
                    WHERE id_pesanan = :pid
                ");
                $upPesanan->execute(['pid' => $movement['id_pesanan']]);
            }

            // Insert into catatan_kas
            $catStmt = $pdo->query("SELECT id FROM kategori_kas WHERE jenis = 'income' AND nama = 'Penjualan' LIMIT 1");
            $categoryId = $catStmt->fetchColumn();

            if (!$categoryId) {
                $categoryId = $pdo->query("SELECT id FROM kategori_kas WHERE jenis = 'income' LIMIT 1")->fetchColumn();
            }

            $quantity = (int) ($movement['jumlah'] ?? $movement['quantity'] ?? 1);
            $unitPrice = (float) ($movement['harga_satuan'] ?? $movement['unit_price'] ?? 0);
            $totalAmount = $quantity * $unitPrice;
            $customerName = $movement['customer_name'] ?? $movement['nama_pelanggan'] ?? 'Pembeli';
            $invoiceNo = $movement['no_faktur'] ?? ('TRX-' . $movementId);
            $productName = $movement['product_name'] ?? $movement['nama'] ?? 'Barang';
            $note = "Pelunasan Kasbon {$customerName} ({$invoiceNo}) - {$productName}";

            $cashStmt = $pdo->prepare("
                INSERT INTO catatan_kas (id_pengguna, id_pelanggan, id_kategori_kas, id_pesanan, id_mutasi_stok, jenis, nominal, tanggal_transaksi, catatan, dibuat_pada, diperbarui_pada)
                VALUES (:id_pengguna, :id_pelanggan, :id_kategori_kas, :id_pesanan, :id_mutasi_stok, 'income', :amount, :entry_date, :note, NOW(), NOW())
            ");
            $cashStmt->execute([
                'id_pengguna' => $userId,
                'id_pelanggan' => $movement['id_pelanggan'] ?? null,
                'id_kategori_kas' => $categoryId,
                'id_pesanan' => $movement['id_pesanan'] ?? null,
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

        $owner = get_warung_owner();
        $user = auth_user();
        $pdo = db();

        $stmt = $pdo->prepare("
            SELECT 
                sm.id AS movement_id,
                sm.id AS id_mutasi_stok,
                sm.id_pesanan,
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
            LEFT JOIN catatan_kas ce ON (ce.id_mutasi_stok = sm.id OR (sm.id_pesanan IS NOT NULL AND ce.id_pesanan = sm.id_pesanan)) AND ce.dihapus_pada IS NULL
            LEFT JOIN pengguna u ON u.id = sm.id_pengguna
            WHERE sm.id = :id AND sm.dihapus_pada IS NULL
            LIMIT 1
        ");
        $stmt->execute(['id' => $movementId]);
        $tx = $stmt->fetch();

        if (!$tx) {
            set_flash('error', 'Data detail transaksi tidak ditemukan.');
            redirect('/history');
        }

        // Check if multi-item order
        if (!empty($tx['id_pesanan'])) {
            $itemsStmt = $pdo->prepare("
                SELECT dp.*, p.nama AS nama_produk, p.satuan, p.gambar AS product_image
                FROM detail_pesanan dp
                JOIN produk p ON p.id = dp.id_produk
                WHERE dp.id_pesanan = :id AND dp.dihapus_pada IS NULL
            ");
            $itemsStmt->execute(['id' => $tx['id_pesanan']]);
            $items = $itemsStmt->fetchAll();
            if (count($items) > 1) {
                $tx['items'] = $items;
                // Calculate grand total from pesanan
                $pesanan = $pdo->query("SELECT total_harga, jumlah_bayar, kembalian FROM pesanan WHERE id_pesanan = " . (int)$tx['id_pesanan'])->fetch();
                if ($pesanan) {
                    $tx['subtotal'] = (float)$pesanan['total_harga'];
                }
            }
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

        $owner = get_warung_owner();
        $user = auth_user();
        $pdo = db();

        // Check mutasi_stok
        $stmt = $pdo->prepare("SELECT * FROM mutasi_stok WHERE id = :id AND dihapus_pada IS NULL LIMIT 1");
        $stmt->execute(['id' => $movementId]);
        $mov = $stmt->fetch();

        $pesananId = $mov['id_pesanan'] ?? null;
        if (!$pesananId && !$mov) {
            $chkPesanan = $pdo->prepare("SELECT id_pesanan FROM pesanan WHERE id_pesanan = ? LIMIT 1");
            $chkPesanan->execute([$movementId]);
            $pesananId = $chkPesanan->fetchColumn();
        }

        if ($pesananId) {
            $orderStmt = $pdo->prepare("
                SELECT 
                    o.id_pesanan,
                    o.no_faktur AS invoice_no,
                    o.tanggal_pesanan AS movement_date,
                    o.total_harga,
                    o.jumlah_bayar,
                    o.kembalian,
                    o.metode_pembayaran,
                    o.status_pembayaran AS payment_status,
                    o.catatan AS note,
                    c.nama AS customer_name,
                    c.telepon AS customer_phone,
                    u.nama AS creator_name
                FROM pesanan o
                LEFT JOIN pelanggan c ON c.id = o.id_pelanggan
                LEFT JOIN pengguna u ON u.id = o.id_pengguna
                WHERE o.id_pesanan = :id
                LIMIT 1
            ");
            $orderStmt->execute(['id' => $pesananId]);
            $order = $orderStmt->fetch();

            $itemsStmt = $pdo->prepare("
                SELECT 
                    dp.*,
                    p.nama AS nama_produk,
                    p.satuan
                FROM detail_pesanan dp
                JOIN produk p ON p.id = dp.id_produk
                WHERE dp.id_pesanan = :id AND dp.dihapus_pada IS NULL
            ");
            $itemsStmt->execute(['id' => $pesananId]);
            $items = $itemsStmt->fetchAll();

            $receipt = [
                'movement_id' => $movementId,
                'id_pesanan' => $pesananId,
                'invoice_no' => $order['invoice_no'],
                'movement_date' => $order['movement_date'],
                'payment_status' => $order['payment_status'],
                'subtotal' => (float)$order['total_harga'],
                'total_harga' => (float)$order['total_harga'],
                'jumlah_bayar' => (float)$order['jumlah_bayar'],
                'kembalian' => (float)$order['kembalian'],
                'note' => $order['note'],
                'customer_name' => $order['customer_name'],
                'customer_phone' => $order['customer_phone'],
                'items' => $items,
            ];

            view('receipt.show', [
                'receipt' => $receipt,
                'owner' => $owner,
                'user' => $user,
                'title' => 'Struk Penjualan #' . $order['invoice_no'],
            ], 'guest');
            return;
        }

        // Fallback for non-order movements (e.g. personal, damaged, adjustment)
        if ($mov) {
            $prodStmt = $pdo->prepare("SELECT nama, satuan FROM produk WHERE id = ?");
            $prodStmt->execute([$mov['id_produk']]);
            $prod = $prodStmt->fetch();

            $receipt = [
                'movement_id' => $mov['id'],
                'invoice_no' => $mov['no_faktur'],
                'movement_date' => $mov['tanggal_mutasi'] ?: $mov['dibuat_pada'],
                'payment_status' => $mov['status_pembayaran'],
                'quantity' => $mov['jumlah'],
                'unit_price' => (float)$mov['harga_satuan'],
                'subtotal' => (float)$mov['jumlah'] * (float)$mov['harga_satuan'],
                'total_harga' => (float)$mov['jumlah'] * (float)$mov['harga_satuan'],
                'jumlah_bayar' => (float)$mov['jumlah'] * (float)$mov['harga_satuan'],
                'kembalian' => 0.00,
                'product_name' => $prod['nama'] ?? 'Barang',
                'unit' => $prod['satuan'] ?? 'item',
                'note' => $mov['catatan'],
                'customer_name' => null,
                'items' => [],
            ];

            view('receipt.show', [
                'receipt' => $receipt,
                'owner' => $owner,
                'user' => $user,
                'title' => 'Bukti Pengeluaran Barang #' . $mov['no_faktur'],
            ], 'guest');
            return;
        }

        set_flash('error', 'Data struk tidak ditemukan.');
        redirect('/history');
    }
}
