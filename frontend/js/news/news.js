const API_URL = 'http://localhost:8000/api/v1';
const token = localStorage.getItem('token');
let currentPage = 1;
let lastPage = 1;

async function loadNews(page = 1) {
    const res = await fetch(`${API_URL}/news?page=${page}`);
    const data = await res.json();
    const newsList = document.getElementById('news-list');
    newsList.innerHTML = '';

    data.data.forEach(n => {
    newsList.innerHTML += `
        <li class="p-5 bg-white/80 dark:bg-gray-800/90 rounded-2xl shadow-md dark:text-gray-100 transition-colors duration-500">
        <strong class="text-gray-800 dark:text-gray-100">${n.title}</strong>  
        <span class="text-sm text-gray-600 dark:text-gray-400"> - <a href="${n.username}">${n.username}</a></span>  
        <p class="mt-1 text-gray-700 dark:text-gray-300">${n.content}</p>
        ${n.author && n.author === localStorage.getItem('user_name') ? `
        <div class="flex gap-2 mt-2">
            <button onclick='editNews(${n.id}, ${JSON.stringify(n.title)}, ${JSON.stringify(n.content)}, ${n.category_id || "null"})' class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded transition-colors duration-300">Editar</button>
            <button onclick="deleteNews(${n.id})" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded transition-colors duration-300">Excluir</button>
        </div>` : ''}
        </li>`;
    });

    currentPage = data.meta.current_page;
    lastPage = data.meta.last_page;
    document.getElementById('page-info').innerText = `Página ${currentPage} de ${lastPage}`;
}

async function saveNews() {
    const id = document.getElementById('news-id').value;
    const title = document.getElementById('news-title').value;
    const content = document.getElementById('news-content').value;
    if (!title || !content) return alert('Título e conteúdo são obrigatórios!');

    const url = id ? `${API_URL}/news/${id}` : `${API_URL}/news`;
    const method = id ? 'PUT' : 'POST';

    await fetch(url, {
    method,
    headers: {
        'Content-Type': 'application/json', 
        'Authorization': 'Bearer ' + token
    },
        body: JSON.stringify({
            title, 
            content
        })
    });

    resetForm();
    loadNews(currentPage);
}

function editNews(id, title, content) {
    document.getElementById('news-id').value = id;
    document.getElementById('news-title').value = title;
    document.getElementById('news-content').value = content;
    window.scrollTo(0,0);
}

function resetForm() {
    document.getElementById('news-id').value = '';
    document.getElementById('news-title').value = '';
    document.getElementById('news-content').value = '';
}

async function deleteNews(id) {
    if (!confirm('Deseja realmente excluir esta notícia?')) return;
    await fetch(`${API_URL}/news/${id}`, {
    method: 'DELETE',
    headers: {'Authorization': 'Bearer ' + token}
    });
    loadNews(currentPage);
}

function prevPage() {
    if (currentPage > 1) loadNews(currentPage - 1);
}

function nextPage() {
    if (currentPage < lastPage) loadNews(currentPage + 1);
}

// Carregar notícias ao abrir
loadNews();