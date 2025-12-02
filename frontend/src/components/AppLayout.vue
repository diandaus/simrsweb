<template>
  <div class="app-wrapper">
    <!-- Mobile Sidebar Overlay -->
    <div class="sidebar-overlay" :class="{ show: showMobileSidebar }" @click="closeMobileSidebar"></div>

    <!-- Sidebar -->
    <aside class="sidebar" :class="{ collapsed: sidebarCollapsed, 'show-mobile': showMobileSidebar }">
      <div class="sidebar-header">
        <div class="logo">
          <div class="logo-icon">
            <img v-if="settings.customLogo" :src="settings.customLogo" alt="Logo" class="custom-logo-img">
            <img v-else-if="defaultLogo" :src="defaultLogo" alt="Logo" class="custom-logo-img" @error="handleLogoError">
            <span v-else>🏥</span>
          </div>
          <transition name="fade">
            <div v-if="!sidebarCollapsed" class="logo-text">
              <div class="hospital-name">SIMRS</div>
              <small class="hospital-id">ID: {{ user?.nip || 'Admin' }}</small>
            </div>
          </transition>
        </div>
      </div>

      <nav class="sidebar-nav">
        <template v-for="item in menuItems" :key="item.path || item.label">
          <!-- Menu item with submenu -->
          <div v-if="item.submenu" class="nav-item-wrapper">
            <div
              class="nav-item"
              :class="{ active: isActiveParent(item.submenu), 'has-submenu': true, expanded: expandedMenus.includes(item.label) }"
              @click="toggleSubmenu(item.label)">
              <span class="nav-icon">
                <img v-if="item.icon && item.icon.startsWith('/')" :src="item.icon" :alt="item.label" class="menu-icon-img">
                <span v-else>{{ item.icon }}</span>
              </span>
              <transition name="fade">
                <span v-if="!sidebarCollapsed" class="nav-text">{{ item.label }}</span>
              </transition>
              <transition name="fade">
                <span v-if="!sidebarCollapsed" class="submenu-arrow">{{ expandedMenus.includes(item.label) ? '▼' : '▶' }}</span>
              </transition>
            </div>
            <!-- Submenu items -->
            <transition name="submenu">
              <div v-if="expandedMenus.includes(item.label) && !sidebarCollapsed" class="submenu">
                <router-link
                  v-for="subitem in item.submenu"
                  :key="subitem.path"
                  :to="subitem.path"
                  class="submenu-item"
                  :class="{ active: isActive(subitem.path) }">
                  <span class="submenu-dot">•</span>
                  <span class="submenu-text">{{ subitem.label }}</span>
                </router-link>
              </div>
            </transition>
          </div>

          <!-- Regular menu item without submenu -->
          <router-link
            v-else
            :to="item.path"
            class="nav-item"
            :class="{ active: isActive(item.path) }"
            @click="closeAllSubmenus">
            <span class="nav-icon">
              <img v-if="item.icon && item.icon.startsWith('/')" :src="item.icon" :alt="item.label" class="menu-icon-img">
              <span v-else>{{ item.icon }}</span>
            </span>
            <transition name="fade">
              <span v-if="!sidebarCollapsed" class="nav-text">{{ item.label }}</span>
            </transition>
          </router-link>
        </template>
      </nav>

      <div class="sidebar-footer">
        <button 
          v-if="user?.role === 'admin'"
          class="settings-btn" 
          @click="openSettings" 
          :title="sidebarCollapsed ? 'Pengaturan' : ''">
          <span>⚙️</span>
          <transition name="fade">
            <span v-if="!sidebarCollapsed" class="settings-text">Setting</span>
          </transition>
        </button>
        <button class="toggle-btn" @click="toggleSidebar">
          <span>{{ sidebarCollapsed ? '→' : '←' }}</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="main-wrapper">
      <!-- Navbar -->
      <header class="navbar">
        <div class="navbar-left">
          <button class="hamburger-btn" @click="toggleMobileSidebar" title="Menu">
            <span class="hamburger-icon">
              <span></span>
              <span></span>
              <span></span>
            </span>
          </button>
          <div v-if="user?.role === 'admin'" class="search-box">
            <input
              type="text"
              placeholder="Cari fitur disini..."
              class="search-input"
            />
            <span class="search-icon">🔍</span>
          </div>
        </div>

        <div class="navbar-right">
          <div class="user-menu">
            <div class="user-avatar" @click="openAvatarUpload" style="cursor: pointer; position: relative;" title="Klik untuk upload foto">
              <img 
                v-if="user?.foto_profil" 
                :src="user.foto_profil" 
                alt="Avatar" 
                class="avatar-img"
                @error="handleAvatarError"
                @load="handleAvatarLoad"
              >
              <span v-else>{{ user?.nama?.charAt(0) || 'U' }}</span>
              <div class="avatar-upload-icon">📷</div>
            </div>
            <input
              ref="avatarInput"
              type="file"
              accept="image/*"
              style="display: none;"
              @change="handleAvatarUpload"
            >
            <div class="user-info">
              <div class="user-name">{{ user?.nama || 'User' }}</div>
              <small class="user-role">{{ user?.nip || 'Staff' }}</small>
            </div>
            <button class="logout-btn" @click="logout" title="Logout">
              <span><img src="/images/log-out.png" style="width: 35px; height: 35px; object-fit: contain;"></span>
            </button>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="page-content">
        <slot></slot>
      </main>
    </div>

    <!-- Modal Tambah Pasien -->
    <div class="modal fade" :class="{ show: showModal, 'd-block': showModal }" tabindex="-1" v-if="showModal">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">➕ Tambah Pasien Baru</h5>
            <button type="button" class="btn-close btn-close-white" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitPasien">
              <div class="row g-3">
                <!-- No. Rekam Medis -->
                <div class="col-md-6">
                  <label class="form-label">No. Rekam Medis <span class="text-danger">*</span></label>
                  <input v-model="form.no_rkm_medis" type="text" class="form-control" required>
                </div>

                <!-- Nama Pasien -->
                <div class="col-md-6">
                  <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                  <input v-model="form.nm_pasien" type="text" class="form-control" required>
                </div>

                <!-- No. KTP -->
                <div class="col-md-6">
                  <label class="form-label">No. KTP</label>
                  <input v-model="form.no_ktp" type="text" class="form-control" maxlength="16">
                </div>

                <!-- No. BPJS -->
                <div class="col-md-6">
                  <label class="form-label">No. BPJS</label>
                  <input v-model="form.no_peserta" type="text" class="form-control">
                </div>

                <!-- Jenis Kelamin -->
                <div class="col-md-6">
                  <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                  <select v-model="form.jk" class="form-select" required>
                    <option value="">Pilih...</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                  </select>
                </div>

                <!-- Tempat Lahir -->
                <div class="col-md-6">
                  <label class="form-label">Tempat Lahir</label>
                  <input v-model="form.tmp_lahir" type="text" class="form-control">
                </div>

                <!-- Tanggal Lahir -->
                <div class="col-md-6">
                  <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                  <input v-model="form.tgl_lahir" type="date" class="form-control" required>
                </div>

                <!-- Golongan Darah -->
                <div class="col-md-6">
                  <label class="form-label">Golongan Darah</label>
                  <select v-model="form.gol_darah" class="form-select">
                    <option value="">Pilih...</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="AB">AB</option>
                    <option value="O">O</option>
                  </select>
                </div>

                <!-- Agama -->
                <div class="col-md-6">
                  <label class="form-label">Agama</label>
                  <select v-model="form.agama" class="form-select">
                    <option value="">Pilih...</option>
                    <option value="Islam">Islam</option>
                    <option value="Kristen">Kristen</option>
                    <option value="Katholik">Katholik</option>
                    <option value="Hindu">Hindu</option>
                    <option value="Budha">Budha</option>
                    <option value="Konghucu">Konghucu</option>
                  </select>
                </div>

                <!-- Status Nikah -->
                <div class="col-md-6">
                  <label class="form-label">Status Nikah</label>
                  <select v-model="form.stts_nikah" class="form-select">
                    <option value="">Pilih...</option>
                    <option value="BELUM MENIKAH">Belum Menikah</option>
                    <option value="MENIKAH">Menikah</option>
                    <option value="CERAI">Cerai</option>
                    <option value="JANDA">Janda</option>
                    <option value="DUDHA">Duda</option>
                  </select>
                </div>

                <!-- Pendidikan -->
                <div class="col-md-6">
                  <label class="form-label">Pendidikan</label>
                  <select v-model="form.pnd" class="form-select">
                    <option value="">Pilih...</option>
                    <option value="SD">SD</option>
                    <option value="SMP">SMP</option>
                    <option value="SMA">SMA</option>
                    <option value="D1">D1</option>
                    <option value="D2">D2</option>
                    <option value="D3">D3</option>
                    <option value="D4">D4</option>
                    <option value="S1">S1</option>
                    <option value="S2">S2</option>
                    <option value="S3">S3</option>
                  </select>
                </div>

                <!-- Pekerjaan -->
                <div class="col-md-6">
                  <label class="form-label">Pekerjaan</label>
                  <input v-model="form.pekerjaan" type="text" class="form-control">
                </div>

                <!-- Alamat -->
                <div class="col-12">
                  <label class="form-label">Alamat</label>
                  <textarea v-model="form.alamat" class="form-control" rows="2"></textarea>
                </div>

                <!-- No. Telepon -->
                <div class="col-md-6">
                  <label class="form-label">No. Telepon</label>
                  <input v-model="form.no_tlp" type="text" class="form-control">
                </div>
              </div>

              <div v-if="formError" class="alert alert-danger mt-3">{{ formError }}</div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
            <button type="button" class="btn btn-primary" @click="submitPasien" :disabled="formLoading">
              <span v-if="formLoading" class="spinner-border spinner-border-sm me-2"></span>
              Simpan
            </button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade" :class="{ show: showModal }" v-if="showModal"></div>

    <!-- Modal Pengaturan -->
    <div class="modal fade" :class="{ show: showSettingsModal, 'd-block': showSettingsModal }" tabindex="-1" v-if="showSettingsModal">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">⚙️ Pengaturan Aplikasi</h5>
            <button type="button" class="btn-close btn-close-white" @click="closeSettingsModal"></button>
          </div>
          <div class="modal-body">
            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs mb-4" role="tablist">
              <li class="nav-item" role="presentation">
                <button
                  class="nav-link"
                  :class="{ active: settingsTab === 'appearance' }"
                  @click="settingsTab = 'appearance'"
                  type="button">
                  🎨 Tampilan
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button
                  class="nav-link"
                  :class="{ active: settingsTab === 'users' }"
                  @click="settingsTab = 'users'"
                  type="button">
                  👥 Manajemen User
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button
                  class="nav-link"
                  :class="{ active: settingsTab === 'permissions' }"
                  @click="settingsTab = 'permissions'"
                  type="button">
                  🔐 Hak Akses Menu
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button
                  class="nav-link"
                  :class="{ active: settingsTab === 'info' }"
                  @click="settingsTab = 'info'"
                  type="button">
                  ℹ️ Informasi
                </button>
              </li>
            </ul>

            <!-- Tab: Tampilan -->
            <div v-show="settingsTab === 'appearance'">
              <div class="settings-section">
              <h6 class="settings-title">🎨 Tampilan</h6>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Tema Warna Sidebar</label>
                  <select v-model="settings.sidebarTheme" class="form-select" @change="applySidebarTheme">
                    <option value="purple">Ungu (Default)</option>
                    <option value="cyan">Cyan Tosca</option>
                    <option value="blue">Biru</option>
                    <option value="green">Hijau</option>
                    <option value="orange">Orange</option>
                    <option value="red">Merah</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Ukuran Font</label>
                  <select v-model="settings.fontSize" class="form-select">
                    <option value="small">Kecil</option>
                    <option value="medium">Sedang (Default)</option>
                    <option value="large">Besar</option>
                  </select>
                </div>
                <div class="col-12">
                  <label class="form-label">Logo Aplikasi</label>
                  <div class="logo-upload-section">
                    <div class="current-logo">
                      <div class="logo-preview">
                        <img v-if="settings.customLogo" :src="settings.customLogo" alt="Logo" class="preview-img">
                        <img v-else-if="defaultLogo" :src="defaultLogo" alt="Logo Default" class="preview-img">
                        <div v-else class="default-logo">🏥</div>
                      </div>
                      <div class="logo-info">
                        <small class="text-muted">
                          {{ settings.customLogo ? 'Logo Custom (Upload)' : (defaultLogo ? 'Logo Default (icon.png)' : 'Logo Default (Emoji)') }}
                        </small>
                        <small v-if="!settings.customLogo && defaultLogo" class="text-muted" style="font-size: 0.75rem;">
                          📁 /public/icons/icon.png
                        </small>
                      </div>
                    </div>
                    <div class="logo-actions">
                      <input
                        ref="logoInput"
                        type="file"
                        accept="image/png,image/jpeg,image/jpg,image/svg+xml"
                        @change="handleLogoUpload"
                        style="display: none">
                      <button type="button" class="btn btn-sm btn-primary" @click="$refs.logoInput.click()">
                        📁 Pilih Logo
                      </button>
                      <button
                        v-if="settings.customLogo"
                        type="button"
                        class="btn btn-sm btn-danger"
                        @click="removeLogo">
                        🗑️ Hapus Logo
                      </button>
                    </div>
                  </div>
                  <small class="text-muted d-block mb-1">
                    📤 <strong>Upload:</strong> Format PNG, JPG, SVG (Maks. 2MB). Ukuran rekomendasi: 200x200px
                  </small>
                  <small class="text-muted d-block">
                    📁 <strong>File Default:</strong> Letakkan file <code>icon.png</code> di folder <code>/public/icons/</code>
                  </small>
                </div>
              </div>
            </div>

              <hr>

              <div class="settings-section">
                <h6 class="settings-title">🔔 Notifikasi</h6>
                <div class="form-check form-switch mb-3">
                  <input v-model="settings.enableNotifications" class="form-check-input" type="checkbox" id="enableNotif">
                  <label class="form-check-label" for="enableNotif">
                    Aktifkan Notifikasi
                  </label>
                </div>
                <div class="form-check form-switch mb-3">
                  <input v-model="settings.soundEnabled" class="form-check-input" type="checkbox" id="enableSound">
                  <label class="form-check-label" for="enableSound">
                    Suara Notifikasi
                  </label>
                </div>
              </div>
            </div>

            <!-- Tab: Manajemen User -->
            <div v-show="settingsTab === 'users'">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="settings-title mb-0">👥 Daftar User Admin</h6>
                <button class="btn btn-sm btn-primary" @click="showAddUserModal">
                  ➕ Tambah User
                </button>
              </div>

              <div class="table-responsive">
                <table class="table table-hover">
                  <thead class="table-light">
                    <tr>
                      <th width="80">Avatar</th>
                      <th>Username</th>
                      <th>Nama</th>
                      <th>NIP</th>
                      <th>Role</th>
                      <th>Status</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-if="loadingUsers">
                      <td colspan="7" class="text-center py-4">
                        <div class="spinner-border spinner-border-sm me-2"></div>
                        Memuat data...
                      </td>
                    </tr>
                    <tr v-else-if="userList.length === 0">
                      <td colspan="7" class="text-center py-4 text-muted">
                        Tidak ada data user
                      </td>
                    </tr>
                    <tr v-else v-for="userItem in userList" :key="userItem.id">
                      <td>
                        <div class="user-avatar-table">
                          <img v-if="userItem.foto_profil" :src="userItem.foto_profil" :alt="userItem.nama" class="avatar-img-table">
                          <span v-else class="avatar-initial">{{ (userItem.nama || userItem.username || 'U').charAt(0).toUpperCase() }}</span>
                        </div>
                      </td>
                      <td>{{ userItem.username }}</td>
                      <td>{{ userItem.nama || '-' }}</td>
                      <td>{{ userItem.nip || '-' }}</td>
                      <td>
                        <span class="badge" :class="userItem.role === 'admin' ? 'bg-primary' : 'bg-secondary'">
                          {{ userItem.role }}
                        </span>
                      </td>
                      <td>
                        <span class="badge" :class="userItem.status === 'active' ? 'bg-success' : 'bg-danger'">
                          {{ userItem.status }}
                        </span>
                      </td>
                      <td>
                        <button class="btn btn-sm btn-warning me-1" @click="editUser(userItem)" title="Edit">
                          ✏️
                        </button>
                        <button class="btn btn-sm btn-danger" @click="deleteUser(userItem.id)" title="Hapus">
                          🗑️
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Tab: Hak Akses Menu -->
            <div v-show="settingsTab === 'permissions'">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="settings-title mb-0">🔐 Hak Akses Menu User</h6>
              </div>

              <div class="table-responsive">
                <table class="table table-hover">
                  <thead class="table-light">
                    <tr>
                      <th width="20%">Username</th>
                      <th width="20%">Nama</th>
                      <th width="10%">Role</th>
                      <th width="40%">Akses Menu</th>
                      <th width="10%">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-if="loadingUsers">
                      <td colspan="5" class="text-center py-4">
                        <div class="spinner-border spinner-border-sm me-2"></div>
                        Memuat data...
                      </td>
                    </tr>
                    <tr v-else-if="userList.length === 0">
                      <td colspan="5" class="text-center py-4 text-muted">
                        Tidak ada data user
                      </td>
                    </tr>
                    <tr v-else v-for="userItem in userList" :key="userItem.id">
                      <td>{{ userItem.username }}</td>
                      <td>{{ userItem.nama || '-' }}</td>
                      <td>
                        <span class="badge" :class="userItem.role === 'admin' ? 'bg-primary' : 'bg-secondary'">
                          {{ userItem.role }}
                        </span>
                      </td>
                      <td>
                        <div v-if="userItem.role === 'admin'" class="text-muted fst-italic">
                          Admin memiliki akses ke semua menu
                        </div>
                        <div v-else-if="!userItem.menu_permissions || userItem.menu_permissions.length === 0" class="text-warning">
                          Belum ada akses menu
                        </div>
                        <div v-else class="d-flex flex-wrap gap-1">
                          <span
                            v-for="menu in userItem.menu_permissions"
                            :key="menu"
                            class="badge bg-info">
                            {{ getMenuLabel(menu) }}
                          </span>
                        </div>
                      </td>
                      <td>
                        <button
                          class="btn btn-sm btn-primary"
                          @click="editUserPermissions(userItem)"
                          title="Kelola Hak Akses">
                          🔐 Kelola
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Tab: Informasi -->
            <div v-show="settingsTab === 'info'">
              <div class="settings-section">
                <h6 class="settings-title">ℹ️ Informasi Aplikasi</h6>
                <table class="table table-sm">
                  <tr>
                    <td width="150"><strong>Versi Aplikasi</strong></td>
                    <td>v1.0.0</td>
                  </tr>
                  <tr>
                    <td><strong>Nama Aplikasi</strong></td>
                    <td>SIMRS Khanza</td>
                  </tr>
                  <tr>
                    <td><strong>Pengguna</strong></td>
                    <td>{{ user?.nama || 'Admin' }}</td>
                  </tr>
                  <tr>
                    <td><strong>NIP</strong></td>
                    <td>{{ user?.nip || '-' }}</td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="resetSettings">Reset Default</button>
            <button type="button" class="btn btn-primary" @click="saveSettings">Simpan Pengaturan</button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade" :class="{ show: showSettingsModal }" v-if="showSettingsModal"></div>

    <!-- Modal Add/Edit User -->
    <div class="modal fade" :class="{ show: showUserModal, 'd-block': showUserModal }" tabindex="-1" v-if="showUserModal" style="z-index: 1060;">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">{{ editingUser ? '✏️ Edit User' : '➕ Tambah User Baru' }}</h5>
            <button type="button" class="btn-close btn-close-white" @click="closeUserModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveUser">
              <!-- Search Pegawai -->
              <div class="mb-3" v-if="!editingUser">
                <label class="form-label">🔍 Pilih Pegawai</label>
                <div class="position-relative">
                  <input
                    v-model="pegawaiSearch"
                    type="text"
                    class="form-control"
                    placeholder="Klik atau ketik untuk mencari pegawai..."
                    @input="filterPegawai"
                    @focus="openPegawaiDropdown"
                    @click="openPegawaiDropdown">
                  <div
                    v-if="showPegawaiDropdown"
                    class="pegawai-dropdown">
                    <div v-if="loadingPegawai" class="text-center py-3">
                      <div class="spinner-border spinner-border-sm me-2"></div>
                      Memuat data pegawai...
                    </div>
                    <div v-else-if="filteredPegawaiList.length === 0" class="text-center py-3 text-muted">
                      Tidak ada data pegawai
                    </div>
                    <div
                      v-else
                      v-for="pegawai in filteredPegawaiList"
                      :key="pegawai.nik"
                      class="pegawai-item"
                      @click="selectPegawai(pegawai)">
                      <div class="pegawai-name">{{ pegawai.nama }}</div>
                      <small class="text-muted">NIK: {{ pegawai.nik }}</small>
                    </div>
                  </div>
                  <div class="dropdown-arrow">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                      <path d="M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z"/>
                    </svg>
                  </div>
                </div>
                <small class="text-muted">Pilih pegawai untuk mengisi data otomatis ({{ allPegawaiList.length }} pegawai)</small>
              </div>

              <hr v-if="!editingUser">

              <div class="mb-3">
                <label class="form-label">Username <span class="text-danger">*</span></label>
                <input
                  v-model="userForm.username"
                  type="text"
                  class="form-control"
                  :disabled="editingUser"
                  required
                  placeholder="Contoh: admin, spv, user01">
                <small class="text-muted">Username digunakan untuk login</small>
              </div>

              <div class="mb-3">
                <label class="form-label">Password <span class="text-danger" v-if="!editingUser">*</span></label>
                <input
                  v-model="userForm.password"
                  type="password"
                  class="form-control"
                  :required="!editingUser"
                  placeholder="Masukkan password">
                <small class="text-muted" v-if="editingUser">Kosongkan jika tidak ingin mengubah password</small>
              </div>

              <div class="mb-3">
                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                <input
                  v-model="userForm.nama"
                  type="text"
                  class="form-control"
                  required
                  :readonly="pegawaiSelected"
                  placeholder="Masukkan nama lengkap">
              </div>

              <div class="mb-3">
                <label class="form-label">NIP Pegawai</label>
                <input
                  v-model="userForm.nip"
                  type="text"
                  class="form-control"
                  :readonly="pegawaiSelected"
                  placeholder="Masukkan NIP (opsional)">
              </div>

              <div class="mb-3">
                <label class="form-label">Email</label>
                <input
                  v-model="userForm.email"
                  type="email"
                  class="form-control"
                  placeholder="Masukkan email (opsional)">
              </div>

              <div class="mb-3">
                <label class="form-label">No. Telepon</label>
                <input
                  v-model="userForm.no_telp"
                  type="text"
                  class="form-control"
                  :readonly="pegawaiSelected"
                  placeholder="Masukkan no. telepon (opsional)">
              </div>

              <div class="mb-3">
                <label class="form-label">Role</label>
                <select v-model="userForm.role" class="form-select">
                  <option value="user">User</option>
                  <option value="admin">Admin</option>
                </select>
              </div>

              <div v-if="userFormError" class="alert alert-danger">{{ userFormError }}</div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeUserModal">Batal</button>
            <button type="button" class="btn btn-primary" @click="saveUser" :disabled="userFormLoading">
              <span v-if="userFormLoading" class="spinner-border spinner-border-sm me-2"></span>
              {{ editingUser ? 'Update' : 'Simpan' }}
            </button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade" :class="{ show: showUserModal }" v-if="showUserModal" style="z-index: 1055;"></div>

    <!-- Modal Kelola Hak Akses Menu -->
    <div class="modal fade" :class="{ show: showPermissionsModal, 'd-block': showPermissionsModal }" tabindex="-1" v-if="showPermissionsModal" style="z-index: 1060;">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">🔐 Kelola Hak Akses Menu - {{ selectedUserForPermissions?.nama }}</h5>
            <button type="button" class="btn-close btn-close-white" @click="closePermissionsModal"></button>
          </div>
          <div class="modal-body">
            <div v-if="selectedUserForPermissions?.role === 'admin'" class="alert alert-info">
              <i class="bi bi-info-circle"></i> User dengan role <strong>Admin</strong> memiliki akses ke semua menu secara otomatis.
            </div>
            <div v-else>
              <p class="text-muted mb-3">Pilih menu yang dapat diakses oleh user <strong>{{ selectedUserForPermissions?.username }}</strong></p>
              
              <div class="permissions-grid">
                <div v-for="menuItem in availableMenus" :key="menuItem.key" class="permission-card-wrapper">
                  <!-- Main Menu Card -->
                  <div
                    class="permission-card"
                    :class="{ selected: isMenuSelected(menuItem.key) }"
                    @click="toggleMenuPermission(menuItem.key)">
                    <div class="permission-icon">{{ menuItem.icon }}</div>
                    <div class="permission-label">{{ menuItem.label }}</div>
                    <div class="permission-checkbox">
                      <input
                        type="checkbox"
                        :checked="isMenuSelected(menuItem.key)"
                        @click.stop="toggleMenuPermission(menuItem.key)">
                    </div>
                  </div>

                  <!-- Submenu Cards (if has submenu) -->
                  <div v-if="menuItem.hasSubmenu" class="submenu-cards">
                    <div
                      v-for="submenu in menuItem.submenus"
                      :key="`${menuItem.key}-${submenu.key}`"
                      class="permission-card submenu"
                      :class="{ selected: isSubmenuSelected(menuItem.key, submenu.key) }"
                      @click="toggleSubmenuPermission(menuItem.key, submenu.key)">
                      <div class="submenu-indicator">└─</div>
                      <div class="permission-label">{{ submenu.label }}</div>
                      <div class="permission-checkbox">
                        <input
                          type="checkbox"
                          :checked="isSubmenuSelected(menuItem.key, submenu.key)"
                          @click.stop="toggleSubmenuPermission(menuItem.key, submenu.key)">
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-3">
                <small class="text-muted">
                  <strong>Catatan:</strong> Menu dengan submenu (Rawat Jalan, Rawat Inap, dll) akan memberikan akses ke semua submenu di dalamnya.
                </small>
              </div>

              <div v-if="permissionsFormError" class="alert alert-danger mt-3">{{ permissionsFormError }}</div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closePermissionsModal">Batal</button>
            <button
              v-if="selectedUserForPermissions?.role !== 'admin'"
              type="button"
              class="btn btn-primary"
              @click="savePermissions"
              :disabled="permissionsFormLoading">
              <span v-if="permissionsFormLoading" class="spinner-border spinner-border-sm me-2"></span>
              Simpan Hak Akses
            </button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade" :class="{ show: showPermissionsModal }" v-if="showPermissionsModal" style="z-index: 1055;"></div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import apiClient from '@/api/axios'
