<script setup>
import { ref, onMounted, computed } from 'vue'
import { 
  ShoppingCart, 
  Save, 
  Loader2, 
  ListOrdered, 
  Search, 
  Plus, 
  Minus, 
  Trash2, 
  Package, 
  RefreshCw,
  Info
} from 'lucide-vue-next'
import api from '../../axios'

const products = ref([])
const transactions = ref([])
const cart = ref([])
const searchQuery = ref('')
const loadingProducts = ref(true)
const loadingTransactions = ref(true)
const submitting = ref(false)

const fetchProducts = async () => {
  loadingProducts.value = true
  try {
    const response = await api.get('/products')
    products.value = response.data
  } catch (error) {
    console.error('Error fetching products', error)
  } finally {
    loadingProducts.value = false
  }
}

const fetchTransactions = async () => {
  loadingTransactions.value = true
  try {
    const response = await api.get('/transactions')
    transactions.value = response.data.reverse()
  } catch (error) {
    console.error('Error fetching transactions', error)
  } finally {
    loadingTransactions.value = false
  }
}

// Filter produk berdasarkan input pencarian
const filteredProducts = computed(() => {
  if (!searchQuery.value) return products.value
  const query = searchQuery.value.toLowerCase().trim()
  return products.value.filter(p => 
    p.nama_produk.toLowerCase().includes(query)
  )
})

// Menambahkan produk ke keranjang belanja
const addToCart = (product) => {
  if (product.stok < 1) {
    alert('Stok produk ini sedang habis!')
    return
  }

  const existingItem = cart.value.find(item => item.product.id === product.id)
  
  if (existingItem) {
    if (existingItem.jumlah + 1 > product.stok) {
      alert(`Stok tidak mencukupi! Maksimal pembelian untuk produk ini adalah ${product.stok} pcs.`)
      return
    }
    existingItem.jumlah++
  } else {
    cart.value.push({
      product: product,
      jumlah: 1
    })
  }
}

// Mengurangi kuantitas produk di keranjang
const decreaseQuantity = (item) => {
  if (item.jumlah > 1) {
    item.jumlah--
  } else {
    removeFromCart(item)
  }
}

// Menambah kuantitas produk di keranjang
const increaseQuantity = (item) => {
  if (item.jumlah + 1 > item.product.stok) {
    alert(`Stok tidak mencukupi! Hanya tersedia ${item.product.stok} pcs.`)
    return
  }
  item.jumlah++
}

// Menghapus item dari keranjang
const removeFromCart = (item) => {
  cart.value = cart.value.filter(i => i.product.id !== item.product.id)
}

// Mengosongkan keranjang belanja
const clearCart = () => {
  if (confirm('Apakah Anda yakin ingin mengosongkan keranjang belanja?')) {
    cart.value = []
  }
}

// Menghitung grand total belanjaan
const cartTotal = computed(() => {
  return cart.value.reduce((total, item) => total + (item.product.harga_jual * item.jumlah), 0)
})

// Menghitung total barang dalam keranjang
const cartTotalItems = computed(() => {
  return cart.value.reduce((total, item) => total + item.jumlah, 0)
})

// Menyimpan seluruh transaksi keranjang ke backend
const submitTransaction = async () => {
  if (cart.value.length === 0) return
  
  submitting.value = true
  try {
    const payload = {
      items: cart.value.map(item => ({
        product_id: item.product.id,
        jumlah: item.jumlah
      }))
    }

    await api.post('/transactions', payload)
    
    // Reset keranjang belanja dan refresh data
    cart.value = []
    await fetchProducts()
    await fetchTransactions()
    
    alert('Transaksi multi-produk berhasil disimpan!')
  } catch (error) {
    console.error('Error submitting transaction', error)
    if (error.response && error.response.status === 400) {
      alert(error.response.data.message || 'Gagal menyimpan transaksi. Pastikan stok cukup.')
    } else {
      alert('Terjadi kesalahan sistem saat memproses transaksi.')
    }
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  fetchProducts()
  fetchTransactions()
})

