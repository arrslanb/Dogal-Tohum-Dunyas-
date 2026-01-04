<div id="chat-trigger" class="fixed bottom-6 right-6 z-50 cursor-pointer group animate-bounce-slow">
    <div class="bg-nature-green text-white w-16 h-16 rounded-full flex items-center justify-center shadow-2xl transform transition duration-300 group-hover:scale-110 group-hover:rotate-12 border-4 border-white">
        <span class="text-3xl">🤖</span>
    </div>
    <div class="absolute bottom-20 right-0 bg-white px-4 py-2 rounded-xl shadow-lg text-sm text-gray-600 font-bold whitespace-nowrap opacity-0 group-hover:opacity-100 transition duration-300 transform translate-y-2 group-hover:translate-y-0 pointer-events-none border border-gray-100">
        Yardımcı olabilir miyim?
    </div>
</div>

<div id="chat-window" class="fixed bottom-24 right-6 w-80 md:w-96 bg-white rounded-2xl shadow-2xl z-50 hidden flex flex-col border border-gray-100 transform scale-95 opacity-0 transition duration-300 origin-bottom-right h-[550px] max-h-[80vh]">
    
    <div class="bg-gradient-to-r from-nature-dark to-green-900 p-4 flex-none flex items-center justify-between text-white shadow-md rounded-t-2xl">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center text-xl backdrop-blur-sm">🌱</div>
            <div>
                <h3 class="font-bold text-sm md:text-base">Doğal Asistan</h3>
                <span class="text-xs text-green-300 flex items-center gap-1">
                    <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span> Çevrimiçi
                </span>
            </div>
        </div>
        <button id="close-chat" class="text-white/80 hover:text-white text-2xl hover:rotate-90 transition transform">&times;</button>
    </div>

    <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50 min-h-0">
        <div class="flex items-start gap-2 animate-fade-in-up">
            <div class="w-8 h-8 bg-nature-green rounded-full flex items-center justify-center text-white text-xs flex-shrink-0 shadow-sm">🤖</div>
            <div class="bg-white p-3 rounded-2xl rounded-tl-none shadow-sm text-gray-700 text-sm border border-gray-100 leading-relaxed">
                Merhaba! Ben yapay zeka asistanın. 🌱<br>Sana nasıl yardımcı olabilirim?
            </div>
        </div>
        
        <div class="flex flex-wrap gap-2 pl-10" id="initial-quick-replies">
            <button onclick="sendQuickMessage('Kargom nerede?')" class="bg-white border border-green-200 text-nature-dark text-xs px-3 py-2 rounded-full hover:bg-green-50 transition shadow-sm font-medium">🚚 Kargom Nerede?</button>
            <button onclick="sendQuickMessage('Domates tohumu öner')" class="bg-white border border-green-200 text-nature-dark text-xs px-3 py-2 rounded-full hover:bg-green-50 transition shadow-sm font-medium">🍅 Domates Öner</button>
            <button onclick="sendQuickMessage('Nasılsın?')" class="bg-white border border-green-200 text-nature-dark text-xs px-3 py-2 rounded-full hover:bg-green-50 transition shadow-sm font-medium">👋 Nasılsın?</button>
        </div>
        
        <div id="scroll-anchor"></div>
    </div>

    <div id="typing-indicator" class="hidden flex-none px-4 py-2 bg-gray-50 text-xs text-gray-400 italic border-t border-gray-100 flex items-center gap-1">
        <span>Asistan yazıyor</span>
        <span class="animate-bounce">.</span><span class="animate-bounce delay-100">.</span><span class="animate-bounce delay-200">.</span>
    </div>

    <div class="p-3 bg-white border-t border-gray-100 flex-none rounded-b-2xl">
        <form id="chat-form" class="flex gap-2 relative">
            <input type="text" id="user-input" class="flex-1 bg-gray-100 border-0 rounded-full pl-4 pr-10 py-3 text-sm focus:ring-2 focus:ring-nature-green outline-none transition placeholder-gray-400" placeholder="Bir şeyler yaz..." autocomplete="off">
            <button type="submit" class="bg-nature-green text-white w-10 h-10 rounded-full flex items-center justify-center hover:bg-nature-dark transition shadow-md absolute right-1 top-1">
                ➤
            </button>
        </form>
    </div>
</div>

<style>
    #chat-messages::-webkit-scrollbar { width: 6px; }
    #chat-messages::-webkit-scrollbar-track { background: #f9fafb; }
    #chat-messages::-webkit-scrollbar-thumb { background-color: #d1d5db; border-radius: 20px; }
    @keyframes fade-in-up { 0% { opacity: 0; transform: translateY(10px); } 100% { opacity: 1; transform: translateY(0); } }
    .animate-fade-in-up { animation: fade-in-up 0.3s ease-out forwards; }
    .animate-bounce-slow { animation: bounce 3s infinite; }
