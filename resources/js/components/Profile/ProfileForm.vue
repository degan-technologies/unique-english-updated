<template>
    <div class="account-settings">
      <aside class="sidebar">
        <div class="logo">FinPay</div>
        <nav>
          <ul>
            <li>Dashboard</li>
            <li>Fund Wallet</li>
            <li>Cards and Receipts</li>
            <li>Transactions</li>
            <li>Notifications</li>
            <li class="active">Settings</li>
            <li>Log Out</li>
          </ul>
        </nav>
      </aside>
      <main class="settings-content">
        <header class="settings-header">
          <h1>Account Settings</h1>
          <div class="user-info">
            <span>Michael Nelson</span>
            <img src="profile-pic-url" alt="Profile Picture" />
          </div>
        </header>
        <section class="profile-settings">
          <nav class="settings-nav">
            <ul>
              <li class="active">Profile Settings</li>
              <li>Password</li>
              <li>Notifications</li>
              <li>Verification</li>
            </ul>
          </nav>
          <form class="settings-form">
            <div class="profile-picture">
              <img src="current-avatar-url" alt="Current Avatar" />
              <div class="picture-buttons">
                <button class="upload-btn">Upload New</button>
                <button class="delete-btn">Delete Avatar</button>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label for="first-name">First Name *</label>
                <input type="text" id="first-name" placeholder="First Name" />
              </div>
              <div class="form-group">
                <label for="last-name">Last Name *</label>
                <input type="text" id="last-name" placeholder="Last Name" />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" placeholder="example@gmail.com" />
              </div>
              <div class="form-group">
                <label for="mobile-number">Mobile Number *</label>
                <input type="text" id="mobile-number" placeholder="0806 123 7890" />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Gender</label>
                <div class="gender-options">
                  <input type="radio" id="male" name="gender" value="male" />
                  <label for="male">Male</label>
                  <input type="radio" id="female" name="gender" value="female" />
                  <label for="female">Female</label>
                </div>
              </div>
              <div class="form-group">
                <label for="id">ID</label>
                <input type="text" id="id" placeholder="1559 000 7788 8DER" disabled />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label for="tax-id-number">Tax Identification Number</label>
                <input type="text" id="tax-id-number" placeholder="12345678" />
              </div>
              <div class="form-group">
                <label for="tax-id-country">Tax Identification Country</label>
                <input type="text" id="tax-id-country" placeholder="Nigeria" />
              </div>
            </div>
            <div class="form-group full-width">
              <label for="residential-address">Residential Address</label>
              <input type="text" id="residential-address" placeholder="Ib Street Oorogun, Ibadan" />
            </div>
            <button type="submit" class="save-btn">Save Changes</button>
          </form>
        </section>
      </main>
    </div>
  </template>
  
  <script>
  export default {
    name: "AccountSettings",
  };
  </script>
  
  <style scoped>
  .account-settings {
    display: flex;
    font-family: Arial, sans-serif;
    background-color: #f4f5f7;
  }
  
  .sidebar {
    width: 250px;
    background-color: #2a3f54;
    color: white;
    padding: 20px;
    min-height: 100vh;
  }
  
  .sidebar .logo {
    font-size: 24px;
    font-weight: bold;
    text-transform: uppercase;
    margin-bottom: 30px;
    color: #ffffff;
  }
  
  .sidebar nav ul {
    list-style: none;
    padding: 0;
  }
  
  .sidebar nav ul li {
    padding: 10px 15px;
    color: #dcdcdc;
    cursor: pointer;
    transition: all 0.3s ease;
  }
  
  .sidebar nav ul li.active,
  .sidebar nav ul li:hover {
    background-color: #1a2530;
    color: white;
  }
  
  .settings-content {
    flex-grow: 1;
    padding: 20px;
    background-color: #ffffff;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  }
  
  .settings-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e0e0e0;
    padding-bottom: 15px;
  }
  
  .settings-header .user-info {
    display: flex;
    align-items: center;
    gap: 10px;
  }
  
  .profile-settings {
    margin-top: 20px;
  }
  
  .settings-nav ul {
    display: flex;
    list-style: none;
    padding: 0;
    border-bottom: 1px solid #e0e0e0;
  }
  
  .settings-nav ul li {
    margin-right: 20px;
    padding-bottom: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
  }
  
  .settings-nav ul li.active {
    border-bottom: 2px solid #007bff;
    color: #007bff;
  }
  
  .settings-form {
    margin-top: 20px;
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
  }
  
  .form-group {
    flex: 1;
  }
  
  .form-group.full-width {
    flex: 100%;
  }
  
  .form-group label {
    font-size: 14px;
    margin-bottom: 5px;
    display: block;
  }
  
  .form-group input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
  }
  
  .gender-options {
    display: flex;
    gap: 10px;
    align-items: center;
  }
  
  .profile-picture {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
  }
  
  .profile-picture img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
  }
  
  .picture-buttons button {
    padding: 8px 15px;
    border: none;
    background-color: #f0f0f0;
    color: #333;
    border-radius: 4px;
    cursor: pointer;
  }
  
  .save-btn {
    padding: 10px 20px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s ease;
  }
  
  .save-btn:hover {
    background-color: #0056b3;
  }
  </style>
  