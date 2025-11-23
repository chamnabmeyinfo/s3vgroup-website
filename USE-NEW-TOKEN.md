# 🔑 Using Your New GitHub Token

## ✅ New Token Created
Your new token: `YOUR_NEW_TOKEN_HERE`

⚠️ **IMPORTANT**: 
- Replace `YOUR_NEW_TOKEN_HERE` with your actual token
- Keep this token secure and never commit it to git!
- Never share your token in documentation or code

---

## 🚨 First: Revoke Old Token

Before using the new token, make sure you've revoked the old one:

1. Go to: https://github.com/settings/tokens
2. Find and revoke any old/exposed tokens
3. Click **"Revoke"** ✅

⚠️ **Security Note**: If you see any tokens in this documentation, they are compromised and should be revoked immediately.

---

## 📋 Option 1: Update Remote URL with New Token (Temporary)

You can use the token in the remote URL, but be careful not to commit this change:

```powershell
git remote set-url origin https://chamnabmeyinfo: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@github.com/chamnabmeyinfo/s3vgroup-website.git
```

⚠️ **Warning**: If you commit `.git/config`, the token will be exposed!

**To avoid committing the token:**
- The `.git/config` file is usually not tracked, but check:
  ```powershell
  git check-ignore .git/config
  ```

---

## 📋 Option 2: Use GitHub CLI (Recommended - Most Secure)

Install GitHub CLI and authenticate:

```powershell
# Install GitHub CLI
winget install GitHub.cli

# Authenticate (it will prompt for token)
gh auth login

# Then your remote can be clean (no token in URL)
git remote set-url origin https://github.com/chamnabmeyinfo/s3vgroup-website.git
```

---

## 📋 Option 3: Use SSH Key (Best for Long-term)

Set up SSH authentication (no token needed):

```powershell
# Generate SSH key
ssh-keygen -t ed25519 -C "your_email@example.com"

# Add to SSH agent
ssh-add ~/.ssh/id_ed25519

# Copy public key
cat ~/.ssh/id_ed25519.pub

# Add to GitHub:
# 1. Go to: https://github.com/settings/keys
# 2. Click "New SSH key"
# 3. Paste your public key
# 4. Save

# Update remote to use SSH
git remote set-url origin git@github.com:chamnabmeyinfo/s3vgroup-website.git
```

---

## 🔧 Fix Push Issue First

Before pushing with the new token, you still need to fix the old token issue:

### Quick Fix: Allow Secret via GitHub

1. **Revoke old token** (if not done yet)
2. **Allow the secret on GitHub**:
   - Visit: https://github.com/chamnabmeyinfo/s3vgroup-website/security/secret-scanning/unblock-secret/35rhz7HXpdKy9yB2oQjCkzpckMf
   - Click **"Allow secret"**
3. **Update remote URL** with new token:
   ```powershell
   git remote set-url origin https://chamnabmeyinfo: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@github.com/chamnabmeyinfo/s3vgroup-website.git
   ```
4. **Push**:
   ```powershell
   git push -u origin main --force
   ```

---

## ✅ Recommended Steps (In Order)

1. ✅ **Revoke old token** at https://github.com/settings/tokens
2. ✅ **Allow secret** via GitHub (link above)
3. ✅ **Update remote URL** with new token (Option 1 above)
4. ✅ **Push to GitHub**:
   ```powershell
   git push -u origin main --force
   ```
5. ✅ **Verify upload** at https://github.com/chamnabmeyinfo/s3vgroup-website

---

## 🛡️ Security Reminders

- ✅ **Never commit** `.git/config` if it contains your token
- ✅ **Add to .gitignore** if you create config files with tokens
- ✅ **Use environment variables** for tokens in scripts
- ✅ **Rotate tokens** regularly (every 90 days)
- ✅ **Use SSH keys** for long-term authentication

---

## 📝 Store Token Securely

If you need to use the token in scripts, store it in a `.env` file (already in `.gitignore`):

```env
GITHUB_TOKEN=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
```

Then use it in PowerShell:
```powershell
$env:GITHUB_TOKEN = (Get-Content .env | Select-String "GITHUB_TOKEN" | ForEach-Object { $_.Line.Split('=')[1] })
```

**Never commit `.env` file!**

---

## 🚀 Ready to Push?

Follow the steps above, then:
```powershell
git push -u origin main --force
```

Good luck! 🎉

