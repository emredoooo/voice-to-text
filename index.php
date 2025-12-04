<?php
require_once 'functions.php';
requireLogin();

$user = $_SESSION['user'];
$notes = getNotes($user['id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voice To Text</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="theme-color" content="#6366f1">
</head>

<body>
    <div class="app-container">
        <header class="header">
            <div class="header-top">
                <div class="logo">Voice To Text</div>
                <button id="mobileMenuBtn" class="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <div class="header-actions" id="headerActions">
                <button id="themeToggle" class="theme-toggle" title="Toggle Dark Mode">
                    <i class="fas fa-moon"></i>
                </button>
                <div class="user-menu">
                    <?php if ($user['id'] == 1): ?>
                        <a href="admin.php" class="btn-logout"
                            style="color: var(--primary-color); border-color: var(--primary-color);">
                            <i class="fas fa-users-cog"></i> Admin
                        </a>
                    <?php endif; ?>
                    <span class="user-name">Hi, <?php echo htmlspecialchars($user['name']); ?></span>
                    <a href="auth.php?action=logout" class="btn-logout">Logout</a>
                </div>
            </div>
        </header>

        <main>
            <section class="recording-area">
                <button id="micBtn" class="mic-btn" title="Click to Start Recording">
                    <i class="fas fa-microphone"></i>
                    <div class="visualizer">
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                    </div>
                </button>
                <div id="statusText" class="status-text">pencet disini untuk start</div>

                <textarea id="transcript" class="transcript-box"
                    placeholder="ngomong dulu baru nanti muncul disini..."></textarea>

                <button id="saveBtn" class="btn btn-primary" style="max-width: 200px; margin: 0 auto;" disabled>
                    simpen catatan
                </button>
            </section>

            <section class="notes-section">
                <h2 class="section-title">catatan yang disimpen :</h2>
                <div id="notesList" class="notes-list">
                    <?php foreach ($notes as $note): ?>
                        <div class="note-card">
                            <div class="note-meta">
                                <span><?php echo date('M j, Y H:i', $note['timestamp']); ?></span>
                            </div>
                            <div class="note-content"><?php echo nl2br($note['text']); ?></div>
                            <div class="note-actions">
                                <button class="btn btn-copy"
                                    onclick="copyToClipboard(this, `<?php echo addslashes($note['text']); ?>`)">
                                    <i class="far fa-copy"></i> Salin
                                </button>
                                <button class="btn btn-copy" onclick="editNote('<?php echo $note['id']; ?>', this)">
                                    <i class="far fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-copy" style="color: var(--danger-color);"
                                    onclick="deleteNote('<?php echo $note['id']; ?>')">
                                    <i class="far fa-trash-alt"></i> Hapus
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($notes)): ?>
                        <div style="text-align: center; color: var(--text-muted); padding: 2rem;">
                            Belum ada catatan !
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

    <script src="script.js?v=<?php echo time(); ?>"></script>
</body>

</html>