import Swal from 'sweetalert2'

const router = useRouter()
const route = useRoute()
const sidebarCollapsed = ref(false)
const showMobileSidebar = ref(false)
const showModal = ref(false)
const showSettingsModal = ref(false)
const formLoading = ref(false)
const formError = ref('')
const expandedMenus = ref([]) // No default expanded
const form = ref({
  no_rkm_medis: '',
  nm_pasien: '',
  no_ktp: '',
  no_peserta: '',
  jk: '',
  tmp_lahir: '',
  tgl_lahir: '',
  gol_darah: '',
  agama: '',
  stts_nikah: '',
  pnd: '',
  pekerjaan: '',
  alamat: '',
  no_tlp: ''
})

const settings = ref({
  sidebarTheme: 'purple',
  fontSize: 'medium',
  enableNotifications: true,
  soundEnabled: true,
  customLogo: ''
})

const logoInput = ref(null)
const defaultLogo = ref('/icons/icon.png')
const logoError = ref(false)

// User Management
const settingsTab = ref('appearance')
const showUserModal = ref(false)
const userList = ref([])
const loadingUsers = ref(false)
const editingUser = ref(null)
const userForm = ref({
  username: '',
  password: '',
  nama: '',
  nip: '',
  email: '',
  no_telp: '',
  role: 'user'
})
const userFormLoading = ref(false)
const userFormError = ref('')

