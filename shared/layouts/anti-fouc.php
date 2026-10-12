<style>
  html.app-styles-loading { 
    background: #0a0a0a !important; 
  }
  html.app-styles-loading body { 
    visibility: hidden !important; 
  }
</style>

<script>
  (() => {
    document.documentElement.classList.add('app-styles-loading');
    
    const revealAuthPage = () => document.documentElement.classList.remove('app-styles-loading');
    
    document.addEventListener('DOMContentLoaded', () => requestAnimationFrame(revealAuthPage), { once: true });
    
    window.setTimeout(revealAuthPage, 1500);
  })();
</script>
