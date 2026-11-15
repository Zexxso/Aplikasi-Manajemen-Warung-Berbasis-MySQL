<?php

return [
    'name' => '007_backfill_sales_to_pesanan',
    'up' => function (PDO $pdo) {
        // 1. Hapus data kulakan lama sesuai instruksi user
        $pdo->exec("DELETE FROM catatan_kas WHERE id_mutasi_stok = 6 OR (jenis = 'expense' AND catatan LIKE 'Kulakan%')");
        $pdo->exec("DELETE FROM mutasi_stok WHERE id = 6 OR (jenis = 'in' AND alasan = 'purchase')");

        // 2. Ambil semua transaksi penjualan lama di mutasi_stok
        $stmt = $pdo->query("SELECT * FROM mutasi_stok WHERE jenis = 'out' AND alasan = 'sale' ORDER BY id ASC");
        $sales = $stmt->fetchAll();

        foreach ($sales as $sale) {
            $faktur = $sale['no_faktur'] ?: ('TRX-' . date('ymd', strtotime($sale['dibuat_pada'])) . '-' . str_pad($sale['id'], 4, '0', STR_PAD_LEFT));
            $subtotal = (float)$sale['jumlah'] * (float)$sale['harga_satuan'];
            $status = $sale['status_pembayaran'] ?: 'paid';
            $jumlahBayar = ($status === 'debt') ? 0.00 : $subtotal;
            $kembalian = 0.00;
            $tgl = $sale['tanggal_mutasi'] ?: $sale['dibuat_pada'];

            // Cek apakah pesanan dengan no_faktur ini sudah pernah dibuat
            $chk = $pdo->prepare("SELECT id_pesanan FROM pesanan WHERE no_faktur = ? LIMIT 1");
            $chk->execute([$faktur]);
            $existing = $chk->fetch();

            if ($existing) {
                $idPesanan = $existing['id_pesanan'];
            } else {
                $ins = $pdo->prepare("
                    INSERT INTO pesanan (
                        no_faktur, id_pelanggan, id_pengguna, tanggal_pesanan,
                        total_harga, jumlah_bayar, kembalian, metode_pembayaran,
                        status_pembayaran, catatan, dibuat_pada, diperbarui_pada
                    ) VALUES (
                        :faktur, :pelanggan, :pengguna, :tanggal,
                        :total, :bayar, :kembalian, 'cash',
                        :status, :catatan, :dibuat, :diperbarui
                    )
                ");
                $ins->execute([
                    'faktur' => $faktur,
                    'pelanggan' => $sale['id_pelanggan'] ?: null,
                    'pengguna' => $sale['id_pengguna'] ?: 1,
                    'tanggal' => $tgl,
                    'total' => $subtotal,
                    'bayar' => $jumlahBayar,
                    'kembalian' => $kembalian,
                    'status' => $status,
                    'catatan' => $sale['catatan'],
                    'dibuat' => $sale['dibuat_pada'],
                    'diperbarui' => $sale['diperbarui_pada'] ?: $sale['dibuat_pada']
                ]);
                $idPesanan = (int)$pdo->lastInsertId();
            }

            // Masukkan ke detail_pesanan
            $insDetail = $pdo->prepare("
                INSERT INTO detail_pesanan (
                    id_pesanan, id_produk, jumlah, harga_satuan,
                    harga_modal, subtotal, dibuat_pada, diperbarui_pada
                ) VALUES (
                    :pesanan, :produk, :jumlah, :harga,
                    :modal, :subtotal, :dibuat, :diperbarui
                )
            ");
            $insDetail->execute([
                'pesanan' => $idPesanan,
                'produk' => $sale['id_produk'],
                'jumlah' => $sale['jumlah'],
                'harga' => $sale['harga_satuan'],
                'modal' => $sale['harga_modal'] ?: 0,
                'subtotal' => $subtotal,
                'dibuat' => $sale['dibuat_pada'],
                'diperbarui' => $sale['diperbarui_pada'] ?: $sale['dibuat_pada']
            ]);

            // Hubungkan mutasi_stok dan catatan_kas ke id_pesanan
            $updMutasi = $pdo->prepare("UPDATE mutasi_stok SET id_pesanan = :pesanan WHERE id = :id");
            $updMutasi->execute(['pesanan' => $idPesanan, 'id' => $sale['id']]);

            $updKas = $pdo->prepare("UPDATE catatan_kas SET id_pesanan = :pesanan WHERE id_mutasi_stok = :mutasi");
            $updKas->execute(['pesanan' => $idPesanan, 'mutasi' => $sale['id']]);
        }
    },
    'down' => function (PDO $pdo) {
        $pdo->exec("UPDATE mutasi_stok SET id_pesanan = NULL;");
        $pdo->exec("UPDATE catatan_kas SET id_pesanan = NULL;");
        $pdo->exec("DELETE FROM detail_pesanan;");
        $pdo->exec("DELETE FROM pesanan;");
    }
];