// Pegawai Search
const pegawaiSearch = ref('')
const allPegawaiList = ref([])
const filteredPegawaiList = ref([])
const loadingPegawai = ref(false)
const showPegawaiDropdown = ref(false)
const pegawaiSelected = ref(false)

// Permissions Management
const showPermissionsModal = ref(false)
const selectedUserForPermissions = ref(null)
const selectedMenuPermissions = ref([])
const permissionsFormLoading = ref(false)
const permissionsFormError = ref('')

// Available menus untuk permission
const availableMenus = [
  { key: 'dashboard', label: 'Dashboard', icon: '📊' },
  { key: 'pasien', label: 'Daftar Pasien', icon: '👥' },
  {
    key: 'rawat-jalan',
    label: 'Rawat Jalan',
    icon: '🩺',
    hasSubmenu: true,
    submenus: [
      { key: 'igd', label: 'IGD' },
      { key: 'poli', label: 'Poli' }
    ]
  },
  { key: 'rawat-inap', label: 'Rawat Inap', icon: '🛏️' },
  { key: 'laboratorium', label: 'Laboratorium', icon: '🔬' },
  { key: 'radiologi', label: 'Radiologi', icon: '🩻' },
  { key: 'jadwal-operasi', label: 'Jadwal Operasi', icon: '🏥' },
  { key: 'farmasi', label: 'Farmasi', icon: '💊' },
]

