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
                    div.innerHTML = `
                        <div>Deposit: ${numberFormat(dep.amount)}</div>
                        <div>Final: ${numberFormat(final)}</div>
                        <div class="countdown" data-end="${dep.end_time}"></div>
                        <button class="cancel-btn" data-id="${dep.id}">Cancel</button>
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
                                    updateLimit(res.remaining);
                                    loadActive(containerId);
                                }
                            });
                    });
                });
                startCountdown(container);
            });
    }

    function startCountdown(container) {
        const depEls = container.querySelectorAll('.countdown');
        function tick() {
            const now = Date.now();
            depEls.forEach(el => {
                const end = new Date(el.dataset.end).getTime();
                let diff = Math.max(0, end - now);
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
                const container = document.getElementById('historyDeposits');
                container.innerHTML = '';
                if (!data.history || data.history.length === 0) {
                    container.innerHTML = '<p>No history.</p>';
                    return;
                }
                const ul = document.createElement('ul');
                data.history.forEach(dep => {
                    const final = dep.amount + dep.interest;
                    const li = document.createElement('li');
                    li.textContent = `${new Date(dep.start_time).toLocaleString()} - ${new Date(dep.end_time).toLocaleString()} : ${numberFormat(final)}`;
                    ul.appendChild(li);
                });
                container.appendChild(ul);
            });
    }

    initDeposit();
});