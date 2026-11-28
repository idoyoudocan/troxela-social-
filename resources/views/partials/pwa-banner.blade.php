<div class="pwa-banner" id="pwa-banner" hidden>
  <img class="pwa-banner-icon" src="/icons/icon-192.png" alt="Troxela" width="48" height="48">
  <div class="pwa-banner-text">
    <strong>Troxela অ্যাপ ইনস্টল করুন</strong>
    <span>ফোনে অ্যাপের মতো ব্যবহার করুন — দ্রুত খুলবে, ইন্টারনেট ছাড়াও পাতা দেখা যাবে।</span>
  </div>
  <button class="pwa-install-btn" type="button" id="pwa-install-btn">ইনস্টল করুন</button>
  <button class="pwa-close" type="button" id="pwa-close" aria-label="বন্ধ করুন">&times;</button>
</div>
<div class="pwa-banner" id="pwa-ios" hidden>
  <img class="pwa-banner-icon" src="/icons/icon-192.png" alt="Troxela" width="48" height="48">
  <div class="pwa-banner-text">
    <strong>Troxela অ্যাপ ইনস্টল করুন</strong>
    <span>Safari-র Share বোতাম → Add to Home Screen চাপুন।</span>
  </div>
  <button class="pwa-close" type="button" id="pwa-ios-close" aria-label="বন্ধ করুন">&times;</button>
</div>
<script>
(function(){
  if (!('serviceWorker' in navigator)) return;
  navigator.serviceWorker.register('/sw.js').catch(function(){});
  var isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
  if (isStandalone) return;
  var KEY = 'troxela-pwa-dismissed';
  var dismissedAt = parseInt(localStorage.getItem(KEY) || '0', 10);
  var dismissed = dismissedAt && (Date.now() - dismissedAt) < 7 * 24 * 60 * 60 * 1000;
  var banner = document.getElementById('pwa-banner');
  var ios = document.getElementById('pwa-ios');
  var installBtn = document.getElementById('pwa-install-btn');
  var closeBtn = document.getElementById('pwa-close');
  var iosCloseBtn = document.getElementById('pwa-ios-close');
  var deferredPrompt = null;
  function dismiss(){
    if (banner) banner.hidden = true;
    if (ios) ios.hidden = true;
    try { localStorage.setItem(KEY, String(Date.now())); } catch (e) {}
  }
  if (closeBtn) closeBtn.addEventListener('click', dismiss);
  if (iosCloseBtn) iosCloseBtn.addEventListener('click', dismiss);
  window.addEventListener('beforeinstallprompt', function(e){
    e.preventDefault();
    deferredPrompt = e;
    if (!dismissed && banner) banner.hidden = false;
  });
  if (installBtn) installBtn.addEventListener('click', function(){
    if (!deferredPrompt) return;
    deferredPrompt.prompt();
    deferredPrompt.userChoice.then(function(){
      deferredPrompt = null;
      if (banner) banner.hidden = true;
    }).catch(function(){});
  });
  window.addEventListener('appinstalled', function(){
    if (banner) banner.hidden = true;
    if (ios) ios.hidden = true;
  });
  // iOS Safari has no beforeinstallprompt: show the manual hint instead.
  var isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent);
  var isSafari = /^((?!chrome|android|crios|fxios).)*safari/i.test(navigator.userAgent);
  if (isIOS && isSafari && !dismissed && ios) ios.hidden = false;
})();
</script>