const formatRp = (angka) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka)
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-bold text-slate-800">Kasir POS (Point of Sale)</h2>
        <p class="text-slate-500 mt-1">Klik pada produk untuk menambahkan ke keranjang belanja.</p>
      </div>
      <div class="flex items-center gap-2">
        <button 
          @click="() => { fetchProducts(); fetchTransactions(); }"
          class="flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-all border border-slate-200 text-sm font-medium"
          :disabled="loadingProducts || loadingTransactions"
        >
          <RefreshCw class="w-4 h-4" :class="{'animate-spin': loadingProducts || loadingTransactions}" />
          <span>Refresh Data</span>
        </button>
      </div>
    </div>

    <!-- POS Workspace Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <!-- SISI KIRI: Katalog Produk (Dominan) -->
      <div class="lg:col-span-2 space-y-4">
        <!-- Pencarian Produk -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex items-center gap-3">
          <div class="relative flex-1">
            <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
            <input 
              v-model="searchQuery"
              type="text"
              placeholder="Cari nama makanan, minuman, atau produk lainnya..."
              class="w-full pl-11 pr-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all text-slate-700 bg-slate-50/50"
            />
          </div>
          <span class="text-xs text-slate-500 font-medium px-3 py-1.5 bg-slate-100 rounded-lg hidden sm:inline-block">
            {{ filteredProducts.length }} Produk
          </span>
        </div>

        <!-- Grid Katalog Produk -->
        <div v-if="loadingProducts" class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
          <Loader2 class="w-8 h-8 animate-spin text-emerald-600 mx-auto mb-3" />
          <p class="text-slate-500">Memuat katalog produk...</p>
        </div>

        <div v-else-if="filteredProducts.length === 0" class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
          <Package class="w-12 h-12 text-slate-300 mx-auto mb-3" />
          <p class="text-slate-500 font-medium">Tidak ada produk yang cocok dengan pencarian Anda.</p>
        </div>

        <div v-else class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
          <div 
            v-for="product in filteredProducts" 
            :key="product.id"
            @click="addToCart(product)"
            class="bg-white rounded-2xl border border-slate-200 p-4 hover:border-emerald-500 hover:shadow-lg hover:shadow-emerald-500/5 transition-all duration-300 cursor-pointer flex flex-col justify-between group relative overflow-hidden"
            :class="{'opacity-60 cursor-not-allowed bg-slate-50 border-dashed': product.stok < 1}"
          >
            <!-- Badge jika stok habis -->
            <div v-if="product.stok < 1" class="absolute inset-0 bg-slate-900/5 backdrop-blur-[0.5px] z-10 flex items-center justify-center">
              <span class="bg-rose-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">Habis</span>
            </div>

            <div class="space-y-3">
              <!-- Ikon Produk Premium -->
              <div 
                class="w-12 h-12 rounded-xl flex items-center justify-center transition-colors"
                :class="product.stok > 0 ? 'bg-emerald-50 text-emerald-600 group-hover:bg-emerald-500 group-hover:text-white' : 'bg-slate-100 text-slate-400'"
              >
                <Package class="w-6 h-6" />
              </div>

              <!-- Info Produk -->
              <div>
                <h4 class="font-bold text-slate-800 line-clamp-2 leading-snug group-hover:text-emerald-700 transition-colors">
                  {{ product.nama_produk }}
                </h4>
                <p class="text-sm font-semibold text-emerald-600 mt-1">
                  {{ formatRp(product.harga_jual) }}
                </p>
              </div>
            </div>

            <!-- Stok Badge -->
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
              <span class="text-slate-400">Stok</span>
              <span 
                class="font-semibold px-2 py-0.5 rounded-full"
                :class="product.stok > 10 ? 'bg-emerald-50 text-emerald-700' : product.stok > 0 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700'"
              >
                {{ product.stok }} pcs
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- SISI KANAN: Keranjang Belanja & Checkout -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden sticky top-6 flex flex-col max-h-[80vh]">
          
          <!-- Header Keranjang -->
          <div class="p-4 border-b border-slate-200 bg-emerald-50/40 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-700">
                <ShoppingCart class="w-4 h-4" />
              </div>
              <div>
                <h3 class="font-bold text-slate-800">Keranjang Belanja</h3>
                <p class="text-xs text-slate-500 font-medium">{{ cartTotalItems }} item dipilih</p>
              </div>
            </div>
            <button 
              v-if="cart.length > 0"
              @click="clearCart"
              class="text-xs font-semibold text-rose-600 hover:text-rose-700 hover:bg-rose-50 px-2 py-1.5 rounded-lg transition-all"
            >
              Kosongkan
            </button>
          </div>

          <!-- List Item Belanjaan -->
          <div class="flex-1 overflow-y-auto p-4 space-y-3 min-h-[250px]">
            <div v-if="cart.length === 0" class="h-full flex flex-col items-center justify-center text-center py-12 px-6">
              <div class="w-16 h-16 bg-slate-50 border border-slate-100 rounded-full flex items-center justify-center mb-3">
                <ShoppingCart class="w-7 h-7 text-slate-300" />
              </div>
              <p class="font-bold text-slate-700 text-sm">Keranjang Kosong</p>
              <p class="text-xs text-slate-400 mt-1 max-w-[180px] mx-auto">
                Silakan klik kartu produk di sebelah kiri untuk berbelanja.
              </p>
            </div>

            <div 
              v-else 
              v-for="item in cart" 
              :key="item.product.id"
              class="flex items-center justify-between p-3 border border-slate-100 rounded-xl bg-slate-50/50 hover:bg-slate-50 transition-all gap-2"
            >
              <!-- Info Item -->
              <div class="flex-1 min-w-0">
                <h5 class="font-bold text-slate-800 text-sm truncate">{{ item.product.nama_produk }}</h5>
                <p class="text-xs text-slate-500 mt-0.5">{{ formatRp(item.product.harga_jual) }}</p>
                <p class="text-xs font-semibold text-emerald-600 mt-1">
                  Subtotal: {{ formatRp(item.product.harga_jual * item.jumlah) }}
                </p>
              </div>

              <!-- Kontrol Jumlah & Aksi -->
              <div class="flex items-center gap-2 flex-shrink-0">
                <div class="flex items-center bg-white border border-slate-200 rounded-lg overflow-hidden p-0.5 shadow-sm">
                  <!-- Kurang kuantitas -->
                  <button 
                    @click="decreaseQuantity(item)"
                    class="p-1 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded transition-all"
                  >
                    <Minus class="w-3.5 h-3.5" />
                  </button>
                  
                  <!-- Kuantitas -->
                  <span class="w-7 text-center font-bold text-slate-700 text-sm">
                    {{ item.jumlah }}
                  </span>

                  <!-- Tambah kuantitas -->
                  <button 
                    @click="increaseQuantity(item)"
                    class="p-1 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded transition-all"
                  >
                    <Plus class="w-3.5 h-3.5" />
                  </button>
                </div>

                <!-- Tombol Hapus -->
                <button 
                  @click="removeFromCart(item)"
                  class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-all"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>

          <!-- Footer Keranjang & Checkout -->
          <div class="p-4 border-t border-slate-200 bg-slate-50 space-y-4">
            <!-- Ringkasan Harga -->
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Total Pembayaran</p>
                <p class="text-2xl font-extrabold text-emerald-600 mt-0.5">{{ formatRp(cartTotal) }}</p>
              </div>
            </div>

            <!-- Tombol Simpan Transaksi -->
            <button 
              @click="submitTransaction"
              type="button" 
              :disabled="submitting || cart.length === 0"
              class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 rounded-xl shadow-md hover:shadow-lg hover:shadow-emerald-600/30 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none"
            >
              <Loader2 v-if="submitting" class="w-5 h-5 animate-spin" />
              <Save v-else class="w-5 h-5" />
              <span>Simpan Transaksi</span>
            </button>
          </div>

        </div>
      </div>

    </div>

    <!-- Riwayat Transaksi Penjualan -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
      <div class="p-4 border-b border-slate-200 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
            <ListOrdered class="w-4 h-4" />
          </div>
          <div>
            <h3 class="font-bold text-slate-800">Riwayat Penjualan Terbaru</h3>
            <p class="text-xs text-slate-500 mt-0.5">Daftar item produk yang baru saja laku terjual.</p>
          </div>
        </div>
      </div>
      
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider">
              <th class="px-6 py-4 border-b border-slate-200">Waktu Transaksi</th>
              <th class="px-6 py-4 border-b border-slate-200">Nama Produk</th>
              <th class="px-6 py-4 border-b border-slate-200">Jumlah Beli</th>
              <th class="px-6 py-4 border-b border-slate-200 text-right">Pendapatan Bersih</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 text-slate-700 text-sm">
            <tr v-if="loadingTransactions">
              <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                <Loader2 class="w-6 h-6 animate-spin text-slate-400 mx-auto mb-2" />
                Memuat riwayat transaksi...
              </td>
            </tr>
            <tr v-else-if="transactions.length === 0">
              <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                  <ShoppingCart class="w-5 h-5 text-slate-400" />
                </div>
                Belum ada transaksi tercatat.
              </td>
            </tr>
            <tr v-for="trx in transactions.slice(0, 10)" :key="trx.id" class="hover:bg-slate-50/50 transition-colors">
              <td class="px-6 py-4 text-slate-500">{{ new Date(trx.created_at).toLocaleString('id-ID') }}</td>
              <td class="px-6 py-4 font-semibold text-slate-900">{{ trx.product ? trx.product.nama_produk : 'Produk Dihapus' }}</td>
              <td class="px-6 py-4">
                <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md text-xs font-semibold">
                  {{ trx.jumlah }} pcs
                </span>
              </td>
              <td class="px-6 py-4 text-right font-bold text-emerald-600">{{ formatRp(trx.total_harga) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="transactions.length > 10" class="p-4 border-t border-slate-200 bg-slate-50 text-center text-xs text-slate-500 flex items-center justify-center gap-1.5">
        <Info class="w-4 h-4 text-slate-400" />
        <span>Menampilkan 10 transaksi terbaru. Untuk selengkapnya lihat di menu Laporan.</span>
      </div>
    </div>
  </div>
</template>
