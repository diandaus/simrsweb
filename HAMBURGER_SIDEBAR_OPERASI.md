# 🍔 Hamburger Sidebar - Input Operasi View

## 📋 Overview

Sidebar informasi pasien di halaman Input Operasi telah diubah menjadi **hamburger menu** yang dapat dibuka/tutup, memberikan lebih banyak ruang untuk konten form operasi.

---

## ✨ Fitur Hamburger Sidebar

### 1. **Toggle Sidebar**
- ✅ Klik hamburger icon (☰) untuk buka/tutup sidebar
- ✅ Klik tombol ✕ di dalam sidebar untuk menutup
- ✅ Klik overlay (area gelap) untuk menutup
- ✅ Auto-close saat switch tab (mobile)

### 2. **Posisi & Animasi**
- ✅ Sidebar slide dari kiri dengan smooth animation
- ✅ Overlay transparan muncul saat sidebar terbuka
- ✅ Transition duration: 0.3s ease-in-out

### 3. **Responsive Behavior**
- ✅ **Desktop/Tablet**: Sidebar hidden by default, bisa dibuka
- ✅ **Mobile Portrait**: Sidebar 85% width (max 320px)
- ✅ **Mobile Landscape**: Sidebar 280px width
- ✅ **Small Mobile**: Sidebar 90% width (max 280px)

---

## 🎯 UI Components

### Hamburger Button
```vue
<button class="hamburger-btn" @click="toggleSidebar">
  <span class="hamburger-icon">
    <span></span>  <!-- 3 garis horizontal -->
    <span></span>
    <span></span>
  </span>
</button>
```

**Lokasi**: Di sebelah kiri tabs navigation

**Style**:
- Width: 24px
- 3 garis horizontal (3px height)
- Color: #0891b2
- Hover: background #f1f5f9

### Sidebar Overlay
```vue
<div v-if="showSidebar" class="sidebar-overlay" @click="closeSidebar"></div>
```

**Fungsi**: 
- Block interaksi dengan konten di belakang sidebar
- Close sidebar saat diklik
- Background: rgba(0, 0, 0, 0.5)
- Z-index: 998

### Close Button (dalam Sidebar)
```vue
<button class="btn-close-sidebar" @click="closeSidebar">✕</button>
```

**Style**:
- Circular button
- Size: 32x32px
- Background: rgba(255,255,255,0.2)
- Hover: rotate 90deg

---

## 📱 Responsive Breakpoints

### Desktop (> 1024px)
```css
.patient-sidebar {
  width: 320px;
  transform: translateX(-100%); /* Hidden by default */
}

.hamburger-btn {
  display: flex; /* Visible */
}
```

### Tablet (768px - 1024px)
```css
.patient-sidebar {
  width: 280px;
  transform: translateX(-100%);
}

.hamburger-btn {
  display: flex;
}
```

### Mobile Portrait (< 768px)
```css
.patient-sidebar {
  width: 85%;
  max-width: 320px;
  height: 100vh;
  position: fixed;
}

.sidebar-overlay {
  display: block;
}
```

### Small Mobile (< 480px)
```css
.patient-sidebar {
  width: 90%;
  max-width: 280px;
}
```

### Landscape Mode
```css
.patient-sidebar {
  width: 280px;
  height: 100vh;
}
```

---

## 🔧 JavaScript Logic

### State Management
```javascript
const showSidebar = ref(false)

const toggleSidebar = () => {
  showSidebar.value = !showSidebar.value
}

const closeSidebar = () => {
  showSidebar.value = false
}
```

### Auto-Close on Tab Switch
```javascript
watch(activeTab, () => {
  if (window.innerWidth <= 768) {
    closeSidebar()
  }
})
```

**Behavior**: 
- Saat user switch tab (Tim → Laporan → Anestesi → Obat)
- Sidebar auto-close di mobile
- Tidak auto-close di desktop/tablet

---

## 🎨 Layout Comparison

### Before (Fixed Sidebar)
```
Desktop:
┌──────────┬─────────────────────────────┐
│ Sidebar  │  Tabs | Content             │
│ 320px    │                             │
│ (Fixed)  │                             │
└──────────┴─────────────────────────────┘

Mobile: Sidebar jadi header (50vh), mengurangi ruang konten
```

### After (Hamburger Menu)
```
Desktop/Tablet/Mobile:
┌────────────────────────────────────────┐
│ [☰] Tabs | Content              [✕]   │
│                                        │
│  Full Width Content Area               │
│                                        │
└────────────────────────────────────────┘

Saat Hamburger Diklik:
┌──────────┐
│ Sidebar  │ [Overlay] Content
│ Info     │
│ Pasien   │
└──────────┘
```

