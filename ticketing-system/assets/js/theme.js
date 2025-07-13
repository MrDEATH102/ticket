// theme.js
function setTheme(mode) {
    document.documentElement.setAttribute('data-theme', mode);
    localStorage.setItem('theme', mode);
}
function toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme') || 'light';
    setTheme(current === 'light' ? 'dark' : 'light');
}
(function () {
    const saved = localStorage.getItem('theme') || 'light';
    setTheme(saved);
    // دکمه سوییچ
    let btn = document.createElement('button');
    btn.className = 'toggle-theme';
    btn.title = 'تغییر حالت روشن/تاریک';
    btn.innerHTML = '<span id="theme-icon">🌙</span>';
    btn.onclick = function() {
        toggleTheme();
        document.getElementById('theme-icon').textContent = document.documentElement.getAttribute('data-theme') === 'dark' ? '☀️' : '🌙';
    };
    document.body.appendChild(btn);
    // آیکون اولیه
    document.getElementById('theme-icon').textContent = saved === 'dark' ? '☀️' : '🌙';
})(); 