const sidebarThemes = {
  purple: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
  cyan: 'linear-gradient(135deg, #06b6d4 0%, #0891b2 100%)',
  blue: 'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)',
  green: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
  orange: 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)',
  red: 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)'
}

const handleLogoError = () => {
  // Jika icon.png tidak ditemukan, coba icon.svg
  if (defaultLogo.value === '/icons/icon.png' && !logoError.value) {
    defaultLogo.value = '/icons/icon.svg'
    logoError.value = false
  } else {
    // Jika icon.svg juga tidak ada, fallback ke emoji
    defaultLogo.value = null
    logoError.value = true
  }
}

// User data sebagai ref untuk reactivity
const userDataFromStorage = localStorage.getItem('user')
const user = ref(userDataFromStorage ? JSON.parse(userDataFromStorage) : null)

// Watch untuk perubahan localStorage (jika ada perubahan dari tab lain)
if (typeof window !== 'undefined') {
  window.addEventListener('storage', (e) => {
    if (e.key === 'user') {
      user.value = e.newValue ? JSON.parse(e.newValue) : null
    }
  })
}

const openModalTambahPasien = () => {
  showModal.value = true
}

const allMenuItems = [
  { path: '/dashboard', icon: '📊', label: 'Dashboard', key: 'dashboard' },
  { path: '/pasien', icon: '👥', label: 'Daftar Pasien', key: 'pasien' },
  {
    icon: '/images/consultation.png',
    label: 'Rawat Jalan',
    key: 'rawat-jalan',
    submenu: [
      { path: '/rawat-jalan/igd', label: 'IGD' },
      { path: '/rawat-jalan/poli', label: 'Poli' }
    ]
  },
  {
    icon: '🛏️',
    label: 'Rawat Inap',
    key: 'rawat-inap',
    submenu: [
      { path: '/rawat-inap/belum-pulang', label: 'Belum Pulang' },
      { path: '/rawat-inap/pulang', label: 'Pulang' }
    ]
  },
  {
    icon: '🔬',
    label: 'Laboratorium',
    key: 'laboratorium',
    submenu: [
      { path: '/laboratorium/permintaan-pk', label: 'Permintaan Lab PK' },
      { path: '/laboratorium/permintaan-pa', label: 'Permintaan Lab PA' },
      { path: '/laboratorium/periksa-pk', label: 'Periksa Lab PK' },
      { path: '/laboratorium/periksa-pa', label: 'Periksa Lab PA' }
    ]
  },
  {
    icon: '/images/x-rays.png',
    label: 'Radiologi',
    key: 'radiologi',
    submenu: [
      { path: '/radiologi/permintaan', label: 'Permintaan Radiologi' },
      { path: '/radiologi/periksa', label: 'Periksa Radiologi' }
    ]
  },
  { path: '/jadwal-operasi', icon: '/images/surgery-room.png', label: 'Jadwal Operasi', key: 'jadwal-operasi' },
  { path: '/farmasi', icon: '/images/online-pharmacy.png', label: 'Farmasi', key: 'farmasi' },
]

// Filtered menu items based on user permissions
const menuItems = computed(() => {
  const currentUser = user.value

  // Admin has access to all menus
  if (currentUser?.role === 'admin') {
    return allMenuItems
  }

  // Filter based on user permissions
  const permissions = currentUser?.menu_permissions || []

  // If no permissions set, show all menus (backward compatibility)
  if (!permissions || permissions.length === 0) {
    return allMenuItems
  }

  return allMenuItems.filter(item => {
    // Check if main menu has permission
    if (!permissions.includes(item.key)) {
      return false
    }

    // If menu has submenu, filter submenu based on permissions
    if (item.submenu && item.submenu.length > 0) {
      // Filter submenu berdasarkan permission
      const filteredSubmenu = item.submenu.filter(subitem => {
        // Extract submenu key from path (e.g., /rawat-jalan/igd -> igd)
        const submenuKey = subitem.path.split('/').pop()
        const fullPermissionKey = `${item.key}-${submenuKey}`

        // Check if specific submenu permission exists
        return permissions.includes(fullPermissionKey)
      })

      // If no submenu has permission, hide the parent menu
      if (filteredSubmenu.length === 0) {
        return false
      }

      // Return item with filtered submenu
      return {
        ...item,
        submenu: filteredSubmenu
      }
    }

    return true
  }).map(item => {
    // Apply submenu filtering for items with submenu
    if (item.submenu && item.submenu.length > 0) {
      const filteredSubmenu = item.submenu.filter(subitem => {
        const submenuKey = subitem.path.split('/').pop()
        const fullPermissionKey = `${item.key}-${submenuKey}`
        return permissions.includes(fullPermissionKey)
      })

      return {
        ...item,
        submenu: filteredSubmenu
      }
    }
    return item
  })
})

