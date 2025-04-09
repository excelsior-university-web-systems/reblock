const AdmZip = require('adm-zip');
const archiver = require('archiver');
const fs = require('fs-extra'); // fs-extra provides extra methods like removeSync.
const path = require('path');

// Define key paths and variables
const zipFile = 'reblock.zip';
const tempDir = path.join(__dirname, 'temp');
const pluginFolderName = 'reblock'; // folder inside the zip
const pluginFolderPath = path.join(tempDir, pluginFolderName);

// Step 1: Clean up and create temporary directory.
if (fs.existsSync(tempDir)) {
  fs.removeSync(tempDir);
}
fs.mkdirSync(tempDir, { recursive: true });

// Step 2: Extract the zip file into the temporary directory.
console.log('Extracting zip file...');
const zip = new AdmZip(zipFile);
zip.extractAllTo(tempDir, true);

// Remove the original zip file if needed.
fs.removeSync(zipFile);
console.log('Extraction complete and original zip removed.');

// Step 3: Remove unwanted files (e.g., package.json and README.md).
const unwantedFiles = ['package.json', 'README.md'];
unwantedFiles.forEach(file => {
  const filePath = path.join(pluginFolderPath, file);
  if (fs.existsSync(filePath)) {
    fs.removeSync(filePath);
    console.log(`Removed: ${file}`);
  }
});

// Step 4: Create a new zip archive using archiver.
const outputZip = zipFile; // reuse the same filename or change as needed.
const output = fs.createWriteStream(outputZip);
const archive = archiver('zip', { zlib: { level: 9 } });

// Listen for archive completion
output.on('close', () => {
  console.log(`New zip archive created: ${outputZip} (${archive.pointer()} bytes)`);
  // Clean up temporary directory after archiving is done.
  fs.removeSync(tempDir);
  console.log('Temporary files cleaned up.');
});

archive.on('error', err => { throw err; });

// Pipe the archive data to the file.
archive.pipe(output);

// The second parameter ensures that the folder structure (with "reblock" as the folder)
// is preserved inside the archive.
archive.directory(pluginFolderPath, pluginFolderName);

// Finalize the archive (finish appending files).
archive.finalize();
