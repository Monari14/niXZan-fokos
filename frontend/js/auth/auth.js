const API_URL = 'http://localhost:8000/api/v1';
function setToken(token) { localStorage.setItem('token', token); }

async function login() {
    const loginValue = document.getElementById('login').value;
    const password = document.getElementById('password').value;

    const res = await fetch(`${API_URL}/auth/login`, {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({login: loginValue, password})
    });
    const data = await res.json();
    if(data.token) {
    setToken(data.token);
    window.location.href = 'news.html';
    } else alert(JSON.stringify(data));
}

async function register() {
    const name = document.getElementById('name').value;
    const username = document.getElementById('username').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('reg-password').value;
    const password_confirmation = document.getElementById('password_confirmation').value;

    const res = await fetch(`${API_URL}/auth/register`, {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({name, username, email, password, password_confirmation})
    });
    const data = await res.json();
    if(data.token) {
    setToken(data.token);
    window.location.href = 'news.html';
    } else alert(JSON.stringify(data));
}