const isActive = (path) => {
  if (path === '/dashboard') {
    return route.path === '/' || route.path === '/dashboard'
  }
  return route.path.startsWith(path)
}

const isActiveParent = (submenu) => {
  return submenu.some(item => route.path.startsWith(item.path))
}

const toggleSubmenu = (label) => {
  if (sidebarCollapsed.value) {
    return // Don't toggle if sidebar is collapsed
  }

  const index = expandedMenus.value.indexOf(label)
  if (index > -1) {
    // If already expanded, close it
    expandedMenus.value.splice(index, 1)
  } else {
    // Close all other submenus and open only this one
    expandedMenus.value = [label]
  }
}

const closeAllSubmenus = () => {
  expandedMenus.value = []
  // Close mobile sidebar saat klik menu
  if (window.innerWidth <= 768) {
    closeMobileSidebar()
  }
}

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
}

const toggleMobileSidebar = () => {
  // Di desktop, toggle collapse biasa
  if (window.innerWidth > 768) {
    toggleSidebar()
  } else {
    // Di mobile, toggle show/hide sidebar
    showMobileSidebar.value = !showMobileSidebar.value
  }
}

const closeMobileSidebar = () => {
  showMobileSidebar.value = false
}

const logout = async () => {
  const result = await Swal.fire({
    icon: 'warning',
    title: 'Konfirmasi Logout',
    text: 'Apakah Anda yakin ingin logout?',
    showCancelButton: true,
    confirmButtonColor: '#667eea',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, Logout',
    cancelButtonText: 'Batal'
  })

  if (result.isConfirmed) {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    router.push('/login')
  }
}

// Avatar upload functionality
const avatarInput = ref(null)

const openAvatarUpload = () => {
  if (avatarInput.value) {
    avatarInput.value.click()
  }
}

const handleAvatarError = (event) => {
  console.error('Error loading avatar image:', event.target.src)
  // Fallback ke inisial jika gambar gagal dimuat
  event.target.style.display = 'none'
}

const handleAvatarLoad = () => {
  console.log('Avatar image loaded successfully')
}

const handleAvatarUpload = async (event) => {
  const file = event.target.files[0]
  if (!file) return

  // Validasi file
  if (!file.type.startsWith('image/')) {
    await Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: 'File harus berupa gambar',
      confirmButtonColor: '#667eea'
    })
    return
  }

  // Validasi ukuran file (max 2MB)
  if (file.size > 2 * 1024 * 1024) {
    await Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: 'Ukuran file maksimal 2MB',
      confirmButtonColor: '#667eea'
    })
    return
  }

  // Show loading
  Swal.fire({
    title: 'Uploading...',
    text: 'Sedang mengupload foto profil',
    allowOutsideClick: false,
    allowEscapeKey: false,
    didOpen: () => {
      Swal.showLoading()
    }
  })

  try {
    const formData = new FormData()
    formData.append('foto', file)
    if (user.value?.nip) {
      formData.append('nip', user.value.nip)
    }

    const { data } = await apiClient.post('/users/upload-avatar', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    if (data.success) {
      // Update user data di localStorage
      const userData = JSON.parse(localStorage.getItem('user'))
      userData.foto_profil = data.foto_url
      localStorage.setItem('user', JSON.stringify(userData))

      // Update ref user untuk reactivity
      user.value = { ...userData }
      
      console.log('Foto URL:', data.foto_url)
      console.log('User updated:', user.value)

      await Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Foto profil berhasil diupload',
        confirmButtonColor: '#667eea'
      })
    } else {
      throw new Error(data.message || 'Upload gagal')
    }
  } catch (error) {
    console.error('Upload error:', error)
    await Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: error.response?.data?.message || 'Gagal mengupload foto profil',
      confirmButtonColor: '#667eea'
    })
  }

  // Reset input
  if (avatarInput.value) {
    avatarInput.value.value = ''
  }
}

const closeModal = () => {
  showModal.value = false
  formError.value = ''
  // Reset form
  form.value = {
    no_rkm_medis: '',
    nm_pasien: '',
    no_ktp: '',
    no_peserta: '',
    jk: '',
    tmp_lahir: '',
    tgl_lahir: '',
    gol_darah: '',
    agama: '',
    stts_nikah: '',
    pnd: '',
    pekerjaan: '',
    alamat: '',
    no_tlp: ''
  }
}

const submitPasien = async () => {
  formLoading.value = true
  formError.value = ''

  try {
    const { data } = await apiClient.post('/pasien', form.value)

    if (data.success) {
      closeModal()
      Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: 'Pasien berhasil ditambahkan!',
        confirmButtonColor: '#667eea'
      }).then(() => {
        // Reload halaman jika sedang di halaman daftar pasien
        if (route.path === '/pasien') {
          router.go(0)
        } else {
          router.push('/pasien')
        }
      })
    } else {
      formError.value = 'Gagal menambahkan pasien'
    }
  } catch (err) {
    console.error('Submit pasien error:', err)
    formError.value = err.response?.data?.message || 'Terjadi kesalahan saat menambahkan pasien'
  } finally {
    formLoading.value = false
  }
}

// Settings Modal Functions
const openSettings = async () => {
  showSettingsModal.value = true
  settingsTab.value = 'appearance'

  // Load users when opening settings
  if (settingsTab.value === 'users') {
    await loadUsers()
  }
}

const closeSettingsModal = () => {
  showSettingsModal.value = false
}

// Watch for tab changes to load users when needed
watch(settingsTab, async (newTab) => {
  if ((newTab === 'users' || newTab === 'permissions') && userList.value.length === 0) {
    await loadUsers()
  }
})

// User Management Functions
const loadUsers = async () => {
  loadingUsers.value = true
  try {
    const { data } = await apiClient.get('/users')
    if (data.success) {
      userList.value = data.data
    }
  } catch (err) {
    console.error('Load users error:', err)
    Swal.fire({
      icon: 'error',
      title: 'Gagal Memuat Data',
      text: err.response?.data?.message || 'Terjadi kesalahan saat memuat data user',
      confirmButtonColor: '#667eea'
    })
  } finally {
    loadingUsers.value = false
  }
}

// Pegawai Search Functions
const loadAllPegawai = async () => {
  if (allPegawaiList.value.length > 0) {
    // Sudah di-load sebelumnya
    filteredPegawaiList.value = allPegawaiList.value
    return
  }

  loadingPegawai.value = true
  try {
    const { data } = await apiClient.get('/users/search-pegawai')

    if (data.success) {
      allPegawaiList.value = data.data
      filteredPegawaiList.value = data.data
    }
  } catch (err) {
    console.error('Load pegawai error:', err)
    Swal.fire({
      icon: 'error',
      title: 'Gagal Memuat Data',
      text: 'Tidak dapat memuat data pegawai',
      confirmButtonColor: '#667eea'
    })
  } finally {
    loadingPegawai.value = false
  }
}

const openPegawaiDropdown = async () => {
  showPegawaiDropdown.value = true
  if (allPegawaiList.value.length === 0) {
    await loadAllPegawai()
  }
}

const filterPegawai = () => {
  const query = pegawaiSearch.value.toLowerCase().trim()

  if (!query) {
    filteredPegawaiList.value = allPegawaiList.value
    return
  }

  filteredPegawaiList.value = allPegawaiList.value.filter(pegawai => {
    return pegawai.nama.toLowerCase().includes(query) ||
           pegawai.nik.toLowerCase().includes(query)
  })
}

const selectPegawai = (pegawai) => {
  // Username menggunakan NIK pegawai (sama dengan SIMRS Java)
  const username = pegawai.nik

  // Fill form
  userForm.value.username = username
  userForm.value.nama = pegawai.nama
  userForm.value.nip = pegawai.nik  // Gunakan nik dari pegawai
  userForm.value.email = pegawai.email || ''
  userForm.value.no_telp = ''  // Reset no_telp, bisa diisi manual

  // Set search text
  pegawaiSearch.value = pegawai.nama

  // Close dropdown
  showPegawaiDropdown.value = false
  pegawaiSelected.value = true

  // Notify user
  Swal.fire({
    icon: 'success',
    title: 'Data Pegawai Dipilih',
    text: `Data ${pegawai.nama} berhasil dimuat. Silakan isi password.`,
    timer: 2000,
    showConfirmButton: false
  })
}

const showAddUserModal = () => {
  editingUser.value = null
  userForm.value = {
    username: '',
    password: '',
    nama: '',
    nip: '',
    email: '',
    no_telp: '',
    role: 'user'
  }
  pegawaiSearch.value = ''
  filteredPegawaiList.value = allPegawaiList.value
  pegawaiSelected.value = false
  userFormError.value = ''
  showUserModal.value = true
}

