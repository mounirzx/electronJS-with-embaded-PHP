const { ipcRenderer } = require('electron');

document.getElementById('runPHP').addEventListener('click', () => {
  ipcRenderer.send('run-php');
});

ipcRenderer.on('php-result', (event, result) => {
  document.getElementById('phpResult').innerText = result;
});
