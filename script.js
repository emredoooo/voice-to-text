const micBtn = document.getElementById('micBtn');
const statusText = document.getElementById('statusText');
const transcriptArea = document.getElementById('transcript');
const saveBtn = document.getElementById('saveBtn');
const themeToggle = document.getElementById('themeToggle');
const mobileMenuBtn = document.getElementById('mobileMenuBtn');
const headerActions = document.getElementById('headerActions');

// Mobile Menu Toggle
if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', () => {
        headerActions.classList.toggle('show');
    });
}

// Theme Logic
const savedTheme = localStorage.getItem('theme') || 'light';
document.documentElement.setAttribute('data-theme', savedTheme);
updateThemeIcon(savedTheme);

themeToggle.addEventListener('click', () => {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    updateThemeIcon(newTheme);
});

function updateThemeIcon(theme) {
    const icon = themeToggle.querySelector('i');
    if (theme === 'dark') {
        icon.className = 'fas fa-sun';
    } else {
        icon.className = 'fas fa-moon';
    }
}

let recognition;
let isRecording = false;
let lastProcessedIndex = 0;

// Initialize Speech Recognition
if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    recognition = new SpeechRecognition();
    recognition.continuous = false; // Changed to false for better mobile stability
    recognition.interimResults = true;
    recognition.lang = 'id-ID';

    recognition.onstart = () => {
        // Only update UI if we are genuinely starting, not just restarting
        if (!micBtn.classList.contains('listening')) {
            micBtn.classList.add('listening');
            statusText.textContent = 'Listening...';
        }
    };

    recognition.onend = () => {
        // If user wants to record, restart automatically
        if (isRecording) {
            try {
                recognition.start();
            } catch (e) {
                console.log("Restart ignored", e);
            }
        } else {
            micBtn.classList.remove('listening');
            micBtn.classList.remove('speaking');
            statusText.textContent = 'Tap microphone to start';
        }
    };

    // Detect speech start/end for animation
    recognition.onspeechstart = () => {
        micBtn.classList.add('speaking');
        statusText.textContent = 'Detecting speech...';
    };

    recognition.onspeechend = () => {
        micBtn.classList.remove('speaking');
        statusText.textContent = 'Processing...';
    };

    recognition.onresult = (event) => {
        // Also trigger speaking animation on result to be sure
        micBtn.classList.add('speaking');
        clearTimeout(window.speakingTimer);
        window.speakingTimer = setTimeout(() => {
            micBtn.classList.remove('speaking');
        }, 500);

        let finalTranscript = '';

        // With continuous=false, we usually get just one result set per session
        // We can trust the last result in the list is the one we want
        for (let i = event.resultIndex; i < event.results.length; ++i) {
            if (event.results[i].isFinal) {
                finalTranscript += event.results[i][0].transcript;
            }
        }

        // Append final transcript to textarea
        if (finalTranscript) {
            const currentText = transcriptArea.value;
            // Add space if needed
            const prefix = currentText && !currentText.endsWith(' ') ? ' ' : '';
            transcriptArea.value = currentText + prefix + finalTranscript;
        }

        updateSaveButton();
    };

    recognition.onerror = (event) => {
        console.error('Speech recognition error', event.error);

        // Ignore "no-speech" error as we will just restart
        if (event.error === 'no-speech') {
            return;
        }

        statusText.textContent = 'Error: ' + event.error;

        // For fatal errors, stop recording
        if (event.error === 'not-allowed' || event.error === 'service-not-allowed') {
            isRecording = false;
            micBtn.classList.remove('listening');
            micBtn.classList.remove('speaking');

            if (event.error === 'not-allowed') {
                Swal.fire({
                    icon: 'error',
                    title: 'Akses Ditolak',
                    text: 'Microphone tidak diizinkan. Mohon izinkan akses microphone di pengaturan browser.'
                });
            }
        }
    };
} else {
    statusText.textContent = 'Web Speech API not supported in this browser.';
    micBtn.disabled = true;
    Swal.fire({
        icon: 'error',
        title: 'Browser Tidak Support',
        text: 'Browser anda tidak mendukung fitur Voice to Text. Coba gunakan Google Chrome terbaru.'
    });
}

