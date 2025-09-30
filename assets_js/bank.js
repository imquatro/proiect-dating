document.addEventListener('DOMContentLoaded', () => {
    const bank = document.getElementById('bank');
    if (!bank) return;
    const buttons = bank.querySelectorAll('.bank-btn');
    const tabs = bank.querySelectorAll('.bank-tab');

    function numberFormat(n) {
        return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function moneyHtml(n) {
        return `<span class="money-amount"><img src="img/money.png" class="money-icon" alt="">${numberFormat(n)}</span>`;
    }

    function updateLimit(info) {
        const el = document.getElementById('depositLimit');
        const btn = document.getElementById('depositBtn');
        if (!el || !btn || !info) return;
        const { count = 0, max = 0, vip = false, vip_max = 5, base_max = 2 } = info;
        const baseCount = Math.min(count, base_max);
        let text = `Deposits today: ${baseCount}/${base_max}`;
        if (vip) {
            text += ` | VIP: ${count}/${vip_max}`;
        }
        el.textContent = text;
        btn.disabled = count >= max;
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
                    updateLimit(data);
                })
                .catch(() => {
                    btn.disabled = false;
                });
        });
        loadActive('activeDeposits');
    }

    function updatePreview() {
        const hours = parseInt(document.getElementById('depositHours').value, 10);
        const actualHours = hours + 1;
        const amount = 1000000;
        const interest = hours * 1000;
        const final = amount + interest;
        document.getElementById('depositPreview').innerHTML = `Deposit: ${moneyHtml(amount)} | Interest: ${moneyHtml(interest)} | Final after ${actualHours}h: ${moneyHtml(final)}`;
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
        document.getElementById('loanPreview').innerHTML = `Borrow: ${moneyHtml(amount)} | Payback: ${moneyHtml(due)}`;
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
                    let html = `<div>Borrowed: ${moneyHtml(loan.amount)}</div><div>Remaining: ${moneyHtml(remaining)}</div>`;
                    if (loan.payments && loan.payments.length) {
                        html += '<ul>';
                        loan.payments.forEach(p => {
                            html += `<li>${p.quantity}x ${p.item_name} - ${moneyHtml(p.applied)}</li>`;
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
                updateLimit(data);
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
                    div.innerHTML = `
                        <div>Deposit: ${moneyHtml(dep.amount)}</div>
                        <div>Final: ${moneyHtml(final)}</div>
                        <div class="countdown" data-end="${dep.display_end}" data-id="${dep.id}"></div>
                        <button class="cancel-btn" data-id="${dep.id}">Cancel</button>
                        <button class="claim-btn" data-id="${dep.id}" style="display:none;">Claim</button>
                    `;
                    container.appendChild(div);
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
                                    updateLimit(res);
                                    loadActive(containerId);
                                }
                            });
                    });
                    const claimBtn = div.querySelector('.claim-btn');
                    claimBtn.addEventListener('click', () => {
                        claimBtn.disabled = true;
                        fetch('bank_api.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `action=claim&id=${dep.id}&force=1`
                        })
                            .then(r => r.json())
                            .then(res => {
                                if (res.error) {
                                    const m = document.getElementById('depositMessage');
                                    if (m) m.textContent = res.error;
                                    claimBtn.disabled = false;
                                } else {
                                    updateLimit(res);
                                    loadActive(containerId);
                                }
                            });
                    });
                });
                startCountdown(container);
            });
    }

    function startCountdown(container) {
        const depDivs = container.querySelectorAll('.active-deposit');
        function tick() {
            const now = Date.now();
            depDivs.forEach(div => {
                const el = div.querySelector('.countdown');
                const end = new Date(el.dataset.end).getTime();
                let diff = end - now;
                if (diff <= 0) {
                    el.textContent = '00:00:00';
                    const cancelBtn = div.querySelector('.cancel-btn');
                    if (cancelBtn) cancelBtn.style.display = 'none';
                    const claimBtn = div.querySelector('.claim-btn');
                    if (claimBtn) claimBtn.style.display = 'inline-block';
                    return;
                }
                diff = Math.max(0, diff);
                const h = Math.floor(diff / 3600000);
                diff %= 3600000;
                const m = Math.floor(diff / 60000);
                diff %= 60000;
                const s = Math.floor(diff / 1000);
                el.textContent = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
            });
        }
        tick();
        setInterval(tick, 1000);
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
                        li.innerHTML = `${new Date(dep.start_time).toLocaleString()} - ${new Date(dep.display_end).toLocaleString()} : ${moneyHtml(final)}`;
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
                        li.innerHTML = `${start.toLocaleString()} - ${end.toLocaleString()} | Borrowed: ${moneyHtml(l.amount)} | Sales: ${l.payments} | Duration: ${years}y ${days}d ${hours}h ${mins}m ${secs}s`;
                        ulL.appendChild(li);
                    });
                    loanContainer.appendChild(ulL);
                }
            });
    }

    initDeposit();
});