</style>

<script>
    const trigger = document.getElementById('chat-trigger');
    const windowEl = document.getElementById('chat-window');
    const closeBtn = document.getElementById('close-chat');
    const form = document.getElementById('chat-form');
    const input = document.getElementById('user-input');
    const messages = document.getElementById('chat-messages');
    const scrollAnchor = document.getElementById('scroll-anchor');
    const typing = document.getElementById('typing-indicator');

    function toggleChat() {
        if (windowEl.classList.contains('hidden')) {
            windowEl.classList.remove('hidden');
            setTimeout(() => { windowEl.classList.remove('scale-95', 'opacity-0'); scrollToBottom(); }, 10);
        } else {
            windowEl.classList.add('scale-95', 'opacity-0');
            setTimeout(() => { windowEl.classList.add('hidden'); }, 300);
        }
    }

    trigger.addEventListener('click', toggleChat);
    closeBtn.addEventListener('click', toggleChat);

    function scrollToBottom() {
        scrollAnchor.scrollIntoView({ behavior: "smooth", block: "end" });
    }

    // Dışarıdan tetiklenebilmesi için global yapıyoruz
    window.sendQuickMessage = function(text) {
        input.value = text;
        form.dispatchEvent(new Event('submit'));
    }

    function addMessage(text, sender, action = null, quickReplies = []) {
        const div = document.createElement('div');
        const isUser = sender === 'user';
        div.className = `flex items-start gap-2 animate-fade-in-up ${isUser ? 'justify-end' : ''}`;
        
        let html = '';
        if (!isUser) html += `<div class="w-8 h-8 bg-nature-green rounded-full flex items-center justify-center text-white text-xs flex-shrink-0 shadow-sm">🤖</div>`;
        html += `<div class="${isUser ? 'bg-nature-dark text-white rounded-tr-none' : 'bg-white text-gray-700 border border-gray-100 rounded-tl-none'} p-3 rounded-2xl shadow-sm text-sm max-w-[85%] leading-relaxed">${text}</div>`;
        
        div.innerHTML = html;
        messages.insertBefore(div, scrollAnchor);

        // Aksiyon butonu (Link) varsa ekle
        if (action) {
            const actionDiv = document.createElement('div');
            actionDiv.className = 'flex justify-start pl-10 animate-fade-in-up';
            actionDiv.innerHTML = `<a href="${action.link}" class="bg-green-100 text-nature-dark px-4 py-2 rounded-xl text-xs font-bold hover:bg-green-200 transition mt-1 inline-flex items-center gap-2 border border-green-200 shadow-sm">${action.text} ➜</a>`;
            messages.insertBefore(actionDiv, scrollAnchor);
        }
        
        // Hızlı cevap butonları varsa ekle
        if (quickReplies && quickReplies.length > 0) {
            const chipsDiv = document.createElement('div');
            chipsDiv.className = 'flex flex-wrap gap-2 pl-10 mt-2 animate-fade-in-up mb-2';
            quickReplies.forEach(reply => {
                const btn = document.createElement('button');
                btn.className = 'bg-white border border-gray-200 text-gray-600 text-xs px-3 py-1.5 rounded-full hover:bg-green-50 hover:text-nature-green hover:border-green-200 transition shadow-sm cursor-pointer';
                btn.innerText = reply;
                btn.onclick = () => sendQuickMessage(reply);
                chipsDiv.appendChild(btn);
            });
            messages.insertBefore(chipsDiv, scrollAnchor);
        }
        scrollToBottom();
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;

        // Kullanıcı mesajını ekle
        addMessage(text, 'user');
        input.value = '';
        typing.classList.remove('hidden');
        scrollToBottom();

        // 🟢 Robot ile iletişim
        // robot.php dosyasının ana dizinde olduğunu varsayıyoruz
        fetch('robot.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: text })
        })
        .then(response => {
            if (!response.ok) throw new Error("Hata: " + response.status);
            return response.json();
        })
        .then(data => {
            typing.classList.add('hidden');
            // Gelen cevabı ekrana bas
            addMessage(data.reply, 'bot', data.action, data.quick_replies);
        })
        .catch(error => {
            typing.classList.add('hidden');
            console.error(error);
            addMessage("Şu an bağlantı kuramıyorum 😔 Lütfen 'robot.php' dosyasının ana dizinde olduğundan emin ol.", 'bot');
        });
    });
</script>