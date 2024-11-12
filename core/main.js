const { app, BrowserWindow } = require('electron');

function createMainWindow () {
    const mainWindow = new BrowserWindow({
        title: 'CaptioMirror',
        width: 1080,
        height: 1920,
        webPreferences: {
            nodeIntegration: true,
            contextIsolation: false,
            enableRemoteModule: true,
            webSecurity: false, // Allowing all origins; adjust if necessary
          }
    });
    
    mainWindow.loadURL('http://localhost/CaptioMirror-IoT-Based-Smart-Mirror-System-with-AI-Assistant/index.php');

    mainWindow.on('closed', () => {
        app.quit(); // Ensures the app quits when the main window is closed
    });
}

app.whenReady().then(createMainWindow);

app.on('window-all-closed', () => {
    if (process.platform !== 'darwin') {
        app.quit(); // Closes the app on Windows/Linux when all windows are closed
    }
});

app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) {
        createMainWindow(); // Recreates the main window when the app is activated (macOS behavior)
    }
});
