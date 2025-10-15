function normalizeImg(path){
    if(!path) return '';
    path = path.replace(/^\/+/,'');
    return path.startsWith('img/') ? path : 'img/' + path;
}

function initAdminPanel(panel){
    const imgPrefix = panel.dataset.prefix || '';
    // tab switching
    const tabs = panel.querySelectorAll('.fa-tab-header button');
    const contents = panel.querySelectorAll('.fa-tab-content');
    tabs.forEach(btn => {
        btn.addEventListener('click', () => {
            tabs.forEach(b => b.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            const target = panel.querySelector(`#fa-tab-${btn.dataset.tab}`);
            if (target) target.classList.add('active');
        });
    });


    // add-item form behaviour
    const typeSel = panel.querySelector('select[name="item_type"]');
    if (typeSel) {
        const waterFields = panel.querySelectorAll('.water-field');
        const feedFields = panel.querySelectorAll('.feed-field');
        const updateFields = () => {
            const isPlant = typeSel.value === 'plant';
            waterFields.forEach(f => f.style.display = isPlant ? 'block' : 'none');
            feedFields.forEach(f => f.style.display = isPlant ? 'none' : 'block');
        };
        updateFields();
        typeSel.addEventListener('change', updateFields);
    }

    // edit-item form behaviour
    const editForm = panel.querySelector('#fa-edit-form');
    if (editForm) {
        const editItems = panel.querySelectorAll('.fa-edit-item');
        editItems.forEach(item => {
            item.addEventListener('click', () => {
                // Elimină selecția de pe toate items
                editItems.forEach(i => i.classList.remove('selected'));
                // Adaugă selecție pe item-ul curent
                item.classList.add('selected');
                
                const id = item.dataset.id;
                fetch(`farm_admin/get_item.php?id=${id}`, {
                    method: 'GET',
                    credentials: 'same-origin'
                })
                .then(res => res.json())
                .then(data => {
                    // get_item.php returnează direct item-ul, nu { success: true, item: {...} }
                    if (data && data.id) {
                        // Populează formularul cu datele
                        editForm.querySelector('[name="id"]').value = data.id;
                        editForm.querySelector('[name="name"]').value = data.name;
                        editForm.querySelector('[name="item_type"]').value = data.item_type;
                        editForm.querySelector('[name="slot_type"]').value = data.slot_type;
                        editForm.querySelector('[name="image_name"]').value = data.image_plant;
                        
                        // Calculează ore, minute, secunde pentru water
                        editForm.querySelector('[name="water_hours"]').value = Math.floor(data.water_interval / 3600);
                        editForm.querySelector('[name="water_minutes"]').value = Math.floor((data.water_interval % 3600) / 60);
                        editForm.querySelector('[name="water_seconds"]').value = data.water_interval % 60;
                        
                        // Calculează ore, minute, secunde pentru feed
                        editForm.querySelector('[name="feed_hours"]').value = Math.floor(data.feed_interval / 3600);
                        editForm.querySelector('[name="feed_minutes"]').value = Math.floor((data.feed_interval % 3600) / 60);
                        editForm.querySelector('[name="feed_seconds"]').value = data.feed_interval % 60;
                        
                        editForm.querySelector('[name="water_times"]').value = data.water_times;
                        editForm.querySelector('[name="feed_times"]').value = data.feed_times;
                        editForm.querySelector('[name="price"]').value = data.price;
                        editForm.querySelector('[name="sell_price"]').value = data.sell_price;
                        editForm.querySelector('[name="production"]').value = data.production;
                        editForm.querySelector('[name="barn_capacity"]').value = data.barn_capacity;
                        
                        // Trigger change event pentru a afișa/ascunde câmpurile corecte
                        const typeSelect = editForm.querySelector('[name="item_type"]');
                        if (typeSelect) {
                            typeSelect.dispatchEvent(new Event('change'));
                        }
                        
                        editForm.style.display = 'block';
                        editForm.scrollIntoView({ behavior: 'smooth' });
                    } else {
                        alert('Error loading item data');
                    }
                })
                .catch(err => {
                    console.error('Error fetching item:', err);
                    alert('Error loading item');
                });
            });
        });
    }
    
    // Edit form type change behaviour
    if (editForm) {
        const editTypeSel = editForm.querySelector('[name="item_type"]');
        if (editTypeSel) {
            const updateEditFields = () => {
                const isPlant = editTypeSel.value === 'plant';
                const waterFields = editForm.querySelectorAll('.water-field');
                const feedFields = editForm.querySelectorAll('.feed-field');
                waterFields.forEach(f => f.style.display = isPlant ? 'block' : 'none');
                feedFields.forEach(f => f.style.display = isPlant ? 'none' : 'block');
            };
            editTypeSel.addEventListener('change', updateEditFields);
        }
    }

    // delete-item form behaviour
    const deleteItems = panel.querySelectorAll('.fa-delete-item');
    const deleteBtn = panel.querySelector('#fa-delete-item-btn');
    let selectedDeleteId = null;
    
    deleteItems.forEach(item => {
        item.addEventListener('click', () => {
            deleteItems.forEach(i => i.classList.remove('selected'));
            item.classList.add('selected');
            selectedDeleteId = item.dataset.id;
            deleteBtn.disabled = false;
        });
    });
    
    if (deleteBtn) {
        deleteBtn.addEventListener('click', () => {
            if (!selectedDeleteId) return;
            
            if (confirm('Are you sure you want to delete this item?')) {
                fetch('farm_admin/delete_item.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: selectedDeleteId }),
                    credentials: 'same-origin'
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        });
    }

    // VIP form
    const vipForm = panel.querySelector('#fa-vip-form');
    if (vipForm) {
        vipForm.addEventListener('submit', e => {
            e.preventDefault();
            const formData = new FormData(vipForm);
            fetch('farm_admin/save_vip.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('VIP item saved successfully!');
                    vipForm.reset();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => console.error(err));
        });
    }

    const achForm = document.querySelector('#fa-ach-form');
    if (achForm) {
        achForm.addEventListener('submit', e => {
            e.preventDefault();
            const formData = new FormData(achForm);
            fetch('farm_admin/save_achievement.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Achievement saved successfully!');
                    achForm.reset();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => console.error(err));
        });
    }

    // Helper form behaviour
    const helperForm = panel.querySelector('#fa-helper-form');
    if (helperForm) {
        helperForm.addEventListener('submit', e => {
            e.preventDefault();
            const formData = new FormData(helperForm);
            fetch('farm_admin/save_helper.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Helper saved successfully!');
                    helperForm.reset();
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error saving helper');
            });
        });
    }

    // Edit Helper form behaviour
    const helperEditGrid = panel.querySelector('.fa-edit-helper-grid');
    const helperEditForm = panel.querySelector('#fa-helper-edit-form');
    if (helperEditGrid && helperEditForm) {
        helperEditGrid.addEventListener('click', e => {
            const helperEl = e.target.closest('.fa-helper-item');
            if (helperEl) {
                // Elimină selecția de pe toate helpers
                const allHelpers = panel.querySelectorAll('.fa-helper-item');
                allHelpers.forEach(h => h.classList.remove('selected'));
                // Adaugă selecție pe helper-ul curent
                helperEl.classList.add('selected');
                
                helperEditForm.querySelector('[name="id"]').value = helperEl.dataset.id;
                helperEditForm.querySelector('[name="name"]').value = helperEl.dataset.name;
                helperEditForm.querySelector('[name="image"]').value = helperEl.dataset.image;
                helperEditForm.querySelector('[name="message_file"]').value = helperEl.dataset.message;
                helperEditForm.querySelector('[name="waters"]').value = helperEl.dataset.waters;
                helperEditForm.querySelector('[name="feeds"]').value = helperEl.dataset.feeds;
                helperEditForm.querySelector('[name="harvests"]').value = helperEl.dataset.harvests;
                helperEditForm.style.display = 'block';
                helperEditForm.scrollIntoView({ behavior: 'smooth' });
            }
        });

        helperEditForm.addEventListener('submit', e => {
            e.preventDefault();
            const formData = new FormData(helperEditForm);
            fetch('farm_admin/update_helper.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Helper updated successfully!');
                    helperEditForm.style.display = 'none';
                    // Optionally refresh the helper list or reload
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error updating helper');
            });
        });
    }

    // Add Users functionality
    initUserCreation(panel);
    
    // Admin Grades functionality  
    initAdminGrades(panel);
    
    // PVP System functionality
    initPvpSystem(panel);
}

