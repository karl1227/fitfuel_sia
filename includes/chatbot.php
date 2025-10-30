<!-- AI Chatbot -->
<script>
// Chatbot UI - Ensure DOM is ready
(function(){
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initChatbot);
	} else {
		initChatbot();
	}
	
	function initChatbot() {
	// Prevent duplicate initialization
	if (document.getElementById('ff-chatbot')) {
		return;
	}
	
	// Ensure body exists
	if (!document.body) {
		setTimeout(initChatbot, 100);
		return;
	}
	
	const root = document.createElement('div');
	root.id = 'ff-chatbot-wrapper';
	root.innerHTML = `
		<div id="ff-chatbot" style="position: fixed; bottom: 1rem; right: 1rem; z-index: 9999;">
			<div id="ff-chat-panel" class="hidden w-80 md:w-96 h-[32rem] bg-white rounded-lg shadow-xl border flex flex-col">
				<div class="px-4 py-2 bg-emerald-600 text-white flex items-center justify-between flex-shrink-0">
					<span class="font-semibold">FitFuel Assistant</span>
					<button id="ff-chat-close" class="text-white/80 hover:text-white text-xl leading-none">✕</button>
				</div>
				<div id="ff-chat-messages" class="p-3 space-y-3 overflow-y-auto text-sm flex-1 min-h-0"></div>
				<div class="border-t p-3 space-y-2 flex-shrink-0 bg-white">
					<div id="ff-chat-quick" class="flex flex-wrap gap-2 mb-2"></div>
					<div class="flex gap-2">
						<input id="ff-chat-input" type="text" placeholder="Ask about products or FAQs..." class="flex-1 border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
						<button id="ff-chat-send" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700 transition-colors text-sm font-medium">Send</button>
					</div>
				</div>
			</div>
			<button id="ff-chat-bubble" style="width: 3.5rem; height: 3.5rem; border-radius: 50%; background-color: #10b981; color: white; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center; cursor: pointer; border: none;" class="hover:bg-emerald-700 transition-colors">
				<i class="fas fa-comments" style="font-size: 1.5rem;"></i>
			</button>
		</div>`;
	document.body.appendChild(root);

	const panel = document.getElementById('ff-chat-panel');
	const bubble = document.getElementById('ff-chat-bubble');
	const closeBtn = document.getElementById('ff-chat-close');
	const messages = document.getElementById('ff-chat-messages');
	const input = document.getElementById('ff-chat-input');
	const sendBtn = document.getElementById('ff-chat-send');
	const quick = document.getElementById('ff-chat-quick');
	
	// Safety checks - exit if elements not found
	if (!panel || !bubble || !messages || !input || !sendBtn || !quick) {
		console.error('Chatbot elements not found');
		return;
	}

	function addMsg(text, who){
		const div = document.createElement('div');
		div.className = 'max-w-[85%] ' + (who==='you' ? 'ml-auto text-right' : '');
		div.innerHTML = `<div class="inline-block px-3 py-2 rounded ${who==='you' ? 'bg-emerald-600 text-white' : 'bg-slate-100'}">${escapeHtml(text).replace(/\n/g,'<br>')}</div>`;
		messages.appendChild(div);
		messages.scrollTop = messages.scrollHeight;
	}
	function escapeHtml(s){
		return String(s||'').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#039;'}[c]));
	}
	async function send(text){
		if (!text) return;
		addMsg(text, 'you');
		input.value = '';
		try{
			const res = await fetch('chatbot.php', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({message: text})});
			const data = await res.json();
			if (data && data.success){
				addMsg(data.reply, 'bot');
				renderQuick(data.quick_replies||[]);
			} else {
				addMsg('Sorry, something went wrong.', 'bot');
			}
		}catch(e){ addMsg('Network error. Please try again.', 'bot'); }
	}
	function renderQuick(items){
		quick.innerHTML='';
		items.forEach(it => {
			const b = document.createElement('button');
			b.className='text-xs bg-slate-100 hover:bg-slate-200 px-2 py-1 rounded';
			b.textContent = it.label;
			b.addEventListener('click', async ()=>{
				// Show user's message first (as if they typed it)
				addMsg(it.label, 'you');
				try{
					const res = await fetch('chatbot.php', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({intent: 'faq', faq_key: it.faq_key, message: it.label})});
					const data = await res.json();
					if (data && data.success){ 
						addMsg(data.reply, 'bot');
						// Update quick replies if new ones are provided
						if (data.quick_replies) renderQuick(data.quick_replies);
					}
				}catch(e){
					addMsg('Sorry, something went wrong.', 'bot');
				}
			});
			quick.appendChild(b);
		});
	}

	if (bubble) bubble.addEventListener('click', ()=>{ panel.classList.toggle('hidden'); });
	if (closeBtn) closeBtn.addEventListener('click', ()=>{ panel.classList.add('hidden'); });
	if (sendBtn) sendBtn.addEventListener('click', ()=> send(input.value.trim()));
	if (input) input.addEventListener('keydown', (e)=>{ if (e.key==='Enter') send(input.value.trim()); });

	// Greet and preload quick replies
	(async function init(){
		addMsg('Hi! I can help you find products and answer FAQs. Try typing a product name or click a quick suggestion below.', 'bot');
		try{
			const res = await fetch('chatbot.php', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({message: ''})});
			const data = await res.json();
			if (data && data.quick_replies) renderQuick(data.quick_replies);
		}catch(e){}
	})();
	}
})();
</script>

