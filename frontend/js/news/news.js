const API_URL = 'http://localhost:8000/api/v1';
const token = localStorage.getItem('token');
let currentPage = 1;
let lastPage = 1;

async function loadNews(page = 1) {
    // --- CACHE ---
    const cacheKey = `news_page_${page}`;
    const cacheStr = localStorage.getItem(cacheKey);
    let data = null;
    let now = Date.now();
    if (cacheStr) {
        try {
            const cache = JSON.parse(cacheStr);
            if (cache.expire > now) {
                data = cache.data;
            } else {
                localStorage.removeItem(cacheKey);
            }
        } catch {}
    }
    if (!data) {
        const res = await fetch(`${API_URL}/news?page=${page}`);
        data = await res.json();
        localStorage.setItem(cacheKey, JSON.stringify({ data, expire: now + 60000 })); // 60s
    }
    // --- FIM CACHE ---
    const newsList = document.getElementById('news-list');
    newsList.innerHTML = '';

    data.data.forEach(n => {
        newsList.innerHTML += `
        <li class="p-5 bg-white/80 dark:bg-gray-800/90 rounded-2xl shadow-md dark:text-gray-100 transition-colors duration-500 cursor-pointer hover:ring-2 ring-blue-400" onclick="openNewsModal(${n.id})">
            <strong class="text-gray-800 dark:text-gray-100">${n.title}</strong>  
            <span class="text-sm text-gray-600 dark:text-gray-400"> - <a href="profile.html?u=${encodeURIComponent(n.username)}" onclick="event.stopPropagation()">${n.username}</a> - ${n.created_at_human}</span>  
            <p class="mt-1 text-gray-700 dark:text-gray-300">${n.content}</p>
            ${n.author && n.author === localStorage.getItem('user_name') ? `
            <div class="flex gap-2 mt-2">
                <button onclick='event.stopPropagation();editNews(${n.id}, ${JSON.stringify(n.title)}, ${JSON.stringify(n.content)}, ${n.category_id || "null"})' class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded transition-colors duration-300">Editar</button>
                <button onclick="event.stopPropagation();deleteNews(${n.id})" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded transition-colors duration-300">Excluir</button>
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


// Modal
async function openNewsModal(newsId) {
    const modal = document.getElementById('news-modal');
    const modalContent = document.getElementById('modal-news-content');
    const commentsList = document.getElementById('modal-comments');
    const commentInput = document.getElementById('modal-comment-input');
    const commentBtn = document.getElementById('modal-comment-btn');
    modal.classList.remove('hidden');
    modalContent.innerHTML = '<div class="text-center py-8">Carregando...</div>';
    commentsList.innerHTML = '';
    commentInput.value = '';
    commentBtn.disabled = true;

    // Buscar detalhes da notícia
    const res = await fetch(`${API_URL}/news/${newsId}`, {
        method: 'GET',
        headers: {'Content-Type': 'application/json'},
    });
    const news = await res.json();

    const n = news.data || {};
    let liked = !!n.liked_by_me;
    let likeCount = n.likes_count || 0;

    // Exibir avatar, username, data, likes
    modalContent.innerHTML = `
        <div class="flex items-center gap-3 mb-2">
            <img src="${n.avatar || '/s/i/avatar-default.png'}" alt="avatar" class="w-10 h-10 rounded-full border border-gray-300 dark:border-gray-700">
            <div>
                <div class="font-semibold text-gray-800 dark:text-gray-100">${n.author || ''} <span class="text-sm text-gray-500">@${n.username || ''}</span></div>
                <div class="text-xs text-gray-500">${n.created_at_human || ''}</div>
            </div>
        </div>
        <h2 class="text-2xl font-bold mb-1">${n.title || ''}</h2>
        <p class="mb-3 text-gray-800 dark:text-gray-200">${n.content || ''}</p>
        <div class="flex items-center gap-3 mb-2">
        </div>
    `;

    // Botão de curtir removido

    // --- CACHE COMENTÁRIOS ---
    const commentsCacheKey = `comments_news_${newsId}`;
    const commentsCacheStr = localStorage.getItem(commentsCacheKey);
    let comments = null;
    let now = Date.now();
    if (commentsCacheStr) {
        try {
            const cache = JSON.parse(commentsCacheStr);
            if (cache.expire > now) {
                comments = cache.data;
            } else {
                localStorage.removeItem(commentsCacheKey);
            }
        } catch {}
    }
    if (!comments) {
        const resComments = await fetch(`${API_URL}/news/${newsId}/comment`, {
            headers: { 'Authorization': 'Bearer ' + token }
        });
        comments = await resComments.json();
        localStorage.setItem(commentsCacheKey, JSON.stringify({ data: comments, expire: now + 30000 })); // 30s
    }
    commentsList.innerHTML = comments.data && comments.data.length ? comments.data.map(c => {
        let username = (c.user && c.user.username) ? c.user.username : (c.username || c.author || 'Usuário');
        let avatar = (c.user && c.user.avatar) ? c.user.avatar : '';
        return `<li class="bg-gray-100 dark:bg-gray-800 rounded-lg px-3 py-2 flex items-center gap-2">
            ${avatar ? `<img src="${avatar}" alt="avatar" class="w-7 h-7 rounded-full border border-gray-300 dark:border-gray-700">` : ''}
            <b>${username}:</b> ${c.content}
        </li>`;
    }).join('') : '<li class="text-gray-500">Nenhum comentário ainda.</li>';

    // Habilitar botão comentar
    commentInput.oninput = () => {
        commentBtn.disabled = !commentInput.value.trim();
    };
    commentBtn.disabled = true;
    commentBtn.onclick = async () => {
        const content = commentInput.value.trim();
        if (!content) return;
        await fetch(`${API_URL}/news/${newsId}/comment`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({ content })
        });
        commentInput.value = '';
        commentBtn.disabled = true;
        // Atualizar comentários e cache
        const resComments = await fetch(`${API_URL}/news/${newsId}/comment`, {
            headers: { 'Authorization': 'Bearer ' + token }
        });
        const comments = await resComments.json();
        localStorage.setItem(`comments_news_${newsId}`, JSON.stringify({ data: comments, expire: Date.now() + 30000 }));
        commentsList.innerHTML = comments.data && comments.data.length ? comments.data.map(c => {
            let username = (c.user && c.user.username) ? c.user.username : (c.username || c.author || 'Usuário');
            let avatar = (c.user && c.user.avatar) ? c.user.avatar : '';
            return `<li class=\"bg-gray-100 dark:bg-gray-800 rounded-lg px-3 py-2 flex items-center gap-2\">${avatar ? `<img src=\"${avatar}\" alt=\"avatar\" class=\"w-7 h-7 rounded-full border border-gray-300 dark:border-gray-700\">` : ''}<b>${username}:</b> ${c.content}</li>`;
        }).join('') : '<li class="text-gray-500">Nenhum comentário ainda.</li>';
    };
}

// Fechar modal
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('close-modal').onclick = () => {
        document.getElementById('news-modal').classList.add('hidden');
    };
    // Fechar ao clicar fora do modal
    document.getElementById('news-modal').addEventListener('click', e => {
        if (e.target === document.getElementById('news-modal')) {
            document.getElementById('news-modal').classList.add('hidden');
        }
    });
});

loadNews();