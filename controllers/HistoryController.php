<?php

require_once __DIR__ . '/../helpers/helpers.php';

class HistoryController
{
    public function index(): void
    {
        if (!auth_check()) {
            redirect('/login');
        }

        $ownerId = warung_owner_id();
        $filter = $_GET['filter'] ?? 'all';
        if (!in_array($filter, ['all', 'stock', 'cash'])) {
            $filter = 'all';
        }

        $items = [];

        // 1. Fetch cash entries (both manual and generated from stock movements)
        if (in_array($filter, ['all', 'cash', 'stock'])) {
            $sql = "
                SELECT 
                    ce.id AS cash_id,
                    ce.id AS id_catatan_kas,
                    ce.jenis AS cash_type,
                    ce.jenis,
                    ce.nominal AS amount,
                    ce.nominal,
                    ce.tanggal_transaksi AS entry_date,
                    ce.tanggal_transaksi,
                    ce.catatan AS cash_note,
                    ce.catatan,
                    cc.nama AS category_name,
                    cc.nama AS nama_kategori,
                    sm.id AS movement_id,
                    sm.id AS id_mutasi_stok,
                    sm.no_faktur AS invoice_no,
                    sm.no_faktur,
                    sm.jenis AS movement_type,
                    sm.jenis AS jenis_mutasi,
                    sm.alasan AS movement_reason,
                    sm.alasan AS alasan_mutasi,
                    sm.status_pembayaran AS payment_status,
                    sm.status_pembayaran,
                    sm.jumlah AS quantity,
                    sm.jumlah,
                    sm.harga_satuan AS unit_price,
                    sm.harga_satuan,
                    sm.tanggal_mutasi AS movement_date,
                    sm.tanggal_mutasi,
                    p.nama AS product_name,
                    p.nama AS nama_produk,
                    p.satuan AS unit,
                    p.satuan,
                    cust.id AS customer_id,
                    cust.id AS id_pelanggan,
                    cust.kode AS customer_code,
                    cust.kode,
                    cust.nama AS customer_name,
                    cust.nama AS nama_pelanggan
                FROM catatan_kas ce
                LEFT JOIN kategori_kas cc ON cc.id = ce.id_kategori_kas
                LEFT JOIN mutasi_stok sm ON sm.id = ce.id_mutasi_stok
                LEFT JOIN produk p ON p.id = sm.id_produk
                LEFT JOIN pelanggan cust ON cust.id = COALESCE(sm.id_pelanggan, ce.id_pelanggan)
                WHERE ce.id_pengguna = :id_pengguna 
                  AND ce.dihapus_pada IS NULL
            ";

            if ($filter === 'stock') {
                $sql .= " AND ce.id_mutasi_stok IS NOT NULL";
            }

            $sql .= " ORDER BY ce.tanggal_transaksi DESC, ce.id DESC";

            $stmt = db()->prepare($sql);
            $stmt->execute(['id_pengguna' => $ownerId]);
            $cashEntries = $stmt->fetchAll();

            foreach ($cashEntries as $cash) {
                $hasMovement = !empty($cash['movement_id']);
                $hasProduct = !empty($cash['product_name']);
                $isSale = $hasMovement && ($cash['movement_type'] === 'out') && (($cash['movement_reason'] ?? 'sale') === 'sale');

                // Title
                if ($hasProduct) {
                    $title = $cash['product_name'];
                } elseif (!empty($cash['cash_note'])) {
                    $title = $cash['cash_note'];
                } else {
                    $title = $cash['category_name'] ?? 'Kas';
                }

                // Subtitle
                $timeStr = !empty($cash['entry_date']) ? format_time($cash['entry_date']) : '';
                if ($hasMovement && $hasProduct) {
                    $sign = $cash['movement_type'] === 'out' ? '-' : '+';
                    $subtitle = "{$sign}{$cash['quantity']} {$cash['unit']}" . ($timeStr ? " · {$timeStr}" : '');
                } else {
                    $subtitle = $timeStr;
                }

                $isIncome = $cash['cash_type'] === 'income';
                $amountFormatted = ($isIncome ? '+Rp ' : '-Rp ') . number_format($cash['amount'], 0, ',', '.');
                $invoiceNo = $cash['invoice_no'] ?: ($hasMovement ? generate_invoice_no((int)$cash['movement_id'], $cash['entry_date']) : null);

                $items[] = [
                    'id' => $cash['cash_id'],
                    'delete_type' => $hasMovement ? 'stock' : 'cash',
                    'delete_id' => $hasMovement ? (int) $cash['movement_id'] : (int) $cash['cash_id'],
                    'movement_id' => $hasMovement ? (int) $cash['movement_id'] : null,
                    'timestamp' => strtotime($cash['entry_date'] ?: 'now'),
                    'date_header' => format_date_id($cash['entry_date'] ?: date('Y-m-d H:i:s')),
                    'time_formatted' => $timeStr,
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'type' => $cash['cash_type'],
                    'amount' => (float) $cash['amount'],
                    'amount_formatted' => $amountFormatted,
                    'category_name' => $cash['category_name'] ?? ($isIncome ? 'Pemasukan' : 'Pengeluaran'),
                    'customer_id' => $cash['customer_id'] ?? null,
                    'customer_code' => $cash['customer_code'] ?? null,
                    'customer_name' => $cash['customer_name'] ?? null,
                    'is_sale' => $isSale,
                    'invoice_no' => $invoiceNo,
                    'payment_status' => $cash['payment_status'] ?? 'paid',
                    'payment_method' => 'cash',
                    'product_name' => $cash['product_name'] ?? null,
                    'quantity' => $cash['quantity'] !== null ? (int) $cash['quantity'] : null,
                    'unit' => $cash['unit'] ?? 'item',
                    'unit_price' => $cash['unit_price'] ? (float) $cash['unit_price'] : ($hasMovement && (int)$cash['quantity'] > 0 ? (float)$cash['amount'] / (int)$cash['quantity'] : (float)$cash['amount']),
                    'note' => $cash['cash_note'] ?? '',
                ];
            }
        }

        // 2. Fetch pure stock movements without cash entry (e.g. KASBON sales, personal use, damaged, adjustment)
        if (in_array($filter, ['all', 'stock'])) {
            $stmt = db()->prepare("
                SELECT 
                    sm.id AS movement_id,
                    sm.id AS id_mutasi_stok,
                    sm.no_faktur AS invoice_no,
                    sm.no_faktur,
                    sm.jenis AS movement_type,
                    sm.jenis AS jenis_mutasi,
                    sm.alasan AS reason,
                    sm.alasan,
                    sm.status_pembayaran AS payment_status,
                    sm.status_pembayaran,
                    sm.jumlah AS quantity,
                    sm.jumlah,
                    sm.harga_satuan AS unit_price,
                    sm.harga_satuan,
                    sm.tanggal_mutasi AS movement_date,
                    sm.tanggal_mutasi,
                    sm.catatan AS note,
                    sm.catatan,
                    p.nama AS product_name,
                    p.nama AS nama_produk,
                    p.satuan AS unit,
                    p.satuan,
                    cust.id AS customer_id,
                    cust.id AS id_pelanggan,
                    cust.kode AS customer_code,
                    cust.kode,
                    cust.nama AS customer_name,
                    cust.nama AS nama_pelanggan
                FROM mutasi_stok sm
                LEFT JOIN produk p ON p.id = sm.id_produk
                LEFT JOIN pelanggan cust ON cust.id = sm.id_pelanggan
                WHERE sm.id_pengguna = :id_pengguna 
                  AND sm.dihapus_pada IS NULL
                  AND sm.id NOT IN (
                      SELECT id_mutasi_stok FROM catatan_kas 
                      WHERE id_mutasi_stok IS NOT NULL AND dihapus_pada IS NULL
                  )
                ORDER BY sm.tanggal_mutasi DESC, sm.id DESC
            ");
            $stmt->execute(['id_pengguna' => $ownerId]);
            $pureMovements = $stmt->fetchAll();

            foreach ($pureMovements as $sm) {
                $title = $sm['product_name'] ?? 'Barang';
                $timeStr = !empty($sm['movement_date']) ? format_time($sm['movement_date']) : '';
                $sign = $sm['movement_type'] === 'out' ? '-' : '+';
                $unit = $sm['unit'] ?? 'item';
                $subtitle = "{$sign}{$sm['quantity']} {$unit}" . ($timeStr ? " · {$timeStr}" : '');
                $isKasbon = ($sm['reason'] === 'sale' && $sm['payment_status'] === 'debt');
                $invoiceNo = $sm['invoice_no'] ?: generate_invoice_no((int)$sm['movement_id'], $sm['movement_date']);

                if ($isKasbon) {
                    $custName = $sm['customer_name'] ?: 'Pembeli Kasbon';
                    $totalKasbon = (float)$sm['quantity'] * (float)$sm['unit_price'];
                    $reasonLabel = "Kasbon";
                    $amountFormatted = "Rp " . number_format($totalKasbon, 0, ',', '.');
                    $type = 'debt';
                    $subtitle = "-{$sm['quantity']} {$unit} · {$timeStr}";
                } else {
                    $reasonLabel = match ($sm['reason']) {
                        'personal' => 'Pemakaian Pribadi',
                        'damaged' => 'Barang Rusak',
                        'adjustment' => 'Penyesuaian Stok',
                        'purchase' => 'Kulakan',
                        'sale' => 'Penjualan',
                        default => 'Mutasi Stok',
                    };
                    $amountFormatted = $reasonLabel;
                    $type = 'none';
                    $totalKasbon = 0;
                }

                $items[] = [
                    'id' => $sm['movement_id'],
                    'delete_type' => 'stock',
                    'delete_id' => (int) $sm['movement_id'],
                    'movement_id' => (int) $sm['movement_id'],
                    'timestamp' => strtotime($sm['movement_date'] ?: 'now'),
                    'date_header' => format_date_id($sm['movement_date'] ?: date('Y-m-d H:i:s')),
                    'time_formatted' => $timeStr,
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'type' => $type,
                    'amount' => $totalKasbon,
                    'amount_formatted' => $amountFormatted,
                    'category_name' => $reasonLabel,
                    'customer_id' => $sm['customer_id'] ?? null,
                    'customer_code' => $sm['customer_code'] ?? null,
                    'customer_name' => $sm['customer_name'] ?? null,
                    'is_sale' => ($sm['reason'] === 'sale'),
                    'invoice_no' => $invoiceNo,
                    'payment_status' => $sm['payment_status'] ?? 'paid',
                    'payment_method' => $isKasbon ? 'debt' : 'cash',
                    'product_name' => $sm['product_name'] ?? null,
                    'quantity' => (int) $sm['quantity'],
                    'unit' => $unit,
                    'unit_price' => (float) $sm['unit_price'],
                    'note' => $sm['note'] ?? '',
                ];
            }
        }

        // 3. Sort all descending by timestamp
        usort($items, function ($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        // 4. Group by date header
        $groupedTransactions = [];
        foreach ($items as $item) {
            $groupedTransactions[$item['date_header']][] = $item;
        }

        view('history.index', [
            'groupedTransactions' => $groupedTransactions,
            'filter' => $filter,
            'title' => 'Riwayat - Warungku',
        ]);
    }

    public function destroy(string $type, int $id): void
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

            if ($type === 'stock') {
                $stmt = $pdo->prepare("SELECT * FROM mutasi_stok WHERE id = :id AND id_pengguna = :id_pengguna AND dihapus_pada IS NULL FOR UPDATE");
                $stmt->execute(['id' => $id, 'id_pengguna' => $ownerId]);
                $movement = $stmt->fetch();

                if (!$movement) {
                    throw new Exception("Data pergerakan stok tidak ditemukan atau sudah dihapus.");
                }

                $productId = $movement['id_produk'] ?? $movement['product_id'];

                // Get product
                $prodStmt = $pdo->prepare("SELECT * FROM produk WHERE id = :id AND id_pengguna = :id_pengguna FOR UPDATE");
                $prodStmt->execute(['id' => $productId, 'id_pengguna' => $ownerId]);
                $product = $prodStmt->fetch();

                if ($product) {
                    $movType = $movement['jenis'] ?? $movement['type'];
                    $movQty = (int) ($movement['jumlah'] ?? $movement['quantity']);
                    $currentStock = (int) ($product['stok'] ?? $product['stock']);
                    $prodName = $product['nama'] ?? $product['name'] ?? 'Barang';
                    $prodUnit = $product['satuan'] ?? $product['unit'] ?? 'item';

                    if ($movType === 'in') {
                        // Kulakan reversal: minus stock
                        if ($currentStock < $movQty) {
                            throw new Exception("Tidak dapat membatalkan transaksi kulakan. Stok saat ini untuk {$prodName} tersisa {$currentStock} {$prodUnit}, kurang dari {$movQty} {$prodUnit} yang ingin dibatalkan.");
                        }
                        $newStock = $currentStock - $movQty;
                    } else {
                        // Sale/out reversal: add stock back
                        $newStock = $currentStock + $movQty;
                    }

                    $upProd = $pdo->prepare("UPDATE produk SET stok = :stock, diperbarui_pada = NOW() WHERE id = :id");
                    $upProd->execute(['stock' => $newStock, 'id' => $product['id']]);
                }

                // Soft delete associated cash entry
                $delCash = $pdo->prepare("UPDATE catatan_kas SET dihapus_pada = NOW(), diperbarui_pada = NOW() WHERE id_mutasi_stok = :movement_id AND id_pengguna = :id_pengguna AND dihapus_pada IS NULL");
                $delCash->execute(['movement_id' => $id, 'id_pengguna' => $ownerId]);

                // Soft delete stock movement
                $delMov = $pdo->prepare("UPDATE mutasi_stok SET dihapus_pada = NOW(), diperbarui_pada = NOW() WHERE id = :id AND id_pengguna = :id_pengguna");
                $delMov->execute(['id' => $id, 'id_pengguna' => $ownerId]);

                $productName = $product['nama'] ?? $product['name'] ?? 'Barang';
                $pdo->commit();

                set_flash('status', "Transaksi {$productName} berhasil dihapus. Stok dan catatan kas telah dikembalikan.");
            } elseif ($type === 'cash') {
                $stmt = $pdo->prepare("SELECT * FROM catatan_kas WHERE id = :id AND id_pengguna = :id_pengguna AND dihapus_pada IS NULL FOR UPDATE");
                $stmt->execute(['id' => $id, 'id_pengguna' => $ownerId]);
                $cash = $stmt->fetch();

                if (!$cash) {
                    throw new Exception("Data kas tidak ditemukan atau sudah dihapus.");
                }

                $stockMovementId = $cash['id_mutasi_stok'] ?? $cash['stock_movement_id'] ?? null;
                if (!empty($stockMovementId)) {
                    // Call stock deletion logic
                    $pdo->rollBack();
                    $this->destroy('stock', (int) $stockMovementId);
                    return;
                }

                $delCash = $pdo->prepare("UPDATE catatan_kas SET dihapus_pada = NOW(), diperbarui_pada = NOW() WHERE id = :id AND id_pengguna = :id_pengguna");
                $delCash->execute(['id' => $id, 'id_pengguna' => $ownerId]);

                $note = ($cash['catatan'] ?? $cash['note']) ?: 'Transaksi kas';
                $pdo->commit();

                set_flash('status', "Transaksi {$note} berhasil dihapus dari catatan kas.");
            } else {
                throw new Exception("Tipe transaksi tidak valid.");
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            set_flash('error', $e->getMessage());
        }

        back();
    }
}

