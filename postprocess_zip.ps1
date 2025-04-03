# Define variables
$ZIP_FILE = "reblock.zip"
$TEMP_DIR = "temp"
$PLUGIN_FOLDER = "reblock"

# Create temporary directory if it doesn't exist
if (-Not (Test-Path $TEMP_DIR)) {
    New-Item -Path $TEMP_DIR -ItemType Directory | Out-Null
}

# Extract the zip file into the temporary directory
Expand-Archive -Path $ZIP_FILE -DestinationPath $TEMP_DIR -Force

# Remove the original zip file
Remove-Item -Path $ZIP_FILE -Force

# Remove package.json and README.md if they exist in the plugin folder
$pkgFile = Join-Path $TEMP_DIR "$PLUGIN_FOLDER\package.json"
if (Test-Path $pkgFile) {
    Remove-Item -Path $pkgFile -Force
}

$readmeFile = Join-Path $TEMP_DIR "$PLUGIN_FOLDER\README.md"
if (Test-Path $readmeFile) {
    Remove-Item -Path $readmeFile -Force
}

# Repackage the contents into a new zip file
Push-Location $TEMP_DIR
Compress-Archive -Path $PLUGIN_FOLDER -DestinationPath "..\$ZIP_FILE" -Force
Pop-Location

# Clean up temporary directory
Remove-Item -Path $TEMP_DIR -Recurse -Force