#!/usr/bin/env bash

# Define variables
ZIP_FILE="reblock.zip"
TEMP_DIR="temp"
PLUGIN_FOLDER="reblock"

mkdir -p "$TEMP_DIR"

# Extract the zip file into a temporary directory
unzip "$ZIP_FILE" -d "$TEMP_DIR"

# Remove the origial zip file created by WordPress script
rm -f "$ZIP_FILE"

# Remove package.json if it exists at the root or in the plugin folder
rm -f "$TEMP_DIR/reblock/package.json"
rm -f "$TEMP_DIR/reblock/README.md"

# Repackage the contents into a new zip file
cd "$TEMP_DIR"
zip -r "../$ZIP_FILE" "$PLUGIN_FOLDER"
cd ..

# Clean up temporary directory
rm -rf "$TEMP_DIR"
