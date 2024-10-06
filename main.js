const { app, BrowserWindow, ipcMain } = require('electron');
const { spawn } = require('child_process');
const path = require('path');

let phpServer;

function createWindow() {
  const mainWindow = new BrowserWindow({
    width: 800,
    height: 600,
    webPreferences: {
      preload: path.join(__dirname, 'preload.js'),
      nodeIntegration: true,
      contextIsolation: false,
    },
  });

  mainWindow.loadFile(path.join(__dirname, 'index.html'));
}

app.on('ready', () => {
  // Spawn PHP server
  phpServer = spawn('php\\php.exe', ['-S', 'localhost:8000', '-t', path.join(__dirname, 'php')], { cwd: __dirname });

  phpServer.stdout.on('data', (data) => {
    console.log(`PHP server stdout: ${data}`);
  });

  phpServer.stderr.on('data', (data) => {
    console.error(`PHP server stderr: ${data}`);
  });

  phpServer.on('close', (code) => {
    console.log(`PHP server process exited with code ${code}`);
  });

  createWindow();
});

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit();
  }
});

app.on('activate', () => {
  if (BrowserWindow.getAllWindows().length === 0) {
    createWindow();
  }
});

ipcMain.on('run-php', (event) => {
    console.log('Executing PHP script...');
    const { execFile } = require('child_process');
    execFile('php\\php.exe', [path.join(__dirname, 'php', 'hello.php')], (error, stdout, stderr) => {
      if (error) {
        console.error('PHP execution error:', error);
        event.reply('php-result', error.message);
        return;
      }
      if (stderr) {
        console.error('PHP stderr:', stderr);
        event.reply('php-result', stderr);
        return;
      }
      console.log('PHP stdout:', stdout);
      event.reply('php-result', stdout);
    });
  });

ipcMain.on('close-php-server', () => {
  // Close PHP server
  if (phpServer) {
    phpServer.kill();
  }
});
