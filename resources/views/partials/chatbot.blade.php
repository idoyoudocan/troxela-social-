<div class="chatbot" id="chatbot">
  <button class="chatbot-fab" type="button" id="chatbot-fab" aria-label="Troxela সহকারীর সাথে কথা বলুন" aria-expanded="false" title="Troxela সহকারী">
    <span class="chatbot-fab-mark" id="chatbot-fab-mark">T</span>
  </button>
  <section class="chatbot-panel" id="chatbot-panel" role="dialog" aria-label="Troxela সহকারী" hidden>
    <header class="chatbot-head">
      <div>
        <strong>Troxela সহকারী</strong>
        <small>Troxela নিয়ে যেকোনো প্রশ্ন করুন</small>
      </div>
      <div class="chatbot-head-actions">
        <button class="chatbot-sound" type="button" id="chatbot-sound" aria-label="সাউন্ড বন্ধ করুন" title="সাউন্ড চালু/বন্ধ">&#128266;</button>
        <button class="chatbot-close" type="button" id="chatbot-close" aria-label="বন্ধ করুন">&times;</button>
      </div>
    </header>
    <div class="chatbot-messages" id="chatbot-messages">
      <div class="chatbot-msg bot">আসসালামু আলাইকুম! আমি Troxela-র সহকারী। পোস্ট, মেসেজ, ফলো বা পাসওয়ার্ড নিয়ে যেকোনো প্রশ্ন করতে পারেন।</div>
    </div>
    <div class="chatbot-chips" id="chatbot-chips">
      <button type="button" data-q="Troxela কি?">Troxela কি?</button>
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
  var fabMark = document.getElementById('chatbot-fab-mark');
  var closeBtn = document.getElementById('chatbot-close');
  var soundBtn = document.getElementById('chatbot-sound');
  var form = document.getElementById('chatbot-form');
  var input = document.getElementById('chatbot-input');
  var messages = document.getElementById('chatbot-messages');
  var chips = document.getElementById('chatbot-chips');
  var token = document.querySelector('meta[name="csrf-token"]');
  if(!panel || !fab) return;

  var hideTimer = null;
  var soundOn = true;
  try { soundOn = localStorage.getItem('troxela-chat-sound') !== 'off'; } catch(e) {}

  function renderSoundBtn(){
    soundBtn.innerHTML = soundOn ? '&#128266;' : '&#128263;';
    soundBtn.setAttribute('aria-label', soundOn ? 'সাউন্ড বন্ধ করুন' : 'সাউন্ড চালু করুন');
    soundBtn.setAttribute('title', soundOn ? 'সাউন্ড বন্ধ করুন' : 'সাউন্ড চালু করুন');
  }
  renderSoundBtn();
  soundBtn.addEventListener('click', function(){
    soundOn = !soundOn;
    try { localStorage.setItem('troxela-chat-sound', soundOn ? 'on' : 'off'); } catch(e) {}
    renderSoundBtn();
    if(soundOn) playChime();
  });

  // Soft two-note chime, made with WebAudio — no audio file needed.
  var audioCtx = null;
  function playChime(){
    if(!soundOn) return;
    try {
      if(!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
      if(audioCtx.state === 'suspended') audioCtx.resume();
      var now = audioCtx.currentTime;
      [[659.25, 0], [880, 0.16]].forEach(function(note){
        var osc = audioCtx.createOscillator();
        var gain = audioCtx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(note[0], now + note[1]);
        gain.gain.setValueAtTime(0.0001, now + note[1]);
        gain.gain.exponentialRampToValueAtTime(0.12, now + note[1] + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.0001, now + note[1] + 0.35);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start(now + note[1]);
        osc.stop(now + note[1] + 0.4);
      });
    } catch(e) {}
  }

  function setOpen(open){
    if(open){
      if(hideTimer){ clearTimeout(hideTimer); hideTimer = null; }
      panel.hidden = false;
      requestAnimationFrame(function(){
        requestAnimationFrame(function(){ panel.classList.add('open'); });
      });
      fabMark.textContent = '\u00d7';
      fab.setAttribute('aria-expanded', 'true');
      input.focus();
    } else {
      panel.classList.remove('open');
      fabMark.textContent = 'T';
      fab.setAttribute('aria-expanded', 'false');
      if(hideTimer) clearTimeout(hideTimer);
      hideTimer = setTimeout(function(){ panel.hidden = true; hideTimer = null; }, 180);
    }
  }
  function isOpen(){ return !panel.hidden && panel.classList.contains('open'); }
  fab.addEventListener('click', function(){ setOpen(!isOpen()); });
  closeBtn.addEventListener('click', function(){ setOpen(false); });
  document.addEventListener('keydown', function(e){
    if(e.key === 'Escape' && isOpen()) setOpen(false);
  });

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
      playChime();
    }).catch(function(){
      typing.remove();
      addMsg('দুঃখিত, সংযোগে সমস্যা হয়েছে। আবার চেষ্টা করুন।', 'bot');
      playChime();
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