const editUser = (userItem) => {
  editingUser.value = userItem
  userForm.value = {
    username: userItem.username,
    password: '',
    nama: userItem.nama || '',
    nip: userItem.nip || '',
    email: userItem.email || '',
    no_telp: userItem.no_telp || '',
    role: userItem.role || 'user'
  }
  userFormError.value = ''
  showUserModal.value = true
}

const closeUserModal = () => {
  showUserModal.value = false
  editingUser.value = null
  userForm.value = {
    username: '',
    password: '',
    nama: '',
    nip: '',
    email: '',
    no_telp: '',
    role: 'user'
  }
  pegawaiSearch.value = ''
  filteredPegawaiList.value = allPegawaiList.value
  pegawaiSelected.value = false
  userFormError.value = ''
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  const dropdown = event.target.closest('.pegawai-dropdown')
  const input = event.target.closest('input')
  if (!dropdown && !input) {
    showPegawaiDropdown.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

const saveUser = async () => {
  userFormLoading.value = true
  userFormError.value = ''

  try {
    const payload = {
      username: userForm.value.username,
      nama: userForm.value.nama,
      nip: userForm.value.nip,
      email: userForm.value.email,
      no_telp: userForm.value.no_telp,
      role: userForm.value.role
    }

    // Only add password if provided
    if (userForm.value.password) {
      payload.password = userForm.value.password
    }

    const endpoint = editingUser.value ? `/users/${editingUser.value.id}` : '/users'
    const method = editingUser.value ? 'put' : 'post'

    const { data } = await apiClient[method](endpoint, payload)

    if (data.success) {
      closeUserModal()
      await loadUsers()
      Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: editingUser.value ? 'User berhasil diupdate!' : 'User berhasil ditambahkan!',
        confirmButtonColor: '#667eea'
      })
    } else {
      userFormError.value = data.message || 'Gagal menyimpan user'
    }
  } catch (err) {
    console.error('Save user error:', err)
    userFormError.value = err.response?.data?.message || 'Terjadi kesalahan saat menyimpan user'
  } finally {
    userFormLoading.value = false
  }
}

