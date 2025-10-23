
#!/bin/bash

# Create backup folder with timestamp
BACKUP_DIR="changed_files_backup_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"

# Get list of changed files since October 20, 2025
git log --since="2025-10-20" --name-only --pretty=format: | sort -u | grep -v '^$' > /tmp/changed_files.txt

# Read each file and copy it to the backup directory
while IFS= read -r file; do
    if [ -f "$file" ]; then
        # Create directory structure in backup folder
        mkdir -p "$BACKUP_DIR/$(dirname "$file")"
        # Copy the file
        cp "$file" "$BACKUP_DIR/$file"
        echo "Copied: $file"
    fi
done < /tmp/changed_files.txt

echo "Backup completed in: $BACKUP_DIR"
echo "Total files copied: $(find "$BACKUP_DIR" -type f | wc -l)"