function initUserCreation(panel) {
    // User creation tab switching
    const userTabs = panel.querySelectorAll('.user-tab-btn');
    const userContents = panel.querySelectorAll('.user-tab-content');
    
    userTabs.forEach(btn => {
        btn.addEventListener('click', () => {
            userTabs.forEach(b => b.classList.remove('active'));
            userContents.forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            const target = panel.querySelector(`#user-tab-${btn.dataset.usertab}`);
            if (target) target.classList.add('active');
        });
    });
    
    // Auto create users form
    const autoForm = panel.querySelector('#fa-auto-users-form');
    if (autoForm) {
        autoForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('farm_admin/create_users_auto.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const resultDiv = panel.querySelector('#auto-users-result');
                if (data.success) {
                    resultDiv.innerHTML = `<div class="success">${data.message}</div>`;
                    // Update current password display
                    const currentPasswordDisplay = panel.querySelector('#current_password_display');
                    if (currentPasswordDisplay) {
                        currentPasswordDisplay.textContent = data.current_password || 'password123';
                    }
                } else {
                    resultDiv.innerHTML = `<div class="error">${data.message}</div>`;
                }
            })
            .catch(error => {
                const resultDiv = panel.querySelector('#auto-users-result');
                resultDiv.innerHTML = `<div class="error">Error: ${error.message}</div>`;
        });
    });
    }
    
    // Manual create user form
    const manualForm = panel.querySelector('#fa-manual-user-form');
    if (manualForm) {
        manualForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('farm_admin/create_user_manual.php', {
            method: 'POST',
                body: formData
        })
            .then(response => response.json())
        .then(data => {
                const resultDiv = panel.querySelector('#manual-user-result');
            if (data.success) {
                    resultDiv.innerHTML = `<div class="success">${data.message}</div>`;
                    this.reset();
                } else {
                    resultDiv.innerHTML = `<div class="error">${data.message}</div>`;
                }
            })
            .catch(error => {
                const resultDiv = panel.querySelector('#manual-user-result');
                resultDiv.innerHTML = `<div class="error">Error: ${error.message}</div>`;
            });
        });
    }
    
    // Update passwords form
    const passwordForm = panel.querySelector('#fa-update-passwords-form');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const newPassword = this.querySelector('#new_password_input').value;
            const confirmPassword = this.querySelector('#confirm_password_input').value;
            
            if (newPassword !== confirmPassword) {
                const resultDiv = panel.querySelector('#update-passwords-result');
                resultDiv.innerHTML = `<div class="error">Passwords do not match!</div>`;
                return;
            }
            
            const formData = new FormData(this);
            
            fetch('farm_admin/update_all_passwords.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
                .then(data => {
                const resultDiv = panel.querySelector('#update-passwords-result');
                    if (data.success) {
                    resultDiv.innerHTML = `<div class="success">${data.message}</div>`;
                    this.reset();
                } else {
                    resultDiv.innerHTML = `<div class="error">${data.message}</div>`;
                }
            })
            .catch(error => {
                const resultDiv = panel.querySelector('#update-passwords-result');
                resultDiv.innerHTML = `<div class="error">Error: ${error.message}</div>`;
                });
        });
    }

    // Password visibility toggle
    const passwordInputs = panel.querySelectorAll('.password-input-container input[type="password"]');
    passwordInputs.forEach(input => {
        const toggleBtn = input.parentElement.querySelector('.password-toggle-btn');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                const icon = toggleBtn.querySelector('i');
                if (icon) {
                    icon.className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
                }
            });
        }
    });
    
    // Update current password display when default password changes
    const defaultPasswordInput = panel.querySelector('#default_password_input');
    const currentPasswordDisplay = panel.querySelector('#current_password_display');
    if (defaultPasswordInput && currentPasswordDisplay) {
        defaultPasswordInput.addEventListener('input', () => {
            currentPasswordDisplay.textContent = defaultPasswordInput.value;
        });
    }
}

