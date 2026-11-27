<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>My Profile — Lumos</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/toast.css">
  <style>
    :root {
      --forest-green: #1F3B2C; 
      --olive-green: #556B2F; 
      --burnt-orange: #CC5500;
      --clay: #C2643F; 
      --warm-sand: #ECE2CF; 
      --cream: #F4EEDF;
    }
    body { background: var(--forest-green); color: var(--cream); font-family: 'Inter', sans-serif; margin: 0; padding: 0; }
    .page-title { font-family: 'Fraunces'; font-size: 32px; margin-bottom: 20px; }
    
    .profile-container { display: grid; grid-template-columns: 1fr 2fr; gap: 30px; }
    .card { background: rgba(244, 238, 223, 0.05); border: 1px solid var(--olive-green); border-radius: 12px; padding: 30px; }
    
    /* Left Side - Avatar */
    .avatar-section { text-align: center; }
    .avatar-preview { width: 150px; height: 150px; border-radius: 50%; background: var(--clay); margin: 0 auto 20px; object-fit: cover; border: 3px solid var(--burnt-orange); }
    .user-badge { display: inline-block; background: rgba(204, 85, 0, 0.2); color: var(--burnt-orange); padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; margin-bottom: 10px; }
    
    /* Right Side - Form */
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
    .form-group label { display: block; font-size: 13px; color: var(--warm-sand); margin-bottom: 8px; }
    .form-group input, .form-group select { width: 100%; padding: 12px; background: rgba(0,0,0,0.3); border: 1px solid rgba(244, 238, 223, 0.2); border-radius: 8px; color: var(--cream); font-family: 'Inter'; box-sizing: border-box; font-size: 14px; }
    .form-group input:focus, .form-group select:focus { outline: none; border-color: var(--burnt-orange); }
    .form-group input:disabled { opacity: 0.5; cursor: not-allowed; }
    
    .btn { background: var(--burnt-orange); color: var(--cream); padding: 12px 24px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; width: 100%; margin-top: 10px; }
    .btn:hover { background: #e66a15; }
    .btn:disabled { opacity: 0.7; cursor: not-allowed; }

    .section-title { font-family: 'Fraunces'; font-size: 18px; color: var(--burnt-orange); margin-bottom: 15px; border-bottom: 1px solid rgba(204, 85, 0, 0.3); padding-bottom: 10px; }
    
    .bank-verified { display: none; background: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #10b981; padding: 12px; border-radius: 8px; font-weight: 600; font-size: 14px; margin-top: 15px; text-align: center; }

    /* --- CUSTOM SEARCHABLE DROPDOWN STYLES --- */
    .custom-select-wrapper { position: relative; }
    .custom-select-input { cursor: text; }
    .custom-select-dropdown {
      position: absolute; top: 100%; left: 0; right: 0;
      background: #122117; /* Very dark green */
      border: 1px solid var(--olive-green);
      border-top: none;
      border-radius: 0 0 8px 8px;
      max-height: 200px;
      overflow-y: auto;
      z-index: 100;
      display: none;
      box-shadow: 0 10px 20px rgba(0,0,0,0.5);
    }
    .custom-select-dropdown.active { display: block; }
    .custom-select-option {
      padding: 12px 15px;
      cursor: pointer;
      font-size: 14px;
      color: var(--cream);
      border-bottom: 1px solid rgba(85, 107, 47, 0.3);
      transition: background 0.2s, color 0.2s;
    }
    .custom-select-option:last-child { border-bottom: none; }
    .custom-select-option:hover {
      background: var(--burnt-orange);
      color: #fff;
      font-weight: 600;
    }
    .custom-select-option.hidden { display: none; }
  </style>
</head>
<body>
  <h1 class="page-title">My Profile</h1>

  <div class="profile-container">
    <div class="card avatar-section">
      <img src="https://via.placeholder.com/150" id="avatarImg" class="avatar-preview" alt="Avatar">
      <h3 id="fullName" style="margin: 0 0 5px;">Loading...</h3>
      <p style="color: var(--warm-sand); font-size: 14px; margin-bottom: 15px;" id="usernameDisplay">@loading</p>
      <div class="user-badge" id="accountStatus">Active</div>
      <p style="color: var(--warm-sand); font-size: 12px; margin-top: 15px;">Member Since: <span id="joinDate">-</span></p>
    </div>

    <div class="card">
      <!-- SECTION 1: General Details -->
      <h3 class="section-title">General Details</h3>
      <form id="profileForm">
        <div class="form-row">
          <div class="form-group"><label>First Name</label><input type="text" name="first_name" required></div>
          <div class="form-group"><label>Last Name</label><input type="text" name="last_name" required></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Username</label><input type="text" name="username" disabled></div>
          <div class="form-group"><label>Email</label><input type="email" name="email" disabled></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Phone</label><input type="tel" name="phone"></div>
          <div class="form-group">
            <label>Gender</label>
            <select name="gender">
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="other">Other</option>
            </select>
          </div>
        </div>
        <div class="form-group" style="margin-bottom: 20px;">
          <label>Profile Picture (Optional)</label>
          <input type="file" name="avatar" accept="image/*" style="padding: 10px;">
        </div>
        <button type="submit" class="btn">Save Changes</button>
      </form>

      <!-- SECTION 2: Payout Details -->
      <h3 class="section-title" style="margin-top: 40px;">Payout Bank Details</h3>
      <p style="color: var(--warm-sand); font-size: 13px; margin-bottom: 20px;">This is where your prize money will be sent if you win.</p>
      <form id="bankForm">
        <div class="form-row">
          <!-- CUSTOM SEARCHABLE DROPDOWN -->
          <div class="form-group">
            <label>Bank Name</label>
            <div class="custom-select-wrapper">
              <input type="text" id="bankSearchInput" class="custom-select-input" placeholder="Type to search bank..." autocomplete="off" required>
              <input type="hidden" name="bank_code" id="bankCodeInput" required>
              <input type="hidden" name="bank_name" id="bankNameInput" required>
              
              <div class="custom-select-dropdown" id="bankDropdown">
                <!-- Options injected via JS -->
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label>Account Number</label>
            <input type="text" name="account_number" maxlength="10" minlength="10" placeholder="10-digit account number" required>
          </div>
        </div>
        <button type="submit" class="btn" id="bankBtn">Save Bank Details</button>
        <div class="bank-verified" id="bankVerified">
          ✅ Verified Account: <span id="verifiedName"></span>
        </div>
      </form>

    </div>
  </div>

  <script type="module">
    import { api } from '../assets/js/core/api.js';
    import { toast } from '../assets/js/core/toast.js';
    import '../assets/js/core/layout.js';

    const profileForm = document.getElementById('profileForm');
    const bankForm = document.getElementById('bankForm');
    
    // Custom Dropdown Elements
    const bankSearchInput = document.getElementById('bankSearchInput');
    const bankDropdown = document.getElementById('bankDropdown');
    const bankCodeInput = document.getElementById('bankCodeInput');
    const bankNameInput = document.getElementById('bankNameInput');
    
    let allBanks = [];

    async function loadProfile() {
      try {
        const user = await api('users', 'getCurrentUser');
        
        document.getElementById('fullName').textContent = `${user.first_name} ${user.last_name}`;
        document.getElementById('usernameDisplay').textContent = `@${user.username}`;
        document.getElementById('accountStatus').textContent = user.account_status.toUpperCase();
        document.getElementById('joinDate').textContent = new Date(user.created_at).toLocaleDateString();
        
        if (user.avatar_url) document.getElementById('avatarImg').src = user.avatar_url;

        profileForm.elements.first_name.value = user.first_name;
        profileForm.elements.last_name.value = user.last_name;
        profileForm.elements.username.value = user.username;
        profileForm.elements.email.value = user.email;
        profileForm.elements.phone.value = user.phone || '';
        profileForm.elements.gender.value = user.gender || 'male';

        // Populate Bank Details if they exist
        if (user.account_number) {
            bankForm.elements.account_number.value = user.account_number;
            document.getElementById('bankVerified').style.display = 'block';
            document.getElementById('verifiedName').textContent = user.account_name;
            
            // Set the custom input text to show their saved bank
            if (user.bank_name) {
                bankSearchInput.value = user.bank_name;
                bankNameInput.value = user.bank_name;
                bankCodeInput.value = user.bank_code;
            }
        }

      } catch (err) {
        toast.error('Failed to load profile.');
      }
    }

    async function loadBanks() {
        try {
            allBanks = await api('users', 'getBanks');
            
            // Render options initially
            renderBankOptions(allBanks);

            // Search Logic
            bankSearchInput.addEventListener('input', (e) => {
                const term = e.target.value.toLowerCase();
                const filtered = allBanks.filter(b => b.name.toLowerCase().includes(term));
                renderBankOptions(filtered);
                
                // Clear hidden fields if they type again
                bankCodeInput.value = '';
                bankNameInput.value = '';
            });

            // Open dropdown on focus
            bankSearchInput.addEventListener('focus', () => {
                bankDropdown.classList.add('active');
                // Re-render to show all if input is empty, or filtered if not
                if(bankSearchInput.value === '') renderBankOptions(allBanks);
            });

        } catch (err) {
            toast.error('Failed to load bank list.');
        }
    }

    function renderBankOptions(banks) {
        if (banks.length === 0) {
            bankDropdown.innerHTML = `<div class="custom-select-option" style="color: var(--warm-sand);">No banks found</div>`;
            return;
        }

        bankDropdown.innerHTML = banks.map(b => 
            `<div class="custom-select-option" data-code="${b.code}" data-name="${b.name}">${b.name}</div>`
        ).join('');

        // Add click listeners to options
        bankDropdown.querySelectorAll('.custom-select-option').forEach(opt => {
            opt.addEventListener('click', () => {
                const code = opt.getAttribute('data-code');
                const name = opt.getAttribute('data-name');
                
                // If it's the "No banks found" div, do nothing
                if(!code) return;

                bankSearchInput.value = name;
                bankCodeInput.value = code;
                bankNameInput.value = name;
                
                bankDropdown.classList.remove('active');
            });
        });
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!bankSearchInput.contains(e.target) && !bankDropdown.contains(e.target)) {
            bankDropdown.classList.remove('active');
        }
    });

    // --- SUBMIT GENERAL PROFILE ---
    profileForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = profileForm.querySelector('button');
      const originalText = btn.textContent;
      btn.disabled = true; btn.textContent = 'Saving...';

      let avatarBase64 = null;
      const fileInput = profileForm.elements.avatar;
      if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        if (file.size > 5 * 1024 * 1024) {
            toast.error("Image is too large. Max 5MB.");
            btn.disabled = false; btn.textContent = originalText; return;
        }
        avatarBase64 = await new Promise((resolve) => {
          const reader = new FileReader();
          reader.onload = (e) => resolve(e.target.result);
          reader.readAsDataURL(file);
        });
      }

      const body = {
        first_name: profileForm.elements.first_name.value.trim(),
        last_name: profileForm.elements.last_name.value.trim(),
        phone: profileForm.elements.phone.value.trim(),
        gender: profileForm.elements.gender.value
      };
      if (avatarBase64) body.avatar_base64 = avatarBase64;

      try {
        const updatedUser = await api('users', 'update_profile', { method: 'POST', body });
        document.getElementById('fullName').textContent = `${updatedUser.first_name} ${updatedUser.last_name}`;
        if (updatedUser.avatar_url) document.getElementById('avatarImg').src = updatedUser.avatar_url;
        toast.success('Profile updated successfully!');
        profileForm.elements.avatar.value = ''; 
      } catch (err) {
        toast.error(err.message || 'Failed to update profile.');
      } finally {
        btn.disabled = false; btn.textContent = originalText;
      }
    });

    // --- SUBMIT BANK DETAILS ---
    bankForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = document.getElementById('bankBtn');
      const originalText = btn.textContent;
      
      const bankCode = bankCodeInput.value;
      const bankName = bankNameInput.value;
      const accountNumber = bankForm.elements.account_number.value;

      if (!bankCode || !bankName) {
          return toast.error('Please select a bank from the dropdown list.');
      }
      if (accountNumber.length !== 10) {
          return toast.error('Account number must be exactly 10 digits.');
      }

      btn.disabled = true; btn.textContent = 'Verifying...';

      try {
        const res = await api('users', 'update_bank_details', { 
            method: 'POST', 
            body: { bank_code: bankCode, bank_name: bankName, account_number: accountNumber } 
        });
        
        toast.success('Bank details verified and saved!');
        document.getElementById('bankVerified').style.display = 'block';
        document.getElementById('verifiedName').textContent = res.account_name;
        
      } catch (err) {
        toast.error(err.message || 'Failed to verify bank details.');
        document.getElementById('bankVerified').style.display = 'none';
      } finally {
        btn.disabled = false; btn.textContent = originalText;
      }
    });

    loadProfile();
    loadBanks();
  </script>
</body>
</html>