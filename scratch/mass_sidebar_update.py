import os
import re

directory = r'c:\xampp\htdocs\darts\pages'
sidebar_include = "<!-- Sidebar -->\n<?php include '../includes/sidebar.php'; ?>\n"

# Pattern to match the sidebar block
# Matches <!-- Sidebar --> followed by a div with class sidebar and everything until the next comment or div
pattern = re.compile(r'<!-- Sidebar -->\s*<div class="sidebar">.*?</div>', re.DOTALL)

for filename in os.listdir(directory):
    if filename.endswith('.php'):
        filepath = os.path.join(directory, filename)
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        if pattern.search(content):
            print(f"Updating {filename}...")
            new_content = pattern.sub(sidebar_include, content)
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(new_content)
        else:
            # Try a slightly different pattern if the first one fails
            # Some might not have the <!-- Sidebar --> comment
            pattern2 = re.compile(r'<div class="sidebar">.*?</div>', re.DOTALL)
            if pattern2.search(content):
                # Check if it's the actual sidebar by looking for DARTS or some links
                if 'DARTS' in content or 'Dashboard' in content:
                    print(f"Updating {filename} (no comment pattern)...")
                    # We want to be careful not to replace other divs if it's not the sidebar
                    # But usually the first one is the sidebar
                    new_content = pattern2.sub(sidebar_include, content, count=1)
                    with open(filepath, 'w', encoding='utf-8') as f:
                        f.write(new_content)

print("Done.")