const deleteUser = async (userId) => {
  const result = await Swal.fire({
    title: 'Konfirmasi Hapus',
    text: `Yakin ingin menghapus user "${userId}"?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  })

  if (result.isConfirmed) {
    try {
      const { data } = await apiClient.delete(`/users/${userId}`)

      if (data.success) {
        await loadUsers()
        Swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: 'User berhasil dihapus!',
          confirmButtonColor: '#667eea'
        })
      }
    } catch (err) {
      console.error('Delete user error:', err)
      Swal.fire({
        icon: 'error',
        title: 'Gagal Menghapus',
        text: err.response?.data?.message || 'Terjadi kesalahan saat menghapus user',
        confirmButtonColor: '#667eea'
      })
    }
  }
}

// Permissions Management Functions
const getMenuLabel = (menuKey) => {
  const menu = availableMenus.find(m => m.key === menuKey)
  return menu ? menu.label : menuKey
}

const editUserPermissions = (userItem) => {
  selectedUserForPermissions.value = userItem
  selectedMenuPermissions.value = userItem.menu_permissions || []
  permissionsFormError.value = ''
  showPermissionsModal.value = true
}

const closePermissionsModal = () => {
  showPermissionsModal.value = false
  selectedUserForPermissions.value = null
  selectedMenuPermissions.value = []
  permissionsFormError.value = ''
}

const isMenuSelected = (menuKey) => {
  return selectedMenuPermissions.value.includes(menuKey)
}

const isSubmenuSelected = (parentKey, submenuKey) => {
  const fullKey = `${parentKey}-${submenuKey}`
  return selectedMenuPermissions.value.includes(fullKey)
}

const toggleMenuPermission = (menuKey) => {
  const index = selectedMenuPermissions.value.indexOf(menuKey)
  const menuItem = availableMenus.find(m => m.key === menuKey)

  if (index > -1) {
    // Remove main menu
    selectedMenuPermissions.value.splice(index, 1)

    // Also remove all submenus if exists
    if (menuItem && menuItem.hasSubmenu) {
      menuItem.submenus.forEach(submenu => {
        const submenuFullKey = `${menuKey}-${submenu.key}`
        const submenuIndex = selectedMenuPermissions.value.indexOf(submenuFullKey)
        if (submenuIndex > -1) {
          selectedMenuPermissions.value.splice(submenuIndex, 1)
        }
      })
    }
  } else {
    // Add main menu
    selectedMenuPermissions.value.push(menuKey)
  }
}

const toggleSubmenuPermission = (parentKey, submenuKey) => {
  const fullKey = `${parentKey}-${submenuKey}`
  const index = selectedMenuPermissions.value.indexOf(fullKey)

  if (index > -1) {
    selectedMenuPermissions.value.splice(index, 1)
  } else {
    selectedMenuPermissions.value.push(fullKey)
    // Auto-check parent if not checked
    if (!selectedMenuPermissions.value.includes(parentKey)) {
      selectedMenuPermissions.value.push(parentKey)
    }
  }
}

const savePermissions = async () => {
  permissionsFormLoading.value = true
  permissionsFormError.value = ''

  try {
    const { data } = await apiClient.put(`/users/${selectedUserForPermissions.value.id}`, {
      menu_permissions: selectedMenuPermissions.value
    })

    if (data.success) {
      closePermissionsModal()
      await loadUsers()
      Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: 'Hak akses menu berhasil diupdate!',
        confirmButtonColor: '#667eea'
      })
    } else {
      permissionsFormError.value = data.message || 'Gagal menyimpan hak akses'
    }
  } catch (err) {
    console.error('Save permissions error:', err)
    permissionsFormError.value = err.response?.data?.message || 'Terjadi kesalahan saat menyimpan hak akses'
  } finally {
    permissionsFormLoading.value = false
  }
}

const handleLogoUpload = (event) => {
  const file = event.target.files[0]
  if (!file) return

  // Validasi ukuran file (maks 2MB)
  if (file.size > 2 * 1024 * 1024) {
    Swal.fire({
      icon: 'error',
      title: 'File Terlalu Besar',
      text: 'Ukuran file maksimal 2MB',
      confirmButtonColor: '#667eea'
    })
    return
  }

  // Validasi tipe file
  const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/svg+xml']
  if (!allowedTypes.includes(file.type)) {
    Swal.fire({
      icon: 'error',
      title: 'Format File Tidak Valid',
      text: 'Hanya file PNG, JPG, dan SVG yang diperbolehkan',
      confirmButtonColor: '#667eea'
    })
    return
  }

  // Convert to base64
  const reader = new FileReader()
  reader.onload = (e) => {
    settings.value.customLogo = e.target.result
    Swal.fire({
      icon: 'success',
      title: 'Berhasil',
      text: 'Logo berhasil diupload. Klik "Simpan Pengaturan" untuk menyimpan perubahan.',
      confirmButtonColor: '#667eea'
    })
  }
  reader.onerror = () => {
    Swal.fire({
      icon: 'error',
      title: 'Gagal Upload',
      text: 'Terjadi kesalahan saat membaca file',
      confirmButtonColor: '#667eea'
    })
  }
  reader.readAsDataURL(file)
}

const removeLogo = async () => {
  const result = await Swal.fire({
    icon: 'warning',
    title: 'Hapus Logo',
    text: 'Yakin ingin menghapus logo custom dan kembali ke logo default?',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText: 'Batal'
  })

  if (result.isConfirmed) {
    settings.value.customLogo = ''
    Swal.fire({
      icon: 'success',
      title: 'Berhasil',
      text: 'Logo berhasil dihapus. Klik "Simpan Pengaturan" untuk menyimpan perubahan.',
      confirmButtonColor: '#667eea'
    })
  }
}

const applySidebarTheme = () => {
  const sidebar = document.querySelector('.sidebar')
  if (sidebar) {
    sidebar.style.background = sidebarThemes[settings.value.sidebarTheme]
  }
}

const saveSettings = () => {
  localStorage.setItem('app-settings', JSON.stringify(settings.value))
  applySidebarTheme()

  // Apply font size
  const root = document.documentElement
  if (settings.value.fontSize === 'small') {
    root.style.fontSize = '14px'
  } else if (settings.value.fontSize === 'large') {
    root.style.fontSize = '18px'
  } else {
    root.style.fontSize = '16px'
  }

  closeSettingsModal()
  Swal.fire({
    icon: 'success',
    title: 'Berhasil',
    text: 'Pengaturan berhasil disimpan!',
    confirmButtonColor: '#667eea',
    timer: 1500,
    showConfirmButton: false
  })
}

const resetSettings = async () => {
  const result = await Swal.fire({
    icon: 'question',
    title: 'Reset Pengaturan',
    text: 'Yakin ingin mereset ke pengaturan default?',
    showCancelButton: true,
    confirmButtonColor: '#667eea',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, Reset',
    cancelButtonText: 'Batal'
  })

  if (result.isConfirmed) {
    settings.value = {
      sidebarTheme: 'purple',
      fontSize: 'medium',
      enableNotifications: true,
      soundEnabled: true,
      customLogo: ''
    }
    localStorage.removeItem('app-settings')
    applySidebarTheme()
    document.documentElement.style.fontSize = '16px'
    Swal.fire({
      icon: 'success',
      title: 'Berhasil',
      text: 'Pengaturan berhasil direset!',
      confirmButtonColor: '#667eea',
      timer: 1500,
      showConfirmButton: false
    })
  }
}

const loadSettings = () => {
  const savedSettings = localStorage.getItem('app-settings')
  if (savedSettings) {
    settings.value = JSON.parse(savedSettings)
    applySidebarTheme()

    // Apply font size
    const root = document.documentElement
    if (settings.value.fontSize === 'small') {
      root.style.fontSize = '14px'
    } else if (settings.value.fontSize === 'large') {
      root.style.fontSize = '18px'
    } else {
      root.style.fontSize = '16px'
    }
  }
}

// Listen to event from child components
const handleOpenModal = () => {
  showModal.value = true
}

// Auto expand submenu based on current route
watch(() => route.path, (newPath) => {
  // Always reset to empty first
  expandedMenus.value = []

  // Only expand submenu if we're in a submenu page
  if (newPath.startsWith('/rawat-jalan/')) {
    expandedMenus.value = ['Rawat Jalan']
  } else if (newPath.startsWith('/rawat-inap/')) {
    expandedMenus.value = ['Rawat Inap']
  } else if (newPath.startsWith('/laboratorium/')) {
    expandedMenus.value = ['Laboratorium']
  } else if (newPath.startsWith('/radiologi/')) {
    expandedMenus.value = ['Radiologi']
  }
  // If none of the above, expandedMenus stays empty (all submenus closed)
}, { immediate: true })

onMounted(() => {
  window.addEventListener('open-modal-tambah-pasien', handleOpenModal)
  loadSettings()
})

onUnmounted(() => {
  window.removeEventListener('open-modal-tambah-pasien', handleOpenModal)
})
</script>

<style scoped>
.app-wrapper {
  display: flex;
  height: 100vh;
  background-color: #f5f7fa;
}

/* Sidebar */
.sidebar {
  width: 260px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  transition: width 0.3s ease;
  display: flex;
  flex-direction: column;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
  position: relative;
  z-index: 100;
}

.sidebar.collapsed {
  width: 70px;
}

.sidebar-header {
  padding: 1.5rem 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.logo {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.logo-icon {
  font-size: 2rem;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.custom-logo-img {
  width: 45px;
  height: 45px;
  object-fit: contain;
  border-radius: 8px;
}

.logo-text {
  overflow: hidden;
}

.hospital-name {
  font-size: 1.1rem;
  font-weight: 700;
  white-space: nowrap;
}

.hospital-id {
  font-size: 0.75rem;
  opacity: 0.8;
}

.sidebar-nav {
  flex: 1;
  padding: 1rem 0;
  overflow-y: auto;
}

.nav-item-wrapper {
  position: relative;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.875rem 1.5rem;
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  transition: all 0.3s ease;
  position: relative;
}

.nav-item.has-submenu {
  cursor: pointer;
}

.nav-item.has-submenu.expanded {
  background-color: white;
  color: #667eea;
  font-weight: 600;
  border-radius: 12px 12px 0 0;
  margin: 0 0.5rem;
  padding-left: 1rem;
  padding-right: 1rem;
}

.nav-item.has-submenu.expanded .nav-icon {
  color: #667eea;
}

.nav-item.has-submenu.expanded .submenu-arrow {
  color: #667eea;
}

.nav-item:hover {
  background-color: rgba(255, 255, 255, 0.1);
  color: white;
}

.nav-item.has-submenu.expanded:hover {
  background-color: white;
  color: #667eea;
}

.nav-item.active {
  background-color: rgba(255, 255, 255, 0.2);
  color: white;
  font-weight: 600;
}

.nav-item.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background-color: white;
}

.nav-item.has-submenu.expanded::before {
  display: none;
}

.nav-icon {
  font-size: 1.5rem;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.menu-icon-img {
  width: 28px;
  height: 28px;
  object-fit: contain;
  /* Hapus filter untuk menampilkan warna asli icon */
  /* filter: brightness(0) invert(1); */
}

/* Icon image when menu is active */
.nav-item.active .menu-icon-img {
  /* filter: brightness(0) invert(1); */
  opacity: 1;
}

/* Icon image when menu has submenu and expanded */
.nav-item.has-submenu.expanded .menu-icon-img {
  /* filter: brightness(1) invert(0); */
  opacity: 1;
}

.nav-text {
  white-space: nowrap;
  flex: 1;
}

.submenu-arrow {
  font-size: 0.8rem;
  margin-left: auto;
  transition: transform 0.3s ease;
}

.submenu {
  background-color: white;
  overflow: hidden;
  margin: 0 0.5rem 0.5rem 0.5rem;
  border-radius: 0 0 12px 12px;
  padding-top: 0;
  padding-bottom: 0.5rem;
}

.submenu-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1.5rem 0.75rem 2.5rem;
  color: #667eea;
  text-decoration: none;
  transition: all 0.3s ease;
  font-size: 0.95rem;
}

.submenu-item:hover {
  background-color: rgba(102, 126, 234, 0.1);
  color: #667eea;
  font-weight: 500;
}

.submenu-item.active {
  background-color: rgba(102, 126, 234, 0.15);
  color: #667eea;
  font-weight: 600;
}

.submenu-dot {
  font-size: 1.2rem;
  flex-shrink: 0;
}

.submenu-text {
  white-space: nowrap;
}

/* Submenu transition */
.submenu-enter-active,
.submenu-leave-active {
  transition: all 0.3s ease;
}

.submenu-enter-from,
.submenu-leave-to {
  max-height: 0;
  opacity: 0;
}

.submenu-enter-to,
.submenu-leave-from {
  max-height: 200px;
  opacity: 1;
}

.nav-button {
  width: 100%;
  background: none;
  border: none;
  text-align: left;
  cursor: pointer;
}

.nav-button:hover {
  background-color: rgba(255, 255, 255, 0.1);
  color: white;
}

.sidebar-footer {
  padding: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  gap: 0.5rem;
}

.settings-btn {
  flex: 1;
  padding: 0.75rem;
  background-color: rgba(255, 255, 255, 0.1);
  border: none;
  color: white;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 1.2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.settings-btn:hover {
  background-color: rgba(255, 255, 255, 0.2);
}

.settings-text {
  font-size: 0.9rem;
  white-space: nowrap;
}

.toggle-btn {
  flex-shrink: 0;
  width: 50px;
  padding: 0.75rem;
  background-color: rgba(255, 255, 255, 0.1);
  border: none;
  color: white;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 1.2rem;
}

.toggle-btn:hover {
  background-color: rgba(255, 255, 255, 0.2);
}

.sidebar.collapsed .sidebar-footer {
  flex-direction: column;
}

.sidebar.collapsed .settings-btn {
  width: 100%;
}

.sidebar.collapsed .toggle-btn {
  width: 100%;
}

/* Main Wrapper */
.main-wrapper {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* Navbar */
.navbar {
  background-color: white;
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  z-index: 50;
}

.navbar-left {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex: 1;
}

/* Hamburger Menu Button - sama seperti InputOperasiView */
.hamburger-btn {
  background: transparent;
  border: none;
  padding: 0.75rem;
  cursor: pointer;
  display: none; /* Hidden by default on desktop */
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
  border-radius: 8px;
  flex-shrink: 0;
}

.hamburger-btn:hover {
  background: #f1f5f9;
}

.hamburger-icon {
  display: flex;
  flex-direction: column;
  gap: 4px;
  width: 24px;
}

.hamburger-icon span {
  display: block;
  height: 3px;
  background: #667eea;
  border-radius: 2px;
  transition: all 0.3s;
}

.search-box {
  position: relative;
  max-width: 400px;
  width: 100%;
}

.search-input {
  width: 100%;
  padding: 0.75rem 3rem 0.75rem 1rem;
  border: 1px solid #e0e6ed;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: all 0.3s ease;
}

.search-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.search-icon {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  font-size: 1.2rem;
  opacity: 0.5;
}

.navbar-right {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.icon-btn {
  position: relative;
  background: none;
  border: none;
  padding: 0.75rem;
  border-radius: 8px;
  cursor: pointer;
  font-size: 1.3rem;
  transition: all 0.3s ease;
}

.icon-btn:hover {
  background-color: #f5f7fa;
}

.badge {
  position: absolute;
  top: 5px;
  right: 5px;
  background-color: #ef4444;
  color: white;
  font-size: 0.7rem;
  padding: 0.15rem 0.4rem;
  border-radius: 10px;
  font-weight: 600;
}

.user-menu {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 1rem;
  background-color: #f5f7fa;
  border-radius: 10px;
  margin-left: 0.5rem;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 1.1rem;
  overflow: hidden;
  transition: all 0.3s ease;
}

.user-avatar:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.user-avatar .avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.user-avatar .avatar-upload-icon {
  position: absolute;
  bottom: -2px;
  right: -2px;
  width: 18px;
  height: 18px;
  background: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.user-avatar:hover .avatar-upload-icon {
  opacity: 1;
}

.user-info {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-weight: 600;
  font-size: 0.95rem;
  color: #2d3748;
}

.user-role {
  font-size: 0.8rem;
  color: #718096;
}

.logout-btn {
  background: none;
  border: none;
  padding: 0.5rem;
  cursor: pointer;
  font-size: 1.2rem;
  opacity: 0.7;
  transition: opacity 0.3s ease;
}

.logout-btn:hover {
  opacity: 1;
}

/* Page Content */
.page-content {
  flex: 1;
  overflow-y: auto;
  padding: 2rem;
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Responsive */

/* Tablet - 768px to 1024px */
@media (max-width: 1024px) {
  .sidebar {
    width: 220px;
  }

  .page-content {
    padding: 1.5rem;
  }

  .navbar {
    padding: 0.75rem 1.5rem;
  }

  .search-box {
    max-width: 300px;
  }
}

/* Mobile & Small Tablet - max 768px */
@media (max-width: 768px) {
  .app-wrapper {
    position: relative;
  }

  /* Sidebar sebagai overlay - sama seperti InputOperasiView */
  .sidebar {
    position: fixed;
    left: 0;
    top: 0;
    height: 100vh;
    z-index: 1100;
    transition: transform 0.3s ease-in-out;
    transform: translateX(-100%);
  }

  .sidebar.show-mobile {
    transform: translateX(0);
    box-shadow: 4px 0 20px rgba(0, 0, 0, 0.3);
  }

  /* Toggle button selalu terlihat di mobile */
  .hamburger-btn {
    display: flex;
  }

  /* Main wrapper tetap full width */
  .main-wrapper {
    width: 100%;
    margin-left: 0;
  }

  /* Navbar mobile */
  .navbar {
    padding: 0.75rem 1rem;
  }

  .navbar-left {
    gap: 0.5rem;
    flex: 1;
  }

  .search-box {
    display: none; /* Hide search di mobile untuk hemat space */
  }

  /* User info di mobile */
  .user-info {
    display: none;
  }

  .user-menu {
    padding: 0.5rem;
    gap: 0.5rem;
  }

  .user-avatar {
    width: 35px;
    height: 35px;
    font-size: 1rem;
  }

  /* Navbar buttons lebih kecil */
  .icon-btn {
    padding: 0.5rem;
    font-size: 1.1rem;
  }

  /* Page content lebih compact */
  .page-content {
    padding: 1rem;
  }

  /* Modal responsive */
  .modal-dialog {
    margin: 0.5rem;
    max-width: calc(100% - 1rem);
  }

  .modal-dialog-riwayat {
    margin: 0.5rem;
    max-width: calc(100% - 1rem);
  }

  /* Table responsive */
  .table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  /* Sidebar collapsed behavior di mobile */
  .sidebar.collapsed {
    left: 0;
  }

  .sidebar.collapsed.show-mobile {
    left: 0;
  }
}

/* Small Mobile - max 480px */
@media (max-width: 480px) {
  .navbar {
    padding: 0.5rem 0.75rem;
  }

  .page-content {
    padding: 0.75rem;
  }

  .user-avatar {
    width: 30px;
    height: 30px;
    font-size: 0.9rem;
  }

  .icon-btn {
    padding: 0.4rem;
    font-size: 1rem;
  }

  .logout-btn {
    padding: 0.3rem;
  }

  /* Card spacing lebih kecil */
  .card {
    margin-bottom: 0.75rem;
  }

  /* Font size lebih kecil */
  .navbar-right {
    gap: 0.5rem;
  }

  /* Hide beberapa icon button jika perlu */
  .icon-btn:not(.logout-btn) {
    display: none;
  }
}

/* Overlay untuk sidebar mobile - sama seperti InputOperasiView */
.sidebar-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1050;
  display: none;
}

@media (max-width: 768px) {
  .sidebar-overlay.show {
    display: block;
  }
}

/* Scrollbar */
.sidebar-nav::-webkit-scrollbar {
  width: 6px;
}

.sidebar-nav::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
}

.sidebar-nav::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 3px;
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.5);
}

/* Modal styles */
.modal.show {
  display: block;
  background-color: rgba(0, 0, 0, 0.5);
}

.modal-backdrop.show {
  opacity: 0.5;
}

/* Settings Modal */
.settings-section {
  margin-bottom: 1rem;
}

.settings-title {
  font-weight: 600;
  color: #2d3748;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #e0e6ed;
}

/* User Avatar di Tabel */
.user-avatar-table {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  margin: 0 auto;
}

.avatar-img-table {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-initial {
  color: white;
  font-weight: 700;
  font-size: 1.2rem;
  text-transform: uppercase;
}

.form-check-input:checked {
  background-color: #667eea;
  border-color: #667eea;
}

/* Logo Upload Section */
.logo-upload-section {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  padding: 1rem;
  background-color: #f5f7fa;
  border-radius: 8px;
  border: 1px solid #e0e6ed;
}

.current-logo {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.logo-preview {
  width: 80px;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: white;
  border-radius: 8px;
  border: 2px solid #e0e6ed;
  overflow: hidden;
}

.preview-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  padding: 5px;
}

.default-logo {
  font-size: 3rem;
}

.logo-info {
  display: flex;
  flex-direction: column;
}

.logo-actions {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

/* Pegawai Search Dropdown */
.pegawai-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #dee2e6;
  border-radius: 0.375rem;
  max-height: 300px;
  overflow-y: auto;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  margin-top: 4px;
}

.pegawai-item {
  padding: 0.75rem 1rem;
  cursor: pointer;
  border-bottom: 1px solid #f1f3f5;
  transition: background-color 0.2s;
}

.pegawai-item:hover {
  background-color: #e8f4f8;
}

.pegawai-item:last-child {
  border-bottom: none;
}

.pegawai-name {
  font-weight: 500;
  color: #2d3748;
  margin-bottom: 0.25rem;
}

.dropdown-arrow {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
  color: #6b7280;
}

.search-loading {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
}

/* Scrollbar styling */
.pegawai-dropdown::-webkit-scrollbar {
  width: 8px;
}

.pegawai-dropdown::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.pegawai-dropdown::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

.pegawai-dropdown::-webkit-scrollbar-thumb:hover {
  background: #555;
}

/* Permissions Grid */
.permissions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.permission-card-wrapper {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.permission-card {
  border: 2px solid #e0e6ed;
  border-radius: 8px;
  padding: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  background-color: white;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.permission-card:hover {
  border-color: #667eea;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
  transform: translateY(-2px);
}

.permission-card.selected {
  border-color: #667eea;
  background-color: rgba(102, 126, 234, 0.1);
}

.permission-icon {
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
}

.permission-label {
  font-weight: 500;
  color: #2d3748;
  margin-bottom: 0.5rem;
}

.permission-checkbox {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
}

/* Submenu Cards */
.submenu-cards {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  margin-left: 1rem;
}

.permission-card.submenu {
  flex-direction: row;
  padding: 0.6rem 0.8rem;
  align-items: center;
  justify-content: flex-start;
  gap: 0.5rem;
  border-color: #d1d5db;
  background-color: #f9fafb;
}

.permission-card.submenu:hover {
  border-color: #667eea;
  background-color: #f3f4f6;
}

.permission-card.submenu.selected {
  border-color: #667eea;
  background-color: rgba(102, 126, 234, 0.08);
}

.submenu-indicator {
  color: #9ca3af;
  font-size: 0.875rem;
  font-weight: 400;
}

.permission-card.submenu .permission-label {
  margin-bottom: 0;
  font-size: 0.875rem;
  flex: 1;
  text-align: left;
}

.permission-card.submenu .permission-checkbox {
  position: static;
}

.permission-checkbox input[type="checkbox"] {
  width: 20px;
  height: 20px;
  cursor: pointer;
}
</style>
