<div class="chatbot" id="chatbot">
  <button class="chatbot-fab" type="button" id="chatbot-fab" aria-label="Troxela সহকারীর সাথে কথা বলুন" title="Troxela সহকারী">
    <span class="chatbot-fab-mark">T</span>
  </button>
  <section class="chatbot-panel" id="chatbot-panel" aria-label="Troxela সহকারী" hidden>
    <header class="chatbot-head">
      <div>
        <strong>Troxela সহকারী</strong>
        <small>ডেমো চ্যাটবট — সাইট ব্যবহার নিয়ে জিজ্ঞেস করুন</small>
      </div>
      <button class="chatbot-close" type="button" id="chatbot-close" aria-label="বন্ধ করুন">&times;</button>
    </header>
    <div class="chatbot-messages" id="chatbot-messages">
      <div class="chatbot-msg bot">আসসালামু আলাইকুম! আমি Troxela-র সহকারী। পোস্ট, মেসেজ, ফলো বা পাসওয়ার্ড নিয়ে যেকোনো প্রশ্ন করতে পারেন।</div>
    </div>
    <div class="chatbot-chips" id="chatbot-chips">
      <button type="button" data-q="পোস্ট করব কীভাবে?">পোস্ট করব কীভাবে?</button>
      <button type="button" data-q="মেসেজ পাঠাব কীভাবে?">মেসেজ পাঠাব কীভাবে?</button>
      <button type="button" data-q="পাসওয়ার্ড ভুলে গেছি">পাসওয়ার্ড ভুলে গেছি</button>
    </div>
    <form class="chatbot-form" id="chatbot-form">
      <input type="text" id="chatbot-input" placeholder="লিখুন…" maxlength="500" autocomplete="off" aria-label="আপনার প্রশ্ন">
      <button type="submit" aria-label="পাঠান">&#10148;</button>
    </form>
  </section>
</div>
<script>
(function(){
  var panel = document.getElementById('chatbot-panel');
  var fab = document.getElementById('chatbot-fab');
  var closeBtn = document.getElementById('chatbot-close');
  var form = document.getElementById('chatbot-form');
  var input = document.getElementById('chatbot-input');
  var messages = document.getElementById('chatbot-messages');
  var chips = document.getElementById('chatbot-chips');
  var token = document.querySelector('meta[name="csrf-token"]');
  if(!panel || !fab) return;
  function toggle(open){
    panel.hidden = !open;
    if(open) input.focus();
  }
  fab.addEventListener('click', function(){ toggle(panel.hidden); });
  closeBtn.addEventListener('click', function(){ toggle(false); });
  function addMsg(text, who){
    var div = document.createElement('div');
    div.className = 'chatbot-msg ' + who;
    div.textContent = text;
    messages.appendChild(div);
    messages.scrollTop = messages.scrollHeight;
    return div;
  }
  function ask(q){
    if(!q) return;
    addMsg(q, 'user');
    input.value = '';
    var typing = addMsg('লিখছি…', 'bot typing');
    fetch('{{ route('chatbot.message') }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': token ? token.getAttribute('content') : ''
      },
      body: JSON.stringify({ message: q })
    }).then(function(r){ return r.json(); }).then(function(data){
      typing.remove();
      addMsg((data && data.reply) ? data.reply : 'দুঃখিত, এখন উত্তর দিতে পারছি না। আবার চেষ্টা করুন।', 'bot');
    }).catch(function(){
      typing.remove();
      addMsg('দুঃখিত, সংযোগে সমস্যা হয়েছে। আবার চেষ্টা করুন।', 'bot');
    });
  }
  form.addEventListener('submit', function(e){
    e.preventDefault();
    ask(input.value.trim());
  });
  chips.addEventListener('click', function(e){
    var btn = e.target.closest('button[data-q]');
    if(btn) ask(btn.getAttribute('data-q'));
  });
})();
</script>
