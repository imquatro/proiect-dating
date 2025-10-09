<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/includes/db.php';

$user_id = $_SESSION['user_id'];
require_once __DIR__ . '/includes/update_last_active.php';

$stmt = $db->prepare('SELECT money, gold FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$wallet = $stmt->fetch(PDO::FETCH_ASSOC);
$money = $wallet ? (int)$wallet['money'] : 0;
$gold = $wallet ? (int)$wallet['gold'] : 0;

$moneyFormatted = number_format($money, 0, '.', '.');
$goldFormatted = number_format($gold, 0, '.', '.');

ob_start();
?>
<style>
/* Settings overlay - NU acoperă meniurile template-ului */
.settings-overlay {
    position: fixed;
    top: 60px;
    left: 0;
    width: 100%;
    height: calc(100vh - 120px);
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

/* Asigură că meniurile template-ului rămân vizibile */
.top-bar {
    z-index: 1001 !important;
}

.bottom-nav {
    z-index: 1001 !important;
}

.settings-panel {
    width: 90%;
    max-width: 600px;
    height: calc(100vh - 140px);
    max-height: calc(100vh - 140px);
    background: url('img/bg2.png') center/cover no-repeat !important;
    border-radius: 12px;
    box-shadow: 0 0 30px rgba(255, 255, 255, 0.6);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.settings-header {
    background: #ffe9a3;
    padding: 15px 20px;
    color: #4a3a00;
    border-bottom: 2px solid #f6cf49;
    border-radius: 12px 12px 0 0;
}

.settings-nav {
    display: flex;
    gap: 8px;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.nav-btn {
    padding: 8px 16px;
    background: #ffe9a3;
    border: 1px solid #f6cf49;
    border-radius: 6px;
    color: #6c4e09;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    font-size: 14px;
    flex: 1;
    text-align: center;
    cursor: pointer;
}

.nav-btn:hover {
    background: #f6cf49;
    border-color: #e6b800;
}

.nav-btn.active {
    background: #e6b800;
    border-color: #d4a000;
    color: #fff;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.settings-content {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: url('img/bg2.png') center/cover no-repeat !important;
    scrollbar-width: none !important;
    -ms-overflow-style: none !important;
}

.settings-content::-webkit-scrollbar {
    display: none !important;
}

/* Design original pentru bank - identic cu money.css */
.bank-header-img {
    display: none !important;
}

.bank-buttons {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 4px;
    margin: 10px 0;
}

.bank-btn {
    padding: 6px 4px;
    background: #ffe9a3;
    border: 1px solid #f6cf49;
    color: #6c4e09;
    cursor: pointer;
    border-radius: 4px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.bank-btn:hover {
    background: #f6cf49;
    transform: translateY(-1px);
}

.bank-btn.active {
    background: #f6cf49;
}

.bank-tab {
    display: none;
    margin-top: 10px;
    color: #fff;
}

.bank-tab.active {
    display: block;
}

.deposit-form label, .loan-form label {
    display: block;
    margin: 8px 0;
    color: #fff;
    font-weight: 500;
}

.deposit-form input, .loan-form input {
    width: 100%;
    padding: 10px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    font-size: 14px;
    box-sizing: border-box;
}

.deposit-form input::placeholder, .loan-form input::placeholder {
    color: rgba(255, 255, 255, 0.7);
}

.deposit-preview {
    margin: 6px 0;
    font-weight: bold;
    color: #ffd700;
}

.deposit-message {
    margin-top: 6px;
    color: #ff8080;
}

.active-deposit {
    margin-top: 10px;
    padding: 8px;
    border: 1px solid #f6cf49;
    text-align: center;
    background: rgba(0, 0, 0, 0.3);
    border-radius: 6px;
    color: #fff;
}

.apply-frame-btn {
    background: linear-gradient(135deg, #ffd700, #ffb300);
    color: #333;
    border: none;
    padding: 12px 24px;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin: 10px 5px;
}

.apply-frame-btn:hover {
    background: linear-gradient(135deg, #ffb300, #ff8f00);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 179, 0, 0.4);
}

.apply-frame-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* Responsive */
@media (max-width: 768px) {
    .settings-panel {
        width: 95%;
        height: 95vh;
    }
    
    .settings-nav {
        gap: 6px;
    }
    
    .nav-btn {
        padding: 6px 12px;
        font-size: 12px;
    }
    
    .bank-buttons {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="settings-overlay">
    <div class="settings-panel">
        <div class="settings-header">
            <h2 style="margin: 0; text-align: center;">Bank Settings</h2>
            
            <div class="settings-nav">
                <a href="settings_profile.php" class="nav-btn">Profile</a>
                <a href="settings_bank.php" class="nav-btn active">Bank</a>
                <a href="settings_helpers.php" class="nav-btn">Helpers</a>
                <?php
                $stmt = $db->prepare('SELECT is_admin FROM users WHERE id = ?');
                $stmt->execute([$_SESSION['user_id']]);
                $isAdmin = $stmt->fetchColumn() == 1;
                if ($isAdmin): ?>
                <a href="settings_admin.php" class="nav-btn">Admin Panel</a>
                <?php endif; ?>
                <a href="#" class="nav-btn" id="logoutBtn">Logout</a>
            </div>
        </div>

        <div class="settings-content">
            <div class="active-deposit">
                <div style="display: flex; justify-content: center; align-items: center; gap: 15px;">
                    <img src="img/money.png" style="width: 24px; height: 24px;" alt="Money">
                    <span style="font-weight: bold; font-size: 18px;"><?= $moneyFormatted ?></span>
                    <img src="img/gold.png" style="width: 24px; height: 24px;" alt="Gold">
                    <span style="font-weight: bold; font-size: 18px;"><?= $goldFormatted ?></span>
                </div>
            </div>

            <div class="bank-buttons">
                <button class="bank-btn active" data-banktab="deposit">Deposit</button>
                <button class="bank-btn" data-banktab="loan">Loan</button>
            </div>

            <div class="bank-tab active" id="bank-deposit">
                <div class="deposit-form">
                    <label>Deposit Amount</label>
                    <input type="number" id="depositAmount" placeholder="Enter amount to deposit" min="1">
                    <div class="deposit-preview" id="depositPreview"></div>
                    <button class="apply-frame-btn" id="depositBtn">Make Deposit</button>
                    <div class="deposit-message" id="depositMessage"></div>
                </div>
            </div>

            <div class="bank-tab" id="bank-loan">
                <div class="loan-form">
                    <label>Loan Amount</label>
                    <input type="number" id="loanAmount" placeholder="Enter loan amount" min="1">
                    <div class="deposit-preview" id="loanPreview"></div>
                    <button class="apply-frame-btn" id="loanBtn">Request Loan</button>
                    <div class="deposit-message" id="loanMessage"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Bank tabs switching - identic cu bank.js original
    const bank = document.querySelector('.settings-content');
    if (!bank) return;
    
    const buttons = bank.querySelectorAll('.bank-btn');
    const tabs = bank.querySelectorAll('.bank-tab');

    function switchTab(target) {
        buttons.forEach(b => b.classList.remove('active'));
        tabs.forEach(t => t.classList.remove('active'));
        const btn = bank.querySelector(`.bank-btn[data-banktab="${target}"]`);
        const tab = bank.querySelector(`#bank-${target}`);
        if (btn && tab) {
            btn.classList.add('active');
            tab.classList.add('active');
        }
    }

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-banktab');
            switchTab(target);
        });
    });

    // Deposit functionality
    const depositBtn = document.getElementById('depositBtn');
    const depositAmount = document.getElementById('depositAmount');
    const depositPreview = document.getElementById('depositPreview');
    const depositMessage = document.getElementById('depositMessage');
    
    if (depositAmount && depositPreview) {
        depositAmount.addEventListener('input', () => {
            const amount = parseInt(depositAmount.value) || 0;
            if (amount > 0) {
                depositPreview.textContent = `You will deposit: ${amount.toLocaleString()} coins`;
            } else {
                depositPreview.textContent = '';
            }
        });
    }
    
    if (depositBtn) {
        depositBtn.addEventListener('click', () => {
            const amount = parseInt(depositAmount.value);
            if (amount > 0) {
                // Here you would make an AJAX call to process deposit
                depositMessage.textContent = 'Deposit functionality will be implemented here';
                depositMessage.style.color = '#ffd700';
            } else {
                depositMessage.textContent = 'Please enter a valid amount';
                depositMessage.style.color = '#ff8080';
            }
        });
    }

    // Loan functionality
    const loanBtn = document.getElementById('loanBtn');
    const loanAmount = document.getElementById('loanAmount');
    const loanPreview = document.getElementById('loanPreview');
    const loanMessage = document.getElementById('loanMessage');
    
    if (loanAmount && loanPreview) {
        loanAmount.addEventListener('input', () => {
            const amount = parseInt(loanAmount.value) || 0;
            if (amount > 0) {
                loanPreview.textContent = `You will borrow: ${amount.toLocaleString()} coins`;
            } else {
                loanPreview.textContent = '';
            }
        });
    }
    
    if (loanBtn) {
        loanBtn.addEventListener('click', () => {
            const amount = parseInt(loanAmount.value);
            if (amount > 0) {
                // Here you would make an AJAX call to process loan
                loanMessage.textContent = 'Loan functionality will be implemented here';
                loanMessage.style.color = '#ffd700';
            } else {
                loanMessage.textContent = 'Please enter a valid amount';
                loanMessage.style.color = '#ff8080';
            }
        });
    }

    // Close overlay when clicking outside - revine la welcome.php
    const overlay = document.querySelector('.settings-overlay');
    if (overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                window.location.href = 'welcome.php';
            }
        });
    }

    // Logout confirmation
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();

$pageTitle = 'Bank Settings';
$pageCss = '';

include 'template.php';
?>
