<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\Pelanggan;
use App\Models\Produk;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PesananController extends Controller
{
    /**
     * Menampilkan daftar semua pesanan dengan fitur pencarian dan sorting.
     * Ini adalah method untuk halaman utama "Manajemen Pesanan".
     */
    public function index(Request $request)
    {
        $query = Pesanan::with('pelanggan')->latest();

        // Implementasi Searching/Filtering
        if ($request->filled('search')) {
            $query->where('kode_pesanan', 'like', '%' . $request->search . '%')
                  ->orWhereHas('pelanggan', fn($q) => $q->where('nama', 'like', '%' . $request->search . '%'));
        }

        // Implementasi Sorting
        if ($request->filled(['sort_by', 'sort_direction'])) {
            $query->orderBy($request->sort_by, $request->sort_direction);
        }

        $pesanan = $query->paginate(10)->withQueryString();

        // Me-render komponen Vue 'Pesanan/Index.vue' dan mengirimkan data pesanan & filter
        return Inertia::render('Pesanan/Index', [
            'pesanan' => $pesanan,
            'filters' => $request->only(['search', 'sort_by', 'sort_direction'])
        ]);
    }

    /**
     * Menampilkan form untuk membuat pesanan baru.
     */
    public function create()
    {
        // Mengirimkan data master yang dibutuhkan untuk form (misal: daftar pelanggan dan produk)
        return Inertia::render('Pesanan/Create', [
            'pelanggan' => Pelanggan::all(['id', 'nama']),
            'produk' => Produk::where('dapat_dipesan', true)->get(['id', 'nama_produk']),
        ]);
    }

    /**
     * Menyimpan data pesanan baru ke dalam database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pelanggan_id' => 'required|uuid|exists:pelanggan,id',
            'tanggal_pesanan' => 'required|date',
            'catatan' => 'nullable|string',
            'detail_pesanan' => 'required|array|min:1',
            'detail_pesanan.*.produk_id' => 'required|uuid|exists:produk,id',
            'detail_pesanan.*.jumlah' => 'required|integer|min:1',
            'lampiran' => 'nullable|file|mimes:pdf|between:100,500', // Validasi file
        ]);

        // Menggunakan transaction untuk memastikan semua query berhasil atau tidak sama sekali
        DB::transaction(function () use ($validated, $request) {
            $pesanan = Pesanan::create([
                'kode_pesanan' => 'PO-' . time(), // Contoh kode unik sederhana
                'pelanggan_id' => $validated['pelanggan_id'],
                'user_id' => auth()->id(), // Mengambil ID user yang sedang login
                'tanggal_pesanan' => $validated['tanggal_pesanan'],
                'total_harga' => 0, // Akan di-update nanti
                'catatan' => $validated['catatan'],
            ]);

            // ... Logika untuk menyimpan detail_pesanan dan menghitung total harga ...

            // Logika untuk upload file
            if ($request->hasFile('lampiran')) {
                $file = $request->file('lampiran');
                $path = $file->store('lampiran_pesanan', 'public');
                $pesanan->lampiran()->create([
                    'id' => Str::uuid(),
                    'nama_file' => $file->getClientOriginalName(),
                    'path_file' => $path,
                    'ukuran_file' => $file->getSize() / 1024 // Ukuran dalam KB
                ]);
            }
        });

        return redirect()->route('pesanan.index')->with('success', 'Pesanan berhasil dibuat.');
    }

    /**
     * Menampilkan detail spesifik dari sebuah pesanan, termasuk audit trailnya.
     */
    public function show(Pesanan $pesanan)
    {
        // Eager load relasi yang dibutuhkan, termasuk 'audits.user' untuk menampilkan siapa yang melakukan aksi
        $pesanan->load('pelanggan', 'detailPesanan.produk', 'lampiran', 'audits.user');

        return Inertia::render('Pesanan/Show', [
            'pesanan' => $pesanan
        ]);
    }

    /**
     * Menampilkan form untuk mengedit pesanan yang sudah ada.
     */
    public function edit(Pesanan $pesanan)
    {
        $pesanan->load('detailPesanan');
        return Inertia::render('Pesanan/Edit', [
            'pesanan' => $pesanan,
            'pelanggan' => Pelanggan::all(['id', 'nama']),
            'produk' => Produk::where('dapat_dipesan', true)->get(['id', 'nama_produk']),
        ]);
    }

    /**
     * Mengupdate data pesanan yang ada di database.
     */
    public function update(Request $request, Pesanan $pesanan)
    {
        // ... Logika validasi dan update ...
        // Mirip dengan store, tapi menggunakan $pesanan->update()

        return redirect()->route('pesanan.index')->with('success', 'Pesanan berhasil diupdate.');
    }

    /**
     * Menghapus pesanan dari database (Soft Delete).
     */
    public function destroy(Pesanan $pesanan)
    {
        $pesanan->delete(); // Ini akan melakukan soft delete karena ada trait SoftDeletes di model
        return redirect()->route('pesanan.index')->with('success', 'Pesanan berhasil dihapus.');
    }
}