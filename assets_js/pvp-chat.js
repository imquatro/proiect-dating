// PvP Chat Live - Logica chat, polling, notificări
(function() {
    let currentMatchId = null;
    let chatPollInterval = null;
    let notificationPollInterval = null;
    let lastMessageId = 0;

    // Pornește chat-ul pentru un meci
    window.initPvpChat = function(matchId) {
        if (!matchId) return;
        
        currentMatchId = matchId;
        lastMessageId = 0;
        
        // Încarcă mesajele existente
        loadChatMessages();
        
        // Pornește polling pentru mesaje noi (la 3 secunde)
        if (chatPollInterval) clearInterval(chatPollInterval);
        chatPollInterval = setInterval(loadChatMessages, 3000);
        
        // Marchează ca citite când se deschide
        markChatAsRead();
    };

    // Oprește chat-ul
    window.stopPvpChat = function() {
        if (chatPollInterval) {
            clearInterval(chatPollInterval);
            chatPollInterval = null;
        }
        currentMatchId = null;
        lastMessageId = 0;
    };

    // Încarcă mesajele din chat
    function loadChatMessages() {
        if (!currentMatchId) return;

        fetch(`pvp_chat_api.php?action=get_messages&match_id=${currentMatchId}`)
            .then(r => r.json())
            .then(data => {
                if (data.messages) {
                    renderChatMessages(data.messages);
                    
                    // Update last message ID
                    if (data.messages.length > 0) {
                        const lastMsg = data.messages[data.messages.length - 1];
                        if (lastMsg.id > lastMessageId) {
                            lastMessageId = lastMsg.id;
                            
                            // Scroll la ultimul mesaj
                            scrollChatToBottom();
                        }
                    }
                }
            })
            .catch(err => console.error('Error loading chat:', err));
    }

    // Render mesaje în UI
    function renderChatMessages(messages) {
        const container = document.getElementById('chatMessages');
        if (!container) return;

        if (messages.length === 0) {
            container.innerHTML = '<div class="chat-empty">Trimite primul mesaj!</div>';
            return;
        }

        container.innerHTML = '';

        messages.forEach(msg => {
            const isOwn = msg.user_id == window.userId;
            const messageDiv = document.createElement('div');
            messageDiv.className = `chat-message ${isOwn ? 'own' : 'other'}`;
            
            const time = new Date(msg.created_at);
            const timeStr = `${time.getHours().toString().padStart(2, '0')}:${time.getMinutes().toString().padStart(2, '0')}`;
            
            const vipClass = msg.vip == 1 ? 'gold-shimmer' : '';
            
            messageDiv.innerHTML = `
                <div class="chat-message-header">
                    ${!isOwn ? `<img src="${msg.photo || 'default-avatar.png'}" class="chat-message-avatar">` : ''}
                    <span class="chat-message-name ${vipClass}">${msg.username}</span>
                    <span class="chat-message-time">${timeStr}</span>
                </div>
                <div class="chat-message-bubble">${escapeHtml(msg.message)}</div>
            `;
            
            container.appendChild(messageDiv);
        });
    }

    // Scroll la jos
    function scrollChatToBottom() {
        const container = document.getElementById('chatMessages');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    }

    // Trimite mesaj
    window.sendChatMessage = function() {
        const input = document.getElementById('chatInput');
        if (!input || !currentMatchId) return;

        const message = input.value.trim();
        if (!message) return;

        const formData = new FormData();
        formData.append('action', 'send_message');
        formData.append('match_id', currentMatchId);
        formData.append('message', message);

        fetch('pvp_chat_api.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                input.value = '';
                loadChatMessages(); // Reîncarcă pentru a arăta mesajul
            } else if (data.error) {
                alert(data.error);
            }
        })
        .catch(err => console.error('Error sending message:', err));
    };

    // Enter pentru a trimite
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('chatInput');
        if (input) {
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    sendChatMessage();
                }
            });
        }
    });

    // Toggle emoji picker
    window.toggleEmojiPicker = function() {
        const picker = document.getElementById('emojiPicker');
        if (picker) {
            picker.style.display = picker.style.display === 'none' ? 'grid' : 'none';
        }
    };

    // Insert emoji
    window.insertEmoji = function(emoji) {
        const input = document.getElementById('chatInput');
        if (input) {
            input.value += emoji;
            input.focus();
        }
        toggleEmojiPicker();
    };

    // Închide emoji picker când se dă click în afară
    document.addEventListener('click', (e) => {
        const picker = document.getElementById('emojiPicker');
        const emojiBtn = document.querySelector('.emoji-btn');
        
        if (picker && picker.style.display === 'grid' && 
            !picker.contains(e.target) && 
            e.target !== emojiBtn) {
            picker.style.display = 'none';
        }
    });

    // Marchează ca citit
    function markChatAsRead() {
        if (!currentMatchId) return;

        const formData = new FormData();
        formData.append('action', 'mark_read');
        formData.append('match_id', currentMatchId);

        fetch('pvp_chat_api.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Update notificări
                updateChatNotifications();
            }
        })
        .catch(err => console.error('Error marking as read:', err));
    }

    // ===== SISTEM NOTIFICĂRI =====

    // Pornește polling pentru notificări
    function startNotificationPolling() {
        // Check imediat
        updateChatNotifications();
        
        // Apoi la fiecare 5 secunde
        if (notificationPollInterval) clearInterval(notificationPollInterval);
        notificationPollInterval = setInterval(updateChatNotifications, 5000);
    }

    // Update notificări
    function updateChatNotifications() {
        if (!window.userId) return;

        fetch('pvp_chat_api.php?action=get_unread_count')
            .then(r => r.json())
            .then(data => {
                const count = data.unread_count || 0;
                
                // Update indicator top-bar
                updateTopBarIndicator(count);
                
                // Update badge pe tabs și butoane (dacă suntem pe pagina PvP)
                if (window.location.pathname.includes('pvp_battles.php')) {
                    updatePvpPageBadges(count);
                }
            })
            .catch(err => console.error('Error checking notifications:', err));
    }

    // Update indicator în top-bar (DISABLED - nu mai avem iconița)
    function updateTopBarIndicator(count) {
        // Nu mai avem indicator în top-bar
        // Iconița a fost înlăturată la cererea userului
        return;
    }

    // REMOVED: Click pe indicator top-bar (nu mai există)
    // Popup-ul se deschide DOAR din bracket acum

    // Update badges pe pagina PvP (tabs și butoane)
    function updatePvpPageBadges(count) {
        // Găsim meciul userului curent
        fetch('pvp_api.php?action=get_user_current_match')
            .then(r => r.json())
            .then(data => {
                if (!data.has_match) return;

                const matchId = data.match_id;
                
                // Badge pe butonul popup al match card-ului
                const matchCard = document.querySelector(`[data-match-id="${matchId}"]`);
                if (matchCard) {
                    let badge = matchCard.querySelector('.match-chat-badge');
                    if (count > 0) {
                        if (!badge) {
                            badge = document.createElement('span');
                            badge.className = 'match-chat-badge';
                            matchCard.appendChild(badge);
                        }
                        badge.textContent = count;
                    } else if (badge) {
                        badge.remove();
                    }
                }

                // Badge pe tab ligă (folosim league_id din răspuns)
                // Acest lucru va fi implementat în pvp.js prin event
                window.dispatchEvent(new CustomEvent('pvp:updateChatBadge', { 
                    detail: { count, matchId } 
                }));
            });
    }

    // Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Pornește polling la load
    document.addEventListener('DOMContentLoaded', () => {
        if (window.userId) {
            startNotificationPolling();
        }
    });

    // Cleanup la părăsire
    window.addEventListener('beforeunload', () => {
        stopPvpChat();
        if (notificationPollInterval) {
            clearInterval(notificationPollInterval);
        }
    });
})();