function initAdminGrades(panel) {
    // Admin grades tab switching
    const gradesTabs = panel.querySelectorAll('.grades-tab-btn');
    const gradesContents = panel.querySelectorAll('.grades-tab-content');
    
    gradesTabs.forEach(btn => {
        btn.addEventListener('click', () => {
            gradesTabs.forEach(b => b.classList.remove('active'));
            gradesContents.forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            const target = panel.querySelector(`#grades-tab-${btn.dataset.gradestab}`);
            if (target) target.classList.add('active');
        });
    });
    
    // Search users for grade management
    const searchBtn = panel.querySelector('#search-users-btn');
    const searchInput = panel.querySelector('#grade-search-input');
    
    if (searchBtn && searchInput) {
        searchBtn.addEventListener('click', searchUsers);
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') searchUsers();
        });
    }
    
    function searchUsers() {
        const query = searchInput.value.trim();
        if (!query) {
            alert('Please enter a username or email to search');
            return;
        }
        
        fetch(`farm_admin/search_users.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            const usersList = panel.querySelector('#users-list');
            if (data.success && data.users.length > 0) {
                let html = '<h4>Search Results:</h4>';
                data.users.forEach(user => {
                    const gradeNames = {1: 'SUPER_ADMIN', 2: 'ADMIN', 3: 'MODERATOR', 4: 'HELPER', 5: 'USER'};
                    html += `
                        <div class="user-item" data-user-id="${user.id}" data-username="${user.username}" data-current-grade="${user.admin_level}">
                            <div class="user-info">
                                <strong>${user.username}</strong> (${user.email})
                            </div>
                            <div class="user-grade grade-${user.admin_level}">
                                ${gradeNames[user.admin_level] || 'UNKNOWN'} (Level ${user.admin_level})
                            </div>
                            <button class="management-btn" onclick="selectUserForGradeChange(${user.id}, '${user.username}', ${user.admin_level})">
                                Change Grade
                            </button>
                        </div>
                    `;
                });
                usersList.innerHTML = html;
            } else {
                usersList.innerHTML = '<div class="error">No users found matching your search.</div>';
            }
        })
        .catch(error => {
            const usersList = panel.querySelector('#users-list');
            usersList.innerHTML = `<div class="error">Error searching users: ${error.message}</div>`;
        });
    }
    
    // Grade change form
    const gradeChangeForm = panel.querySelector('#fa-grade-change-form');
    if (gradeChangeForm) {
        gradeChangeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('farm_admin/change_user_grade.php', {
            method: 'POST',
                body: formData
        })
            .then(response => response.json())
        .then(data => {
                const resultDiv = panel.querySelector('#grade-change-result');
            if (data.success) {
                    resultDiv.innerHTML = `<div class="success">${data.message}</div>`;
                    this.style.display = 'none';
                    // Refresh the users list
                    searchUsers();
                } else {
                    resultDiv.innerHTML = `<div class="error">${data.message}</div>`;
                }
            })
            .catch(error => {
                const resultDiv = panel.querySelector('#grade-change-result');
                resultDiv.innerHTML = `<div class="error">Error: ${error.message}</div>`;
            });
        });
    }
    
    // Cancel grade change
    const cancelBtn = panel.querySelector('#cancel-grade-change');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            const form = panel.querySelector('#grade-change-form');
            if (form) form.style.display = 'none';
        });
    }
}

// Global function for selecting user for grade change
window.selectUserForGradeChange = function(userId, username, currentGrade) {
    const form = document.querySelector('#grade-change-form');
    const userIdInput = document.querySelector('#selected-user-id');
    const usernameSpan = document.querySelector('#selected-username');
    const currentGradeSpan = document.querySelector('#selected-current-grade');
    const newGradeSelect = document.querySelector('#new-admin-level');
    
    if (form && userIdInput && usernameSpan && currentGradeSpan && newGradeSelect) {
        userIdInput.value = userId;
        usernameSpan.textContent = username;
        currentGradeSpan.textContent = `${getGradeName(currentGrade)} (Level ${currentGrade})`;
        newGradeSelect.value = currentGrade;
        form.style.display = 'block';
    }
};

function getGradeName(level) {
    const gradeNames = {1: 'SUPER_ADMIN', 2: 'ADMIN', 3: 'MODERATOR', 4: 'HELPER', 5: 'USER'};
    return gradeNames[level] || 'UNKNOWN';
}

// Admin panel initialization
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('open-admin-panel');
    if (btn) {
        btn.addEventListener('click', () => {
            fetch('farm_admin/panel.php?ajax=1', { credentials: 'same-origin' })
                .then(res => res.text())
                .then(html => {
                    const temp = document.createElement('div');
                    temp.innerHTML = html.trim();
                    const panel = temp.firstChild;
                    document.body.appendChild(panel);
                    initAdminPanel(panel);
                });
        });
    }
});

        function initPvpSystem(panel) {
            const startEventBtn = panel.querySelector('#startPvpEvent');
            const stopEventBtn = panel.querySelector('#stopPvpEvent');
            const loopToggleInput = panel.querySelector('#pvp-loop-toggle-input');
            const settingsForm = panel.querySelector('#pvp-settings-form');
            const statusDisplay = panel.querySelector('#pvp-status-display');
    
    // Load current loop state
    loadLoopState();
    
    // Load timer settings
    loadTimerSettings();
    
    // START Event button
    if (startEventBtn) {
        startEventBtn.addEventListener('click', async () => {
            if (!confirm('START PVP EVENT?\n\nThis will:\n• Clean ALL existing battles\n• Enable auto-start system\n• Start new tournaments when 32+ players available\n\nContinue?')) {
                return;
            }
            
            startEventBtn.disabled = true;
            startEventBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Starting...';
            
            try {
                const response = await fetch('farm_admin/pvp_start_event.php', {
                    method: 'POST',
                    credentials: 'same-origin'
                });
                const result = await response.json();
                
                if (result.success) {
                    statusDisplay.innerHTML = `<div style="color: #4ecdc4; font-weight: bold;">✅ ${result.message}</div>`;
                    setTimeout(() => location.reload(), 2000);
                } else {
                    statusDisplay.innerHTML = `<div style="color: #ff6b6b;">❌ ${result.error}</div>`;
                }
            } catch (error) {
                statusDisplay.innerHTML = `<div style="color: #ff6b6b;">❌ Network error: ${error.message}</div>`;
            }
            
            startEventBtn.disabled = false;
            startEventBtn.innerHTML = '<i class="fas fa-play"></i> START EVENT';
        });
    }
    
    // STOP Event button
    if (stopEventBtn) {
        stopEventBtn.addEventListener('click', async () => {
            if (!confirm('STOP PVP EVENT?\n\nThis will:\n• Clean ALL existing battles\n• Disable auto-start system\n• Stop all tournaments\n\nContinue?')) {
                return;
            }
            
            stopEventBtn.disabled = true;
            stopEventBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Stopping...';
            
            try {
                const response = await fetch('farm_admin/pvp_stop_event.php', {
                    method: 'POST',
                    credentials: 'same-origin'
                });
                const result = await response.json();
                
                if (result.success) {
                    statusDisplay.innerHTML = `<div style="color: #4ecdc4; font-weight: bold;">✅ ${result.message}</div>`;
                    setTimeout(() => location.reload(), 2000);
                } else {
                    statusDisplay.innerHTML = `<div style="color: #ff6b6b;">❌ ${result.error}</div>`;
                }
            } catch (error) {
                statusDisplay.innerHTML = `<div style="color: #ff6b6b;">❌ Network error: ${error.message}</div>`;
            }
            
            stopEventBtn.disabled = false;
            stopEventBtn.innerHTML = '<i class="fas fa-stop"></i> STOP EVENT';
        });
    }
    
    // Save timer settings
    if (settingsForm) {
        settingsForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(settingsForm);
            const submitBtn = settingsForm.querySelector('button[type="submit"]');
            
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = '💾 Saving...';
            }
            
            try {
                const response = await fetch('farm_admin/pvp_save_settings.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
                const result = await response.json();
                
                if (result.success) {
                    statusDisplay.innerHTML = `<div style="color: #4ecdc4; font-weight: bold;">✅ ${result.message}</div>`;
                } else {
                    statusDisplay.innerHTML = `<div style="color: #ff6b6b;">❌ ${result.error}</div>`;
                }
            } catch (error) {
                statusDisplay.innerHTML = `<div style="color: #ff6b6b;">❌ Network error: ${error.message}</div>`;
            }
            
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = '💾 Save & Apply Now';
            }
        });
    }
    
    // Loop toggle control
    if (loopToggleInput) {
        loopToggleInput.addEventListener('change', async () => {
            const isChecked = loopToggleInput.checked;
            const newState = isChecked ? 'enabled' : 'disabled';
            
            loopToggleInput.disabled = true;
            
            try {
                const response = await fetch('farm_admin/pvp_loop_toggle.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ action: newState }),
                    credentials: 'same-origin'
                });
                const result = await response.json();
                
                if (result.success) {
                    statusDisplay.innerHTML = `<div style="color: #4ecdc4;">✅ ${result.message}</div>`;
                    loopToggleInput.dataset.state = newState;
                } else {
                    statusDisplay.innerHTML = `<div style="color: #ff6b6b;">❌ ${result.error}</div>`;
                    // Revert toggle state
                    loopToggleInput.checked = !isChecked;
                }
            } catch (error) {
                statusDisplay.innerHTML = `<div style="color: #ff6b6b;">❌ Network error: ${error.message}</div>`;
                // Revert toggle state
                loopToggleInput.checked = !isChecked;
            }
            
            loopToggleInput.disabled = false;
        });
    }
    
    // Function to load current loop state from database
    async function loadLoopState() {
        try {
            const response = await fetch('farm_admin/pvp_get_loop_state.php', {
                method: 'GET',
                credentials: 'same-origin'
            });
            const result = await response.json();
            
            if (result.success) {
                loopToggleInput.checked = result.loop_enabled;
                loopToggleInput.dataset.state = result.loop_enabled ? 'enabled' : 'disabled';
            }
        } catch (error) {
            console.error('Error loading loop state:', error);
        }
    }
    
    // Function to load timer settings from database
    async function loadTimerSettings() {
        try {
            const response = await fetch('farm_admin/pvp_get_settings.php', {
                method: 'GET',
                credentials: 'same-origin'
            });
            const result = await response.json();
            
            if (result.success) {
                const battleDurationInput = document.getElementById('battle_duration');
                const finalDisplayInput = document.getElementById('final_display');
                
                if (battleDurationInput) {
                    battleDurationInput.value = result.battle_duration;
                }
                if (finalDisplayInput) {
                    finalDisplayInput.value = result.final_display;
                }
            }
        } catch (error) {
            console.error('Error loading timer settings:', error);
        }
    }
    
}