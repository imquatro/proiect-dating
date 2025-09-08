document.addEventListener('DOMContentLoaded', () => {
    const bank = document.getElementById('bank');
    if (!bank) return;
    const buttons = bank.querySelectorAll('.bank-btn');
    const tabs = bank.querySelectorAll('.bank-tab');

    function numberFormat(n) {
        return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function updateLimit(n) {
        const el = document.getElementById('depositLimit');
        const btn = document.getElementById('depositBtn');
        if (!el || !btn || typeof n !== 'number') return;
        if (n > 0) {
            el.textContent = `Deposits remaining today: ${n}`;
            btn.disabled = false;
        } else {
            el.textContent = 'Daily deposit limit reached';
            btn.disabled = true;
        }
    }

    function updateLoanLimit(n) {
        const el = document.getElementById('loanLimit');
        const btn = document.getElementById('loanBtn');
        if (!el || !btn || typeof n !== 'number') return;
        if (n > 0) {
            el.textContent = `Loans remaining today: ${n}`;
            btn.disabled = false;
        } else {
            el.textContent = 'Daily loan limit reached';
            btn.disabled = true;
        }
    }

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
            if (target === 'deposit') initDeposit();
            if (target === 'loan') initLoan();
            if (target === 'account') loadActive('accountDeposits');
            if (target === 'history') loadHistory();
        });
    });

    function initDeposit() {
        const select = document.getElementById('depositHours');
        if (select && !select.options.length) {
            for (let i = 1; i <= 24; i++) {
                const opt = document.createElement('option');
                opt.value = i;
                opt.textContent = `${i}h`;
                select.appendChild(opt);
            }
        }
        updatePreview();
        select.addEventListener('change', updatePreview);
        const btn = document.getElementById('depositBtn');
        btn.addEventListener('click', () => {
            const hours = parseInt(select.value, 10);
            btn.disabled = true;
            fetch('bank_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=deposit&hours=${hours}`
            })
                .then(r => r.json())
                .then(data => {
                    const msg = document.getElementById('depositMessage');
                    if (data.error) {
                        if (data.error === 'Not enough funds') {
                            msg.textContent = 'You need 1,000,000 coins to deposit.';
                        } else {
                            msg.textContent = data.error;
                        }
                    } else {
                        msg.style.color = '#fff';
                        msg.textContent = 'Deposit successful';
                        loadActive('activeDeposits');
                    }
                    updateLimit(data.remaining);
                })
                .catch(() => {
                    btn.disabled = false;
                });
        });
        loadActive('activeDeposits');
    }

    function updatePreview() {
        const hours = parseInt(document.getElementById('depositHours').value, 10);
        const amount = 1000000;
        const interest = hours * 100;
        const final = amount + interest;
        document.getElementById('depositPreview').textContent = `Deposit: ${numberFormat(amount)} | Interest: ${numberFormat(interest)} | Final after ${hours}h: ${numberFormat(final)}`;
    }

    function initLoan() {
        const slider = document.getElementById('loanAmount');
        slider.addEventListener('input', updateLoanPreview);
        updateLoanPreview();
        const btn = document.getElementById('loanBtn');
        btn.addEventListener('click', () => {
            const amount = parseInt(slider.value, 10);
            btn.disabled = true;
            fetch('bank_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=loan&amount=${amount}`
            })
                .then(r => r.json())
                .then(data => {
                    const msg = document.getElementById('loanMessage');
                    if (data.error) {
                        msg.textContent = data.error;
                    } else {
                        msg.style.color = '#fff';
                        msg.textContent = 'Loan granted';
                        loadLoans();
                    }
                    updateLoanLimit(data.remaining);
                })
                .catch(() => {
                    btn.disabled = false;
                });
        });
        loadLoans();
    }

    function updateLoanPreview() {
        const amount = parseInt(document.getElementById('loanAmount').value, 10);
        const due = amount * 2;
        document.getElementById('loanPreview').textContent = `Borrow: ${numberFormat(amount)} | Payback: ${numberFormat(due)}`;
    }

    function loadLoans() {
        fetch('bank_api.php?action=loan_active')
            .then(r => r.json())
            .then(data => {
                updateLoanLimit(data.remaining);
                const container = document.getElementById('activeLoans');
                const msg = document.getElementById('loanMessage');
                if (msg) msg.textContent = '';
                container.innerHTML = '';
                if (!data.loans || data.loans.length === 0) {
                    container.innerHTML = '<p>No active loans.</p>';
                    return;
                }
                data.loans.forEach(loan => {
                    const div = document.createElement('div');
                    div.className = 'active-deposit';
                    const remaining = loan.amount_due - loan.amount_repaid;
                    let html = `<div>Borrowed: ${numberFormat(loan.amount)}</div><div>Remaining: ${numberFormat(remaining)}</div>`;
                    if (loan.payments && loan.payments.length) {
                        html += '<ul>';
                        loan.payments.forEach(p => {
                            html += `<li>${p.quantity}x ${p.item_name} - ${numberFormat(p.applied)}</li>`;
                        });
                        html += '</ul>';
                    }
                    div.innerHTML = html;
                    container.appendChild(div);
                });
            });
    }

    function loadActive(containerId) {
        fetch('bank_api.php?action=active')
            .then(r => r.json())
            .then(data => {
                updateLimit(data.remaining);
                const container = document.getElementById(containerId);
                const msg = document.getElementById('depositMessage');
                if (msg) msg.textContent = '';
                container.innerHTML = '';
                if (!data.deposits || data.deposits.length === 0) {
                    container.innerHTML = '<p>No active deposits.</p>';
                    return;
                }
                data.deposits.forEach(dep => {
                    const div = document.createElement('div');
                    div.className = 'active-deposit';
                    const final = dep.amount + dep.interest;
                    const countdown = dep.matured ? '<div class="countdown">00:00:00</div>' : `<div class="countdown" data-end="${dep.end_time}"></div>`;
                    const button = dep.matured ? `<button class="claim-btn" data-id="${dep.id}">Claim</button>` : `<button class="cancel-btn" data-id="${dep.id}">Cancel</button>`;
                    div.innerHTML = `
                        <div>Deposit: ${numberFormat(dep.amount)}</div>
                        <div>Final: ${numberFormat(final)}</div>
                        ${countdown}
                        ${button}
                    `;
                    container.appendChild(div);
                    if (dep.matured) {
                        const claimBtn = div.querySelector('.claim-btn');
                        claimBtn.addEventListener('click', () => {
                            claimBtn.disabled = true;
                            fetch('bank_api.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: `action=claim&id=${dep.id}`
                            })
                                .then(r => r.json())
                                .then(res => {
                                    if (res.error) {
                                        const m = document.getElementById('depositMessage');
                                        if (m) m.textContent = res.error;
                                        claimBtn.disabled = false;
                                    } else {
                                        if (window.showFloatingText) {
                                            window.showFloatingText(claimBtn, { money: res.interest });
                                        }
                                        updateLimit(res.remaining);
                                        loadActive(containerId);
                                    }
                                });
                        });
                    } else {
                        const cancelBtn = div.querySelector('.cancel-btn');
                        cancelBtn.addEventListener('click', () => {
                            cancelBtn.disabled = true;
                            fetch('bank_api.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: `action=cancel&id=${dep.id}`
                            })
                                .then(r => r.json())
                                .then(res => {
                                    if (res.error) {
                                        const m = document.getElementById('depositMessage');
                                        if (m) m.textContent = res.error;
                                        cancelBtn.disabled = false;
                                    } else {
                                        updateLimit(res.remaining);
                                        loadActive(containerId);
                                    }
                                });
                        });
                    }
                });
                startCountdown(container, containerId);
            });
    }

    function startCountdown(container, containerId) {
        const depEls = container.querySelectorAll('.countdown[data-end]');
        if (!depEls.length) return;
        let timer;
        function update() {
            const now = Date.now();
            let reload = false;
            depEls.forEach(el => {
                const end = new Date(el.dataset.end).getTime();
                let diff = end - now;
                if (diff <= 0) {
                    diff = 0;
                    reload = true;
                }
                const h = Math.floor(diff / 3600000);
                diff %= 3600000;
                const m = Math.floor(diff / 60000);
                diff %= 60000;
                const s = Math.floor(diff / 1000);
                el.textContent = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
            });
            if (reload) {
                clearInterval(timer);
                loadActive(containerId);
            }
        }
        update();
        timer = setInterval(update, 1000);
    }

    function loadHistory() {
        fetch('bank_api.php?action=history')
            .then(r => r.json())
            .then(data => {
                const depContainer = document.getElementById('historyDeposits');
                depContainer.innerHTML = '';
                if (!data.history || data.history.length === 0) {
                    depContainer.innerHTML = '<p>No deposit history.</p>';
                } else {
                    const ul = document.createElement('ul');
                    data.history.forEach(dep => {
                        const final = dep.amount + dep.interest;
                        const li = document.createElement('li');
                        li.textContent = `${new Date(dep.start_time).toLocaleString()} - ${new Date(dep.end_time).toLocaleString()} : ${numberFormat(final)}`;
                        ul.appendChild(li);
                    });
                    depContainer.appendChild(ul);
                }
                const loanContainer = document.getElementById('historyLoans');
                loanContainer.innerHTML = '';
                if (!data.loan_history || data.loan_history.length === 0) {
                    loanContainer.innerHTML = '<p>No loan history.</p>';
                } else {
                    const ulL = document.createElement('ul');
                    data.loan_history.forEach(l => {
                        const li = document.createElement('li');
                        const start = new Date(l.start_time);
                        const end = new Date(l.repaid_time);
                        const duration = end - start;
                        const sec = Math.floor(duration / 1000);
                        const years = Math.floor(sec / 31536000);
                        const days = Math.floor((sec % 31536000) / 86400);
                        const hours = Math.floor((sec % 86400) / 3600);
                        const mins = Math.floor((sec % 3600) / 60);
                        const secs = sec % 60;
                        li.textContent = `${start.toLocaleString()} - ${end.toLocaleString()} | Borrowed: ${numberFormat(l.amount)} | Sales: ${l.payments} | Duration: ${years}y ${days}d ${hours}h ${mins}m ${secs}s`;
                        ulL.appendChild(li);
                    });
                    loanContainer.appendChild(ulL);
                }
            });
    }

    initDeposit();
});