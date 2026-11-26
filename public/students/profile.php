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
      --forest-green: #1F3B2C; --olive-green: #556B2F; --burnt-orange: #CC5500;
      --clay: #C2643F; --warm-sand: #ECE2CF; --cream: #F4EEDF;
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
    .form-group input, .form-group select { width: 100%; padding: 12px; background: rgba(0,0,0,0.2); border: 1px solid rgba(244, 238, 223, 0.2); border-radius: 8px; color: var(--cream); font-family: 'Inter'; box-sizing: border-box; }
    .form-group input:focus, .form-group select:focus { outline: none; border-color: var(--burnt-orange); }
    .form-group input:disabled { opacity: 0.5; cursor: not-allowed; }
    
    .btn { background: var(--burnt-orange); color: var(--cream); padding: 12px 24px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; width: 100%; margin-top: 10px; }
    .btn:hover { background: #e66a15; }
    .btn:disabled { opacity: 0.7; cursor: not-allowed; }
  </style>
</head>
<body>
  <h1 class="page-title">My Profile</h1>

  <div class="profile-container" id="profileContainer">
    <div class="card avatar-section">
      <img src="https://via.placeholder.com/150" id="avatarImg" class="avatar-preview" alt="Avatar">
      <h3 id="fullName" style="margin: 0 0 5px;">Loading...</h3>
      <p style="color: var(--warm-sand); font-size: 14px; margin-bottom: 15px;" id="usernameDisplay">@loading</p>
      <div class="user-badge" id="accountStatus">Active</div>
      <p style="color: var(--warm-sand); font-size: 12px; margin-top: 15px;">Member Since: <span id="joinDate">-</span></p>
    </div>

    <div class="card">
      <h3 style="margin-top: 0; font-family: 'Fraunces';">Update Details</h3>
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
    </div>
  </div>

  <script type="module">
    import { api } from '../assets/js/core/api.js';
    import { toast } from '../assets/js/core/toast.js';
    import '../assets/js/core/layout.js';

    const form = document.getElementById('profileForm');

    async function loadProfile() {
      try {
        const user = await api('users', 'getCurrentUser');
        
        document.getElementById('fullName').textContent = `${user.first_name} ${user.last_name}`;
        document.getElementById('usernameDisplay').textContent = `@${user.username}`;
        document.getElementById('accountStatus').textContent = user.account_status.toUpperCase();
        document.getElementById('joinDate').textContent = new Date(user.created_at).toLocaleDateString();
        
        if (user.avatar_url) document.getElementById('avatarImg').src = user.avatar_url;

        form.elements.first_name.value = user.first_name;
        form.elements.last_name.value = user.last_name;
        form.elements.username.value = user.username;
        form.elements.email.value = user.email;
        form.elements.phone.value = user.phone || '';
        form.elements.gender.value = user.gender || 'male';

      } catch (err) {
        toast.error('Failed to load profile.');
      }
    }

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = form.querySelector('button');
      const originalBtnText = btn.textContent;
      btn.disabled = true; 
      btn.textContent = 'Saving...';

      // 1. Handle Image Conversion to Base64
      let avatarBase64 = null;
      const fileInput = form.elements.avatar;
      if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        
        // Optional: Prevent uploading massive files (e.g., > 5MB)
        if (file.size > 5 * 1024 * 1024) {
            toast.error("Image is too large. Please choose a file under 5MB.");
            btn.disabled = false; btn.textContent = originalBtnText;
            return;
        }

        avatarBase64 = await new Promise((resolve) => {
          const reader = new FileReader();
          reader.onload = (e) => resolve(e.target.result);
          reader.readAsDataURL(file);
        });
      }

      // 2. Build the payload
      const body = {
        first_name: form.elements.first_name.value.trim(),
        last_name: form.elements.last_name.value.trim(),
        phone: form.elements.phone.value.trim(),
        gender: form.elements.gender.value
      };
      if (avatarBase64) body.avatar_base64 = avatarBase64;

      try {
        // 3. Send to API
        const updatedUser = await api('users', 'update_profile', { method: 'POST', body });
        
        // 4. Instantly update the UI without reloading the page
        document.getElementById('fullName').textContent = `${updatedUser.first_name} ${updatedUser.last_name}`;
        if (updatedUser.avatar_url) {
            document.getElementById('avatarImg').src = updatedUser.avatar_url;
        }

        toast.success('Profile updated successfully!');
        form.elements.avatar.value = ''; // Clear the file input
        
      } catch (err) {
        toast.error(err.message || 'Failed to update profile.');
      } finally {
        btn.disabled = false; 
        btn.textContent = originalBtnText;
      }
    });

    loadProfile();
  </script>
</body>
</html>