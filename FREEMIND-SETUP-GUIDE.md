# FreeMind Setup Guide for Codebase Visualization

## 🎯 What is FreeMind?

FreeMind is a free, open-source mind mapping application written in Java. It's perfect for visualizing your codebase structure.

**Website:** http://freemind.sourceforge.net/

---

## 📥 How to Download FreeMind

### Step 1: Visit the Official Website
Go to: **http://freemind.sourceforge.net/wiki/index.php/Download**

### Step 2: Choose Your Version

**For Windows:**
- Download: **FreeMind-Windows-Installer-1.0.1.exe** (or latest version)
- Direct link: https://sourceforge.net/projects/freemind/files/freemind/1.0.1/FreeMind-Windows-Installer-1.0.1.exe/download

**For Mac:**
- Download: **FreeMind-1.0.1.dmg**
- Direct link: https://sourceforge.net/projects/freemind/files/freemind/1.0.1/FreeMind-1.0.1.dmg/download

**For Linux:**
- Download: **FreeMind-bin-max-1.0.1.tar.gz**
- Or use package manager: `sudo apt-get install freemind` (Ubuntu/Debian)

### Step 3: Install

**Windows:**
1. Run the `.exe` installer
2. Follow the installation wizard
3. FreeMind will be installed (usually in `C:\Program Files\FreeMind\`)

**Mac:**
1. Open the `.dmg` file
2. Drag FreeMind to Applications folder
3. Launch from Applications

**Linux:**
1. Extract the `.tar.gz` file
2. Run `./freemind.sh` from the extracted folder
3. Or use package manager installation

---

## ⚙️ System Requirements

- **Java Runtime Environment (JRE) 1.5 or higher**
  - Most systems already have Java installed
  - If not, download from: https://www.java.com/download/

**Check if Java is installed:**
```bash
java -version
```

If Java is not installed, download it first before installing FreeMind.

---

## 🚀 Quick Start with Your Codebase

### Method 1: Manual Creation (Recommended for Learning)

1. **Open FreeMind**
   - Launch FreeMind application
   - You'll see a blank mind map with "New Mindmap" node

2. **Create Root Node**
   - Click on "New Mindmap"
   - Press `F2` to edit, or double-click
   - Type: **"S3V Group Website"**

3. **Add Main Branches**
   - Select root node
   - Press `Insert` key to add child node
   - Or right-click → "New Child"
   - Add these main branches:
     - Frontend Pages
     - Core System
     - Admin Panel
     - Application Core
     - API Endpoints
     - Utilities
     - Database
     - Documentation

4. **Add Sub-branches**
   - Select a main branch (e.g., "Frontend Pages")
   - Press `Insert` to add child
   - Add files: index.php, products.php, etc.

5. **Continue Building**
   - Use `CODEBASE-STRUCTURE.md` as reference
   - Keep adding branches and nodes

### Method 2: Import from Text (Faster)

1. **Prepare Text File**
   - Open `CODEBASE-STRUCTURE.md`
   - Convert to simple indented text format

2. **Create Text File for Import**
   - Create `freemind-import.txt`
   - Use this format:
   ```
   S3V Group Website
   	Frontend Pages
   		index.php
   		products.php
   		product.php
   	Core System
   		ae-load.php
   		ae-config.php
   ```
   (Use TAB for indentation)

3. **Import in FreeMind**
   - File → Import → Plain Text
   - Select your text file
   - FreeMind will create the structure

---

## 🎨 Customization Tips

### Change Node Colors
1. Select a node
2. Right-click → "Format" → "Node Color"
3. Choose color

### Add Icons
1. Select a node
2. Right-click → "Icon" → Choose icon
3. Use different icons for:
   - 📁 Folders
   - 📄 Files
   - 🔧 Utilities
   - 📚 Documentation

### Organize Layout
- **View → Layout → Free**
- **View → Layout → Top to Bottom**
- **View → Layout → Left to Right**

### Expand/Collapse
- Click `+` or `-` to expand/collapse branches
- Press `Space` to expand/collapse selected node

---

## ⌨️ Useful Keyboard Shortcuts

| Key | Action |
|-----|--------|
| `Insert` | Add child node |
| `Enter` | Add sibling node |
| `F2` | Edit node text |
| `Delete` | Delete node |
| `Space` | Expand/collapse node |
| `Ctrl + C` | Copy node |
| `Ctrl + V` | Paste node |
| `Ctrl + Z` | Undo |
| `Ctrl + S` | Save |

---

## 💾 Save Your Mind Map

1. **File → Save As**
2. Choose location
3. File will be saved as `.mm` (FreeMind format)
4. You can also export to:
   - **HTML** (File → Export → As HTML)
   - **PDF** (File → Export → As PDF)
   - **Image** (File → Export → As Image)

---

## 📋 Step-by-Step: Create Your Codebase Mind Map

### Step 1: Start FreeMind
- Launch the application
- You'll see a blank map

### Step 2: Create Root
- Edit "New Mindmap" → "S3V Group Website"
- Press `F2` to edit

### Step 3: Add Main Categories
Select root node, press `Insert` for each:
1. Frontend Pages
2. Core System (ae-includes/)
3. Admin Panel (ae-admin/)
4. Application Core (app/)
5. API Endpoints (api/)
6. Utilities (bin/)
7. Database (database/)
8. Documentation (docs/)

### Step 4: Add Sub-categories
For each main category:
- Select it
- Press `Insert` to add children
- Add subdirectories and files

### Step 5: Color Code
- Right-click nodes → Format → Node Color
- Use consistent colors for similar items

### Step 6: Save
- File → Save As
- Name it: `s3vgroup-codebase.mm`

---

## 🔧 Troubleshooting

### Java Not Found
**Problem:** "Java not found" error
**Solution:** 
1. Install Java from https://www.java.com/download/
2. Restart FreeMind

### Import Not Working
**Problem:** Text import doesn't work
**Solution:**
- Make sure text uses TAB (not spaces) for indentation
- Or manually create the structure

### Slow Performance
**Problem:** FreeMind is slow with large maps
**Solution:**
- Collapse branches you're not working on
- Use "Folding" feature
- Consider splitting into multiple maps

---

## 📚 Additional Resources

- **Official Documentation:** http://freemind.sourceforge.net/wiki/
- **User Guide:** http://freemind.sourceforge.net/wiki/index.php/User_Guide
- **FAQ:** http://freemind.sourceforge.net/wiki/index.php/FAQ

---

## 🎯 Quick Reference

**Download:** https://sourceforge.net/projects/freemind/
**Latest Version:** 1.0.1 (stable)
**License:** GPL (Free and Open Source)
**Platform:** Windows, Mac, Linux

---

## ✅ Checklist

- [ ] Download FreeMind installer
- [ ] Install FreeMind
- [ ] Verify Java is installed
- [ ] Launch FreeMind
- [ ] Create root node "S3V Group Website"
- [ ] Add main branches
- [ ] Add sub-branches and files
- [ ] Color code different sections
- [ ] Save your mind map
- [ ] Export to PDF/image for documentation

---

**Ready to start?** Download FreeMind and follow the steps above. Use `CODEBASE-STRUCTURE.md` as your reference guide!