// Toggle Recording
micBtn.addEventListener('click', () => {
    if (!recognition) return;

    if (isRecording) {
        recognition.stop();
    } else {
        recognition.start();
    }
});

// Enable/Disable Save Button
transcriptArea.addEventListener('input', updateSaveButton);

function updateSaveButton() {
    saveBtn.disabled = transcriptArea.value.trim().length === 0;
}

// Save Note
saveBtn.addEventListener('click', async () => {
    const text = transcriptArea.value.trim();
    if (!text) return;

    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';
    console.log('Attempting to save:', text);

    try {
        const response = await fetch('auth.php?action=save_note', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ text: text })
        });

        console.log('Response status:', response.status);
        const data = await response.json();
        console.log('Response data:', data);

        if (data.success) {
            // Clear area
            transcriptArea.value = '';
            updateSaveButton();

            Swal.fire({
                icon: 'success',
                title: 'Tersimpan!',
                text: 'Catatan berhasil disimpan',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Gagal menyimpan: ' + (data.message || 'Unknown error')
            });
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Note';
        }
    } catch (err) {
        console.error('Save error:', err);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Terjadi kesalahan saat menyimpan: ' + err.message
        });
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save Note';
    }
});

// Copy to Clipboard
async function copyToClipboard(btn, text) {
    try {
        await navigator.clipboard.writeText(text);
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        setTimeout(() => {
            btn.innerHTML = originalText;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy: ', err);
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);

        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        setTimeout(() => {
            btn.innerHTML = originalText;
        }, 2000);
    }
}

async function deleteNote(id) {
    const result = await Swal.fire({
        title: 'Hapus Catatan?',
        text: "Anda tidak bisa mengembalikannya lagi!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus!'
    });

    if (!result.isConfirmed) return;

    try {
        const response = await fetch('auth.php?action=delete_note', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
        const data = await response.json();
        if (data.success) {
            Swal.fire(
                'Terhapus!',
                'Catatan berhasil dihapus.',
                'success'
            ).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire('Gagal', 'Gagal menghapus: ' + data.message, 'error');
        }
    } catch (err) {
        Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
    }
}

function editNote(id, btn) {
    const card = btn.closest('.note-card');
    const contentDiv = card.querySelector('.note-content');
    const originalText = contentDiv.innerText; // Use innerText to preserve newlines as visual

    // Check if already editing
    if (card.querySelector('textarea')) return;

    const textarea = document.createElement('textarea');
    textarea.className = 'transcript-box';
    textarea.value = originalText;
    textarea.style.minHeight = '100px';

    const saveEditBtn = document.createElement('button');
    saveEditBtn.className = 'btn btn-primary';
    saveEditBtn.innerHTML = 'Save Changes';
    saveEditBtn.style.marginTop = '0.5rem';
    saveEditBtn.onclick = async () => {
        const newText = textarea.value.trim();
        if (!newText) return;

        try {
            const response = await fetch('auth.php?action=update_note', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, text: newText })
            });
            const data = await response.json();
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Catatan diperbarui',
                    timer: 1000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire('Gagal', 'Gagal update: ' + data.message, 'error');
            }
        } catch (err) {
            Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
        }
    };

    const cancelBtn = document.createElement('button');
    cancelBtn.className = 'btn btn-copy';
    cancelBtn.innerHTML = 'Cancel';
    cancelBtn.style.marginTop = '0.5rem';
    cancelBtn.style.marginLeft = '0.5rem';
    cancelBtn.onclick = () => {
        contentDiv.innerHTML = originalText.replace(/\n/g, '<br>'); // Restore
        // Remove edit UI
        textarea.remove();
        saveEditBtn.remove();
        cancelBtn.remove();
        contentDiv.style.display = 'block';
        card.querySelector('.note-actions').style.display = 'flex';
    };

    // Hide original content and actions
    contentDiv.style.display = 'none';
    card.querySelector('.note-actions').style.display = 'none';

    // Insert new UI
    card.insertBefore(textarea, contentDiv.nextSibling);
    card.insertBefore(saveEditBtn, textarea.nextSibling);
    card.insertBefore(cancelBtn, saveEditBtn.nextSibling);
}
