if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('/Appjoteca/sw.js')
    .then(function(registration) {
      console.log('Service Worker registrado correctamente');

      registration.addEventListener('updatefound', function() {
        const newWorker = registration.installing;

        newWorker.addEventListener('statechange', function() {
          if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
            
            const actualizar = confirm('Hay una nueva versión de AppJoteca. ¿Quieres actualizar ahora?');

            if (actualizar) {
              newWorker.postMessage({ action: 'skipWaiting' });
              window.location.reload();
            }
          }
        });
      });
    })
    .catch(function(error) {
      console.log('Error al registrar el Service Worker:', error);
    });
}