**Keuntungan**:
- ✅ Lebih banyak ruang untuk form
- ✅ Sidebar tetap accessible kapan saja
- ✅ UX lebih clean & modern
- ✅ Konsisten dengan mobile app pattern

---

## 🎯 User Flow

### Membuka Sidebar:
1. User klik hamburger button (☰)
2. Sidebar slide masuk dari kiri
3. Overlay muncul di belakang
4. Konten utama tetap di belakang overlay

### Menutup Sidebar:
**Cara 1**: Klik tombol ✕ di dalam sidebar
**Cara 2**: Klik area overlay
**Cara 3**: Switch ke tab lain (auto-close di mobile)

---

## 💡 Tips Penggunaan

### Untuk Petugas Ruang OK:
1. **Lihat Info Pasien**: 
   - Klik hamburger (☰) di kiri atas
   - Lihat No. RM, Nama, Dokter, dll
   - Tutup sidebar untuk fokus input data

2. **Input Data Operasi**:
   - Sidebar tertutup = full screen untuk form
   - Lebih mudah input di tablet/mobile
   - Fokus pada data entry

3. **Switch Antar Tab**:
   - Sidebar auto-close saat pindah tab
   - Tidak perlu manual close
   - Workflow lebih smooth

### Untuk Mobile Users:
- Sidebar 85-90% lebar layar
- Mudah baca info pasien
- Swipe atau klik overlay untuk tutup
- Touch-friendly (min 44x44px tap target)

---

## 🔄 State Management

```javascript
// Sidebar State
showSidebar: ref(false)

// Actions
toggleSidebar()  → Buka/Tutup sidebar
closeSidebar()   → Tutup sidebar (force)

// Auto Actions
- Switch tab (mobile) → auto closeSidebar()
- Click overlay → closeSidebar()
- Click ✕ button → closeSidebar()
```

---

## 🎨 CSS Classes

### Main Classes
```css
.patient-sidebar           → Sidebar container
.patient-sidebar.sidebar-open → Sidebar in view (translateX(0))
.sidebar-overlay          → Dark overlay
.hamburger-btn            → Toggle button
.hamburger-icon           → 3 lines icon
.btn-close-sidebar        → Close button (X)
```

### State Classes
```css
.sidebar-open             → Added when sidebar visible
transform: translateX(-100%) → Hidden state
transform: translateX(0)     → Visible state
```

---

## 📊 Performance

### Animation Performance
- ✅ Using `transform` (GPU accelerated)
- ✅ Transition duration: 0.3s
- ✅ Smooth on mobile devices
- ✅ No layout shift

### Z-Index Layers
```
999 → Sidebar
998 → Overlay
100 → Main content
```

---

## ✅ Testing Checklist

### Desktop
- [x] Hamburger button visible
- [x] Sidebar slides from left
- [x] Overlay appears
- [x] Close button works
- [x] Click overlay closes sidebar
- [x] Content remains interactive after close

### Tablet
- [x] Sidebar 280px width
- [x] Smooth animation
- [x] Touch-friendly buttons
- [x] Auto-close on tab switch

### Mobile Portrait
- [x] Sidebar 85% width (max 320px)
- [x] Full height (100vh)
- [x] Overlay blocks background
- [x] Auto-close on tab switch
- [x] Smooth slide animation

### Mobile Landscape
- [x] Sidebar 280px
- [x] Full height
- [x] Proper overlay
- [x] All interactions work

### Small Mobile
- [x] Sidebar 90% width (max 280px)
- [x] Touch targets ≥ 44px
- [x] Readable text
- [x] Smooth performance

---

## 🚀 Improvements

### What's Better:
1. ✅ **More Screen Space**: Full width untuk form
2. ✅ **Modern UX**: Hamburger menu pattern
3. ✅ **Flexible**: Sidebar on-demand
4. ✅ **Mobile-Optimized**: Better for small screens
5. ✅ **Consistent**: Similar to SOAPView pattern

### User Benefits:
- 🎯 Fokus input data lebih baik
- 📱 Mobile experience optimal
- 🖱️ Desktop experience tetap bagus
- ⚡ Workflow lebih cepat
- 👆 Touch-friendly interactions

---

## 🎉 Summary

Sidebar informasi pasien kini menjadi **hamburger menu** yang:
- ✅ Dapat dibuka/tutup dengan smooth animation
- ✅ Memberikan lebih banyak ruang untuk form operasi
- ✅ Responsive di semua device
- ✅ Auto-close pada mobile saat switch tab
- ✅ Touch-friendly & modern UX

**Hasil**: Halaman Input Operasi lebih user-friendly dan optimal untuk semua ukuran layar! 🎊

