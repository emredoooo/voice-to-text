<?php
require_once 'functions.php';
requireLogin();

if ($_SESSION['user']['id'] != 1) {
    header('Location: index.php');
    exit;
}

$users = getUsers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Users</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Apply theme immediately
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body>
    <div class="app-container">
        <header class="header">
            <div class="logo">User Management</div>
            <div class="header-actions">
                <a href="index.php" class="btn-logout" style="border: none; color: var(--primary-color);">
                    <i class="fas fa-arrow-left"></i> Back to App
                </a>
            </div>
        </header>

        <main>
            <!-- Add User Form -->
            <section class="recording-area" style="text-align: left; padding: 2rem;">
                <h2 class="section-title">Add New User</h2>
                <form id="addUserForm" style="display: grid; gap: 1rem; max-width: 400px; margin: 0 auto;">
                    <div>
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-input" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </form>
            </section>

            <!-- User List -->
            <section class="notes-section">
                <h2 class="section-title">Existing Users</h2>
                <div class="notes-list">
                    <?php foreach ($users as $u): ?>
                        <div class="note-card" style="flex-direction: row; align-items: center; justify-content: space-between;">
                            <div>
                                <div style="font-weight: bold; font-size: 1.1rem;"><?php echo htmlspecialchars($u['name']); ?></div>
                                <div style="color: var(--text-muted); font-size: 0.9rem;">@<?php echo htmlspecialchars($u['username']); ?></div>
                            </div>
                            
                            <div style="display: flex; gap: 0.5rem;">
                                <button class="btn btn-copy" onclick="resetPassword(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['name']); ?>')">
                                    <i class="fas fa-key"></i> Reset Pass
                                </button>
                                <?php if ($u['id'] != 1): ?>
                                    <button class="btn btn-copy" style="color: var(--danger-color);" onclick="deleteUser(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['name']); ?>')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>
    </div>

    <script>
        document.getElementById('addUserForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            
            const result = await Swal.fire({
                title: 'Buat User Baru?',
                text: `Akan membuat user dengan nama ${data.name}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Buat!'
            });

            if (!result.isConfirmed) return;

            try {
                const response = await fetch('auth.php?action=add_user', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const res = await response.json();
                if (res.success) {
                    Swal.fire('Berhasil!', 'User berhasil dibuat.', 'success').then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'Request failed', 'error');
            }
        });

        async function resetPassword(id, name) {
            const { value: newPass } = await Swal.fire({
                title: 'Reset Password',
                input: 'text',
                inputLabel: `Masukkan password baru untuk ${name}`,
                inputPlaceholder: 'Password baru...',
                showCancelButton: true
            });

            if (!newPass) return;

            try {
                const response = await fetch('auth.php?action=reset_password', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, new_password: newPass })
                });
                const res = await response.json();
                if (res.success) {
                    Swal.fire('Berhasil!', 'Password berhasil diubah.', 'success');
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'Request failed', 'error');
            }
        }

        async function deleteUser(id, name) {
            const result = await Swal.fire({
                title: 'Hapus User?',
                text: `Anda yakin ingin menghapus user ${name}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Ya, Hapus!'
            });

            if (!result.isConfirmed) return;

            try {
                const response = await fetch('auth.php?action=delete_user', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                const res = await response.json();
                if (res.success) {
                    Swal.fire('Terhapus!', 'User berhasil dihapus.', 'success').then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'Request failed', 'error');
            }
        }
    </script>
</body>
</html>
